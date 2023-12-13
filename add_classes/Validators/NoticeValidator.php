<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class NoticeValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        return array(
            array(
                'field' => 'status',
                'label' => 'Status',
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'message',
                'label' => 'Notice',
                'rules' => array('required' => '')
            )
        );
    }
}
