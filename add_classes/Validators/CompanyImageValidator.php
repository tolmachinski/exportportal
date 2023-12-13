<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class CompanyImageValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'image',
                'label' => 'Logo',
                'rules' => array('required' => ''),
            ),
        );
    }
}
