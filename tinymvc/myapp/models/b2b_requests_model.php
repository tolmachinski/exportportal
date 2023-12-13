<?php

declare(strict_types=1);

use App\Common\Contracts\B2B\B2bRequestStatus;
use App\Common\Contracts\B2B\B2bRequestLocationType;
use App\Common\Database\Concerns;
use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use App\Common\Database\Relations\RelationInterface;

/**
 * B2b_Requests model.
 */
final class B2b_Requests_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'b2b_date_register';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'b2b_date_update';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'b2b_request';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'B2B_REQUESTS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_request';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_request',
        'b2b_date_register',
        'b2b_date_update',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'id_ticket',
        'moderation_approved_at',
        'moderation_blocked_at',
        'moderation_unblocked_at',
        'moderation_noticed_at',
        'moderation_notices',
        'moderation_blocking',
        'b2b_radius',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_request'              => Types::INTEGER,
        'id_company'              => Types::INTEGER,
        'id_user'                 => Types::INTEGER,
        'id_country'              => Types::INTEGER,
        'id_state'                => Types::INTEGER,
        'id_city'                 => Types::INTEGER,
        'id_type'                 => Types::INTEGER,
        'b2b_radius'              => Types::INTEGER,
        'b2b_chat'                => Types::BOOLEAN,
        'b2b_date_register'       => Types::DATETIME_IMMUTABLE,
        'b2b_date_update'         => Types::DATETIME_IMMUTABLE,
        'b2b_active'              => Types::BOOLEAN,
        'status'                  => B2bRequestStatus::class,
        'viewed_count'            => Types::INTEGER,
        'blocked'                 => Types::BOOLEAN,
        'moderation_is_approved'  => Types::BOOLEAN,
        'moderation_approved_at'  => Types::DATETIME_IMMUTABLE,
        'moderation_blocked_at'   => Types::DATETIME_IMMUTABLE,
        'moderation_unblocked_at' => Types::DATETIME_IMMUTABLE,
        'moderation_noticed_at'   => Types::DATETIME_IMMUTABLE,
        'moderation_activity'     => Types::JSON,
        'moderation_notices'      => Types::JSON,
        'moderation_blocking'     => Types::JSON,
        'type_location'           => B2bRequestLocationType::class,
    ];

    /**
     * Scope a query to filter by request ID.
     */
    protected function scopeId(QueryBuilder $builder, int $requestId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn($this->getPrimaryKey()),
                $builder->createNamedParameter($requestId, ParameterType::INTEGER, $this->nameScopeParameter('requestId'))
            )
        );
    }

    /**
     * Scope a query to filter by user ID.
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_user'),
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope a query to filter by user ID.
     */
    protected function scopeTypeLocation(QueryBuilder $builder, string $location)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('type_location'),
                $builder->createNamedParameter($location, ParameterType::STRING, $this->nameScopeParameter('location'))
            )
        );
    }

    /**
     * Scope query for request IDs.
     *
     * @param int[] $requestIds
     */
    protected function scopeIds(QueryBuilder $builder, array $requestIds): void
    {
        if (empty($requestIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in($this->qualifyColumn($this->getPrimaryKey()), array_map(
                fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("requestId{$i}", true)),
                array_keys($requestIds),
                $requestIds
            ))
        );
    }

    /**
     * Scope query for not request.
     */
    protected function scopeNotRequest(QueryBuilder $builder, int $requestId): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                $this->qualifyColumn($this->getPrimaryKey()),
                $builder->createNamedParameter($requestId, ParameterType::INTEGER, $this->nameScopeParameter('not_request_id'))
            )
        );
    }

    /**
     * Scope a query to filter by request ID.
     */
    protected function scopeCompany(QueryBuilder $builder, int $companyId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_company'),
                $builder->createNamedParameter($companyId, ParameterType::INTEGER, $this->nameScopeParameter('companyId'))
            )
        );
    }

    /**
     * Scope by b2b active.
     *
     * @var int
     */
    protected function scopeActive(QueryBuilder $builder, int $isActiveB2bRequest): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`b2b_active`",
                $builder->createNamedParameter($isActiveB2bRequest, ParameterType::INTEGER, $this->nameScopeParameter('isActiveB2bRequest'))
            )
        );
    }

    /**
     * Scope by b2b blocked.
     *
     * @var int
     */
    protected function scopeBlocked(QueryBuilder $builder, int $isBlocked): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`blocked`",
                $builder->createNamedParameter($isBlocked, ParameterType::INTEGER, $this->nameScopeParameter('isBlocked'))
            )
        );
    }

    /**
     * Scope by b2b request status.
     *
     * @var string
     */
    protected function scopeStatus(QueryBuilder $builder, string $b2bRequestStatus): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`status`",
                $builder->createNamedParameter($b2bRequestStatus, ParameterType::STRING, $this->nameScopeParameter('b2bRequestStatus'))
            )
        );
    }
    /**
     * Scope query for request IDs.
     *
     * @param int[] $companiesIds
     */
    protected function scopeCompanies(QueryBuilder $builder, array $companiesIds): void
    {
        if (empty($companiesIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in($this->qualifyColumn('id_company'), array_map(
                fn (int $i, $id) => $builder->createNamedParameter((int) $id, ParameterType::INTEGER, $this->nameScopeParameter("companyId{$i}", true)),
                array_keys($companiesIds),
                $companiesIds
            ))
        );
    }

    /**
     * Relation with industries.
     */
    protected function industries(): RelationInterface
    {
        return $this->hasManyThrough(
            Categories_Model::class,
            B2b_Request_Industry_Pivot_Model::class,
            'id_request',
            'category_id',
            $this->getPrimaryKey(),
            'id_industry'
        );
    }

    /**
     * Relation with categories.
     */
    protected function categories(): RelationInterface
    {
        return $this->hasManyThrough(
            Categories_Model::class,
            B2b_Request_Category_Pivot_Model::class,
            'id_request',
            'category_id',
            $this->getPrimaryKey(),
            'id_category'
        );
    }

    /**
     * Relation with categories.
     */
    protected function countries(): RelationInterface
    {
        return $this->hasManyThrough(
            Countries_Model::class,
            B2b_Request_Country_Pivot_Model::class,
            'request_id',
            'id',
            $this->getPrimaryKey(),
            'country_id'
        );
    }

    /**
     * Relation with photos.
     */
    protected function photos(): RelationInterface
    {
        return $this->hasMany(B2b_Request_Photo_Model::class, 'request_id')->enableNativeCast();
    }

    /**
     * Relation with followers.
     */
    protected function followers(): RelationInterface
    {
        /** @var B2b_Followers_Model $b2bFollowersModel */
        $b2bFollowersModel = model(B2b_Followers_Model::class);

        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);

        /** @var User_Groups_Model $usersGroupsModel */
        $usersGroupsModel = model(User_Groups_Model::class);
        $usersGroupsTable = $usersGroupsModel->getTable();

        $usersTable = $usersModel->getTable();
        $relation = $this->hasMany(B2b_Followers_Model::class, 'id_request')->enableNativeCast();

        $builder = $relation->getQuery();
        $builder
            ->select("{$b2bFollowersModel->getTable()}.*", "CONCAT({$usersTable}.fname, ' ', {$usersTable}.lname) as username, {$usersTable}.user_photo, {$usersTable}.status, {$usersTable}.user_group, {$usersGroupsTable}.gr_name as group_name")
            ->leftJoin($b2bFollowersModel->getTable(), $usersTable, $usersTable, "{$b2bFollowersModel->getTable()}.id_user = {$usersTable}.{$usersModel->getPrimaryKey()}")
            ->leftJoin($b2bFollowersModel->getTable(), $usersGroupsTable, $usersGroupsTable, "{$usersTable}.user_group = {$usersGroupsTable}.{$usersGroupsModel->getPrimaryKey()}");

        return $relation;
    }

    /**
     * Relation with advice.
     */
    protected function advice(): RelationInterface
    {
        /** @var B2b_Advice_Model $adviceModel */
        $adviceModel = model(B2b_Advice_Model::class);

        /** @var Users_Model $usersModel */
        $usersModel = model(Users_Model::class);
        $usersTable = $usersModel->getTable();

        $realtion = $this->hasMany(B2b_Advice_Model::class, 'id_request');
        $builder = $realtion->getQuery();
        $builder
            ->select("{$adviceModel->getTable()}.*", "CONCAT({$usersTable}.fname, ' ', {$usersTable}.lname) as username, {$usersTable}.user_photo, {$usersTable}.user_group, {$usersTable}.status")
            ->leftJoin($adviceModel->getTable(), $usersTable, $usersTable, "{$adviceModel->getTable()}.id_user = {$usersTable}.{$usersModel->getPrimaryKey()}")
        ;

        return $realtion;
    }

    /**
     * Scope for join with companies.
     */
    protected function bindCompanies(QueryBuilder $builder): void
    {
        /** @var Seller_Companies_Model $companyModel */
        $companyModel = model(Seller_Companies_Model::class);
        $companyTable = $companyModel->getTable();

        $builder
            ->leftJoin(
                $this->getTable(),
                $companyTable,
                $companyTable,
                "`{$companyTable}`.`{$companyModel->getPrimaryKey()}` = `{$this->getTable()}`.`id_company`"
            );
    }

    /**
     * Scope for join with partners types
     */
    protected function bindPartnersTypes(QueryBuilder $builder): void
    {
        /** @var Partners_Types_Model $partnersTypesModel */
        $partnersTypesModel = model(Partners_Types_Model::class);
        $partnersTypesTable = $partnersTypesModel->getTable();

        $builder
            ->leftJoin(
                $this->getTable(),
                $partnersTypesTable,
                $partnersTypesTable,
                "`{$partnersTypesTable}`.`{$partnersTypesModel->getPrimaryKey()}` = `{$this->getTable()}`.`id_type`"
            );
    }

    /**
     * Scope for join with users
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

    /**
     * Resolves static relationships with company.
     */
    protected function company(): RelationInterface
    {
        /** @var Seller_Companies_Model $companyModel */
        $companyModel = model(Seller_Companies_Model::class);
        $companyTable = $companyModel->getTable();
        /** @var Countries_Model $countryModel */
        $countryModel = model(Countries_Model::class);
        $countryTable = $countryModel->getTable();
        /** @var States_Model $stateModel */
        $stateModel = model(States_Model::class);
        $stateTable = $stateModel->getTable();
        /** @var Cities_Model $cityModel */
        $cityModel = model(Cities_Model::class);
        $cityTable = $cityModel->getTable();

        $relation = $this->belongsTo($companyTable, 'id_company', 'id_company');
        $relation->enableNativeCast();
        $builder = $relation->getQuery();
        $builder
            ->select(
                "`{$companyTable}`.`id_company`",
                "`{$companyTable}`.`type_company`",
                "`{$companyTable}`.`name_company`",
                "`{$companyTable}`.`legal_name_company`",
                "`{$companyTable}`.`index_name`",
                "`{$companyTable}`.`latitude`",
                "`{$companyTable}`.`longitude`",
                "`{$companyTable}`.`parent_company`",
                "`{$companyTable}`.`logo_company`",
                "`{$companyTable}`.`address_company`",
                "`{$companyTable}`.`zip_company`",
                "`{$companyTable}`.`id_country`",
                "`{$companyTable}`.`id_state`",
                "`{$companyTable}`.`id_city`",
                "`{$countryTable}`.`country`",
                "`{$countryTable}`.`country_alias`",
                "`{$stateTable}`.`state_name`",
                "`{$cityTable}`.`city`"
            )
            ->leftJoin($companyTable, $countryTable, $countryTable, "`{$companyTable}`.`id_country` = `{$countryTable}`.`{$countryModel->getPrimaryKey()}`")
            ->leftJoin($companyTable, $stateTable, $stateTable, "`{$companyTable}`.`id_state` = `{$stateTable}`.`{$stateModel->getPrimaryKey()}`")
            ->leftJoin($companyTable, $cityTable, $cityTable, "`{$companyTable}`.`id_city` = `{$cityTable}`.`{$cityModel->getPrimaryKey()}`")
        ;

        return $relation;
    }


    /**
     * Scope for join with countries.
     */
    protected function bindActiveCompanies(QueryBuilder $builder): void
    {
        /** @var Seller_Companies_Model $companyModel */
        $companyModel = model(Seller_Companies_Model::class);
        $companyTable = $companyModel->getTable();

        $builder
            ->andWhere("{$companyTable}.`visible_company` = 1")
            ->andWhere("{$companyTable}.`blocked` = 0")->leftJoin(
                $this->getTable(),
                $companyTable,
                $companyTable,
                "`{$companyTable}`.`{$companyModel->getPrimaryKey()}` = `{$this->getTable()}`.`id_company`"
        );
    }
}

// End of file b2b_requests_model.php
// Location: /tinymvc/myapp/models/b2b_requests_model.php
