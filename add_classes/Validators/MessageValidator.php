<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class MessageValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'content',
                'label' => 'Message',
                'rules' => array(
                    'required'      => '',
                    'max_len[1500]' => '',
                ),
            ),
            array(
                'field' => 'id_recipient',
                'label' => 'Recipients',
                'rules' => array(
                    function (string $attr, $value, callable $fail) {
                        if (empty($value)) {
                            $fail('You did not select any recipients. Please choose a recipient first.');
                        }
                    },
                ),
            ),
            array(
                'field' => 'recipients',
                'label' => 'Recipients',
                'rules' => array(
                    function (string $attr, $value, callable $fail) {
                        if (empty($this->getValidationData()->get('recipients'))) {
                            $fail('You did not select any recipients. Please choose a recipient first.');
                        }
                    },
                ),
            ),
        );
    }
}
