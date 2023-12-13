<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Users_Complete_Profile_Options model.
 */
final class Users_Complete_Profile_Options_Model extends Model
{
    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'date_completed';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = null;

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'users_complete_profile';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'USERS_COMPLETE_PROFILE';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = ['id_user', 'profile_key'];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_user'        => Types::INTEGER,
        'date_completed' => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * @param int[] $usersIds
     */
    public function getUsersProfileOptions(array $usersIds): array
    {
        /** @var Groups_Profile_Options_Pivot_Model $groupsProfileOptionsPivotModel */
        $groupsProfileOptionsPivotModel = model(Groups_Profile_Options_Pivot_Model::class);
        $groupsProfileOptionsPivotTable = $groupsProfileOptionsPivotModel->getTable();

        /** @var Complete_Profile_Options_Model $completeProfileOptionsModel */
        $completeProfileOptionsModel = model(Complete_Profile_Options_Model::class);
        $completeProfileOptionsTable = $completeProfileOptionsModel->getTable();

        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        $usersCompleteProfileTable = $this->getTable();

        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder
            ->select([
                "`{$usersTable}`.`idu`",
                "`{$completeProfileOptionsTable}`.*",
                "`{$groupsProfileOptionsPivotTable}`.*",
                "IF(`{$usersCompleteProfileTable}`.`id_user` IS NULL, 0, 1) AS option_completed",
            ])
            ->from($groupsProfileOptionsPivotTable)
            ->innerJoin(
                $groupsProfileOptionsPivotTable,
                $usersTable,
                $usersTable,
                "`{$usersTable}`.`user_group` = `{$groupsProfileOptionsPivotTable}`.`id_group`"
            )
            ->leftJoin(
                $groupsProfileOptionsPivotTable,
                $completeProfileOptionsTable,
                $completeProfileOptionsTable,
                "`{$completeProfileOptionsTable}`.`id_option` = `{$groupsProfileOptionsPivotTable}`.`id_option`"
            )
            ->leftJoin(
                $usersTable,
                $usersCompleteProfileTable,
                $usersCompleteProfileTable,
                "`{$usersTable}`.`idu` = `{$usersCompleteProfileTable}`.`id_user` AND `{$usersCompleteProfileTable}`.`profile_key` = `{$completeProfileOptionsTable}`.`option_alias`"
            )
            ->andWhere(
                $queryBuilder->expr()->in(
                    "`{$usersTable}`.`idu`",
                    array_map(
                        fn ($i, $userId) => $queryBuilder->createNamedParameter((int) $userId, ParameterType::INTEGER, $this->nameScopeParameter("userId_{$i}")),
                        array_keys($usersIds),
                        $usersIds
                    )
                )
            )
        ;

        return arrayByKey($queryBuilder->execute()->fetchAllAssociative(), 'idu', true);
    }

    /**
     * Check if user has completed profile option.
     */
    public function hasProfileOption(int $userId, string $option): bool
    {
        return $this->countAllBy(['scopes' => ['user' => $userId, 'option' => $option]]) > 0;
    }

    /**
     * Return true if the profile is completed 100% and false if some options are not completed
     * (if the group of the user is not sent as a parameter then the group of the user will be taken from the users table - as a subquery).
     *
     * @param int $idUser  - the id of the user
     * @param int $idGroup - the id of the group of the user
     *
     * @return bool
     */
    public function checkIfProfileIsCompleted(int $idUser, int $idGroup = null)
    {
        /** @var Groups_Profile_Options_Pivot_Model $groupsProfileOptionsPivotModel */
        $groupsProfileOptionsPivotModel = model(Groups_Profile_Options_Pivot_Model::class);
        $groupsProfileOptionsPivotTable = $groupsProfileOptionsPivotModel->getTable();

        /** @var Complete_Profile_Options_Model $completeProfileOptionsModel */
        $completeProfileOptionsModel = model(Complete_Profile_Options_Model::class);
        $completeProfileOptionsTable = $completeProfileOptionsModel->getTable();

        $usersCompleteProfileTable = $this->getTable();

        $queryBuilder = $this->createQueryBuilder();
        $queryBuilder
            ->select([
                "IF(`{$usersCompleteProfileTable}`.`id_user` IS NULL, 'no', 'yes') AS option_completed",
            ])
            ->from($groupsProfileOptionsPivotTable)
            ->leftJoin(
                $groupsProfileOptionsPivotTable,
                $completeProfileOptionsTable,
                $completeProfileOptionsTable,
                "`{$completeProfileOptionsTable}`.`id_option` = `{$groupsProfileOptionsPivotTable}`.`id_option`"
            )
            ->leftJoin(
                $completeProfileOptionsTable,
                $usersCompleteProfileTable,
                $usersCompleteProfileTable,
                "`{$usersCompleteProfileTable}`.`id_user` = {$queryBuilder->createNamedParameter($idUser, null, ':idUser')} AND `{$usersCompleteProfileTable}`.`profile_key` = `{$completeProfileOptionsTable}`.`option_alias`"
            )
            ;

        if (!isset($idGroup)) {
            /** @var Users_Model $usersModel */
            $usersModel = model(Users_Model::class);
            $usersTable = $usersModel->getTable();

            $subqueryBuilder = $this->createQueryBuilder();
            $subqueryBuilder
                ->select("`{$usersTable}`.`user_group`")
                ->from($usersTable)
                ->where(
                    $subqueryBuilder->expr()->eq(
                        "`{$usersTable}`.`{$usersModel->getPrimaryKey()}`",
                        $queryBuilder->createNamedParameter($idUser, ParameterType::INTEGER, $this->nameScopeParameter('idu'))
                    )
                )
            ;

            $queryBuilder->andWhere("`{$groupsProfileOptionsPivotTable}`.`id_group` = ({$subqueryBuilder->getSQL()})");
        } else {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq(
                    "{$groupsProfileOptionsPivotTable}.`id_group`",
                    $queryBuilder->createNamedParameter($idGroup, ParameterType::INTEGER, $this->nameScopeParameter('id_group'))
                )
            );
        }

        $results = $queryBuilder->execute()->fetchAllAssociative();

        return !array_key_exists('no', array_column($results, null, 'option_completed'));
    }

    /**
     * Scope a query to filter by user ID.
     */
    protected function scopeUser(QueryBuilder $builder, int $userId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_user'),
                $builder->createNamedParameter((int) $userId, ParameterType::INTEGER, $this->nameScopeParameter('user_id'))
            )
        );
    }

    /**
     * Scope query for groups.
     */
    protected function scopeUsers(QueryBuilder $builder, array $userIds): void
    {
        if (empty($userIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in($this->qualifyColumn('id_user'), array_map(
                fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("user_id_{$i}")),
                array_keys($userIds),
                $userIds
            ))
        );
    }

    /**
     * Scope a query to filter by user ID.
     */
    protected function scopeOption(QueryBuilder $builder, string $option)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('profile_key'),
                $builder->createNamedParameter($option, ParameterType::STRING, $this->nameScopeParameter('option'))
            )
        );
    }

    /**
     * Relation with completion option.
     */
    protected function completionOption(): RelationInterface
    {
        return $this->belongsTo(Complete_Profile_Options_Model::class, 'profile_key', 'option_alias');
    }
}

// End of file users_complete_profile_options_model.php
// Location: /tinymvc/myapp/models/users_complete_profile_options_model.php
