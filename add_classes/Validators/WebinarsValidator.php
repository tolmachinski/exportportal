<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use Symfony\Component\HttpFoundation\ParameterBag;

class WebinarsValidator extends Validator
{
    protected const INCOMING_DATE_FORMAT = 'm/d/Y H:i';
    protected const MAX_REASON_LENGTH = 255;

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return [
            'title' => [
                'field' => $fields->get('title'),
                'label' => $labels->get('title'),
                'rules' => $this->getTitleRules(static::MAX_REASON_LENGTH, $messages),
            ],
            'startDate' => [
                'field' => $fields->get('startDate'),
                'label' => $labels->get('startDate'),
                'rules' => $this->getStartDateRules($messages, $fields),
            ],
            'link' => [
                'field' => $fields->get('link'),
                'label' => $labels->get('link'),
                'rules' => $this->getLinkRules(static::MAX_REASON_LENGTH, $messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'title'     => 'title',
            'startDate' => 'start_date',
            'link'      => 'link',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'title'     => 'Title',
            'startDate' => 'Start date',
            'link'      => 'Link',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return array(
            'startDate.invalidFormat' => translate('validation_invalid_date_format')
        );
    }

   /**
     * Get the Description validation rule.
     */
    protected function getTitleRules(int $maxLength, ParameterBag $messages): array
    {
        return array(
            "required"              => $messages->get('title.required') ?? '',
            "max_len[{$maxLength}]" => $messages->get('title.maxLength') ?? '',
        );
    }

    /**
     * Get the Description validation rule.
     */
    protected function getLinkRules(int $maxLength, ParameterBag $messages): array
    {
        return array(
            "valid_url"             => $messages->get('link.valid_url') ?? '',
            "max_len[{$maxLength}]" => $messages->get('link.maxLength') ?? '',
        );
    }

    /**
     * Get the dateStart field validation rules.
     */
    protected function getStartDateRules(ParameterBag $messages, ParameterBag $fields): array
    {
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

}
