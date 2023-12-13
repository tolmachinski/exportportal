<?php

declare(strict_types=1);

namespace App\Services\Profile;

use App\Common\Contracts\Entities\CountryCodeInterface;
use App\Common\Contracts\User\UserSourceType;
use App\Common\Contracts\User\UserStatus;
use App\Common\Database\Exceptions\WriteException;
use App\Common\Database\Model;
use App\Common\DependencyInjection\ServiceLocator\ModelLocator;
use App\Common\Exceptions\MismatchStatusException;
use App\Common\Exceptions\ProfileCompletionException;
use App\Common\Exceptions\UserNotFoundException;
use App\Event\ProfileUpdateEvent;
use App\Messenger\Message\Event\Lifecycle as LifecycleEvents;
use App\Services\PhoneCodesService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @author Anton Zencenco
 */
final class UserProfileProcessingService
{
    /**
     * The phone codes service.
     */
    private PhoneCodesService $phoneCodesService;

    /**
     * The event dispatcher.
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * The local repository with users.
     */
    private Model $usersRepository;

    /**
     * The event bus instance.
     */
    private MessageBusInterface $eventBus;

    /**
     * Create the service.
     *
     * @param PhoneCodesService   $phoneCodesService the phone codes service
     * @param ModelLocator        $modelLocator      the models locator
     * @param MessageBusInterface $eventBus          the event bus
     */
    public function __construct(
        PhoneCodesService $phoneCodesService,
        EventDispatcherInterface $eventDispatcher,
        ModelLocator $modelLocator,
        MessageBusInterface $eventBus
    ) {
        $this->eventBus = $eventBus;
        $this->eventDispatcher = $eventDispatcher;
        $this->usersRepository = $modelLocator->get(\Users_Model::class);
        $this->phoneCodesService = $phoneCodesService;
    }

    /**
     * Save general profile information.
     *
     * @throws UserNotFoundException if user is not found
     * @throws WriteException        when failed to write changes
     *
     * @return array the updated user profile
     */
    public function saveGeneralProfileInformation(Request $request, array $userProfile): array
    {
        $userId = $userProfile['idu'];

        //region Collect data
        /** @var CountryCodeInterface $phoneCode */
        $phoneCode = $this->phoneCodesService->findAllMatchingCountryCodes($request->request->getInt('phone_code'))->first() ?: null;
        /** @var CountryCodeInterface $faxCode */
        $faxCode = $this->phoneCodesService->findAllMatchingCountryCodes($request->request->getInt('fax_code'))->first() ?: null;
        // Base update block.
        $update = [
            'fname'         => \cleanInput($request->request->get('first_name')),
            'lname'         => \cleanInput($request->request->get('last_name')),
            'legal_name'    => $request->request->has('has_legal_name') ? cleanInput($request->request->get('legal_name')) : '',
            'city'          => $cityId = $request->request->getInt('city') ?: null,
            'state'         => $request->request->getInt('region') ?: null,
            'country'       => $request->request->getInt('country') ?: null,
            'address'       => \cleanInput($request->request->get('address')),
            'phone_code_id' => $phoneCode ? $phoneCode->getId() : null,
            'phone_code'    => \cleanInput($phoneCode ? $phoneCode->getName() : null),
            'phone'         => \cleanInput($request->request->get('phone')),
            'fax_code_id'   => $faxCode ? $faxCode->getId() : null,
            'fax_code'      => \cleanInput($faxCode ? $faxCode->getName() : null),
            'fax'           => \cleanInput($request->request->get('fax')),
            'zip'           => \cleanInput($request->request->get('postal_code')),
        ];
        if ($request->request->has('has_legal_name')) {
            $update['legal_name'] = \cleanInput($request->request->get('legal_name'));
        }

        $citiesRepository = $this->usersRepository->getRelation('city')->getRelated();
        if (null !== ($city = $citiesRepository->findOne($cityId))) {
            $update['user_city_lat'] = $city['city_lat'];
            $update['user_city_lng'] = $city['city_lng'];
        } else {
            $update['user_city_lat'] = null;
            $update['user_city_lng'] = null;
        }
        //endregion Collect data

        //region Update
        if (!$this->usersRepository->updateOne($userId, $update)) {
            throw new WriteException(\sprintf('Failed to update profile for user with ID "%s".', $userId));
        }
        //endregion Update

        // Send event about profile update
        $this->eventBus->dispatch(new LifecycleEvents\UserUpdatedProfileEvent($userId));

        return \array_merge($userProfile, $update);
    }

    /**
     * Save the profile following the legacy logic.
     *
     * @return array the updated user profile
     */
    public function saveLegacyProfileInformation(Request $request, array $userProfile, bool $fullUpdate = false): array
    {
        $userId = $userProfile['idu'];

        //region Collect data
        $update = [
            'fname'      => \cleanInput($request->request->get('fname')),
            'lname'      => \cleanInput($request->request->get('lname')),
            'legal_name' => $request->request->has('checkbox_legal_name') ? cleanInput($request->request->get('legal_name')) : '',
        ];
        if ($fullUpdate) {
            // Get the phone and fax codes
            /** @var CountryCodeInterface $phoneCode */
            $phoneCode = $this->phoneCodesService->findAllMatchingCountryCodes($request->request->getInt('phone_code'))->first() ?: null;
            /** @var CountryCodeInterface $faxCode */
            $faxCode = $this->phoneCodesService->findAllMatchingCountryCodes($request->request->getInt('fax_code'))->first() ?: null;
            // Merge existing data with additional information
            $update = array_merge(
                $update,
                [
                    'city'          => $cityId = $request->request->getInt('port_city') ?: null,
                    'state'         => $request->request->getInt('states') ?: null,
                    'country'       => $request->request->getInt('country') ?: null,
                    'address'       => \cleanInput($request->request->get('address')),
                    'description'   => cleanInput($request->request->get('description')),
                    'phone_code_id' => $phoneCode ? $phoneCode->getId() : null,
                    'phone_code'    => \cleanInput($phoneCode ? $phoneCode->getName() : null),
                    'phone'         => \cleanInput($request->request->get('phone')),
                    'fax_code_id'   => $faxCode ? $faxCode->getId() : null,
                    'fax_code'      => \cleanInput($faxCode ? $faxCode->getName() : null),
                    'fax'           => \cleanInput($request->request->get('fax')),
                    'zip'           => \cleanInput($request->request->get('zip')),
                ]
            );
            // Update search information
            if ($request->request->has('find_type')) {
                $update['user_find_type'] = cleanInput($request->request->get('find_type'));
                $update['user_find_info'] = cleanInput($request->request->get('find_info'));
            }
            // Update city geodata
            $citiesRepository = $this->usersRepository->getRelation('city')->getRelated();
            if (null !== ($city = $citiesRepository->findOne($cityId))) {
                $update['user_city_lat'] = $city['city_lat'];
                $update['user_city_lng'] = $city['city_lng'];
            } else {
                $update['user_city_lat'] = null;
                $update['user_city_lng'] = null;
            }
        }
        //endregion Collect data

        //region Update
        $connection = $this->usersRepository->getConnection();
        $connection->beginTransaction();
        try {
            // Write new user information
            if (!$this->usersRepository->updateOne($userId, $update)) {
                throw new WriteException(\sprintf('Failed to update profile for user with ID "%s".', $userId));
            }

            // Update related accounts
            $this->updateRelatedAccounts(
                $userProfile['id_principal'],
                $userId,
                $updatedProfile = \array_merge($userProfile, $update),
                $syncAccounts = \array_values(\array_map(fn ($v) => (int) $v, (array) $request->request->get('sync_with_accounts'))),
                \array_keys($update),
            );
            // Commit changes
            $connection->commit();
        } catch (\Throwable $e) {
            // Roll back changes (if any)
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            // Roll up the exception
            throw $e;
        }
        //endregion Update

        // Send events about profile update
        foreach ([$userId, ...$syncAccounts] as $accountId) {
            $this->eventBus->dispatch(new LifecycleEvents\UserUpdatedProfileEvent((int) $accountId));
            $this->eventDispatcher->dispatch(new ProfileUpdateEvent($accountId, 'account_preferences'));
        }

        return $updatedProfile;
    }

    /**
     * Saves additional profile information for user.
     *
     * @throws UserNotFoundException if user is not found
     * @throws WriteException        when failed to write changes
     *
     * @return array the updated user profile
     */
    public function saveAdditionalProfileInformation(Request $request, array $userProfile): array
    {
        $userId = $userProfile['idu'];

        //region Collect data
        $update = ['description' => cleanInput($request->request->get('description'))];
        if ($request->request->has('find_type')) {
            $update['user_find_type'] = UserSourceType::from($request->request->get('find_type'));
            $update['user_find_info'] = cleanInput($request->request->get('find_info'));
        }
        //endregion Collect data

        //region Update
        $connection = $this->usersRepository->getConnection();
        $connection->beginTransaction();
        try {
            // Write new user information
            if (!$this->usersRepository->updateOne($userId, $update)) {
                throw new WriteException(\sprintf('Failed to update profile for user with ID "%s".', $userId));
            }

            // Update related accounts
            $this->updateRelatedAccounts(
                $userProfile['id_principal'],
                $userId,
                $updatedProfile = \array_merge($userProfile, $update),
                $syncAccounts = \array_values(\array_map(fn ($v) => (int) $v, (array) $request->request->get('sync_with_accounts'))),
                \array_keys($update),
            );
            // Commit changes
            $connection->commit();
        } catch (\Throwable $e) {
            // Roll back changes (if any)
            if ($connection->isTransactionActive()) {
                $connection->rollBack();
            }

            // Roll up the exception
            throw $e;
        }
        //endregion Update

        // Send events about profile update
        foreach ([$userId, ...$syncAccounts] as $accountId) {
            $this->eventBus->dispatch(new LifecycleEvents\UserUpdatedAdditionalDataEvent((int) $accountId));
            $this->eventDispatcher->dispatch(new ProfileUpdateEvent($accountId, 'account_preferences'));
        }

        return $updatedProfile;
    }

    /**
     * Import import profile information from existing profile.
     *
     * @throws MismatchStatusException    if source account is deleted
     * @throws ProfileCompletionException if source account is not completed
     *
     * @return array the updated target user profile
     */
    public function importProfileInformation(array $sourceProfile, array $targetProfile): array
    {
        $userId = $targetProfile['idu'];

        //region Check access
        if (UserStatus::DELETED() === $sourceProfile['status']) {
            throw new MismatchStatusException(
                \sprintf('Cannot import information from profiles in the status "%s".', (string) UserStatus::DELETED())
            );
        }
        if (
            !$this->usersRepository
                ->getRelation('completeProfileOptions')
                ->getRelated()
                ->has([$sourceProfile['idu'], 'account_preferences'])
        ) {
            throw new ProfileCompletionException('Cannot import information from profiles that are not completed.');
        }
        //endregion Check access

        //region Collect information
        $update = array_intersect_key(
            $sourceProfile,
            array_fill_keys(
                [
                    'fname',
                    'lname',
                    'legal_name',
                    'country',
                    'state',
                    'city',
                    'address',
                    'zip',
                    'phone_code_id',
                    'phone_code',
                    'phone',
                    'fax_code_id',
                    'fax_code',
                    'fax',
                    'description',
                    'user_find_type',
                    'user_find_info',
                    'user_city_lat',
                    'user_city_lng',
                ],
                ''
            )
        );
        //endregion Collect information

        //region Update
        if (!$this->usersRepository->updateOne($userId, $update)) {
            throw new WriteException(
                sprintf('Failed import existing profile information to the profile "%s"', $userId)
            );
        }
        //endregion Update

        // Send event about profile update
        $this->eventBus->dispatch(new LifecycleEvents\UserUpdatedProfileEvent($userId));

        return \array_merge($targetProfile, $update);
    }

    /**
     * Update user related accounts.
     */
    private function updateRelatedAccounts(
        int $principalId,
        int $userId,
        array $baseAccount,
        array $savedSyncSettings,
        ?array $syncColumns = null
    ): void {
        if (
            empty(
                $relatedAccounts = $this->usersRepository->findAllBy([
                    'columns' => [$this->usersRepository->getPrimaryKey()],
                    'scopes'  => ['principal' => $principalId, 'notId' => $userId],
                ])
            )
        ) {
            return;
        }

        $relatedAccounts = \array_column($relatedAccounts, $this->usersRepository->getPrimaryKey(), $this->usersRepository->getPrimaryKey());
        $syncSettings = $baseAccount['sync_with_related_accounts'] ?? [];
        $personalInfoSettings = $syncSettings['personal_info'] ?: [];
        $updateInformation = array_intersect_key(
            $baseAccount,
            array_fill_keys(
                $syncColumns ?? [
                    'fname',
                    'lname',
                    'legal_name',
                    'country',
                    'state',
                    'city',
                    'address',
                    'zip',
                    'phone_code_id',
                    'phone_code',
                    'phone',
                    'fax_code_id',
                    'fax_code',
                    'fax',
                    'description',
                    'user_find_type',
                    'user_find_info',
                    'user_city_lat',
                    'user_city_lng',
                ],
                ''
            )
        );

        foreach ($savedSyncSettings as $accountId) {
            if (!isset($relatedAccounts[$accountId])) {
                continue;
            }

            if (!$this->usersRepository->updateOne($accountId, $updateInformation)) {
                throw new WriteException(\sprintf('Failed to update related account with ID "%s" for user with ID "%s".', $accountId, $userId));
            }
        }

        if (
            !empty(array_diff($personalInfoSettings, $savedSyncSettings))
            || !empty(array_diff($savedSyncSettings, $personalInfoSettings))
        ) {
            $syncSettings['personal_info'] = \array_combine(
                \array_values($savedSyncSettings),
                \array_fill(0, count($savedSyncSettings), [])
            );

            if (!$this->usersRepository->updateOne($userId, ['sync_with_related_accounts' => $syncSettings])) {
                throw new WriteException(\sprintf('Failed to update related accounts information for user with ID "%s".', $userId));
            }
        }
    }
}
