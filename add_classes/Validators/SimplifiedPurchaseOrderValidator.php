<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use Closure;
use Ishippers_Model;

final class SimplifiedPurchaseOrderValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $messages = $this->getMessages();

        return array(
            array(
                'field' => 'due_date',
                'label' => 'Invoice Due Date',
                'rules' => array('required' => '', 'valid_date[m/d/Y]' => '', 'valid_date_future[m/d/Y]' => ''),
            ),
            array(
                'field' => 'number',
                'label' => 'PO Number',
                'rules' => array('required' => '', 'alpha_numeric' => '', 'max_len[12]' => ''),
            ),
            array(
                'field' => 'shipper',
                'label' => 'Shipping',
                'rules' => array(
                    'required' => '',
                    'integer'  => '',
                    function ($attr, $value, Closure $fail) use ($messages) {
                        if (!model(Ishippers_Model::class)->has_shipper((int) $value)) {
                            $fail($messages->get('shipper.exists'));
                        }
                    },
                ),
            ),
            array(
                'field' => 'notes',
                'label' => 'Order Notes',
                'rules' => array('max_len[1000]' => ''),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return array(
            'shipper.exists' => 'The shipping type does not appear to be valid. Please choose the correct one from the list.',
        );
    }
}
