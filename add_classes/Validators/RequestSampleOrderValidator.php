<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class RequestSampleOrderValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'description',
                'label' => 'What do you want included in your Sample Order',
                'rules' => array('required' => '', 'max_len[5000]' => ''),
            ),
        );
    }
}
