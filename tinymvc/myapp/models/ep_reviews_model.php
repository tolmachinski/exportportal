<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Ep_Reviews model
 */
final class Ep_Reviews_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = "ep_reviews";

    /**
     * {@inheritdoc}
     */
    protected string $alias = "EPREVIEWS";

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = "id";

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                => Types::INTEGER,
        'id_user'           => Types::INTEGER,
        'added_date'        => Types::DATETIME_IMMUTABLE,
        'updated_date'      => Types::DATETIME_IMMUTABLE,
        'published_date'    => Types::DATETIME_IMMUTABLE,
        'is_moderated'      => Types::INTEGER,
        'is_published'      => Types::INTEGER,
    ];

    /**
     * Scope query by user ID
     *
     * @param QueryBuilder $builder
     * @param int $userId
     *
     * @return void
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`id_user`",
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope query by user status
     *
     * @param QueryBuilder $builder
     * @param string $userStatus
     *
     * @return void
     */
    protected function scopeUserStatus(QueryBuilder $builder, string $userStatus): void
    {
        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);

        $builder->andWhere(
            $builder->expr()->eq(
                "{$usersModel->getTable()}.`status`",
                $builder->createNamedParameter($userStatus, ParameterType::STRING, $this->nameScopeParameter('userStatus'))
            )
        );
    }

    /**
     * Scope query by is_published column
     *
     * @param QueryBuilder $builder
     * @param int $isPublished
     *
     * @return void
     */
    protected function scopeIsPublished(QueryBuilder $builder, int $isPublished): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`is_published`",
                $builder->createNamedParameter($isPublished, ParameterType::INTEGER, $this->nameScopeParameter('isPublished'))
            )
        );
    }

    /**
     * Scope query by is_moderated column
     *
     * @param QueryBuilder $builder
     * @param int $isModerated
     *
     * @return void
     */
    protected function scopeIsModerated(QueryBuilder $builder, int $isModerated): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`is_moderated`",
                $builder->createNamedParameter($isModerated, ParameterType::INTEGER, $this->nameScopeParameter('isModerated'))
            )
        );
    }

    /**
     * Scope query by is_moderated column
     *
     * @param QueryBuilder $builder
     * @param string $fromDate - Format: Y-m-d
     *
     * @return void
     */
    protected function scopeAddedFromDate(QueryBuilder $builder, string $fromDate): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->getTable()}.`added_date`)",
                $builder->createNamedParameter($fromDate, ParameterType::STRING, $this->nameScopeParameter('addedFromDate'))
            )
        );
    }

    /**
     * Scope query by is_moderated column
     *
     * @param QueryBuilder $builder
     * @param string $toDate - Format: Y-m-d
     *
     * @return void
     */
    protected function scopeAddedToDate(QueryBuilder $builder, string $toDate): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->getTable()}.`added_date`)",
                $builder->createNamedParameter($toDate, ParameterType::STRING, $this->nameScopeParameter('addedToDate'))
            )
        );
    }

    /**
     * Scope query by is_moderated column
     *
     * @param QueryBuilder $builder
     * @param string $fromDate - Format: Y-m-d
     *
     * @return void
     */
    protected function scopePublishedFromDate(QueryBuilder $builder, string $fromDate): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE({$this->getTable()}.`published_date`)",
                $builder->createNamedParameter($fromDate, ParameterType::STRING, $this->nameScopeParameter('publishedFromDate'))
            )
        );
    }

    /**
     * Scope query by is_moderated column
     *
     * @param QueryBuilder $builder
     * @param string $toDate - Format: Y-m-d
     *
     * @return void
     */
    protected function scopePublishedToDate(QueryBuilder $builder, string $toDate): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE({$this->getTable()}.`published_date`)",
                $builder->createNamedParameter($toDate, ParameterType::STRING, $this->nameScopeParameter('publishedToDate'))
            )
        );
    }

    /**
     * Resolves static relationships with user
     *
     * @return RelationInterface
     */
    protected function user(): RelationInterface
    {
        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        /** @var User_Groups_Model $userGroupsModel */
        $userGroupsModel = model(User_Groups_Model::class);
        $userGroupsTable = $userGroupsModel->getTable();

        /** @var RelationInterface $relation */
        $relation = $this->belongsTo(Users_Model::class, 'id_user', 'idu')->enableNativeCast();

        $queryBuilder = $relation->getQuery();

        $queryBuilder->leftJoin(
            $usersTable,
            $userGroupsTable,
            $userGroupsTable,
            "{$usersTable}.user_group = {$userGroupsTable}.idgroup"
        );

        $queryBuilder->select(
            "{$usersTable}.`idu`",
            "{$usersTable}.`fname`",
            "{$usersTable}.`lname`",
            "{$usersTable}.`email`",
            "{$usersTable}.`status`",
            "{$usersTable}.`user_photo`",
            "{$usersTable}.`user_group`",
            "{$userGroupsTable}.`gr_type`",
            "{$userGroupsTable}.`gr_name`",
        );

        return $relation;
    }

    /**
     * Scope for join users
     *
     * @param QueryBuilder $builder
     *
     * @return void
     */
    protected function bindUsers(QueryBuilder $builder): void
    {
        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        $builder
            ->leftJoin(
                $this->getTable(),
                $usersTable,
                $usersTable,
                "`{$usersTable}`.`{$usersModel->getPrimaryKey()}` = `{$this->getTable()}`.`id_user`"
            );
    }
}

/* End of file ep_reviews_model.php */
/* Location: /tinymvc/myapp/models/ep_reviews_model.php */
