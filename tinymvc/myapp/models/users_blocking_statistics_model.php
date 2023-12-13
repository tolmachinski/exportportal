<?php

declare(strict_types=1);

use App\Common\Contracts\User\RestrictionType;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Users_Blocking_Statistics model.
 */
final class Users_Blocking_Statistics_Model extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var null|string
     */
    protected const CREATED_AT = 'blocking_date';

    /**
     * The name of the "cancel_date" column.
     *
     * @var null|string
     */
    protected const CANCELED_AT = 'cancel_date';

    /**
     * The name of the "updated at" column.
     *
     * @var null|string
     */
    protected const UPDATED_AT = null;

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'users_blocking_statistics';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'USERS_BLOCKING_STATISTICS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id',
        self::CREATED_AT,
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                => Types::INTEGER,
        'id_user'           => Types::INTEGER,
        self::CREATED_AT    => Types::DATETIME_IMMUTABLE,
        self::CANCELED_AT   => Types::DATETIME_IMMUTABLE,
        'type'              => RestrictionType::class,
    ];

    /**
     * Scope a query by the user id
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
     * Scope a query by the restriction Type
     *
     * @param QueryBuilder $builder
     * @param RestrictionType $restrictionType
     *
     * @return void
     */
    protected function scopeRestrictionType(QueryBuilder $builder, RestrictionType $restrictionType): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "{$this->getTable()}.`type`",
                $builder->createNamedParameter($restrictionType, ParameterType::STRING, $this->nameScopeParameter('restrictionType'))
            )
        );
    }

    /**
     * Scope query by users ids.
     */
    protected function scopeUsersIds(QueryBuilder $builder, array $usersIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->getTable()}`.`id_user`",
                array_map(
                    fn ($index, $userId) => $builder->createNamedParameter(
                        (int) $userId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("userID{$index}")
                    ),
                    array_keys($usersIds),
                    $usersIds
                )
            )
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

// End of file users_blocking_statistics_model.php
// Location: /tinymvc/myapp/models/users_blocking_statistics_model.php
