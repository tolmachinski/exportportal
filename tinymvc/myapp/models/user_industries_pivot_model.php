<?php

declare(strict_types=1);

use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * User_Industries_Pivot model.
 */
final class User_Industries_Pivot_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'users_relation_industries';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'USER_INDUSTRIES_PIVOT';

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
        'id_user'        => Types::INTEGER,
        'id_industry'    => Types::INTEGER,
        'order_relation' => Types::INTEGER,
    ];

    /**
     * Scope query for user.
     */
    protected function scopeUser(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_user'),
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('user_id'))
            )
        );
    }

    /**
     * Scope query for not user.
     */
    protected function scopeNotUser(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                $this->qualifyColumn('id_user'),
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('not_user_id'))
            )
        );
    }

    /**
     * Scope query for industry.
     */
    protected function scopeIndustry(QueryBuilder $builder, int $industryId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_industry'),
                $builder->createNamedParameter($industryId, ParameterType::INTEGER, $this->nameScopeParameter('industry_id'))
            )
        );
    }

    /**
     * Scope query for not industry.
     */
    protected function scopeNotIndustry(QueryBuilder $builder, int $industryId): void
    {
        $builder->andWhere(
            $builder->expr()->neq(
                $this->qualifyColumn('id_industry'),
                $builder->createNamedParameter($industryId, ParameterType::INTEGER, $this->nameScopeParameter('not_industry_id'))
            )
        );
    }

    /**
     * Scope query for order.
     */
    protected function scopeOrder(QueryBuilder $builder, int $order): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('order_relation'),
                $builder->createNamedParameter($order, ParameterType::INTEGER, $this->nameScopeParameter('order'))
            )
        );
    }

    /**
     * Relation with user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_user');
    }

    /**
     * Relation with category.
     */
    protected function industry(): RelationInterface
    {
        return $this->belongsTo(Categories_Model::class, 'id_industry');
    }
}

// End of file user_industries_pivot_model.php
// Location: /tinymvc/myapp/models/user_industries_pivot_model.php
