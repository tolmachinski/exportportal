<?php

declare(strict_types=1);

use App\Common\Contracts\Calendar\NotificationType;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;

/**
 * Calendar_Notifications model.
 */
final class Calendar_Notifications_Model extends Model
{
    /**
     * {@inheritdoc}
     */
    protected string $table = 'calendar_notifications';

    /**
     * {@inheritdoc}
     */
    protected string $alias = 'CALENDAR_NOTIFICATIONS';

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
        'sending_errors',
    ];

    /**
     * The attributes that should be cast.
     */
    protected array $casts = [
        'id'                => Types::INTEGER,
        'calendar_id'       => Types::INTEGER,
        'count_days'        => Types::INTEGER,
        'notification_type' => NotificationType::class,
        'notification_date' => Types::DATE_IMMUTABLE,
        'created_date'      => Types::DATETIME_IMMUTABLE,
        'is_sent'           => Types::BOOLEAN,
    ];

    /**
     * Scope query by notifications ids.
     */
    protected function scopeIds(QueryBuilder $builder, array $notificationsIds): void
    {
        if (empty($notificationsIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->table}`.`id`",
                array_map(
                    fn ($index, $notificationId) => $builder->createNamedParameter(
                        (int) $notificationId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("notificationID_{$index}")
                    ),
                    array_keys($notificationsIds),
                    $notificationsIds
                )
            )
        );
    }

    /**
     * Scope query for calendar id.
     */
    protected function scopeCalendarId(QueryBuilder $builder, int $calendarId): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`calendar_id`",
                $builder->createNamedParameter($calendarId, ParameterType::INTEGER, $this->nameScopeParameter('calendarId'))
            )
        );
    }

    /**
     * Scope query by calendar ids.
     */
    protected function scopeCalendarIds(QueryBuilder $builder, array $calendarIds): void
    {
        if (empty($calendarIds)) {
            return;
        }

        $builder->andWhere(
            $builder->expr()->in(
                "`{$this->table}`.`calendar_id`",
                array_map(
                    fn ($index, $calendarId) => $builder->createNamedParameter(
                        (int) $calendarId,
                        ParameterType::INTEGER,
                        $this->nameScopeParameter("calendarID_{$index}")
                    ),
                    array_keys($calendarIds),
                    $calendarIds
                )
            )
        );
    }

    /**
     * Scope query for notification state.
     */
    protected function scopeIsSent(QueryBuilder $builder, bool $isSent): void
    {
        $builder->andWhere(
            $builder->expr()->eq(
                "`{$this->table}`.`is_sent`",
                $builder->createNamedParameter($isSent, ParameterType::BOOLEAN, $this->nameScopeParameter('isSent'))
            )
        );
    }

    /**
     * Scope query for notification state.
     */
    protected function scopeNotificationDateLte(QueryBuilder $builder, DateTimeInterface $notificationDate): void
    {
        $builder->andWhere(
            $builder->expr()->lte(
                "`{$this->table}`.`notification_date`",
                $builder->createNamedParameter($notificationDate->format('Y-m-d'), ParameterType::STRING, $this->nameScopeParameter('notificationDateLte'))
            )
        );
    }

    /**
     * Relation with the calendar.
     */
    protected function calendar(): RelationInterface
    {
        return $this->belongsTo(Calendar_Events_Model::class, 'calendar_id')->enableNativeCast();
    }
}

// End of file calendar_notifications_model.php
// Location: /tinymvc/myapp/models/calendar_notifications_model.php
