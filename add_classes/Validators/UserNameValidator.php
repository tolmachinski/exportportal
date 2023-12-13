<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * @author Anton Zencenco
 */
final class UserNameValidator extends Validator
{
    public const MIN_NAME_LENGTH = 2;
    public const MAX_NAME_LENGTH = 50;

    /**
     * The minimum length of the name.
     */
    protected int $minNameLength;

    /**
     * The maximum length of the name.
     */
    protected int $maxNameLength;

    /**
     * The list of the countries.
     */
    protected array $countries;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        int $minNameLength = self::MIN_NAME_LENGTH,
        int $maxNameLength = self::MAX_NAME_LENGTH,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->minNameLength = $minNameLength;
        $this->maxNameLength = $maxNameLength;

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $messages = $this->getMessages();
        $labels = $this->getLabels();
        $fields = $this->getFields();

        return [
            [
                'field' => $fields->get('firstName'),
                'label' => $labels->get('firstName'),
                'rules' => $this->getFirstNameRules($messages, $this->minNameLength, $this->maxNameLength),
            ],
            [
                'field' => $fields->get('lastName'),
                'label' => $labels->get('lastName'),
                'rules' => $this->getLastNameRules($messages, $this->minNameLength, $this->maxNameLength),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'firstName' => 'first_name',
            'lastName'  => 'last_name',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'firstName' => 'First Name',
            'lastName'  => 'Last Name',
        ];
    }

    /**
     * Get the first name field validation rules.
     */
    protected function getFirstNameRules(ParameterBag $messages, int $minLength, int $maxLength): array
    {
        return [
            'required'              => $messages->get('firstName.required') ?? '',
            "min_len[{$minLength}]" => $messages->get('firstName.minLength') ?? '',
            "max_len[{$maxLength}]" => $messages->get('firstName.maxLength') ?? '',
            'valid_user_name'       => $messages->get('firstName.valid') ?? '',
        ];
    }

    /**
     * Get the last name field validation rules.
     */
    protected function getLastNameRules(ParameterBag $messages, int $minLength, int $maxLength): array
    {
        return [
            'required'              => $messages->get('lastName.required') ?? '',
            "min_len[{$minLength}]" => $messages->get('lastName.minLength') ?? '',
            "max_len[{$maxLength}]" => $messages->get('lastName.maxLength') ?? '',
            'valid_user_name'       => $messages->get('lastName.valid') ?? '',
        ];
    }
}
