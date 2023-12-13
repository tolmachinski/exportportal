<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;

class DraftExtendRequestValidator extends Validator
{

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        \DateTime $minDate,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->minDate = $minDate;

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }



    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return array(
            'extend_date'   => 'extend_date',
            'extend_reason' => 'extend_reason'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return array(
            'extend_date'   => 'Extend until',
            'extend_reason' => 'Reason'
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return array(
            'extend_date.minDate'          => translate('validation_min_date'),
            'extend_date.maxDate'          => translate('validation_max_date'),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return [
            [
                'field' => $fields->get('extend_date'),
                'label' => $labels->get('extend_date'),
                'rules' => $this->getDateRules($this->minDate, $messages),
            ],[
                'field' => $fields->get('extend_reason'),
                'label' => $labels->get('extend_reason'),
                'rules' => ['required'=> '', 'max_len[500]' => ''],
            ]
        ];
    }

    protected function getDateRules($minDate, $messages): array
    {
        return [
            'required' => '',
            'valid_date[m/d/Y]' => '',
            function (string $attr, $value, callable $fail) use ($messages, $minDate) {
                $valueDate = new \DateTime($value);
                if (
                    !empty($value)
                    && $valueDate->format('m/d/Y') < $minDate->format('m/d/Y')
                ) {
                    $fail(sprintf($messages->get('extend_date.minDate'), $attr, $minDate->format('m/d/Y')));
                }
            },
            function (string $attr, $value, callable $fail) use ($messages, $minDate) {
                $valueDate = new \DateTime($value);
                $maxDate = $minDate->add(new \DateInterval('P30D'));
                if (
                    !empty($value)
                    && $valueDate->format('m/d/Y') > $maxDate->format('m/d/Y')
                ) {
                    $fail(sprintf($messages->get('extend_date.maxDate'), $attr, $maxDate->format('m/d/Y')));
                }
            },
        ];
    }
}
