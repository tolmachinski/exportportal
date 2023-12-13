<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use Symfony\Component\HttpFoundation\ParameterBag;

class OrderEnvelopeValidator extends Validator
{
    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return [
            [
                'field' => $fields->get('title'),
                'label' => $labels->get('title'),
                'rules' => $this->getTitleRules($messages),
            ],
            [
                'field' => $fields->get('type'),
                'label' => $labels->get('type'),
                'rules' => $this->getTypeRules($messages),
            ],
            [
                'field' => $fields->get('description'),
                'label' => $labels->get('description'),
                'rules' => $this->getDescriptionRules($messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'type'        => 'type',
            'title'       => 'title',
            'description' => 'description',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'title'       => 'Title',
            'type'        => 'Type',
            'description' => 'Description',
        ];
    }

    /**
     * Get the title validation rule.
     */
    protected function getTitleRules(ParameterBag $messages, int $maxLength = 200): array
    {
        return [
            'required'              => $messages->get('title.required') ?? '',
            "max_len[{$maxLength}]" => $messages->get('title.maxLength') ?? '',
        ];
    }

    /**
     * Get the type validation rule.
     */
    protected function getTypeRules(ParameterBag $messages, int $maxLength = 200): array
    {
        return [
            'required'              => $messages->get('type.required') ?? '',
            "max_len[{$maxLength}]" => $messages->get('type.maxLength') ?? '',
        ];
    }

    /**
     * Get the description validation rule.
     */
    protected function getDescriptionRules(ParameterBag $messages, int $maxLength = 500): array
    {
        return [
            'required'              => $messages->get('description.required') ?? '',
            "max_len[{$maxLength}]" => $messages->get('description.maxLength') ?? '',
        ];
    }
}
