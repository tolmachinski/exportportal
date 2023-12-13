<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Exceptions\UserNotFoundException;
use Doctrine\DBAL\Query\QueryBuilder;
use ExportPortal\Bridge\Notifier\DataProvider\UserRuleReferenceProviderInterface;

/**
 * The user profile data provider service.
 *
 * @author Anton Zencenco
 */
final class UserProfileProvider implements UserRuleReferenceProviderInterface
{
    /**
     * The requests repository.
     */
    private Model $usersRepository;

    /**
     * Create the user profile data provider service class.
     *
     * @param Model $usersRepository the user model
     */
    public function __construct(Model $usersRepository)
    {
        $this->usersRepository = $usersRepository;
    }

    /**
     * Get the user repository.
     */
    public function getRepository(): Model
    {
        return $this->usersRepository;
    }

    /**
     * Get user profile.
     */
    public function getProfile(?int $userId): array
    {
        if (
            null === $userId
            || null === ($user = $this->usersRepository->findOne($userId, ['with' => ['group']]))
        ) {
            throw new UserNotFoundException(sprintf('The profile for user with ID "%s" is not found.', $userId));
        }

        return $user;
    }

    /**
     * Get user profile for the edit page.
     */
    public function getProfileForEditPage(?int $userId, bool $includeIndustries = false): array
    {
        if (
            null === $userId
            || null === ($user = $this->usersRepository->findOne($userId, [
                'with'   => array_filter([
                    'city',
                    'state',
                    'country',
                    $includeIndustries ? 'industries' : null,
                    'group',
                    'phoneCode as stored_phone_code',
                    'faxCode as stored_fax_code',
                ]),
            ]))
        ) {
            throw new UserNotFoundException(sprintf('The profile for user with ID "%s" is not found.', $userId));
        }

        return $user;
    }

    /**
     * Get user profile for the edit page.
     */
    public function getProfileForEditForm(?int $userId): array
    {
        if (
            null === $userId
            || null === ($user = $this->usersRepository->findOne($userId, [
                'with'   => array_filter([
                    'city',
                    'phoneCode as stored_phone_code',
                    'faxCode as stored_fax_code',
                ]),
            ]))
        ) {
            throw new UserNotFoundException(sprintf('The profile for user with ID "%s" is not found.', $userId));
        }

        return $user;
    }

    /**
     * Get user profile for details popups.
     */
    public function getDetailedProfile(?int $userId): array
    {
        if (
            null === $userId
            || null === ($user = $this->usersRepository->findOne($userId, [
                'with'   => ['group', 'country', 'state', 'city', 'phoneCode as stored_phone_code', 'faxCode as stored_fax_code'],
            ]))
        ) {
            throw new UserNotFoundException(sprintf('The profile for user with ID "%s" is not found.', $userId));
        }

        return $user;
    }

    /**
     * Get user profiel for work with addendum information.
     */
    public function getAddedndumProfile(?int $userId): array
    {
        if (
            null === $userId
            || null === ($user = $this->usersRepository->findOne($userId, [
                'with'   => ['group', 'industries'],
            ]))
        ) {
            throw new UserNotFoundException(sprintf('The profile for user with ID "%s" is not found.', $userId));
        }

        return $user;
    }

    /**
     * Get the user source profile.
     *
     * @throws UserNotFoundException when user profile is not found
     */
    public function getSourceProfile(?int $userId, int $principalId, array $groupTypes = []): array
    {
        if (
            null === $userId
            || null === ($user = $this->usersRepository->findOne($userId, [
                'with'   => ['group'],
                'scopes' => ['principal' => $principalId],
                'exists' => empty($groupTypes) ? [] : [
                    $this->usersRepository->getRelationsRuleBuilder()->whereHas(
                        'group',
                        function (QueryBuilder $query, RelationInterface $relation) use ($groupTypes) {
                            $relation->getRelated()->getScope('types')->call(
                                $relation->getRelated(),
                                $query,
                                $groupTypes
                            );
                        }
                    ),
                ],
            ]))
        ) {
            throw new UserNotFoundException(sprintf('The source profile for user with ID "%s" is not found.', $userId));
        }

        return $user;
    }

    /**
     * Get list of user references using provided access rules.
     *
     * @param string[] $ruleNames list of rules we use to find user refernces
     *
     * @return iterable<int|string>
     */
    public function getUserRuleReferences(array $ruleNames): iterable
    {
        if (empty($ruleNames)) {
            yield from [];

            return;
        }

        $ruleBulder = $this->usersRepository->getRelationsRuleBuilder();
        $result = $this->usersRepository->findAllBy([
            'columns' => [$this->usersRepository->getPrimaryKey()],
            'exists'  => [
                $ruleBulder->whereHas('group.rights', function (QueryBuilder $query, RelationInterface $relation) use ($ruleNames) {
                    $relation->getRelated()->getScope('aliases')->call(
                        $relation->getRelated(),
                        $query,
                        $ruleNames
                    );
                }),
                $ruleBulder->orWhereHas('additionalRights', function (QueryBuilder $query, RelationInterface $relation) use ($ruleNames) {
                    $relation->getRelated()->getScope('aliases')->call(
                        $relation->getRelated(),
                        $query,
                        $ruleNames
                    );
                }),
            ],
        ]);

        yield from \array_column($result ?? [], $this->usersRepository->getPrimaryKey());
    }
}
