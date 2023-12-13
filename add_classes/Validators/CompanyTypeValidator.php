<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;
use Company_Model;

final class CompanyTypeValidator extends Validator
{
    /**
     * The user group of the company owner.
     *
     * @var int
     */
    private $userGroup;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        int $userGroup,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->userGroup = $userGroup;
        $this->messagesList = new ParameterBag(array_merge($this->messages(), $messages ?? array()));

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $messages = $this->getMessages();
        return [
            [
                'field' => $this->getFields()->get('type') ?? 'type',
                'label' => $this->getLabels()->get('type'),
                'rules' => [
                    'required' => '',
                    function ($attr, $value, $fail) use ($messages) {
                        if (empty($value)) {
                            return;
                        }

                        $type = model(Company_Model::class)->get_company_type($value);
                        if (empty($type)) {
                            $fail(sprintf($messages->get('type.unknown'), $attr));
                        }

                        $allowedGroups = empty($type['allowed_user_groups']) ? [] : json_decode($type['allowed_user_groups'], true);
                        if (!in_array($this->userGroup, $allowedGroups)) {
                            $fail(sprintf($messages->get('type.unacceptable'), $attr));
                        }
                    },
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'type' => 'Type',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'type.unknown'         => translate('validation_company_unknown_type_value', ['{{FIELD_NAME}}' => '%s']),
            'type.unacceptable'    => translate('validation_company_type_unacceptable_value', ['{{FIELD_NAME}}' => '%s']),
        ];
    }
}
