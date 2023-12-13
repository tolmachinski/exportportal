<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class CompanyDescriptionValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'description',
                'label' => 'Description',
                'rules' => array('required' => '', 'html_max_len[20000]' => ''),
            ),
        );
    }
}
