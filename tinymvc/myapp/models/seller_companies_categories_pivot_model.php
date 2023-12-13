<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Seller_Companies_Categories_Pivot model.
 */
final class Seller_Companies_Categories_Pivot_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'company_relation_category';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'COMPANIES_CATEGORIES_PIVOT';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_relation';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_relation',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_relation'    => Types::INTEGER,
        'id_company'     => Types::INTEGER,
        'id_category'    => Types::INTEGER,
    ];

    /**
     * Scope query for company.
     */
    protected function scopeCompany(QueryBuilder $builder, int $companyId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_company'),
                $builder->createNamedParameter($companyId, ParameterType::INTEGER, $this->nameScopeParameter('company_id'))
            )
        );
    }

    /**
     * Scope query for not company.
     */
    protected function scopeNotCompany(QueryBuilder $builder, int $companyId): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                $this->qualifyColumn('id_company'),
                $builder->createNamedParameter($companyId, ParameterType::INTEGER, $this->nameScopeParameter('not_company_id'))
            )
        );
    }

    /**
     * Scope query for category.
     */
    protected function scopeCategory(QueryBuilder $builder, int $categoryId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_category'),
                $builder->createNamedParameter($categoryId, ParameterType::INTEGER, $this->nameScopeParameter('category_id'))
            )
        );
    }

    /**
     * Scope query for not category.
     */
    protected function scopeNotCategory(QueryBuilder $builder, int $categoryId): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                $this->qualifyColumn('id_category'),
                $builder->createNamedParameter($categoryId, ParameterType::INTEGER, $this->nameScopeParameter('not_category_id'))
            )
        );
    }

    /**
     * Relation with company.
     */
    protected function company(): RelationInterface
    {
        return $this->belongsTo(Seller_Companies_Model::class, 'id_company');
    }

    /**
     * Relation with category.
     */
    protected function category(): RelationInterface
    {
        return $this->belongsTo(Categories_Model::class, 'id_caregory');
    }
}

// End of file seller_companies_categories_pivot_model.php
// Location: /tinymvc/myapp/models/seller_companies_categories_pivot_model.php
