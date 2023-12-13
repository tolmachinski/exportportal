<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Contracts\User\UserStatus;
use App\Common\Validation\Legacy\Standalone\Validator;
use Symfony\Component\HttpFoundation\ParameterBag;

final class UserRestoreDataValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $messages = $this->getMessages();

        return [
            [
                'field' => 'user',
                'label' => 'User info',
                'rules' => ['required' => '', 'integer' => ''],
            ], [
                'field' => 'email',
                'label' => 'Email',
                'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_email' => ''],
            ],
            [
                'field' => 'fname',
                'label' => 'First Name',
                'rules' => ['required' => '', 'valid_user_name' => '', 'min_len[2]' => '', 'max_len[50]' => ''],
            ],
            [
                'field' => 'lname',
                'label' => 'Last Name',
                'rules' => ['required' => '', 'valid_user_name' => '', 'min_len[2]' => '', 'max_len[50]' => ''],
            ], [
                'field' => 'status',
                'label' => 'Status',
                'rules' => $this->getStatusRules($messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'status.notFound' => 'This user status is not in use',
        ];
    }

    protected function getStatusRules(ParameterBag $messages): array
    {
        return [
            'required' => '',
            function ($attr, $value, $fail) use ($messages) {
                try {
                    $status = UserStatus::from($value);

                    if (!$status->inUse()) {
                        $fail(sprintf($messages->get('status.notFound', ''), $attr));
                    }
                } catch (\ValueError $e) {
                    $fail(sprintf($messages->get('status.notFound', ''), $attr));
                }
            },
        ];
    }
}
