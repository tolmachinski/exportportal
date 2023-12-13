<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\FlatValidationData;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Envelope\RecipientTypesAwareTrait;
use Symfony\Component\HttpFoundation\ParameterBag;

class OrderEnvelopeSigningTypeValidator extends Validator
{
    use RecipientTypesAwareTrait;

    /**
     * The list of accepted signing types.
     *
     * @var string[]
     */
    private array $acceptedTypes;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        ValidatorAdapter $validatorAdapter,
        array $acceptedTypes = [],
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->acceptedTypes = $acceptedTypes;
        $this->recipientsValidationData = new FlatValidationData();

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * Get the list of accepted signing types.
     *
     * @return string[]
     */
    public function getAcceptedTypes(): array
    {
        return $this->acceptedTypes;
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
                'field' => $fields->get('signingType'),
                'label' => $labels->get('signingType'),
                'rules' => $this->getSigningTypesRules($messages, $this->getAcceptedTypes()),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'signingType' => 'signing_type',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'signingType' => 'signing_type',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'signingType.required'     => 'You need to specify the singing mechanism.',
            'signingType.allowedTypes' => 'The signing mechanis is invalid.',
        ];
    }

    /**
     * Get the description validation rule.
     */
    protected function getSigningTypesRules(ParameterBag $messages, array $allowedTypes): array
    {
        $allowedTypesList = \implode(',', $allowedTypes);

        return [
            'required'                => $messages->get('signingType.required') ?? '',
            "in[{$allowedTypesList}]" => $messages->get('signingType.allowedTypes') ?? '',
        ];
    }
}
