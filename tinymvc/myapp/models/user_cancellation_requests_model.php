<?php

declare(strict_types=1);

use App\Common\Contracts\Cancel\CancellationRequestStatus;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * User_Cancellation_Requests model.
 */
final class User_Cancellation_Requests_Model extends Model
{
    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = 'start_date';

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'update_date';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'user_close_requests';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'USER_CLOSE_REQUESTS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'idreq';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'idpack',
        self::CREATED_AT,
        self::UPDATED_AT,
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'feedback',
        'confirmation_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'idreq'       => Types::INTEGER,
        'user'        => Types::INTEGER,
        'close_date'  => Types::DATE_IMMUTABLE,
        'start_date'  => Types::DATETIME_IMMUTABLE,
        'update_date' => Types::DATETIME_IMMUTABLE,
        'status'      => CancellationRequestStatus::class,
    ];

    /**
     * Scope query by user id.
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                $this->qualifyColumn('user'),
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('user_id'))
            )
        );
    }

    /**
     * Scope event by request statuses.
     */
    protected function scopeUsersIds(QueryBuilder $builder, array $usersIds): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                $this->qualifyColumn('user'),
                array_map(
                    fn ($index, $userId) => $builder->createNamedParameter(
                        (int) $userId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("users_ids_{$index}")
                    ),
                    array_keys($usersIds),
                    $usersIds
                )
            )
        );
    }

    /**
     * Scope a query to filter by request by status.
     */
    protected function scopeStatus(QueryBuilder $builder, CancellationRequestStatus $status)
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
     * Scope event by request statuses.
     *
     * @param CancellationRequestStatus[] $statuses
     */
    protected function scopeStatuses(QueryBuilder $builder, array $statuses): void
    {
        $builder->andWhere(
            $builder->expr()->in(
                $this->qualifyColumn('status'),
                array_map(
                    fn ($index, $status) => $builder->createNamedParameter(
                        $this->castAttributeToDatabaseValue('status', $status),
                        ParameterType::STRING,
                        $this->nameScopeParameter("status_{$index}")
                    ),
                    array_keys($statuses),
                    $statuses
                )
            )
        );
    }

    /**
     * Relation with user.
     */
    protected function user(): RelationInterface
    {
        return $this->belongsTo(Users_Model::class, 'user');
    }
}

// End of file user_cancellation_requests_model.php
// Location: /tinymvc/myapp/models/user_cancellation_requests_model.php
