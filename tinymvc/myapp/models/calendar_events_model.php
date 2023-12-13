<?php

declare(strict_types=1);

use App\Common\Contracts\Calendar\EventType;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Calendar_Events model.
 */
final class Calendar_Events_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'calendar_events';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'CALENDAR_EVENTS';

    /**
     * {@inheritdoc}
     */
    protected $primaryKey = 'id';

    /**
     * {@inheritdoc}
     */
    protected array $guarded = [
        'id',
        'created_date',
    ];

    /**
     * The attributes that are nullable.
     */
    protected array $nullable = [
        'source_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'            => Types::INTEGER,
        'user_id'       => Types::INTEGER,
        'source_id'     => Types::INTEGER,
        'event_type'    => EventType::class,
        'start_date'    => Types::DATETIME_IMMUTABLE,
        'end_date'      => Types::DATETIME_IMMUTABLE,
        'created_date'  => Types::DATETIME_IMMUTABLE,
    ];

    /**
     * Scope query for calendar event type.
     */
    protected function scopeEventType(QueryBuilder $builder, EventType $eventType): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`event_type`",
                $builder->createNamedParameter((string) $eventType, ParameterType::STRING, $this->nameScopeParameter('eventType'))
            )
        );
    }

    /**
     * Scope query for calendar user id.
     */
    protected function scopeUserId(QueryBuilder $builder, int $userId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`user_id`",
                $builder->createNamedParameter($userId, ParameterType::INTEGER, $this->nameScopeParameter('userId'))
            )
        );
    }

    /**
     * Scope query for calendar source id.
     */
    protected function scopeSourceId(QueryBuilder $builder, int $sourceId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`source_id`",
                $builder->createNamedParameter($sourceId, ParameterType::INTEGER, $this->nameScopeParameter('sourceId'))
            )
        );
    }

    /**
     * Scope query for calendar sources ids.
     */
    protected function scopeSourcesIds(QueryBuilder $builder, array $sourcesIds): void
    {
        if (empty($sourcesIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->table}`.`source_id`",
                array_map(
                    fn ($index, $sourceId) => $builder->createNamedParameter(
                        (int) $sourceId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("sourceId_{$index}")
                    ),
                    array_keys($sourcesIds),
                    $sourcesIds
                )
            )
        );
    }

    /**
     * Scope query for calendar event start date lte.
     */
    protected function scopeStartDateLte(QueryBuilder $builder, DateTimeInterface $startDate): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "DATE(`{$this->table}`.`start_date`)",
                $builder->createNamedParameter($startDate->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('startDateLte'))
            ),
        );
    }

    /**
     * Scope query for calendar event end date gte.
     */
    protected function scopeEndDateGte(QueryBuilder $builder, DateTimeInterface $endDate): void
    {
        $builder->andWhere(
            $builder->expr()->gte(
                "DATE(`{$this->table}`.`end_date`)",
                $builder->createNamedParameter($endDate->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('endDateGte'))
            ),
        );
    }

    /**
     * Resolves static relationships with comments.
     */
    protected function notifications(): RelationInterface
    {
        $relation = $this->hasMany(Calendar_Notifications_Model::class, 'calendar_id')->enableNativeCast();

        $relation->getQuery()
            ->addOrderBy('count_days', 'DESC')
            ->addOrderBy('notification_type')
        ;

        return $relation;
    }
}

// End of file calendar_events_model.php
// Location: /tinymvc/myapp/models/calendar_events_model.php
