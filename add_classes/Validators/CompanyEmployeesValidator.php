<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class CompanyEmployeesValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'employees',
                'label' => 'Number of employees',
                'rules' => array('required' => '', 'positive_number' => '', 'max_len[5]' => ''),
            ),
        );
    }
}
