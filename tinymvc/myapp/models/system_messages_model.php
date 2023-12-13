<?php

declare(strict_types=1);

use App\Common\Contracts\Notification\NotificationCategory;
use App\Common\Contracts\Notification\NotificationType;
use App\Common\Database\Model;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * System_Messages model.
 */
final class System_Messages_Model extends Model
{
    /**
     * The name of the "created at" column.
     */
    protected const CREATED_AT = null;

    /**
     * The name of the "updated at" column.
     */
    protected const UPDATED_AT = 'date_modified';

    /**
     * Indicates if model uses timestamps.
     */
    protected bool $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected string $table = 'systmessages';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'SYSTMESSAGES';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'idmess';

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = [
        'idmess',
        self::UPDATED_AT,
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'triggered_actions',
        'date_proofreading',
        'is_proofread',
        'module',
        'log',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'idmess'            => Types::INTEGER,
        'module'            => Types::INTEGER,
        'log'               => Types::JSON,
        'mess_type'         => NotificationCategory::class,
        'type'              => NotificationType::class,
        'date_proofreading' => Types::DATETIME_IMMUTABLE,
        'date_modified'     => Types::DATETIME_IMMUTABLE,
        'date_changed'      => Types::DATETIME_IMMUTABLE,
        'is_proofread'      => Types::BOOLEAN,
        'is_not_used'       => Types::BOOLEAN,
        'edited'            => Types::BOOLEAN,
        'to_delete'         => Types::BOOLEAN,
    ];

    /**
     * Scope query for message code.
     */
    protected function scopeCode(QueryBuilder $builder, string $code): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                'mess_code',
                $builder->createNamedParameter($code, ParameterType::STRING, $this->nameScopeParameter('code'))
            )
        );
    }

    /**
     * Scope query for message codes.
     *
     * @param string[] $codes
     */
    protected function scopeCodes(QueryBuilder $builder, array $codes): void
    {
        if (empty($codes)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in('mess_code', array_map(
                fn (int $i, $alias) => $builder->createNamedParameter($alias, ParameterType::STRING, $this->nameScopeParameter("code_{$i}")),
                array_keys(array_values($codes)),
                $codes
            ))
        );
    }
}

// End of file system_messages_model.php
// Location: /tinymvc/myapp/models/system_messages_model.php
