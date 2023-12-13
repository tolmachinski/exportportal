<?php

declare(strict_types=1);

namespace App\Services;

use App\Common\Contracts\Calendar\EventType;
use Calendar_Events_Model;
use Calendar_Notifications_Model;
use ExportPortal\Bridge\Notifier\Notification\SystemNotification;
use ExportPortal\Bridge\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\NotifierInterface;

final class CalendarEpEventsService
{
    /**
     * The calendar events model
     *
     * @var Calendar_Events_Model
     */
    private $calendarEventsModel;

    /**
     * The calendar notifications model
     *
     * @var Calendar_Notifications_Model
     */
    private $calendarNotificationsModel;

    /**
     * The notifier
     *
     * @var null|NotifierInterface
     */
    private $notifier;

    /**
     * Creates the instance of the service.
     */
    public function __construct(Calendar_Events_Model $calendarEventsModel, Calendar_Notifications_Model $calendarNotificationsModel, ?NotifierInterface $notifier = null)
    {
        $this->calendarEventsModel = $calendarEventsModel;
        $this->calendarNotificationsModel = $calendarNotificationsModel;
        $this->notifier = $notifier;
    }

    /**
     * @var int $eventId - ep event id (online, offline or webinar)
     * @var array $oldVersionEvent - ep event before update
     * @var array $updatedEvent - updated version of ep event
     *
     * @return void
     */
    public function actualizeEvent(int $eventId, array $oldVersionEvent, array $updatedEvent): void
    {
        if (
            $oldVersionEvent['title'] === $updatedEvent['title']
            && $oldVersionEvent['start_date'] === $updatedEvent['start_date']
            && $oldVersionEvent['end_date'] === $updatedEvent['end_date']
            && $oldVersionEvent['is_published'] === $updatedEvent['is_published']
        ) {
            return;
        }

        $calendarEvents = $this->calendarEventsModel->findAllBy([
            'scopes' => [
                'eventType' => EventType::EP_EVENTS(),
                'sourceId'  => $eventId,
            ],
        ]);

        //nothing to update in the calendar
        if (empty($calendarEvents)) {
            return;
        }

        //update records from calendar only if event is published
        if ($updatedEvent['is_published']) {
            $this->calendarEventsModel->updateMany([
                    'title'      => $updatedEvent['title'],
                    'start_date' => (new \DateTimeImmutable())->createFromFormat('Y-m-d H:i:s', $updatedEvent['start_date']),
                    'end_date'   => (new \DateTimeImmutable())->createFromFormat('Y-m-d H:i:s', $updatedEvent['end_date']),
                ],
                [
                    'scopes' => [
                        'eventType' => EventType::EP_EVENTS(),
                        'sourceId'  => $eventId,
                    ],
                ]
            );
        }

        //if event start date was changed, than need to update calendar notifications dates
        if ($oldVersionEvent['start_date'] !== $updatedEvent['start_date'] || $oldVersionEvent['is_published'] !== $updatedEvent['is_published']) {
            $oldStartEventDate = (new \DateTimeImmutable())->createFromFormat('Y-m-d H:i:s', $oldVersionEvent['start_date']);
            $newStartEventDate = (new \DateTimeImmutable())->createFromFormat('Y-m-d H:i:s', $updatedEvent['start_date']);

            //send notification to users about changes of event start date (only for published date)
            if ($updatedEvent['is_published'] && $oldStartEventDate->format('Y-m-d') !== $newStartEventDate->format('Y-m-d')) {
                $updatedEvent['id'] = $eventId;

                $this->notifier->send(
                    new SystemNotification('update_ep_event_from_calendar', [
                        '[eventTitle]'   => cleanOutput($updatedEvent['title']),
                        '[eventPage]'    => sprintf('<a href="%s">event\'s page</a>', getEpEventDetailUrl($updatedEvent)),
                        '[calendarPage]' => sprintf('<a href="%s">calendar page</a>', __SITE_URL . 'calendar/my'),
                    ]),
                    ...array_map(fn($userId) => (new Recipient((int) $userId)), array_column($calendarEvents, 'user_id'))
                );
            }

            $calendarNotifications = $this->calendarNotificationsModel->findAllBy([
                'scopes' => [
                    'calendarIds' => array_column($calendarEvents, 'id')
                ],
            ]);

            if (empty($calendarNotifications)) {
                return;
            }

            //Instead of updating each row individually, we delete the old records,
            // and write new ones with the necessary changes in one query
            $updatedCalendarNotifications = [];
            $currentDate = (new \DateTimeImmutable())->setTime(0, 0, 0, 0);

            foreach ($calendarNotifications as $notification) {
                $notificationDate = $newStartEventDate->sub(new \DateInterval("P{$notification['count_days']}D"));
                //If the date of sending is less than the current one, we mark the notification as sent
                $notification['is_sent'] = ($notificationDate < $currentDate) || !$updatedEvent['is_published'];
                $notification['notification_date'] = $notificationDate;
                $notification['sending_errors'] = null;

                $updatedCalendarNotifications[] = $notification;
            }

            //remove old notifications
            $this->calendarNotificationsModel->deleteAllBy([
                'scopes' => [
                    'calendarIds' => array_column($calendarEvents, 'id')
                ],
            ]);

            //add updated notifications
            $this->calendarNotificationsModel->insertMany($updatedCalendarNotifications);
        }
    }
}
