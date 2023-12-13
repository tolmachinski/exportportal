<?php

declare(strict_types=1);

use App\Common\Database\Concerns;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Shipper companies model.
 */
final class Shipper_Companies_Model extends Model
{
    use Concerns\CanSearch;

    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'create_date';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'orders_shippers';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'SHIPPERS_COMPANIES';

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
        'id_phone_code',
        'phone_code',
        'phone',
        'id_fax_code',
        'fax_code',
        'fax',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                => Types::INTEGER,
        'id_user'           => Types::INTEGER,
        'id_country'        => Types::INTEGER,
        'id_state'          => Types::INTEGER,
        'id_city'           => Types::INTEGER,
        'offices_number'    => Types::INTEGER,
        'co_teu'            => Types::BIGINT,
        'id_phone_code'     => Types::INTEGER,
        'id_fax_code'       => Types::INTEGER,
        'visible'           => Types::BOOLEAN,
        'create_date'       => Types::DATETIME_IMMUTABLE,
        'accreditation'     => Types::BOOLEAN,
        'profile_completed' => Types::BOOLEAN,
    ];

    /**
     * Scope query for ID.
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
     * Scope query by user ids
     *
     * @param QueryBuilder $builder
     * @param array $usersIds
     */
    protected function scopeUsersIds(QueryBuilder $builder, array $usersIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id_user`",
                array_map(
                    fn ($i, $userId) => $builder->createNamedParameter((int) $userId, ParameterType::INTEGER, $this->nameScopeParameter("userId{$i}")),
                    array_keys($usersIds),
                    $usersIds
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
            ['co_name'],
            ['co_name'],
        );
    }

    /**
     * Relation with the user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'id_user')->enableNativeCast();
    }
}

// End of file seller_companies_model.php
// Location: /tinymvc/myapp/models/seller_companies_model.php
