<?php

declare(strict_types=1);

use App\Casts\Company\CompanyTypeCast;
use App\Common\Contracts\Company\CompanyType;
use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Database\Types\Types as CustomTypes;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Seller companies model.
 */
final class Seller_Companies_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'registered_company';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'company_base';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'COMPANY_BASE';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_company';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_company',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'id_country',
        'id_state',
        'id_city',
        'id_phone_code_company',
        'id_fax_code_company',
        'business_number',
        'phone_code_company',
        'phone_company',
        'fax_code_company',
        'fax_company',
        'company_profile_completion',
        'moderation_approved_at',
        'moderation_blocked_at',
        'moderation_unblocked_at',
        'moderation_noticed_at',
        'moderation_activity',
        'moderation_notices',
        'moderation_blocking',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_company'                      => Types::INTEGER,
        'id_user'                         => Types::INTEGER,
        'id_type'                         => Types::INTEGER,
        'id_country'                      => Types::INTEGER,
        'id_state'                        => Types::INTEGER,
        'id_city'                         => Types::INTEGER,
        'id_phone_code_company'           => Types::INTEGER,
        'id_fax_code_company'             => Types::INTEGER,
        'parent_company'                  => Types::INTEGER,
        'type_company'                    => CompanyTypeCast::class,
        'longitude'                       => Types::DECIMAL,
        'latitude'                        => Types::DECIMAL,
        'employees_company'               => Types::INTEGER,
        'revenue_company'                 => CustomTypes::SIMPLE_MONEY,
        'visible_company'                 => Types::BOOLEAN,
        'rating_count_company'            => Types::INTEGER,
        'rating_company'                  => Types::INTEGER,
        'registered_company'              => Types::DATETIME_IMMUTABLE,
        'updated_company'                 => Types::DATETIME_IMMUTABLE,
        'blocked'                         => Types::INTEGER,
        'accreditation'                   => Types::BOOLEAN,
        'profile_completed'               => Types::INTEGER,
        'video_company_pending'           => Types::BOOLEAN,
        'company_profile_completion'      => Types::JSON,
        'company_profile_updated_options' => Types::BOOLEAN,
        'company_profile_updated'         => Types::DATETIME_IMMUTABLE,
        'moderation_is_approved'          => Types::BOOLEAN,
        'moderation_approved_at'          => Types::DATETIME_IMMUTABLE,
        'moderation_blocked_at'           => Types::DATETIME_IMMUTABLE,
        'moderation_unblocked_at'         => Types::DATETIME_IMMUTABLE,
        'moderation_noticed_at'           => Types::DATETIME_IMMUTABLE,
        'moderation_activity'             => Types::JSON,
        'moderation_notices'              => Types::JSON,
        'moderation_blocking'             => Types::JSON,
        'is_featured'                     => Types::BOOLEAN,
    ];

    /**
     * Scope query for id user.
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'id_user',
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope query by user ids.
     */
    protected function scopeUsersIds(QueryBuilder $builder, array $usersIds): void
    {
        if (empty($usersIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in('id_user', array_map(
                fn ($i, $userId) => $builder->createNamedParameter((int) $userId, ParameterType::INTEGER, $this->nameScopeParameter("userId{$i}")),
                array_keys($usersIds),
                $usersIds
            ))
        );
    }

    /**
     * Scope a query by company type.
     */
    protected function scopeType(QueryBuilder $builder, CompanyType $type): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'type_company',
                $builder->createNamedParameter((string) $type, ParameterType::STRING, $this->nameScopeParameter('type_company', true))
            )
        );
    }

    /**
     * Scope a query by id.
     */
    protected function scopeId(QueryBuilder $builder, int $companyId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->getPrimaryKey(),
                $builder->createNamedParameter($companyId, ParameterType::INTEGER, $this->nameScopeParameter('companyId'))
            )
        );
    }

    /**
     * Scope by company blocked.
     *
     * @var int
     */
    protected function scopeBlocked(QueryBuilder $builder, int $isBlockedCompany): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "blocked",
                $builder->createNamedParameter($isBlockedCompany, ParameterType::INTEGER, $this->nameScopeParameter('companyBlocked'))
            )
        );
    }

    /**
     * Scope by company visibility.
     *
     * @var int
     */
    protected function scopeVisible(QueryBuilder $builder, int $isVisibleCompany): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "visible_company",
                $builder->createNamedParameter($isVisibleCompany, ParameterType::INTEGER, $this->nameScopeParameter('companyVisible'))
            )
        );
    }

    /**
     * Scope a query by the branch or not.
     */
    protected function scopeIsBranch(QueryBuilder $builder, bool $isBranch): void
    {
        if ($isBranch) {
            $this->scopeType($builder, CompanyType::BRANCH());
        } else {
            $this->scopeType($builder, CompanyType::COMPANY());
        }
    }

    /**
     * Scope a query to filter by text search.
     */
    protected function scopeSearch(QueryBuilder $builder, string $text): void
    {
        $this->appendSearchConditionsToQuery(
            $builder,
            $text,
            ['name_company'],
            ['name_company'],
        );
    }

    /**
     * Relation with the user.
     */
    protected function type(): RelationInterface
    {
        return $this->belongsTo(Seller_Company_Types_Model::class, 'id_type')->enableNativeCast();
    }

    /**
     * Relation with the user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_user')->enableNativeCast();
    }

    /**
     * Relation with industries.
     */
    protected function ownerGroup(): RelationInterface
    {
        return $this->hasOneThrough(
            User_Groups_Model::class,
            Users_Model::class,
            'idu',
            'idgroup',
            'id_user',
            'user_group',
        );
    }

    /**
     * Relation with the branches.
     */
    protected function branches(): RelationInterface
    {
        return $this->hasMany(Seller_Companies_Model::class, 'parent_company')->enableNativeCast();
    }

    /**
     * Relation with the parent.
     */
    protected function parent(): RelationInterface
    {
        return $this->belongsTo(Seller_Companies_Model::class, 'parent_company')->enableNativeCast();
    }

    /**
     * Relation with the partners.
     */
    protected function partners(): RelationInterface
    {
        /** @var RelationInterface $relation */
        $relation = $this->hasMany(B2b_Partners_Model::class, 'id_company')->enableNativeCast();
        $realtedrepository = $relation->getRelated();
        $partnerRelation = $realtedrepository->getRelation('partner');
        $partnerRepository = $partnerRelation->getRelated();
        $partnerTableAlias = sprintf('%s__b2b_partners', $partnerRepository->getTable());
        $realtedrepository->mergeCasts($partnerRepository->getCasts());
        $queryBuilder = $relation->getQuery();
        $queryBuilder
            ->select($relation->getExistenceCompareKey(), "{$partnerTableAlias}.*")
            ->leftJoin(
                $relation->getRelated()->getTable(),
                $partnerRepository->getTable(),
                $partnerTableAlias,
                "{$partnerRelation->getQualifiedParentKey()} = {$partnerTableAlias}.{$partnerRelation->getParentKey()}"
            )
        ;

        return $relation;
    }

    /**
     * Relation with industries.
     */
    protected function industries(): RelationInterface
    {
        return $this->hasManyThrough(
            Categories_Model::class,
            Seller_Companies_Industries_Pivot_Model::class,
            'id_company',
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
            Seller_Companies_Categories_Pivot_Model::class,
            'id_company',
            'category_id',
            $this->getPrimaryKey(),
            'id_category'
        );
    }

    /**
     * Relation with country.
     */
    protected function country(): RelationInterface
    {
        return $this->belongsTo(Countries_Model::class, 'id_country');
    }

    /**
     * Relation with state/region.
     */
    protected function state(): RelationInterface
    {
        return $this->belongsTo(States_Model::class, 'id_state');
    }

    /**
     * Relation with city.
     */
    protected function city(): RelationInterface
    {
        return $this->belongsTo(Cities_Model::class, 'id_city');
    }

    /**
     * Relation with phone code.
     */
    protected function phoneCode(): RelationInterface
    {
        return $this->belongsTo(Phone_Codes_Model::class, 'id_phone_code_company');
    }

    /**
     * Relation with fax code.
     */
    protected function faxCode(): RelationInterface
    {
        return $this->belongsTo(Phone_Codes_Model::class, 'id_fax_code_company');
    }
}

// End of file seller_companies_model.php
// Location: /tinymvc/myapp/models/seller_companies_model.php
