<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class ThemeValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'types',
                'label' => 'Type',
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'theme',
                'label' => 'Theme',
                'rules' => array('required' => '')
            )
        );
    }
}
