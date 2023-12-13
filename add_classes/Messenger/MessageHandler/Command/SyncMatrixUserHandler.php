<?php

declare(strict_types=1);

namespace App\Messenger\MessageHandler\Command;

use App\Bridge\Matrix\MatrixConnector;
use App\Common\Contracts\Group\GroupType;
use App\Common\Contracts\User\UserStatus;
use App\Common\Database\Model;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Common\Exceptions\ContextAwareException;
use App\Messenger\Message\Command\SyncMatrixUser;
use Country_Model as CountryRepository;
use DateTime;
use DateTimeImmutable;
use ExportPortal\Matrix\Client\Api\RoomParticipationApi;
use ExportPortal\Matrix\Client\Api\UserDataApi;
use ExportPortal\Matrix\Client\ApiException;
use ExportPortal\Matrix\Client\Client as MatrixClient;
use ExportPortal\Matrix\Client\Model\AvatarUrl;
use ExportPortal\Matrix\Client\Model\DisplayName;
use ExportPortal\Matrix\Client\Model\LoginResponse as AuthenticatedUser;
use GuzzleHttp\Exception\RequestException;
use libphonenumber\PhoneNumberUtil;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\Handler\MessageSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\MimeTypes;

/**
 * Synchronises the user's account information on matrix server.
 *
 * @author Anton Zencenco
 */
final class SyncMatrixUserHandler implements MessageSubscriberInterface
{
    use LoggerAwareTrait;

    /**
     * The message bus that will dispatch additional messages.
     */
    private MessageBusInterface $commandBus;

    /**
     * The matrix connector.
     */
    private MatrixConnector $matrixConnector;

    /**
     * The country repository.
     */
    private CountryRepository $countryRepository;

    /**
     * The users repository.
     */
    private Model $usersRepository;

    /**
     * The buyer company repository.
     */
    private Model $buyerCompanyRepository;

    /**
     * The seller company repository.
     */
    private Model $sellerCompanyRepository;

    /**
     * The shipper country repository.
     */
    private Model $shipperCompanyRepository;

    /**
     * The matrix user reference repository.
     */
    private Model $matrixUsersRepository;

    public function __construct(MessageBusInterface $commandBus, MatrixConnector $matrixConnector, ModelLocator $modelLocator)
    {
        $this->logger = $matrixConnector->getConfig()->getLogger();
        $this->commandBus = $commandBus;
        $this->matrixConnector = $matrixConnector;
        $this->countryRepository = $modelLocator->get(CountryRepository::class);
        $this->usersRepository = $modelLocator->get(\Users_Model::class);
        $this->matrixUsersRepository = $modelLocator->get(\Matrix_Users_Model::class);
        $this->buyerCompanyRepository = $modelLocator->get(\Buyer_Companies_Model::class);
        $this->sellerCompanyRepository = $modelLocator->get(\Seller_Companies_Model::class);
        $this->shipperCompanyRepository = $modelLocator->get(\Shipper_Companies_Model::class);
    }

    public function __invoke(SyncMatrixUser $message)
    {
        // Retrieve the sync reference.
        if (null === $userReference = $this->matrixConnector->getUserReferenceProvider()->getReferenceByUserId($userId = $message->getUserId())) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->alert(sprintf('The reference for user ID "%d" is not present in the sync table.', $userId), [
                    'userId'  => $userId,
                    'message' => $message,
                ]);
            }

            return;
        }
        if (null === ($userReference['user'] ?? null)) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->alert(sprintf('The sync reference for user with ID "%d" is not affiliated with the user account.', $userId), [
                    'userId'  => $userId,
                    'message' => $message,
                ]);
            }

            return;
        }
        if (null === $userReference['profile_room_id']) {
            // Silently fail.
            if ($this->logger) {
                $this->logger->warning(sprintf('The profile room is required to snc the user account.', $userId), [
                    'userId'  => $userId,
                    'roomId'  => $userReference['profile_room_id'],
                    'message' => $message,
                ]);
            }

            return;
        }

        try {
            $this->updateProfile(
                $this->matrixConnector->getMatrixClient(),
                $loggedUser = $this->matrixConnector->loginAsMatrixUser($this->matrixConnector->getServiceUserAccount(), $userReference['mxid']),
                $userReference['profile_room_id'],
                $userReference['user'],
                $this->matrixConnector->getConfig()->getEventNamespace()
            );
        } catch (ContextAwareException | RequestException $e) {
            $this->matrixConnector->getExceptionHandler()->handleException($e, 'Failed to send request to the matrix server.');
            if ($e instanceof RequestException) {
                // Roll exception forward - maybe we can recover in the next try.
                throw $e;
            }

            return;
        } finally {
            // After that we need to logout current users
            try {
                $this->matrixConnector->logoutUser($loggedUser);
            } catch (\Throwable $e) {
                // Just roll with it - we don't need to bother with logout.
            }
        }

        // Renew sync date.
        $this->matrixUsersRepository->updateOne($userReference['id'], ['last_sync_at' => new DateTimeImmutable()]);
    }

    /**
     * {@inheritDoc}
     */
    public static function getHandledMessages(): iterable
    {
        yield SyncMatrixUser::class => ['bus' => 'command.bus'];
    }

    /**
     * Updates the user's private profile room.
     */
    protected function updateProfile(MatrixClient $matrixClient, AuthenticatedUser $user, string $roomId, array $userData, string $eventNamspace): void
    {
        $phoneNumberUtil = PhoneNumberUtil::getInstance();
        $mimeTypes = new MimeTypes();
        $mimeTypes->setDefault($mimeTypes);
        /** @var RoomParticipationApi $roomApi */
        $roomApi = \tap($matrixClient->getRoomParticipationApi(), function (RoomParticipationApi $api) use ($user) {
            $api->getConfig()->setAccessToken($user->getAccessToken());
        });
        // /** @var UserDataApi $userDataApi */
        // $userDataApi = \tap($matrixClient->getUserDataApi(), function (UserDataApi $api) use ($user) {
        //     $api->getConfig()->setAccessToken($user->getAccessToken());
        // });

        try {
            $profileData = $this->prepareProfileStateContent($phoneNumberUtil, $mimeTypes, $userData);
            $roomApi->setRoomStateWithKey($roomId, "{$eventNamspace}.profile", '', (object) $profileData);
            // $roomApi->setRoomStateWithKey($roomId, "{$eventNamspace}.profile_room", '', (object) ['status' => 'sync']);
            // $userDataApi->setDisplayName((new DisplayName())->setDisplayname($profileData['name']['fullName']), $user->getUserId());
            // if (!empty($photos = $profileData['photo'] ?? [])) {
            //     $userDataApi->setAvatarUrl((new AvatarUrl())->setAvatarUrl(\current($photos)['url']), $user->getUserId());
            // }
        } catch (ApiException $e) {
            throw new ContextAwareException(
                sprintf('Failed to create new user\'s private profile room with name "%s" on this matrix server.', $roomId),
                ['userId' => $user->getUserId(), 'roomId' => $roomId],
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Prepares the user profile content.
     */
    private function prepareProfileStateContent(PhoneNumberUtil $phoneNumberUtil, MimeTypes $mimeTypes, array $user): array
    {
        /** @var UserStatus $status */
        $status = $user['status'];
        $phoneCodes = $this->countryRepository->getExtendedCountryCodes(\array_filter([
            $user['fax_code_id'] ?? null,
            $user['phone_code_id'] ?? null,
            $company['fax_code_id'] ?? null,
            $company['phone_code_id'] ?? null,
        ]));
        $limitationDate = $this->getAccountLimitationDate($status, $user);
        $profile = [
            'status'       => (string) $status,
            'isMuted'      => $user['is_muted'],
            'isVerified'   => $user['is_verified'],
            'limitedAt'    => null !== $limitationDate ? $limitationDate->format(DateTime::RFC2822) : null,
            'url'          => \getUserLink($user['full_name'], $user['id'], (string) $user['group_type']),
            'name'         => [
                'fullName'  => $user['full_name'],
                'firstName' => $user['first_name'],
                'lastName'  => $user['last_name'],
                'legalName' => $user['legal_name'],
            ],
            'group'        => [
                'name'  => $user['group_name'],
                'type'  => $user['group_type'],
                'alias' => $user['group_alias'],
            ],
            'photo'        => \array_filter([$this->transformUserPhoto($mimeTypes, $user['id'], $user['group_id'], $user['user_photo'])]),
            'phone'        => \array_filter([
                $this->transformPhoneProfileContent($phoneNumberUtil, $user['phone'] ?: null, $phoneCodes[$user['phone_code_id']] ?? null, $user['phone_code'] ?: null),
                $this->transformPhoneProfileContent($phoneNumberUtil, $user['fax'] ?: null, $phoneCodes[$user['fax_code_id']] ?? null, $user['fax_code'] ?: null),
            ]),
        ];
        $company = $this->getUserCompany($user['id'], $userType = $user['group_type']);
        if (null !== $company) {
            $profile['organization'] = $this->prepareOrganizationContent($phoneNumberUtil, $mimeTypes, $userType, $company, $phoneCodes);
        }

        return $profile;
    }

    /**
     * Prepares the user organization content.
     */
    private function prepareOrganizationContent(PhoneNumberUtil $phoneNumberUtil, MimeTypes $mimeTypes, GroupType $userType, ?array $company, array $phoneCodes): ?array
    {
        if (null === $company) {
            return null;
        }

        return [
            'legalName' => $company['legal_name'],
            'name'      => $company['name'],
            'url'       => \getCompanyURL([
                'id_company'   => $company['id'],
                'name_company' => $company['name'],
                'type_company' => $company['type'] ?? 'company',
                'index_name'   => $company['index'] ?? '',
            ]),
            'logo'      => \array_filter([$this->transformCompanyLogo($mimeTypes, (int) $company['id'], $company['logo'] ?? null, $userType)]),
            'phone'     => \array_filter([
                $this->transformPhoneProfileContent($phoneNumberUtil, $company['phone'] ?: null, $phoneCodes[$company['phone_code_id']] ?? null, $company['phone_code'] ?: null),
                $this->transformPhoneProfileContent($phoneNumberUtil, $company['fax'] ?: null, $phoneCodes[$company['fax_code_id']] ?? null, $company['fax_code'] ?: null),
            ]),
        ];
    }

    /**
     * Transforms provided data into accepted photo format.
     */
    private function transformUserPhoto(MimeTypes $mimeTypes, int $userId, int $groupId, ?string $photo): array
    {
        $imagePath = getDisplayImagePath(['{ID}' => $userId, '{FILE_NAME}' => $photo ?? null], 'users.main', ['no_image_group' => $groupId]);
        $pathParts = \explode('.', $imagePath);
        $extension = \end($pathParts);

        return [
            'type' => $mimeTypes->getMimeTypes($extension),
            'path' => \substr($imagePath, \strlen(\App\Common\ROOT_PATH)),
            'url'  => getDisplayImageLink(['{ID}' => $userId, '{FILE_NAME}' => $photo ?? null], 'users.main', ['no_image_group' => $groupId]),
        ];
    }

    /**
     * Transforms provided data into accepted photo format.
     */
    private function transformCompanyLogo(MimeTypes $mimeTypes, int $companyId, ?string $photo, GroupType $groupType): array
    {
        switch ($groupType) {
            case GroupType::from(GroupType::BUYER):
                return [];
            case GroupType::from(GroupType::SELLER):
                $module = 'companies.main';

                break;
            case GroupType::from(GroupType::SHIPPER):
                $module = 'shippers.main';

                break;
        }

        $imagePath = getDisplayImagePath(['{ID}' => $companyId, '{FILE_NAME}' => $photo ?? null], $module ?? '');
        $pathParts = \explode('.', $imagePath);
        $extension = \end($pathParts);

        return [
            'type' => $mimeTypes->getMimeTypes($extension),
            'path' => \substr($imagePath, \strlen(\App\Common\ROOT_PATH)),
            'url'  => getDisplayImageLink(['{ID}' => $companyId, '{FILE_NAME}' => $photo ?? null], $module),
        ];
    }

    /**
     * Transforms provided  data into accepted phone format.
     */
    private function transformPhoneProfileContent(PhoneNumberUtil $phoneNumberUtil, ?string $phone, ?array $phoneCodeData, ?string $fallbackPhoneCode, ?array $types = null): ?array
    {
        if (null === $phone || null === ($phoneCode = $phoneCodeData['ccode'] ?? $fallbackPhoneCode)) {
            return null;
        }

        try {
            $phone = $phoneNumberUtil->parse("{$phoneCode} {$phone}", $phoneCodeData['country_iso3166_alpha2'] ?? null);
        } catch (\Throwable $e) {
            return null;
        }

        return [
            'type'        => $types ?? ['work'],
            'code'        => $phoneCode,
            'uri'         => $phoneNumberUtil->format($phone, \libphonenumber\PhoneNumberFormat::RFC3966),
            'phone'       => $phoneNumberUtil->format($phone, \libphonenumber\PhoneNumberFormat::INTERNATIONAL),
            'countryCode' => $phoneCodeData['country_iso3166_alpha2'] ?? null,
        ];
    }

    /**
     * Get user company.
     */
    private function getUserCompany(int $userId, GroupType $groupType): ?array
    {
        switch ($groupType) {
            case GroupType::from(GroupType::BUYER):
                return $this->buyerCompanyRepository->findOneBy([
                    'columns'    => [
                        $this->buyerCompanyRepository->getPrimaryKey(),
                        $this->buyerCompanyRepository->getPrimaryKey() . ' AS `id`',
                        $this->buyerCompanyRepository->qualifyColumn('`company_name`') . ' AS `name`',
                        $this->buyerCompanyRepository->qualifyColumn('`company_legal_name`') . ' AS `legal_name`',
                        $this->buyerCompanyRepository->qualifyColumn('`company_phone_code_id`') . ' AS `phone_code_id`',
                        $this->buyerCompanyRepository->qualifyColumn('`company_phone_code`') . ' AS `phone_code`',
                        $this->buyerCompanyRepository->qualifyColumn('`company_phone`') . ' AS `phone`',
                        $this->buyerCompanyRepository->qualifyColumn('`company_fax_code_id`') . ' AS `fax_code_id`',
                        $this->buyerCompanyRepository->qualifyColumn('`company_fax_code`') . ' AS `fax_code`',
                        $this->buyerCompanyRepository->qualifyColumn('`company_fax`') . ' AS `fax`',
                        $this->buyerCompanyRepository->qualifyColumn('`company_id_country`') . ' AS `id_country`',
                        $this->buyerCompanyRepository->qualifyColumn('`company_id_state`') . ' AS `id_state`',
                        $this->buyerCompanyRepository->qualifyColumn('`company_id_city`') . ' AS `id_city`',
                        $this->buyerCompanyRepository->qualifyColumn('`company_address`') . ' AS `address`',
                        $this->buyerCompanyRepository->qualifyColumn('`company_zip`') . ' AS `zip`',
                    ],
                    'conditions' => [
                        'filter_by' => [
                            'id_user' => $userId,
                        ],
                    ],
                ]);

            case GroupType::from(GroupType::SELLER):
                return $this->sellerCompanyRepository->findOneBy([
                    'columns'    => [
                        $this->sellerCompanyRepository->getPrimaryKey(),
                        $this->sellerCompanyRepository->getPrimaryKey() . ' AS `id`',
                        $this->sellerCompanyRepository->qualifyColumn('`name_company`') . ' AS `name`',
                        $this->sellerCompanyRepository->qualifyColumn('`legal_name_company`') . ' AS `legal_name`',
                        $this->sellerCompanyRepository->qualifyColumn('`id_phone_code_company`') . ' AS `phone_code_id`',
                        $this->sellerCompanyRepository->qualifyColumn('`phone_code_company`') . ' AS `phone_code`',
                        $this->sellerCompanyRepository->qualifyColumn('`phone_company`') . ' AS `phone`',
                        $this->sellerCompanyRepository->qualifyColumn('`id_fax_code_company`') . ' AS `fax_code_id`',
                        $this->sellerCompanyRepository->qualifyColumn('`fax_code_company`') . ' AS `fax_code`',
                        $this->sellerCompanyRepository->qualifyColumn('`fax_company`') . ' AS `fax`',
                        $this->sellerCompanyRepository->qualifyColumn('`index_name`') . ' AS `index`',
                        $this->sellerCompanyRepository->qualifyColumn('`type_company`') . ' AS `type`',
                        $this->sellerCompanyRepository->qualifyColumn('`id_country`'),
                        $this->sellerCompanyRepository->qualifyColumn('`id_state`'),
                        $this->sellerCompanyRepository->qualifyColumn('`id_city`'),
                        $this->sellerCompanyRepository->qualifyColumn('`address_company`') . ' AS `address`',
                        $this->sellerCompanyRepository->qualifyColumn('`zip_company`') . ' AS `zip`',
                        $this->sellerCompanyRepository->qualifyColumn('`longitude`') . ' AS `lng`',
                        $this->sellerCompanyRepository->qualifyColumn('`latitude`') . ' AS `lat`',
                        $this->sellerCompanyRepository->qualifyColumn('`logo_company`') . ' AS `logo`',
                    ],
                    'conditions' => [
                        'filter_by' => [
                            'id_user'        => $userId,
                            'type_company'   => 'company',
                            'parent_company' => 0,
                        ],
                    ],
                ]);

            case GroupType::from(GroupType::SHIPPER):
                return $this->shipperCompanyRepository->findOneBy([
                    'columns'    => [
                        $this->shipperCompanyRepository->getPrimaryKey(),
                        $this->shipperCompanyRepository->getPrimaryKey() . ' AS `id`',
                        $this->shipperCompanyRepository->qualifyColumn('`co_name`') . ' AS `name`',
                        $this->shipperCompanyRepository->qualifyColumn('`legal_co_name`') . ' AS `legal_name`',
                        $this->shipperCompanyRepository->qualifyColumn('`id_phone_code`') . ' AS `phone_code_id`',
                        $this->shipperCompanyRepository->qualifyColumn('`phone_code`'),
                        $this->shipperCompanyRepository->qualifyColumn('`phone`'),
                        $this->shipperCompanyRepository->qualifyColumn('`id_fax_code`') . ' AS `fax_code_id`',
                        $this->shipperCompanyRepository->qualifyColumn('`fax_code`'),
                        $this->shipperCompanyRepository->qualifyColumn('`fax`'),
                        $this->shipperCompanyRepository->qualifyColumn('`id_country`'),
                        $this->shipperCompanyRepository->qualifyColumn('`id_state`'),
                        $this->shipperCompanyRepository->qualifyColumn('`id_city`'),
                        $this->shipperCompanyRepository->qualifyColumn('`address`'),
                        $this->shipperCompanyRepository->qualifyColumn('`zip`'),
                        $this->shipperCompanyRepository->qualifyColumn('`logo`'),
                    ],
                    'conditions' => [
                        'filter_by' => [
                            'id_user' => $userId,
                        ],
                    ],
                ]);
        }

        return null;
    }

    /**
     * Returns the date when user account was restricted last time.
     */
    private function getAccountLimitationDate(UserStatus $status, array $userData): ?DateTimeImmutable
    {
        if (!$status->isLimited() || UserStatus::DELETED() === $status) {
            return null;
        }

        $relation = $this->usersRepository->getRelation('accountLimitationRecords');
        $relation->addEagerConstraints([$userData]);
        $relation->getQuery()->orderBy('blocking_date', 'DESC')->setMaxResults(1);
        /** @var null|array $result */
        $result = $relation->get(['*'])->first() ?: null;

        return $result['blocking_date'] ?? null;
    }
}
