<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class SampleOrderDeliveryDateValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'rules' => array('required' => '', 'valid_date[m/d/Y]' => ''),
                'field' => 'delivery_date',
                'label' => 'Delivery date',
            ),
        );
    }
}
