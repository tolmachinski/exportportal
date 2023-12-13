<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class CompanyEmailValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'email',
                'label' => 'Email',
                'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => ''),
            ),
        );
    }
}
