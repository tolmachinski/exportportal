<?php

declare(strict_types=1);

namespace App\Validators;

use TinyMVC_Library_validator;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\NestedValidationData;
use Symfony\Component\HttpFoundation\ParameterBag;

class EventAgendaValidator extends Validator
{
    protected const INCOMING_DATE_FORMAT = 'm/d/Y H:i';

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return [
            'description' => [
                'field' => $fields->get('description'),
                'label' => $labels->get('description'),
                'rules' => $this->getDescriptionRules($messages),
            ],
            'dateStart' => [
                'field' => $fields->get('dateStart'),
                'label' => $labels->get('dateStart'),
                'rules' => $this->getDateStartRules($messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'description'       => 'description',
            'dateStart'         => 'date_start',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'description'       => 'Description',
            'dateStart'         => 'Start date',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'description.wrongFieldsCount'  => translate('validation_ep_event_agenda_wrong_fields_count'),
            'startDate.invalidFormat'       => translate('validation_ep_event_agenda_start_date_wrong_format'),
            'description.wrongData'         => translate('validation_ep_event_agenda_description_wrong_data', ['{{FIELD_NAME}}' => '%s']),
            'startDate.wrongData'           => translate('validation_ep_event_agenda_start_date_wrong_data', ['{{FIELD_NAME}}' => '%s']),
            'description.empty'             => translate('validation_ep_event_agenda_description_required'),
            'startDate.empty'               => translate('validation_ep_event_agenda_start_date_required'),
        ];
    }

    /**
     * Get the description validation rules.
     */
    protected function getDescriptionRules(ParameterBag $messages): array
    {
        return [
            function (string $attr, $descriptionFields, callable $fail, TinyMVC_Library_validator $validator) use ($messages) {
                if (null === $descriptionFields) {
                    return;
                }

                if (!$descriptionFields instanceof NestedValidationData) {
                    $fail(sprintf($messages->get('description.wrongData'), $attr));
                    return;
                }

                $descriptionFields = iterator_to_array($descriptionFields->getIterator());

                foreach ($descriptionFields as $descriptionField) {
                    if (empty($descriptionField)) {
                        $fail($messages->get('description.empty'));
                        return;
                    }
                }
            },
            function (string $attr, $descriptionFields, callable $fail, TinyMVC_Library_validator $validator) use ($messages) {
                $dateStartFields = $this->getValidationData()->get('date_start');
                $countDateStartFields = $dateStartFields instanceof NestedValidationData ? $dateStartFields->count() : count($dateStartFields);
                $countDescriptionFields = $descriptionFields instanceof NestedValidationData ? $descriptionFields->count() : count($descriptionFields);

                if ($countDateStartFields != $countDescriptionFields) {
                    $fail($messages->get('description.wrongFieldsCount'));
                }
            }
        ];
    }

    /**
     * Get the dateStart field validation rules.
     */
    protected function getDateStartRules(ParameterBag $messages): array
    {
        return [
            function (string $attr, $startDateFields, callable $fail, TinyMVC_Library_validator $validator) use ($messages) {
                if (null === $startDateFields) {
                    return;
                }

                if (!$startDateFields instanceof NestedValidationData) {
                    $fail(sprintf($messages->get('startDate.wrongData'), $attr));
                    return;
                }

                $startDateFields = iterator_to_array($startDateFields->getIterator());

                foreach ($startDateFields as $startDateField) {
                    if (empty($startDateField)) {
                        $fail($messages->get('startDate.empty'));
                        return;
                    }

                    if (!validateDate($startDateField, self::INCOMING_DATE_FORMAT)) {
                        $fail($messages->get('startDate.invalidFormat'));
                        return;
                    }
                }
            },
        ];
    }
}
