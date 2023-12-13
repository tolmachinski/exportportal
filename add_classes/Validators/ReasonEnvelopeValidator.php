<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use Symfony\Component\HttpFoundation\ParameterBag;

class ReasonEnvelopeValidator extends Validator
{
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
                'field' => $fields->get('reason'),
                'label' => $labels->get('reason'),
                'rules' => $this->getReasonRules($messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'reason' => 'reason',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'reason' => 'Reason',
        ];
    }

    /**
     * Get the description validation rule.
     */
    protected function getReasonRules(ParameterBag $messages, int $maxLength = 500): array
    {
        return [
            'required'              => $messages->get('reason.required') ?? '',
            "max_len[{$maxLength}]" => $messages->get('reason.maxLength') ?? '',
        ];
    }
}
