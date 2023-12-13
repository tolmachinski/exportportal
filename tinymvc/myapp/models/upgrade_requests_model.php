<?php

declare(strict_types=1);

use App\Common\Contracts\Upgrade\UpgradeRequestStatus;
use App\Common\Contracts\Upgrade\UpgradeRequestType;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Upgrade_Requests model.
 */
final class Upgrade_Requests_Model extends Model
{
    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'date_created';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'date_updated';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'upgrade_request';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'UPGRADE_REQUESTS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id_request';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'id_request',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'id_user',
        'id_bill',
        'id_package',
        'date_expire',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id_request'   => Types::INTEGER,
        'id_user'      => Types::INTEGER,
        'id_package'   => Types::INTEGER,
        'id_bill'      => Types::INTEGER,
        'status'       => UpgradeRequestStatus::class,
        'type'         => UpgradeRequestType::class,
        'date_created' => Types::DATETIME_IMMUTABLE,
        'date_updated' => Types::DATETIME_IMMUTABLE,
        'date_expire'  => Types::DATE_IMMUTABLE,
    ];

    /**
     * Scope a query to filter by request by user ID.
     */
    protected function scopeUser(QueryBuilder $builder, int $userId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_user'),
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('user_id'))
            )
        );
    }

    /**
     * Scope a query to filter by request by package ID.
     */
    protected function scopePackage(QueryBuilder $builder, int $packageId)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('id_package'),
                $builder->createNamedParameter($packageId, ParameterType::INTEGER, $this->nameScopeParameter('package_id'))
            )
        );
    }

    /**
     * Scope a query to filter by expiration.
     */
    protected function scopeIsExpired(QueryBuilder $builder, bool $isExpired)
    {
        if ($isExpired) {
            $builder->andWhere(
                $builder->expr()->and(
                    $builder->expr()->isNotNull($this->qualifyColumn('date_expire')),
                    $builder->expr()->lte(
                        $this->qualifyColumn('date_expire'),
                        $this->castAttributeToDatabaseValue('date_expire', new \DateTimeImmutable()),
                    )
                )
            );
        } else {
            $builder->andWhere(
                $builder->expr()->or(
                    $builder->expr()->isNull($this->qualifyColumn('date_expire')),
                    $builder->expr()->gt(
                        $this->qualifyColumn('date_expire'),
                        $this->castAttributeToDatabaseValue('date_expire', new \DateTimeImmutable()),
                    )
                )
            );
        }
    }

    /**
     * Scope a query to filter by request by status.
     */
    protected function scopeStatus(QueryBuilder $builder, UpgradeRequestStatus $status)
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
     * Scope a query to filter by request by statuses.
     *
     * @param UpgradeRequestStatus[] $statuses
     */
    protected function scopeStatuses(QueryBuilder $builder, array $statuses)
    {
        if (empty($statuses)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                $this->qualifyColumn('status'),
                array_map(
                    fn (int $i, UpgradeRequestStatus $status) => $builder->createNamedParameter(
                        $this->castAttributeToDatabaseValue('status', $status),
                        ParameterType::STRING,
                        $this->nameScopeParameter("status_{$i}")
                    ),
                    array_keys($statuses),
                    $statuses
                )
            )
        );
    }

    /**
     * Scope a query to filter by request by type.
     */
    protected function scopeType(QueryBuilder $builder, UpgradeRequestType $type)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('type'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('type', $type),
                    ParameterType::STRING,
                    $this->nameScopeParameter('type')
                )
            )
        );
    }

    /**
     * Scope a query to filter by request by types.
     *
     * @param UpgradeRequestType[] $types
     */
    protected function scopeTypes(QueryBuilder $builder, array $types)
    {
        if (empty($types)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                $this->qualifyColumn('type'),
                array_map(
                    fn (int $i, UpgradeRequestType $type) => $builder->createNamedParameter(
                        $this->castAttributeToDatabaseValue('type', $type),
                        ParameterType::STRING,
                        $this->nameScopeParameter("type_{$i}")
                    ),
                    array_keys($types),
                    $types
                )
            )
        );
    }

    /**
     * Scope a query to filter by request by creation date.
     */
    protected function scopeCreatedAt(QueryBuilder $builder, DateTimeImmutable $createdAt)
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('date_created'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_created', $createdAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('created_at')
                )
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime from.
     */
    protected function scopeCreatedFrom(QueryBuilder $builder, DateTimeImmutable $createdAt)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                $this->qualifyColumn('date_created'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_created', $createdAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('created_from')
                )
            )
        );
    }

    /**
     * Scope a query to filter by creation datetime to.
     */
    protected function scopeCreatedTo(QueryBuilder $builder, DateTimeImmutable $createdAt)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                $this->qualifyColumn('date_created'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_created', $createdAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('created_to')
                )
            )
        );
    }

    /**
     * Scope a query to filter by last updated datetime from.
     */
    protected function scopeUpdatedFrom(QueryBuilder $builder, DateTimeImmutable $updatedAt)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                $this->qualifyColumn('date_updated'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_updated', $updatedAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('updated_from')
                )
            )
        );
    }

    /**
     * Scope a query to filter by last updated datetime to.
     */
    protected function scopeUpdatedTo(QueryBuilder $builder, DateTimeImmutable $updatedAt)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                $this->qualifyColumn('date_updated'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_updated', $updatedAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('updated_to')
                )
            )
        );
    }

    /**
     * Scope a query to filter by last updated datetime from.
     */
    protected function scopeExpireFrom(QueryBuilder $builder, DateTimeInterface $expireAt)
    {
        $builder->andWhere(
            $builder->expr()->gte(
                $this->qualifyColumn('date_expire'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_expire', $expireAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('expired_from')
                )
            )
        );
    }

    /**
     * Scope a query to filter by last updated datetime to.
     */
    protected function scopeExpireTo(QueryBuilder $builder, DateTimeInterface $expireAt)
    {
        $builder->andWhere(
            $builder->expr()->lte(
                $this->qualifyColumn('date_expire'),
                $builder->createNamedParameter(
                    $this->castAttributeToDatabaseValue('date_expire', $expireAt),
                    ParameterType::STRING,
                    $this->nameScopeParameter('expired_to')
                )
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
     * Relation with package.
     */
    protected function package(): RelationInterface
    {
        return $this->belongsTo(Upgrade_Packages_Model::class, 'id_package');
    }
}

// End of file upgrade_requests_model.php
// Location: /tinymvc/myapp/models/upgrade_requests_model.php
