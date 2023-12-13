<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Contracts\Document\DocumentTypeCategory;
use App\Common\Database\Model;
use App\Common\Validation\FlatValidationData;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\ValidationDataInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use TinyMVC_Library_validator;

class VerificationDocumentTypeValidator extends Validator
{
    protected const MAX_TITLE_LENGTH = 250;

    /**
     * The maximum title.
     */
    protected int $maxTitleLength;

    /**
     * The repository for the locales.
     */
    protected ?Model $localesRepository;

    /**
     * The list of the countries.
     */
    protected array $countries;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        array $countries = [],
        int $maxTitleLength = self::MAX_TITLE_LENGTH,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->countries = $countries;
        $this->maxTitleLength = $maxTitleLength;

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    public function validate($validationData): bool
    {
        $validationData = $validationData instanceof ValidationDataInterface ? $validationData : new FlatValidationData($validationData ?? []);
        $validationData->merge($this->makeTitlesValidationData($validationData->get('document_titles') ?? []));

        return parent::validate($validationData);
    }

    /**
     * {@inheritdoc}
     */
    protected function rules(): array
    {
        $fields = $this->getFields();
        $labels = $this->getLabels();
        $messages = $this->getMessages();

        return \array_merge(
            [
                'title'           => [
                    'field' => $fields->get('title'),
                    'label' => $labels->get('title'),
                    'rules' => $this->getTitleRules($this->maxTitleLength, $messages),
                ],
                'category'           => [
                    'field' => $fields->get('category'),
                    'label' => $labels->get('category'),
                    'rules' => $this->getCategoryRules($messages),
                ],
                'groups'          => [
                    'field' => $fields->get('groups'),
                    'label' => $labels->get('groups'),
                    'rules' => $this->getConditionalRequireRules('groups', $messages),
                ],
                'industries'      => [
                    'field' => $fields->get('industries'),
                    'label' => $labels->get('industries'),
                    'rules' => $this->getConditionalRequireRules('industries', $messages),
                ],
                'countries'       => [
                    'field' => $fields->get('countries'),
                    'label' => $labels->get('countries'),
                    'rules' => $this->getConditionalRequireRules('countries', $messages),
                ],
                'groups_required' => [
                    'field' => $fields->get('requiredGroups'),
                    'label' => $labels->get('requiredGroups'),
                    'rules' => $this->getConditionalRequireRules('requiredGroups', $messages),
                ],
            ],
            $this->makeTitlesValidationRules($this->countries, $this->maxTitleLength, $messages)
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'title'          => 'document_title',
            'category'       => 'category',
            'groups'         => 'groups',
            'countries'      => 'countries',
            'industries'     => 'industries',
            'requiredGroups' => 'groups_required',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'title'          => 'Document title',
            'category'       => 'Category',
            'groups'         => 'Users groups',
            'countries'      => 'Countries',
            'industries'     => 'Industries',
            'requiredGroups' => 'Users groups',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'groups.required'          => 'The field %s cannot be empty.',
            'countries.required'       => 'The field %s cannot be empty.',
            'industries.required'      => 'The field %s cannot be empty.',
            'requiredGroups.required'  => 'The field %s cannot be empty.',
            'category.invalid'         => 'The field %s contains unknown or invalid category.',
        ];
    }

    /**
     * Get the title field validation rules.
     */
    protected function getTitleRules(int $maxLength, ParameterBag $messages): array
    {
        return [
            'required'              => $messages->get('title.required') ?? '',
            "max_len[{$maxLength}]" => $messages->get('title.maxLength') ?? '',
        ];
    }

    /**
     * Get the title field validation rules.
     */
    protected function getCategoryRules(ParameterBag $messages): array
    {
        return [
            'required'              => $messages->get('category.required') ?? '',
            function (string $attr, $value, callable $fail) use ($messages) {
                if (null === $value) {
                    return;
                }

                try {
                    DocumentTypeCategory::from($value);
                } catch (\ValueError $e) {
                    $fail(\sprintf($messages->get('category.invalid'), $attr));
                }
            },
        ];
    }

    /**
     * Get the groups field validation rules.
     */
    protected function getConditionalRequireRules(string $field, ParameterBag $messages): array
    {
        return [
            function (string $attr, $value, callable $fail, TinyMVC_Library_validator $validator) use ($field, $messages) {
                if (null === $value) {
                    return;
                }

                if (false === $validator->not_blank($value)) {
                    $fail(\sprintf($messages->get("{$field}.required"), $attr));
                }
            },
        ];
    }

    /**
     * Makes the validation data for titles.
     */
    private function makeTitlesValidationData(array $titles = []): ValidationDataInterface
    {
        $fields = [];
        foreach ($titles as $countryId => $title) {
            $fields["document_title_{$countryId}"] = $title;
        }

        return new FlatValidationData($fields);
    }

    /**
     * Makes the validation rules for titles.
     */
    private function makeTitlesValidationRules(array $countries, int $maxLength, ParameterBag $messages): array
    {
        $rules = [];
        foreach ($countries as $countryId => $countryName) {
            $rules[] = [
                'field' => "document_title_{$countryId}",
                'label' => "Document title for country {$countryName}",
                'rules' => [
                    "max_len[{$maxLength}]" => $messages->get('title.maxLength') ?? '',
                ],
            ];
        }

        return $rules;
    }
}
