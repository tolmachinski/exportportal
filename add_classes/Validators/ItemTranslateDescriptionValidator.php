<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;
use TinyMVC_Library_validator;

class ItemTranslateDescriptionValidator extends Validator
{

    protected const MAX_DESCRIPTION_LENGTH = 20000;

    /**
     * The max year value.
     *
     * @var int
     */

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
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

        return array(
            'language'   => array(
                'field' => $fields->get('language'),
                'label' => $labels->get('language'),
                'rules' => $this->getLanguageRules($messages),
            ),
            'text'     => array(
                'field' => $fields->get('text'),
                'label' => $labels->get('text'),
                'rules' => $this->getTextRules(static::MAX_DESCRIPTION_LENGTH, $messages),
            ),
            'translate'   => array(
                'field' => $fields->get('translate'),
                'label' => $labels->get('translate'),
                'rules' => $this->getTranslateRules($messages),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return array(
            'language'      => 'additional_description_language',
            'text'          => 'additional_description_text',
            'translate'     => 'additional_description_translate',
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return array(
            'language'      => 'Language for description in another language',
            'text'          => 'Description in another language',
            'translate'     => 'Translate the description in English',
        );
    }

    /**
     * Get the language validation rule.
     */
    protected function getLanguageRules(ParameterBag $messages): array
    {
        return array(
            'required' => $messages->get('language.required') ?? '',
            'natural'  => $messages->get('language.natural') ?? '',
            'is_number'=> $messages->get('language.number') ?? '',
        );
    }

    /**
     * Get the translate validation rule.
     */
    protected function getTranslateRules(ParameterBag $messages): array
    {
        return array(
            'natural'  => $messages->get('translate.natural') ?? '',
            'is_number'=> $messages->get('translate.number') ?? '',
        );
    }

    /**
     * Get the text validation rule.
     */
    protected function getTextRules(int $maxLength, ParameterBag $messages): array
    {
        return array(
            'required'                   => $messages->get('text.required') ?? '',
            "html_max_len[{$maxLength}]" => $messages->get('text.maxLength') ?? '',
        );
    }
}
