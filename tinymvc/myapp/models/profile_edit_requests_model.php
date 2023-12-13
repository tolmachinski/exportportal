<?php

declare(strict_types=1);

use App\Common\Contracts\EditRequest\EditRequestStatus;
use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Profile_Edit_Requests model.
 */
final class Profile_Edit_Requests_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'created_at_date';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'updated_at_date';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'profile_edit_requests';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'PROFILE_EDIT_REQUESTS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
        'created_at_date',
        'updated_at_date',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'id_phone_code',
        'id_fax_code',
        'id_country',
        'id_state',
        'id_city',
        'legal_name',
        'created_at_date',
        'updated_at_date',
        'accepted_at_date',
        'declined_at_date',
        'update_legal_name',
        'decline_reason',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        // simple casts
        'id'                => Types::INTEGER,
        'id_user'           => Types::INTEGER,
        'id_city'           => Types::INTEGER,
        'id_state'          => Types::INTEGER,
        'id_country'        => Types::INTEGER,
        'id_phone_code'     => Types::INTEGER,
        'id_fax_code'       => Types::INTEGER,
        'status'            => EditRequestStatus::class,
        'created_at_date'   => Types::DATETIME_IMMUTABLE,
        'updated_at_date'   => Types::DATETIME_IMMUTABLE,
        'declined_at_date'  => Types::DATETIME_IMMUTABLE,
        'accepted_at_date'  => Types::DATETIME_IMMUTABLE,
        'update_legal_name' => Types::BOOLEAN,
    ];

    /**
     * Returns the paginator prepared for datagrid.
     */
    public function paginateForGrid(?array $filters = [], ?array $ordering = [], ?int $perPage = null, ?int $page = 1): array
    {
        $paginator = $this->getPaginator(['scopes' => $filters], $perPage, $page);
        $paginator['all'] = $this->countAllBy(['scopes' => $commonFilters ?? []]);
        $paginator['data'] = $this->findAllBy([
            'with'   => ['extendedUser as user'],
            'scopes' => $filters,
            'order'  => $ordering ?? [],
            'limit'  => $perPage,
            'skip'   => (($page ?? 1) - 1) * $perPage,
        ]);

        return $paginator;
    }

    /**
     * Scope a query to filter by request ID.
     */
    protected function scopeId(QueryBuilder $builder, int $requestId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn($this->getPrimaryKey()),
                $builder->createNamedParameter($requestId, ParameterType::INTEGER, $this->nameScopeParameter('request'))
            )
        );
    }

    /**
     * Scope a query to filter by user.
     */
    protected function scopeUser(QueryBuilder $builder, int $userId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_user'),
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('user'))
            )
        );
    }

    /**
     * Scope a query to filter by status.
     */
    protected function scopeStatus(QueryBuilder $builder, EditRequestStatus $status)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('status'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('status', $status),
                    ParameterType::STRING,
                    $this->nameScopeParameter('status')
                )
            )
        );
    }

    /**
     * Scope a query to filter by text search.
     */
    protected function scopeSearch(QueryBuilder $builder, string $text): void
    {
        $this->appendSearchConditionsToQuery(
            $builder,
            $text,
            ['first_name', 'last_name', 'legal_name'],
            ['first_name', 'last_name', 'legal_name'],
        );
    }

    /**
     * Scope a query to filter by creation date from.
     */
    protected function scopeCreatedFromDate(QueryBuilder $builder, DateTimeImmutable $createdAt)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                $this->qualifyColumn('created_at_date'),
                $builder->createNamedParameter(
                    // Cast the datetime value to the format supported by database
                    $this->castAttributeToDatabaseValue('created_at_date', $createdAt->setTime(0, 0, 0, 0)),
                    ParameterType::STRING,
                    $this->nameScopeParameter('createdFrom')
                )
            )
        );
    }

    /**
     * Scope a query to filter by creation date to.
     */
    protected function scopeCreatedToDate(QueryBuilder $builder, DateTimeImmutable $createdAt)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                $this->qualifyColumn('created_at_date'),
                $builder->createNamedParameter(
                    // Cast the datetime value to the format supported by database
                    $this->castAttributeToDatabaseValue('created_at_date', $createdAt->setTime(0, 0, 0, 0)),
                    ParameterType::STRING,
                    $this->nameScopeParameter('createdTo')
                )
            )
        );
    }

    /**
     * Scope a query to filter by update date from.
     */
    protected function scopeUpdatedFromDate(QueryBuilder $builder, DateTimeImmutable $updatedAt)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                $this->qualifyColumn('updated_at_date'),
                $builder->createNamedParameter(
                    // Cast the datetime value to the format supported by database
                    $this->castAttributeToDatabaseValue('updated_at_date', $updatedAt->setTime(23, 59, 59, 999999)),
                    ParameterType::STRING,
                    $this->nameScopeParameter('updatedFromDate')
                )
            )
        );
    }

    /**
     * Scope a query to filter by update date to.
     */
    protected function scopeUpdatedToDate(QueryBuilder $builder, DateTimeImmutable $updatedAt)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                $this->qualifyColumn('updated_at_date'),
                $builder->createNamedParameter(
                    // Cast the datetime value to the format supported by database
                    $this->castAttributeToDatabaseValue('updated_at_date', $updatedAt->setTime(23, 59, 59, 999999)),
                    ParameterType::STRING,
                    $this->nameScopeParameter('updatedToDate')
                )
            )
        );
    }

    /**
     * Scope a query to filter by accepted date from.
     */
    protected function scopeAcceptedFromDate(QueryBuilder $builder, DateTimeImmutable $acceptedAt)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                $this->qualifyColumn('accepted_at_date'),
                $builder->createNamedParameter(
                    // Cast the datetime value to the format supported by database
                    $this->castAttributeToDatabaseValue('accepted_at_date', $acceptedAt->setTime(0, 0, 0, 0)),
                    ParameterType::STRING,
                    $this->nameScopeParameter('acceptedFrom')
                )
            )
        );
    }

    /**
     * Scope a query to filter by accepted date to.
     */
    protected function scopeAcceptedToDate(QueryBuilder $builder, DateTimeImmutable $acceptedAt)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                $this->qualifyColumn('accepted_at_date'),
                $builder->createNamedParameter(
                    // Cast the datetime value to the format supported by database
                    $this->castAttributeToDatabaseValue('accepted_at_date', $acceptedAt->setTime(0, 0, 0, 0)),
                    ParameterType::STRING,
                    $this->nameScopeParameter('acceptedTo')
                )
            )
        );
    }

    /**
     * Scope a query to filter by declined date from.
     */
    protected function scopeDeclinedFromDate(QueryBuilder $builder, DateTimeImmutable $declinedAt)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                $this->qualifyColumn('declined_at_date'),
                $builder->createNamedParameter(
                    // Cast the datetime value to the format supported by database
                    $this->castAttributeToDatabaseValue('declined_at_date', $declinedAt->setTime(23, 59, 59, 999999)),
                    ParameterType::STRING,
                    $this->nameScopeParameter('declinedFromDate')
                )
            )
        );
    }

    /**
     * Scope a query to filter by declined date to.
     */
    protected function scopeDeclinedToDate(QueryBuilder $builder, DateTimeImmutable $declinedAt)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                $this->qualifyColumn('declined_at_date'),
                $builder->createNamedParameter(
                    // Cast the datetime value to the format supported by database
                    $this->castAttributeToDatabaseValue('declined_at_date', $declinedAt->setTime(23, 59, 59, 999999)),
                    ParameterType::STRING,
                    $this->nameScopeParameter('declinedToDate')
                )
            )
        );
    }

    /**
     * Relation with user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_user')->enableNativeCast();
    }

    /**
     * Relation with the users (extended).
     */
    protected function extendedUser(): RelationInterface
    {
        $relation = $this->user()->setName('extendedUser');
        /** @var User_Groups_Model $userGroups */
        $userGroups = $this->resolveRelatedModel(User_Groups_Model::class);
        $related = $relation->getRelated();
        $builder = $relation->getQuery();
        $table = $related->getTable();
        $related->mergeCasts(['group_type' => $userGroups->getCasts()['gr_type'], 'group_alias' => $userGroups->getCasts()['gr_alias']]);
        $builder
            ->select(
                "`{$table}`.*",
                "TRIM(CONCAT({$related->qualifyColumn('fname')}, ' ', {$related->qualifyColumn('lname')})) AS `full_name`",
                "{$related->qualifyColumn('fname')} as `first_name`",
                "{$related->qualifyColumn('lname')} as `last_name`",
                "{$userGroups->qualifyColumn('`gr_name`')} as `group_name`",
                "{$userGroups->qualifyColumn('`gr_type`')} as `group_type`",
                "{$userGroups->qualifyColumn('`gr_alias`')} as `group_alias`",
            )
            ->innerJoin(
                $table,
                $userGroups->getTable(),
                $userGroups->getTable(),
                "{$related->qualifyColumn('user_group')} = {$userGroups->qualifyColumn($userGroups->getPrimaryKey())}"
            )
        ;

        return $relation;
    }

    /**
     * Relation with country.
     */
    protected function country(): RelationInterface
    {
        return $this->belongsTo(Countries_Model::class, 'id_country')->enableNativeCast();
    }

    /**
     * Relation with state/region.
     */
    protected function state(): RelationInterface
    {
        return $this->belongsTo(States_Model::class, 'id_state')->enableNativeCast();
    }

    /**
     * Relation with city.
     */
    protected function city(): RelationInterface
    {
        return $this->belongsTo(Cities_Model::class, 'id_city')->enableNativeCast();
    }

    /**
     * Relation with profile edit request documents.
     */
    protected function documents(): RelationInterface
    {
        return $this->hasMany(Profile_Edit_Request_Documents_Model::class, 'id_request')->enableNativeCast();
    }

    /**
     * Relation with phone code.
     */
    protected function phoneCode(): RelationInterface
    {
        return $this->belongsTo(Phone_Codes_Model::class, 'id_phone_code')->enableNativeCast();
    }

    /**
     * Relation with fax code.
     */
    protected function faxCode(): RelationInterface
    {
        return $this->belongsTo(Phone_Codes_Model::class, 'id_fax_code')->enableNativeCast();
    }
}

// End of file profile_edit_requests_model.php
// Location: /tinymvc/myapp/models/profile_edit_requests_model.php
