<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;
use App\Common\Validation\Legacy\Standalone\Validator;

class PartnerValidator extends Validator
{
    protected const MAX_NAME_LENGTH = 100;
    protected $isActionAddPartner;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        bool $isActionAddPartner = true,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->isActionAddPartner = $isActionAddPartner;

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
            'name' => [
                'field' => $fields->get('name'),
                'label' => $labels->get('name'),
                'rules' => $this->getNameRules(static::MAX_NAME_LENGTH, $messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'name'  => 'name',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'name'  => 'Name',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Get the name field validation rules.
     */
    protected function getNameRules(int $maxLength, ParameterBag $messages): array
    {
        return [
            'required'              => $messages->get('name.required') ?? '',
            "max_len[{$maxLength}]" => $messages->get('name.maxLength') ?? '',
        ];
    }
}
