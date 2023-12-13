<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

class ReasonValidator extends Validator
{
    protected const MAX_REASON_LENGTH = 500;

    /**
     * The maximum reason length.
     */
    protected int $maxReasonLength;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        int $maxReasonLength = self::MAX_REASON_LENGTH,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->maxReasonLength = $maxReasonLength;

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

        return [
            'reason' => [
                'field' => $fields->get('reason'),
                'label' => $labels->get('reason'),
                'rules' => $this->getReasonRules($this->maxReasonLength, $messages),
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
     * Get the reason field validation rules.
     */
    protected function getReasonRules(int $maxLength, ParameterBag $messages): array
    {
        return [
            'required'              => $messages->get('reason.required') ?? '',
            "max_len[{$maxLength}]" => $messages->get('reason.maxLength') ?? '',
        ];
    }
}
