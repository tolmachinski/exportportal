<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Database\Model;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

class VerificationDocumentTypeLocaleValidator extends Validator
{
    protected const MAX_TITLE_LENGTH = 250;

    /**
     * The maximum title.
     */
    protected int $maxTitleLength;

    /**
     * The locales is repository.
     */
    protected ?Model $localesRepository;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        ?Model $localesRepository = null,
        int $maxTitleLength = self::MAX_TITLE_LENGTH,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->maxTitleLength = $maxTitleLength;
        $this->localesRepository = $localesRepository;

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
            'title' => [
                'field' => $fields->get('title'),
                'label' => $labels->get('title'),
                'rules' => $this->getTitleRules($this->maxTitleLength, $messages),
            ],
            'locale' => [
                'field' => $fields->get('locale'),
                'label' => $labels->get('locale'),
                'rules' => $this->getLocaleRules($this->localesRepository, $messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'title'  => 'title',
            'locale' => 'language',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'title'  => 'Title',
            'locale' => 'Language',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'locale.notFound'  => \translate('systmess_error_lang_id_not_found'),
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
     * Get the locale ID field validation rules.
     */
    protected function getLocaleRules(?Model $localesRepository, ParameterBag $messages): array
    {
        $rules = [
            'required' => $messages->get('locale.required') ?? '',
            'integer'  => $messages->get('locale.integer') ?? '',
        ];
        if (null !== $localesRepository) {
            $rules[] = function (string $attr, $value, callable $fail) use ($messages, $localesRepository) {
                if (
                    null === $value
                    || !$localesRepository->has($value)
                ) {
                    $fail(sprintf($messages->get('locale.notFound'), $attr));
                }
            };
        }

        return $rules;
    }
}
