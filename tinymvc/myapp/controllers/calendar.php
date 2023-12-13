<?php

declare(strict_types=1);

use App\Common\Contracts\Calendar\EventType;
use App\Common\Contracts\Calendar\NotificationType;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Validators\CalendarEventNotificationsValidator;

/**
 * Controller Calendar
 */
class Calendar_Controller extends TinyMVC_Controller
{
    /**
     * Action for open dashboard page with the calendar
     */
    public function my()
    {
        checkPermision('have_calendar');

        views()->displayWebpackTemplate([
            'content'           => 'calendar/index_view',
        ]);
    }

    /**
     * Action for getting calendar events by ajax from the calendar dashboard page
     */
    public function ajax_get_calendar_events()
    {
        checkPermisionAjax('have_calendar');

        try {
            $calendarStartDate = (new \DateTimeImmutable())->createFromFormat('m/d/Y', request()->request->get('startDate'));
            //one month can contain max 42 day per page
            $calendarEndDate = $calendarStartDate->add(new \DateInterval('P42D'));
        } catch (\Throwable $th) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        /** @var Calendar_Events_Model $calendarEventsModel */
        $calendarEventsModel = model(Calendar_Events_Model::class);

        jsonResponse(
            '',
            'success',
            [
                'calendar' => $calendarEventsModel->findAllBy([
                    'scopes' => [
                        'startDateLte' => $calendarEndDate,
                        'endDateGte'   => $calendarStartDate,
                        'userId'       => id_session(),
                    ],
                ])
            ]
        );
    }

    /**
     * Action for open popup with notifications settings calendar/open_notifications_settings?calendar=${event.id}
     * Action for open popup with notifications settings calendar/open_notifications_settings?type=ep_events&source=${event.sourceId}
     */
    public function open_notifications_settings()
    {
        checkIsAjax();
        checkPermisionAjaxModal('have_calendar');

        $query = request()->query;
        $userId = id_session();
        $now = new DateTimeImmutable();

        /** @var Calendar_Events_Model $calendarEventsModel */
        $calendarEventsModel = model(Calendar_Events_Model::class);

        //calendarId is mandatory field for edit notifications action
        if (!empty($calendarId = $query->getInt('calendar'))) {
            $calendar = $calendarEventsModel->findOne($calendarId, [
                'scopes' => [
                    'userId' => $userId,
                ],
                'with'   => [
                    'notifications',
                ],
            ]);

            if (empty($calendar)) {
                messageInModal(translate('systmess_error_invalid_data'));
            }

            //check if is a past event
            if ($calendar['end_date'] < $now) {
                messageInModal(translate('systmess_error_edit_calendar_notifications_event_is_past'));
            }

            $remainingDaysBeforeStart = (int) $now->diff($calendar['start_date'])->format('%r%a');

            if ($remainingDaysBeforeStart < 1) {
                messageInModal(translate('systmess_error_open_calendar_notifications_not_available'));
            }

            //the last notification can be sent no later than one day before the start
            $validationRules = [
                'minDays' => $remainingDaysBeforeStart < 1 ? 0 : 1,
                'maxDays' => $remainingDaysBeforeStart < 1 ? 0 : $remainingDaysBeforeStart,
            ];

            $calendar['notifications'] = null === $calendar['notifications'] ? [] : $calendar['notifications']->toArray();
        } else {
            //eventType and sourceId are mandatory fields for adding an event to the calendar
            if (empty($eventType = $query->get('type')) || null === EventType::tryFrom($eventType)) {
                messageInModal(translate('systmess_error_invalid_data'));
            }

            if (empty($sourceId = $query->getInt('source'))) {
                messageInModal(translate('systmess_error_invalid_data'));
            }

            $calendar = $calendarEventsModel->findOneBy([
                'scopes' => [
                    'eventType' => EventType::tryFrom($eventType),
                    'sourceId'  => $sourceId,
                    'userId'    => $userId,
                ],
            ]);

            if (!empty($calendar)) {
                messageInModal(translate('systmess_error_add_to_calendar_already_exist'), 'info');
            }

            switch ($eventType) {
                case EventType::EP_EVENTS:
                    /** @var Elasticsearch_Ep_Events_Model $elasticsearchEventsModel */
                    $elasticsearchEventsModel = model(Elasticsearch_Ep_Events_Model::class);
                    if (empty($epEvent = array_shift($elasticsearchEventsModel->getEvents(['id' => $sourceId])))) {
                        messageInModal(translate('systmess_error_invalid_data'));
                    }

                    $eventEndDate = (new \DateTimeImmutable())->createFromFormat('Y-m-d H:i:s', $epEvent['end_date']);
                    if ($eventEndDate < (new \DateTimeImmutable())) {
                        messageInModal(translate('systmess_error_add_to_calendar_past_ep_event'));
                    }

                    $eventStartdate = (new \DateTimeImmutable())->createFromFormat('Y-m-d H:i:s', $epEvent['start_date']);
                    $remainingDaysBeforeStart = (int) $now->diff($eventStartdate)->format('%r%a');

                    //the last notification can be sent no later than one day before the start
                    $validationRules = [
                        'minDays' => $remainingDaysBeforeStart < 1 ? 0 : 1,
                        'maxDays' => $remainingDaysBeforeStart < 1 ? 0 : $remainingDaysBeforeStart,
                    ];

                    break;
            }
        }

        views(
            'new/calendar/notifications_settings_view',
            array_filter(
                [
                'remainingDaysBeforeStart' => $remainingDaysBeforeStart,
                'validationRules'          => $validationRules ?? null,
                'formAction'               => empty($calendar) ? __SITE_URL . 'calendar/add' : __SITE_URL . 'calendar/edit_notifications',
                'eventType'                => $eventType ?? null,
                'sourceId'                 => $sourceId ?? null,
                'calendar'                 => $calendar ?? null,
                ],
                fn ($value) => null !== $value
            )
        );
    }

    /**
     * Action for getting popup with calendar event details and some actions buttons
     */
    public function get_calendar_event_details()
    {
        checkIsAjax();
        checkPermisionAjaxModal('have_calendar');

        if (empty($calendarId = (int) uri()->segment(3))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        /** @var Calendar_Events_Model $calendarEventsModel */
        $calendarEventsModel = model(Calendar_Events_Model::class);

        $calendar = $calendarEventsModel->findOne($calendarId);
        if (empty($calendar) || id_session() != $calendar['user_id']) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $eventDetailsData = ['calendar' => $calendar];

        switch ($calendar['event_type']) {
            case EventType::EP_EVENTS():
                /** @var Elasticsearch_Ep_Events_Model $elasticsearchEventsModel */
                $elasticsearchEventsModel = model(Elasticsearch_Ep_Events_Model::class);

                $epEvent = array_shift($elasticsearchEventsModel->getEvents(['id' => $calendar['source_id']]));

                $eventDetailsData['calendar']['source'] = $epEvent;
                $eventDetailsData['calendar']['short_description'] = $epEvent['short_description'] ?: '';
                $eventDetailsData['calendar']['isUnpublishedEvent'] = empty($epEvent);
                $eventDetailsData['calendar']['previewUrl'] = getEpEventDetailUrl($epEvent ?: []);

                if (!empty($epEvent['country'])) {
                    $eventDetailsData['calendar']['location'] = [
                        'country' => [
                            'id'   => $epEvent['country']['id'],
                            'name' => $epEvent['country']['name'],
                        ],
                        'state'   => [
                            'id'   => $epEvent['state']['id'],
                            'name' => $epEvent['state']['name'],
                        ],
                        'city'    => [
                            'id'   => $epEvent['city']['id'],
                            'name' => $epEvent['city']['name'],
                        ],
                    ];
                }

                break;
        }

        if ($calendar['start_date']->format('Y') !== $calendar['end_date']->format('Y')) {
            $eventDetailsData['calendar']['duration'] = "{$calendar['start_date']->format('j M, Y')} - {$calendar['end_date']->format('j M, Y')}";
        } elseif ($calendar['start_date']->format('n') !== $calendar['end_date']->format('n')) {
            $eventDetailsData['calendar']['duration'] = "{$calendar['start_date']->format('j M')} - {$calendar['end_date']->format('j M, Y')}";
        } elseif ($calendar['start_date']->format('j') !== $calendar['end_date']->format('j')) {
            $eventDetailsData['calendar']['duration'] = "{$calendar['start_date']->format('j')}-{$calendar['end_date']->format('j M, Y')}";
        } else {
            $eventDetailsData['calendar']['duration'] = $calendar['start_date']->format('j M, Y');
        }

        jsonResponse('', 'success', [
            'content' => views()->fetch('new/calendar/details_view', $eventDetailsData),
        ]);
    }

    /**
     * Action to save a calendar event to the DB by ajax
     */
    public function add()
    {
        checkIsAjax();
        checkPermisionAjax('have_calendar');

        /** @var Calendar_Events_Model $calendarEventsModel */
        $calendarEventsModel = model(Calendar_Events_Model::class);

        $request = request()->request;
        $eventType = $request->get('type');

        switch ($eventType) {
            case EventType::EP_EVENTS:
                /** @var Elasticsearch_Ep_Events_Model $elasticsearchEventsModel */
                $elasticsearchEventsModel = model(Elasticsearch_Ep_Events_Model::class);

                if (
                    empty($epEventId = $request->getInt('source'))
                    || empty($epEvent = array_shift($elasticsearchEventsModel->getEvents(['id' => $epEventId])))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $eventEndDate = (new \DateTimeImmutable())->createFromFormat('Y-m-d H:i:s', $epEvent['end_date']);
                $eventStartDate = (new \DateTimeImmutable())->createFromFormat('Y-m-d H:i:s', $epEvent['start_date']);
                if ($eventEndDate < (new \DateTimeImmutable())) {
                    jsonResponse(translate('systmess_error_add_to_calendar_past_ep_event'));
                }

                $userId = id_session();

                if (!empty($calendarEventsModel->findOneBy([
                    'scopes' => [
                        'eventType' => EventType::EP_EVENTS(),
                        'sourceId'  => $epEventId,
                        'userId'    => $userId,
                    ],
                ]))) {
                    jsonResponse(translate('systmess_error_add_to_calendar_already_exist'), 'info');
                }

                //region validation
                $now = new DateTimeImmutable();
                $remainingDaysBeforeStart = (int) $now->diff($eventStartDate)->format('%r%a');
                $adapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
                $validator = new CalendarEventNotificationsValidator($adapter, $remainingDaysBeforeStart);

                if (!$validator->validate($request->all())) {
                    jsonResponse(
                        \array_map(
                            fn (ConstraintViolation $violation) => $violation->getMessage(),
                            \iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }
                //endregion validation

                $calendarEvent = [
                    'event_type' => EventType::EP_EVENTS(),
                    'start_date' => $eventStartDate,
                    'source_id'  => $epEventId,
                    'end_date'   => $eventEndDate,
                    'user_id'    => $userId,
                    'title'      => $epEvent['title'],
                ];

                break;

            default:
                jsonResponse(translate('systmess_error_invalid_data'));

                break;
        }

        if (empty($calendarId = $calendarEventsModel->insertOne($calendarEvent))) {
            jsonResponse(translate('systmess_internal_server_error'));
        }

        /** @var Calendar_Notifications_Model $calendarNotificationsModel */
        $calendarNotificationsModel = model(Calendar_Notifications_Model::class);

        if (!empty($notifications = array_values((array) $request->get('notifications')))) {
            $notificationTypes = array_values((array) $request->get('types'));

            $notificationsData = [];
            foreach ($notifications as $notificationKey => $countDays) {
                $notificationDate = $calendarEvent['start_date']->sub(new \DateInterval("P{$countDays}D"));

                $notificationsData[] = [
                    'calendar_id'       => $calendarId,
                    'count_days'        => (int) $countDays,
                    'notification_type' => NotificationType::tryFrom($notificationTypes[$notificationKey]),
                    'notification_date' => $notificationDate,
                    'is_sent'           => false,
                ];
            }

            $calendarNotificationsModel->insertMany($notificationsData);
        }

        jsonResponse(translate('systmess_success_add_to_calendar_ep_event'), 'success');
    }

    /**
     * Action to remove a calendar event from the DB by ajax
     */
    public function remove()
    {
        checkIsAjax();
        checkPermisionAjax('have_calendar');

        $request = request()->request;
        $eventType = EventType::tryFrom($request->get('type'));
        if (null === $eventType || empty($sourceId = $request->getInt('source'))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        /** @var Calendar_Events_Model $calendarEventsModel */
        $calendarEventsModel = model(Calendar_Events_Model::class);

        if (empty($calendarEvent = $calendarEventsModel->findOneBy([
            'scopes' => [
                'eventType' => $eventType,
                'sourceId'  => $sourceId,
                'userId'    => id_session(),
            ],
        ]))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $calendarEventsModel->deleteOne($calendarEvent['id']);

        jsonResponse(translate('systmess_success_remove_event_from_calendar'), 'success');
    }

    /**
     * Action to edite  calendar event notifications by ajax
     */
    public function edit_notifications()
    {
        checkIsAjax();
        checkPermisionAjax('have_calendar');

        $request = request()->request;
        $userId = id_session();

        /** @var Calendar_Events_Model $calendarEventsModel */
        $calendarEventsModel = model(Calendar_Events_Model::class);

        if (
            empty($calendarId = $request->getInt('calendar'))
            || empty($calendar = $calendarEventsModel->findOne($calendarId, ['with' => ['notifications']]))
            || $calendar['user_id'] !== $userId
        ) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        //region validation
        $now = new DateTimeImmutable();
        $remainingDaysBeforeStart = (int) $now->diff($calendar['start_date'])->format('%r%a');

        if ($remainingDaysBeforeStart < 1) {
            jsonResponse(translate('systmess_error_open_calendar_notifications_not_available'));
        }

        $adapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
        $validator = new CalendarEventNotificationsValidator($adapter, $remainingDaysBeforeStart);

        if (!$validator->validate($request->all())) {
            jsonResponse(
                \array_map(
                    fn (ConstraintViolation $violation) => $violation->getMessage(),
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }
        //endregion validation

        /** @var Calendar_Notifications_Model $calendarNotificationsModel */
        $calendarNotificationsModel = model(Calendar_Notifications_Model::class);

        $calendarNotificationsModel->deleteAllBy([
            'scopes' => [
                'calendarId' => $calendarId,
            ],
        ]);

        if (empty($notifications = array_values((array) $request->get('notifications')))) {
            jsonResponse(translate('systmess_success_edit_calendar_event_notifications'), 'success');
        }

        $notificationTypes = array_values((array) $request->get('types'));

        $notificationsData = [];
        foreach ($notifications as $notificationKey => $countDays) {
            $notificationDate = $calendar['start_date']->sub(new \DateInterval("P{$countDays}D"));

            $notificationsData[] = [
                'calendar_id'       => $calendarId,
                'count_days'        => (int) $countDays,
                'notification_type' => NotificationType::tryFrom($notificationTypes[$notificationKey]),
                'notification_date' => $notificationDate,
                'is_sent'           => false,
            ];
        }

        $calendarNotificationsModel->insertMany($notificationsData);

        jsonResponse(translate('systmess_success_edit_calendar_event_notifications'), 'success');
    }
}

// End of file calendar.php
// Location: /tinymvc/myapp/controllers/calendar.php
