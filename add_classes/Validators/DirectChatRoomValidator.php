<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use ExportPortal\Contracts\Chat\Recource\ResourceType;
use Symfony\Component\HttpFoundation\ParameterBag;

class DirectChatRoomValidator extends Validator
{
    protected const MAX_SUBJECT_LENGTH = 255;

    /**
     * The max length of the subject.
     */
    protected int $maxSubjectLength;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        ?int $maxSubjectLength = self::MAX_SUBJECT_LENGTH,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->maxSubjectLength = $maxSubjectLength;

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return [
            'subject' => [
                'field' => $fields->get('subject'),
                'label' => $labels->get('subject'),
                'rules' => $this->getSubjectRules($messages, $this->maxSubjectLength),
            ],
            'resourceType' => [
                'field' => $fields->get('resourceType'),
                'label' => $labels->get('resourceType'),
                'rules' => $this->getResourceTypeRules($messages),
            ],
            'resourceId' => [
                'field' => $fields->get('resourceId'),
                'label' => $labels->get('resourceId'),
                'rules' => $this->getResourceIdRules($messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'subject'      => 'subject',
            'resourceId'   => 'resource_id',
            'resourceType' => 'resource_type',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'subject'      => 'Subject',
            'resourceId'   => 'Resource Id',
            'resourceType' => 'Resource Type',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'subject.notBlank'      => translate('validation_chat_direct_messaging_subject_not_blank', ['{{FIELD_NAME}}' => '%s']),
            'subject.maxLength'     => translate('validation_chat_direct_messaging_subject_max_length', ['{{FIELD_NAME}}' => '%s', '{{MAX_LENGTH}}' => '%d']),
            'resourceType.notBlank' => translate('validation_chat_direct_messaging_resource_type_not_blank', ['{{FIELD_NAME}}' => '%s']),
            'resourceType.invalid'  => translate('validation_chat_direct_messaging_resource_type_invalid', ['{{FIELD_NAME}}' => '%s']),
            'resourceId.notBlank'   => translate('validation_chat_direct_messaging_resource_id_invalid', ['{{FIELD_NAME}}' => '%s']),
            'resourceId.integer'    => translate('validation_chat_direct_messaging_resource_id_integer', ['{{FIELD_NAME}}' => '%s']),
        ];
    }

    /**
     * Get the description validation rules.
     */
    protected function getSubjectRules(ParameterBag $messages, int $maxLength): array
    {
        return [
            'not_blank[true]'       => $messages->get('subject.notBlank') ?? '',
            "max_len[{$maxLength}]" => $messages->get('subject.maxLength') ?? '',
        ];
    }

    /**
     * Get the description validation rules.
     */
    protected function getResourceTypeRules(ParameterBag $messages): array
    {
        return [
            'not_blank' => $messages->get('resourceType.notBlank') ?? '',
            function (string $attr, $value, \Closure $fail) use ($messages) {
                if (!empty($value) && null === $this->makeResourceTypeFromRaw($value)) {
                    $fail(sprintf($messages->get('resourceType.invalid', ''), $attr));
                }
            },
        ];
    }

    /**
     * Get the description validation rules.
     */
    protected function getResourceIdRules(ParameterBag $messages): array
    {
        return [
            'not_blank[true]' => $messages->get('resourceId.notBlank') ?? '',
            'integer'         => $messages->get('resourceId.integer') ?? '',
        ];
    }

    /**
     * Makes resource type from raw value.
     */
    private function makeResourceTypeFromRaw(?string $resourceType): ?ResourceType
    {
        if (null === $resourceType) {
            return null;
        }

        try {
            return ResourceType::from($resourceType);
        } catch (\ValueError $e) {
            return null;
        }
    }
}
