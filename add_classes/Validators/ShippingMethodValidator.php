<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Shipping_Types_Model;

class ShippingMethodValidator extends Validator
{
    public function __construct(
        ValidatorAdapter $validatorAdapter,
        $needValidateAlias = true,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->needValidateAlias = $needValidateAlias;
        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();

        return [
            'name' => [
                'field' => $fields->get('name'),
                'label' => $labels->get('name'),
                'rules' => $this->getNameRules(),
            ],
            'alias' => [
                'field' => $fields->get('alias'),
                'label' => $labels->get('alias'),
                'rules' => $this->getAliasRules(),
            ],
            'short_desc' => [
                'field'  => $fields->get('short_desc'),
                'label'  => $labels->get('short_desc'),
                'rules'  => $this->getShortDescRules(),
            ],
            'full_desc' => [
                'field' => $fields->get('full_desc'),
                'label' => $labels->get('full_desc'),
                'rules' => $this->getFullDescRules(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'name'       => 'name',
            'alias'      => 'alias',
            'short_desc' => 'short_desc',
            'full_desc'  => 'full_desc',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'name'       => 'Name',
            'alias'      => 'Alias',
            'short_desc' => 'Short Description',
            'full_desc'  => 'Full Description',
        ];
    }

    /**
     * Get the name validation rule.
     */
    protected function getNameRules(): array
    {
        return [
            'required'    => '',
            'max_len[50]' => '',
        ];
    }

    /**
     * Get the alias validation rule.
     */
    protected function getAliasRules(): array
    {
        return $this->needValidateAlias ? [
            'required'     => '',
            'min_len[2]'   => '',
            'max_len[100]' => '',
            function (string $attr, $alias, callable $fail) {
                if (empty($alias)) {
                    return;
                }

                if (!is_string($alias)) {
                    $fail(translate('systmess_error_invalid_data'));
                }

                /** @var Shipping_Types_Model $shippingTypesModel */
                $shippingTypesModel = model(Shipping_Types_Model::class);

                $shippingType = $shippingTypesModel->findOneBy([
                    'conditions' => [
                        'alias' => strtolower($alias),
                    ],
                ]);

                if (!empty($shippingType)) {
                    $fail(translate('systmess_error_alias_is_not_unique'));
                }
            },
        ] : [];
    }

    /**
     * Get the short desc validation rule.
     */
    protected function getShortDescRules(): array
    {
        return [
            'required'     => '',
            'max_len[500]' => '',
        ];
    }

    /**
     * Get the full desc validation rule.
     */
    protected function getFullDescRules(): array
    {
        return [
            'required'      => '',
            'max_len[2500]' => '',
        ];
    }
}
