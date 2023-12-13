<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class MessageSubjectValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'subject',
                'label' => 'Subject',
                'rules' => array('required' => '', 'max_len[200]' => ''),
            ),
        );
    }
}
