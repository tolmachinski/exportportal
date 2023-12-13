<?php

declare(strict_types=1);

use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Buyer companies model.
 */
final class Buyer_Companies_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'company_buyer';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'BUYER_COMPANIES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'company_id_country',
        'company_id_state',
        'company_id_city',
        'company_phone_code_id',
        'company_phone_code',
        'company_phone',
        'company_fax_code_id',
        'company_fax_code',
        'company_fax',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                    => Types::INTEGER,
        'id_user'               => Types::INTEGER,
        'id_type'               => Types::INTEGER,
        'company_id_country'    => Types::INTEGER,
        'company_id_state'      => Types::INTEGER,
        'company_id_city'       => Types::INTEGER,
        'company_phone_code_id' => Types::INTEGER,
        'company_fax_code_id'   => Types::INTEGER,
    ];

    /**
     * Scope query for user ID.
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
                'id_user',
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
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
            ['company_name'],
            ['company_name'],
        );
    }

    /**
     * Relation with the buyer.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_user')->enableNativeCast();
    }
}

// End of file seller_companies_model.php
// Location: /tinymvc/myapp/models/seller_companies_model.php
