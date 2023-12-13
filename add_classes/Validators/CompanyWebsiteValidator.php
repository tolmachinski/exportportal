<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class CompanyWebsiteValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'website',
                'label' => 'Website',
                'rules' => array('valid_url' => ''),
            ),
        );
    }
}
