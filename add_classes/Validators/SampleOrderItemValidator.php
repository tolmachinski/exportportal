<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\IndexAwareValidatorTrait;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Standalone\IndexAwareValidatorInterface;

final class SampleOrderItemValidator extends Validator implements IndexAwareValidatorInterface
{
    use IndexAwareValidatorTrait;

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'rules'         => array('required' => '', 'integer' => '', 'natural' => '', 'min[1]' => '', 'max[99999999999]' => ''),
                'field'         => 'quantity',
                'label'         => 'Quantity',
                'indexed_label' => 'Quantity #%s',
            ),
            array(
                'rules'         => array('required' => '', 'positive_number' => '', 'min[0.01]' => '', 'max[99999999999.99]' => ''),
                'field'         => 'price',
                'label'         => 'Price',
                'indexed_label' => 'Price #%s',
            ),
        );
    }
}
