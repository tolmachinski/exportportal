<?php

declare(strict_types=1);

namespace App\Validators;

use Closure;
use Country_Model;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

class DownloadableMaterialsValidator extends Validator
{
    const MIN_NAME_SIZE = 2;
    const MAX_NAME_SIZE = 50;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
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

        return array(
            'email' => array(
                'field' => $fields->get('email'),
                'label' => $labels->get('email'),
                'rules' => $this->getEmailRules($messages),
            ),
            'fname' => array(
                'field' => $fields->get('fname'),
                'label' => $labels->get('fname'),
                'rules' => $this->getNameRules($messages),
            ),
            'lname' => array(
                'field' => $fields->get('lname'),
                'label' => $labels->get('lname'),
                'rules' => $this->getNameRules($messages),
            ),
            'country' => array(
                'field' => $fields->get('country'),
                'label' => $labels->get('country'),
                'rules' => $this->getCountryRules($messages),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return array(
            'email'   => 'email',
            'fname'   => 'fname',
            'lname'   => 'lname',
            'country' => 'country',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return array(
            'email'   => 'Email',
            'fname'   => 'Fname',
            'lname'   => 'Lname',
            'country' => 'Country',
        );
    }

    /**
     * Get the email validation rule.
     */
    protected function getEmailRules(ParameterBag $messages): array
    {
        return array(
            'required'               => $messages->get('email.required') ?? '',
            'no_whitespaces'         => $messages->get('email.noWhitespaces') ?? '',
            'valid_email'            => $messages->get('email.validEmail') ?? '',
        );
    }

    /**
     * Get the name validation rules.
     */
    protected function getNameRules(ParameterBag $messages): array
    {
        return array(
            'required'               => $messages->get('name.required') ?? '',
            'valid_user_name'        => $messages->get('name.validName') ?? '',
            "min_len[" . self::MIN_NAME_SIZE . "]"  => $messages->get('name.minLength') ?? '',
            "max_len[" . self::MAX_NAME_SIZE . "]"  => $messages->get('name.maxLength') ?? '',
        );
    }

    /**
     * Get the country validation rules.
     */
    protected function getCountryRules(ParameterBag $messages): array
    {
        return array(
            'required' => $messages->get('name.required') ?? '',
            function (string $attr, $value, Closure $fail) use ($messages) {
                if (null !== $value && !model(Country_Model::class)->has_country($value)) {
                    $fail($messages->get('country.valid', ''));
                }
            },
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'country.valid' => 'The country does not appear to be valid. Please choose the correct one from the list.',
        ];
    }
}
