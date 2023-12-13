<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class SampleOrderValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'description',
                'label' => 'What do you want to be included in your Sample Order?',
                'rules' => array('required' => '', 'max_len[5000]' => ''),
            ),
        );
    }
}
