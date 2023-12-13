<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

class EmailValidator extends Validator
{
    public function __construct(
        ValidatorAdapter $validatorAdapter,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    protected const MAX_EMAIL_LENGTH = 100;

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return array(
            'email' => array(
                'field' => $fields->get('email'),
                'label' => $labels->get('email'),
                'rules' => $this->getEmailRules($messages, static::MAX_EMAIL_LENGTH),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return array(
            'email' => 'email',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return array(
            'email' => 'email',
        );
    }

    /**
     * Get the email validation rule.
     */
    protected function getEmailRules(ParameterBag $messages, int $maxLength): array
    {
        return [
            'required'              => $messages->get('email.required') ?? '',
            'no_whitespaces'        => $messages->get('email.noWhitespaces') ?? '',
            "max_len[{$maxLength}]" => $messages->get('email.maxLength') ?? '',
            'valid_email'           => $messages->get('email.validEmail') ?? '',
        ];
    }
}
