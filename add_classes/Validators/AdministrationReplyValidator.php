<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class AdministrationReplyValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'content',
                'label' => 'Message',
                'rules' => array(
                    'required'      => '',
                    'max_len[1500]' => '',
                ),
            ),
        );
    }
}
