<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use Symfony\Component\HttpFoundation\ParameterBag;

class PickOfTheMonthValidator extends Validator
{
    protected const INCOMING_DATE_FORMAT = 'm/d/Y';
    protected const MAX_REASON_LENGTH = 500;

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return [
            'comment' => [
                'field' => $fields->get('comment'),
                'label' => $labels->get('comment'),
                'rules' => $this->getCommentRules(static::MAX_REASON_LENGTH, $messages),
            ],
            'dateStart' => [
                'field' => $fields->get('dateStart'),
                'label' => $labels->get('dateStart'),
                'rules' => $this->getDateStartRules($messages, $fields),
            ],
            'dateEnd' => [
                'field' => $fields->get('dateEnd'),
                'label' => $labels->get('dateEnd'),
                'rules' => $this->getDateEndRules($messages, $fields),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'comment'    => 'comment',
            'dateStart'  => 'start_date',
            'dateEnd'    => 'end_date',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'comment'    => 'Comment',
            'dateStart'  => 'Start date',
            'dateEnd'    => 'End date',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return array(
            'startDate.invalidFormat'       => translate('validation_invalid_date_format'),
            'endDate.lessThanStartDate'     => translate('validation_start_date_greater_than_end_date'),
            'endDate.invalidFormat'         => translate('validation_invalid_date_format'),
        );
    }

   /**
     * Get the Description validation rule.
     */
    protected function getCommentRules(int $maxLength, ParameterBag $messages): array
    {
        return array(
            "max_len[{$maxLength}]"  => $messages->get('comment.maxLength') ?? '',
        );
    }

    /**
     * Get the dateStart field validation rules.
     */
    protected function getDateStartRules(ParameterBag $messages, ParameterBag $fields): array
    {
        $endDate = $fields->get('endDate') ?? 'end_date';
        return [
            'required'  => $messages->get('startDate.empty'),
            function (string $attr, $startDateField, callable $fail) use ($messages)
            {
                if (!validateDate($startDateField, self::INCOMING_DATE_FORMAT)) {
                    $fail($messages->get('startDate.invalidFormat'));
                    return;
                }
            },
        ];
    }

    /**
     * Get the dateStart field validation rules.
     */
    protected function getDateEndRules(ParameterBag $messages, ParameterBag $fields): array
    {
        $startDate = $fields->get('endDate') ?? 'start_date';
        return [
            'required'  => $messages->get('endDate.empty'),
            function (string $attr, $endDateField, callable $fail) use ($messages) {
                if (!validateDate($endDateField, self::INCOMING_DATE_FORMAT)) {
                    $fail($messages->get('endDate.invalidFormat'));
                    return;
                }
            },
            function (string $attr, $value, callable $fail) use ($messages, $startDate) {
                if (empty($value)) {
                    return;
                }
                if (empty($startDate)) {
                    return;
                }
                $startDate = new \DateTime($this->getValidationData()->get($startDate));
                $endDate = new \DateTime($value);
                if ($endDate < $startDate) {
                    $fail(sprintf($messages->get('endDate.lessThanStartDate'), $attr));
                }
            },
        ];
    }
}
