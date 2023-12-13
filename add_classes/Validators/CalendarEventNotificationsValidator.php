<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Contracts\Calendar\NotificationType;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\ValidationDataInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Validator as LegacyValidator;

class CalendarEventNotificationsValidator extends Validator
{
    /**
     * The calendar event
     *
     * @var int
     */
    protected $remainingDaysBeforeEventStart;

    /**
     * @var int
     */
    protected const MAX_NOTIFICATIONS_COUNT = 6;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        int $remainingDaysBeforeEventStart = 0,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->remainingDaysBeforeEventStart = $remainingDaysBeforeEventStart;

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        $remainingDaysBeforeEventStart = $this->remainingDaysBeforeEventStart;

        return [
            'email' => [
                'field' => $fields->get('notifications'),
                'label' => $labels->get('notifications'),
                'rules' => $this->getNotificationsRules(static::MAX_NOTIFICATIONS_COUNT, $remainingDaysBeforeEventStart, $messages, $fields),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'notificationsTypes' => 'types',
            'notifications'      => 'notifications',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'notifications' => 'Notifications',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'notifications.wrongFormat'        => translate('systmess_validation_edit_calendar_notifications_wrong_data_format'),
            'notifications.limitExceeded'      => translate('systmess_error_validation_calendar_notifications_limit_exceeded', ['{{MAX_NOTIFICATIONS_COUNT}}' => static::MAX_NOTIFICATIONS_COUNT]),
            'notificationsTypes.wrongFormat'   => translate('systmess_validation_edit_calendar_notifications_wrong_types_data_format'),
            'notifications.wrongCounters'      => translate('systmess_error_invalid_data'),
            'notifications.notNatural'         => translate('systmess_error_invalid_data'),
            'notifications.lessThanMinimum'    => translate('systmess_validation_edit_calendar_notifications_less_than_minimum', [
                '{{NOTIFICATION_COUNT_DAYS}}' => '%d',
                '{{MIN_COUNT_DAYS}}'          => '%d',
            ]),
            'notifications.greaterThanMaximum' => translate('systmess_validation_edit_calendar_notifications_greater_than_maximum', [
                '{{NOTIFICATION_COUNT_DAYS}}' => '%d',
                '{{MAX_COUNT_DAYS}}'          => '%d',
            ]),
            'notificationsTypes.wrongValue'    => translate('systmess_validation_edit_calendar_notifications_invalid_data', [
                '{{{{COUNT_DAYS}}}}' => '%s',
            ]),
            'notificationsTypes.emptyType'     => translate('systmess_validation_edit_calendar_notifications_empty_type', [
                '{{COUNT_DAYS}}' => '%s',
            ]),
            'notifications.haveDuplicates'     => translate('systmess_validation_edit_calendar_notifications_have_dauplicates'),
        ];
    }

    /**
     * Get the email validation rule.
     */
    protected function getNotificationsRules(int $maxNotificationsCount, int $remainingDaysBeforeEventStart, ParameterBag $messages, ParameterBag $fields): array
    {
        return [
            function (string $attr, $notifications, callable $fail, LegacyValidator $validator) use ($maxNotificationsCount, $remainingDaysBeforeEventStart, $messages, $fields) {
                /** @var ValidationDataInterface */
                $notificationsTypes = $this->getValidationData()->get($fields->get('notificationsTypes'));

                //check if there are notifications
                if (null === $notifications && null === $notificationsTypes) {
                    return true;
                }

                //checking the data integrity of notifications
                if (!is_a($notifications, ValidationDataInterface::class)) {
                    $fail($messages->get('notifications.wrongFormat', ''));

                    return false;
                }

                //checking the data integrity of notifications types
                if (!is_a($notificationsTypes, ValidationDataInterface::class)) {
                    $fail($messages->get('notificationsTypes.wrongFormat', ''));

                    return false;
                }

                //check if the notification limit has not been exceeded
                if ($notifications->count() > $maxNotificationsCount) {
                    $fail($messages->get('notifications.limitExceeded', ''));

                    return false;
                }

                //check if the number of notifications matches the number of notification types
                if ($notifications->count() !== $notificationsTypes->count()) {
                    $fail($messages->get('notifications.wrongCounters', ''));

                    return false;
                }

                $maxCountDays = $remainingDaysBeforeEventStart < 1 ? 0 : $remainingDaysBeforeEventStart;
                $minCountDays = $remainingDaysBeforeEventStart < 1 ? 0 : 1;

                //check if notifications are within the time range before the event starts
                $notifications = array_values(iterator_to_array($notifications->getIterator()));
                $notificationsData = [];
                foreach ($notifications as $countDays) {
                    $notificationsData[] = [
                        'countDays' => $countDays,
                    ];

                    if (!$validator->is_natural_no_zero($countDays)) {
                        $fail($messages->get('notifications.notNatural', ''));

                        continue;
                    }

                    if (!$validator->min($countDays, $minCountDays)) {
                        $fail(sprintf($messages->get('notifications.lessThanMinimum', ''), (int) $countDays, $minCountDays));

                        continue;
                    }

                    if (!$validator->max($countDays, $maxCountDays)) {
                        $fail(sprintf($messages->get('notifications.greaterThanMaximum', ''), (int) $countDays, $maxCountDays));

                        continue;
                    }
                }

                //check notifications types
                $notificationsTypes = array_values(iterator_to_array($notificationsTypes->getIterator()));
                foreach ($notificationsTypes as $key => $notificationsType) {
                    $notificationsData[$key]['type'] = $notificationsType;
                    $notificationsData[$key]['alias'] = "{$notificationsType}-{$notificationsData[$key]['countDays']}";

                    if (empty($notificationsType)) {
                        $fail(sprintf($messages->get('notificationsTypes.emptyType'), $notificationsData[$key]['countDays']));

                        continue;
                    }

                    if (null === NotificationType::tryFrom($notificationsType)) {
                        $fail(sprintf($messages->get('notificationsTypes.wrongValue', ''), $notificationsData[$key]['countDays']));

                        continue;
                    }
                }

                //check notification duplicates
                if (count($notificationsData) !== count(array_unique(array_column($notificationsData, 'alias')))) {
                    $fail($messages->get('notifications.haveDuplicates', ''));
                }
            },
        ];
    }
}
