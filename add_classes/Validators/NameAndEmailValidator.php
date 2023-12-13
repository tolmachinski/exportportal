<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

final class NameAndEmailValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return array(
            array(
                'field' => 'name',
                'label' => $labels->get('name'),
                'rules' => array(
                    'required'        => $messages->get('name.required') ?? '',
                    'valid_user_name' => $messages->get('name.validName') ?? '',
                    'min_len[2]'      => $messages->get('name.minSize') ?? '',
                    'max_len[200]'    => $messages->get('name.maxSize') ?? '',
                ),
            ),
            array(
                'field' => 'email',
                'label' => $labels->get('email'),
                'rules' => array(
                    'required'       => $messages->get('email.required') ?? '',
                    'no_whitespaces' => $messages->get('email.noWhitespaces') ?? '',
                    'valid_email'    => $messages->get('email.valid') ?? '',
                    'max_len[254]'   => $messages->get('email.maxSize') ?? '',
                ),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return array(
            'name'  => 'Name',
            'email' => 'Email',
        );
    }
}
