<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;

class EpReviewValidator extends Validator
{
    protected const MAX_MESSAGE_LENGTH = 250;

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();

        return [
            'message' => [
                'field' => $fields->get('message'),
                'label' => $labels->get('message'),
                'rules' => $this->getMessageRules(),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'message'  => 'message',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'message'  => 'Message',
        ];
    }

    /**
     * Get the short description validation rules.
     */
    protected function getMessageRules(): array
    {
        $maxLength = static::MAX_MESSAGE_LENGTH;

        return [
            'required'                  => '',
            "max_len[{$maxLength}]"     => '',
        ];
    }
}
