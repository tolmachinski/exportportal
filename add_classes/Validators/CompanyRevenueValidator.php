<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class CompanyRevenueValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'revenue',
                'label' => 'Annual Revenue, in USD',
                'rules' => array('required' => '', 'positive_number' => ''),
            ),
        );
    }
}
