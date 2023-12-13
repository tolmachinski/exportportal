<?php

declare(strict_types=1);

namespace App\Validators;

use App\Filesystem\EpEventSpeakersFilePathGenerator;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

class SpeakerValidator extends Validator
{
    protected const MAX_NAME_LENGTH = 100;
    protected const MAX_POSITION_LENGTH = 100;
    protected $isActionAddSpeaker;
    protected $encryptedFolderName;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        bool $isActionAddSpeaker = true,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->isActionAddSpeaker = $isActionAddSpeaker;

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
            'fullName' => [
                'field' => $fields->get('fullName'),
                'label' => $labels->get('fullName'),
                'rules' => $this->getNameRules(static::MAX_NAME_LENGTH, $messages),
            ],
            'position' => [
                'field' => $fields->get('position'),
                'label' => $labels->get('position'),
                'rules' => $this->getPositionRules(static::MAX_POSITION_LENGTH, $messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'fullName' => 'name',
            'position' => 'position',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'fullName' => 'Full Name',
            'position' => 'Position',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [];
    }

    /**
     * Get the name field validation rules.
     */
    protected function getNameRules(int $maxLength, ParameterBag $messages): array
    {
        return [
            'required'              => $messages->get('fname.required') ?? '',
            "max_len[{$maxLength}]" => $messages->get('fname.maxLength') ?? '',
        ];
    }

    /**
     * Get the position field validation rules.
     */
    protected function getPositionRules(int $maxLength, ParameterBag $messages): array
    {
        return [
            'required'              => $messages->get('position.required') ?? '',
            "max_len[{$maxLength}]" => $messages->get('position.maxLength') ?? '',
        ];
    }
}
