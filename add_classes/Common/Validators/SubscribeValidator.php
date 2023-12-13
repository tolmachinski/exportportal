<?php

declare(strict_types=1);

namespace App\Common\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use Symfony\Component\HttpFoundation\ParameterBag;

final class SubscribeValidator extends Validator
{
    protected const MAX_EMAIL_LENGTH = 100;

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
                'field' => $fields->get('email'),
                'label' => $labels->get('email'),
                'rules' => $this->getEmailRules($messages, static::MAX_EMAIL_LENGTH),
            ],
            [
                'field' => $fields->get('termsCond'),
                'label' => $labels->get('termsCond'),
                'rules' => $this->getTermsRules($messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'email'     => 'email',
            'termsCond' => 'terms_cond',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'email'     => 'Email',
            'termsCond' => 'Terms and Conditions',
        ];
    }

    /**
     * Get the email validation rule.
     */
    protected function getEmailRules(ParameterBag $messages, int $maxLength): array
    {
        return [
            'required'              => $messages->get('email.required') ?? '',
            'no_whitespaces'        => $messages->get('email.noWhitespaces') ?? '',
            'valid_email'           => $messages->get('email.validEmail') ?? '',
            "max_len[{$maxLength}]" => $messages->get('email.maxLength') ?? '',
        ];
    }

    /**
     * Get the Terms and Conditions validation rule.
     */
    protected function getTermsRules(ParameterBag $messages): array
    {
        return [
            'required' => $messages->get('termsCond.required') ?? '',
        ];
    }
}
