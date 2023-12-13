<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Common\Contracts\Group\GroupType;
use App\Common\Contracts\User\UserStatus;
use App\Common\Database\Model;
use TinyMVC_Library_Session as LegacySessionHandler;

/**
 * The user account data provider service.
 *
 * @author Anton Zencenco
 */
final class AccountProvider
{
    /**
     * The session handler.
     */
    private LegacySessionHandler $sessionHandler;

    /**
     * The users repository.
     */
    private Model $usersRepository;

    /**
     * The profile options repository.
     */
    private Model $profileOptionsRepository;

    /**
     * Create the user profile data provider service class.
     *
     * @param Model                $usersRepository the user model
     * @param LegacySessionHandler $sessionHandler  the session handler
     */
    public function __construct(Model $usersRepository, Model $profileOptionsRepository, LegacySessionHandler $sessionHandler)
    {
        $this->sessionHandler = $sessionHandler;
        $this->usersRepository = $usersRepository;
        $this->profileOptionsRepository = $profileOptionsRepository;
    }

    /**
     * Finds related accounts for user.
     *
     * @return array<int,mixed[]>
     */
    public function getRelatedAccounts(int $userId, ?int $principalId): array
    {
        $foundAccounts = $this->sessionHandler->get('accounts');
        if (null === $foundAccounts || empty($foundAccounts)) {
            $foundAccounts = [];
            if (null === $principalId) {
                return [];
            }

            $usersRepository = $this->usersRepository;
            $foundAccounts = [];
            $foundUsers = $usersRepository->findAllBy([
                'with'    => ['group'],
                'scopes'  => ['principal' => $principalId, 'notStatus' => UserStatus::DELETED()],
                'columns' => [
                    "{$usersRepository->qualifyColumn('idu')}",
                    "CONCAT_WS(' ', {$usersRepository->qualifyColumn('fname')}, {$usersRepository->qualifyColumn('lname')}) as `user_name`",
                    "{$usersRepository->qualifyColumn('user_photo')}",
                    "{$usersRepository->qualifyColumn('id_principal')}",
                    "{$usersRepository->qualifyColumn('user_group')}",
                    "{$usersRepository->qualifyColumn('is_verified')}",
                    "{$usersRepository->qualifyColumn('status')}",
                ],
            ]);
            foreach ($foundUsers as $user) {
                $account = $user;
                $account['gr_name'] = $user['group']['gr_name'];
                $account['gr_type'] = $user['group']['gr_type'];
                unset($account['group']);

                $foundAccounts[] = $account;
            }
        } else {
            foreach ($foundAccounts as &$account) {
                $account['idu'] = (int) $account['idu'];
                $account['user_group'] = (int) $account['user_group'];
                $account['is_verified'] = filter_var($account['is_verified'], FILTER_VALIDATE_BOOL);
                $account['gr_type'] = GroupType::from($account['gr_type']);
                $account['status'] = UserStatus::from($account['status']);
            }
        }
        $foundAccounts = \array_column($foundAccounts, null, 'idu');
        $foundAccounts = \array_diff_key($foundAccounts, [$userId => $userId]);
        foreach ($foundAccounts as &$account) {
            $groupLabel = 'Buyer';
            if (is_verified_seller($account['user_group']) || is_certified_seller($account['user_group'])) {
                $groupLabel = 'Seller';
            } elseif (is_verified_manufacturer($account['user_group']) || is_certified_manufacturer($account['user_group'])) {
                $groupLabel = 'Manufacturer';
            }

            $account['accountLabel'] = $groupLabel;
        }

        return $foundAccounts;
    }

    /**
     * Get the source account for the user.
     *
     * @return array<int,string>
     */
    public function getSourceAccounts(int $userId, string $option, array $relatedAccounts = []): array
    {
        if (
            empty($relatedAccounts)
            || $this->profileOptionsRepository->countAllBy(['scopes' => ['user' => $userId, 'option' => $option]]) > 0
        ) {
            return [];
        }

        $personalInformationSourceAccounts = [];
        $profileCompletion = array_map(
            fn (array $list) => array_unique(array_column($list, 'profile_key')),
            arrayByKey(
                $this->profileOptionsRepository->findAllBy(['scopes' => ['users' => array_keys($relatedAccounts)]]),
                'id_user',
                true
            )
        );

        foreach ($profileCompletion as $relatedAccountId => $completeProfileOptions) {
            if (\in_array($option, $completeProfileOptions)) {
                $personalInformationSourceAccounts[(int) $relatedAccountId] = $relatedAccounts[$relatedAccountId]['accountLabel'];
            }
        }

        return $personalInformationSourceAccounts;
    }
}
