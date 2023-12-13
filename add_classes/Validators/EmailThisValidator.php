<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

class EmailThisValidator extends Validator
{
    protected $maxMessageLength;
    protected $maxAllowedEmails;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        int $maxAllowedEmails,
        int $maxMessageLength = 1000,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->maxMessageLength = $maxMessageLength;
        $this->maxAllowedEmails = $maxAllowedEmails;

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
            'emails' => [
                'field' => $fields->get('emails'),
                'label' => $labels->get('emails'),
                'rules' => $this->getEmailsRules($messages, $this->maxAllowedEmails),
            ],
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
            'emails'  => 'emails',
            'message' => 'message',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'emails' => 'Emails',
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

    /**
     * Get the email validation rule.
     */
    protected function getEmailsRules(ParameterBag $messages, int $maxEmails): array
    {
        return [
            [
                "max_emails_count[{$maxEmails}]" => '',
                'no_whitespaces'                 => '',
                'valid_emails'                   => '',
                'required'                       => '',
            ]
        ];
    }
}
