<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;

final class AdministrationMessageValidator extends Validator
{
    /**
     * The flag that indicates that the subject must be checked.
     *
     * @var bool
     */
    private $checkSubject;

    /**
     * Creates the phone validator.
     */
    public function __construct(
        ValidatorAdapter $validator,
        bool $checkSubject = true,
        ?array $fields = null,
        ?array $labels = null,
        ?array $messages = null
    ) {
        $this->checkSubject = $checkSubject;

        parent::__construct($validator, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $rules = [
            [
                'field' => 'content',
                'label' => 'Message',
                'rules' => array(
                    'required'     => '',
                    'max_len[500]' => '',
                ),
            ]
        ];

        if ($this->checkSubject) {
            $rules[] = [
                'field' => 'subject',
                'label' => 'Subject',
                'rules' => array(
                    'required'     => '',
                    'max_len[200]' => '',
                ),
            ];
        }

        return $rules;
    }
}
