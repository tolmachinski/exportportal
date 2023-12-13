<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use TinyMVC_Library_Vindecoder;

final class VinValidator extends Validator
{
    /**
     * The VIN decoder instance.
     *
     * @var TinyMVC_Library_Vindecoder
     */
    private $decoder;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        TinyMVC_Library_Vindecoder $decoder,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        parent::__construct($validatorAdapter, $messages, $labels, $fields);

        $this->decoder = $decoder;
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
            'vin' => array(
                'field' => $fields->get('vin'),
                'label' => $labels->get('vin'),
                'rules' => array(
                    'required' => $messages->get('vin.required') ?? '',
                    function (string $attr, $value, callable $fail) use ($messages) {
                        if (empty($value)) {
                            return;
                        }

                        if (empty($this->decoder->decode($value, 'both'))) {
                            $fail($messages->get('vin.valid'));
                        }
                    },
                    function (string $attr, $value, callable $fail) use ($messages) {
                        if (empty($value)) {
                            return;
                        }

                        if (empty($this->decoder->is_used($value, 'both'))) {
                            $fail($messages->get('vin.notUsed'));
                        }
                    },
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return array(
            'vin' => 'vin_code',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return array(
            'vin' => 'VIN',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return array(
            'vin.valid'   => 'The VIN number is not correct.',
            'vin.notUsed' => 'The VIN is already used by other vehicle.',
        );
    }
}
