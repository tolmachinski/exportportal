<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

class ShareThisValidator extends Validator
{
    protected $maxMessageLength;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        int $maxMessageLength = 1000,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->maxMessageLength = $maxMessageLength;

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
            'message' => [
                'field' => $fields->get('message'),
                'label' => $labels->get('message'),
                'rules' => $this->getMessageRules($messages, $this->maxMessageLength),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'message' => 'message',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'message' => 'Message',
        ];
    }

    /**
     * Get the email validation rule.
     */
    protected function getMessageRules(ParameterBag $messages, int $maxLength): array
    {
        return [
            'required'              => '',
            "max_len[{$maxLength}]" => '',
        ];
    }
}
