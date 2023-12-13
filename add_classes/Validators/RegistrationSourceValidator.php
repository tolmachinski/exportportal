<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Contracts\User\UserSourceType;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;
use ValueError;

/**
 * @author Anton Zencenco
 */
class RegistrationSourceValidator extends Validator
{
    /**
     * The maximum length value.
     */
    private int $maxLength;

    /**
     * Creates the validator.
     */
    public function __construct(
        ValidatorAdapter $validator,
        int $maxLength = 500,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->maxLength = $maxLength;

        parent::__construct($validator, $messages, $labels, $fields);
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
                'field' => $fields->get('type'),
                'label' => $labels->get('type'),
                'rules' => $this->getTypeRules($messages),
            ],
            [
                'field' => $fields->get('info'),
                'label' => $labels->get('info'),
                'rules' => $this->getInfoRules($messages, $this->maxLength),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'type' => 'find_type',
            'info' => 'find_info',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'type' => 'Type',
            'info' => 'Source information',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'type.invalid' => 'The type field contains invalid value.',
        ];
    }

    /**
     * Get the type field validation rules.
     */
    protected function getTypeRules(ParameterBag $messages): array
    {
        return [
            'required' => '',
            function (string $attr, $value, callable $fail) use ($messages) {
                if (null === $value) {
                    return;
                }

                try {
                    UserSourceType::from($value);
                } catch (ValueError $e) {
                    $fail($messages->get('type.invalid') ?? '');
                }
            },
        ];
    }

    /**
     * Get the source information rules field validation rules.
     */
    protected function getInfoRules(ParameterBag $messages, int $maxLength): array
    {
        return [
            "max_len[{$maxLength}]" => $messages->get('info.maxLength') ?? '',
        ];
    }
}
