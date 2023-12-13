<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\NestedValidationData;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\ParameterBag;

class EpDocsTemporaryFilesValidator extends Validator
{
    const MIN_FILES = 1;

    const MAX_FILES = 10;

    /**
     * The min amount of files.
     */
    protected int $minFiles;

    /**
     * The max amount of files.
     */
    protected int $maxFiles;

    /**
     * Creates the validator.
     */
    public function __construct(
        ValidatorAdapter $validator,
        ?int $minFiles = self::MIN_FILES,
        ?int $maxFiles = self::MAX_FILES,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->minFiles = $minFiles ?? static::MIN_FILES;
        $this->maxFiles = $maxFiles ?? static::MAX_FILES;

        parent::__construct($validator, $messages, $labels, $fields);
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
            [
                'field' => $fields->get('files'),
                'label' => $labels->get('files'),
                'rules' => $this->getFilesRules($messages, $this->minFiles, $this->maxFiles, 0 === $this->minFiles),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'files' => 'files',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'files' => 'Document',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'files.notEmpty'    => 'The document file is required.',
            'files.invalid'     => 'The document file is not uploaded or uploaded with errors.',
            'files.min'         => 'The amount of the documents must be at least {MIN}.',
            'files.max'         => 'The amount of the documents must be at most {MAX}.',
        ];
    }

    /**
     * Get the document validation rule.
     */
    protected function getFilesRules(ParameterBag $messages, int $minFiles, int $maxFiles, bool $allowEmpty): array
    {
        $rules = [];
        if (!$allowEmpty) {
            $rules['not_empty'] = $messages->get('files.notEmpty') ?? '';
        }
        if (0 !== $minFiles) {
            $rules[] = function ($attribute, ?NestedValidationData $files, $fail) use ($messages, $minFiles) {
                if (null === $files) {
                    return;
                }

                if ($files->count() < $minFiles) {
                    $fail(\str_replace(['{ATTR}', '{MIN}'], [$attribute, $minFiles], $messages->get('files.min')));
                }
            };
        }
        $rules[] = function ($attribute, ?NestedValidationData $files, $fail) use ($messages, $maxFiles) {
            if (null === $files) {
                return;
            }

            if ($files->count() > $maxFiles) {
                $fail(\str_replace(['{ATTR}', '{MAX}'], [$attribute, $maxFiles], $messages->get('files.max')));
            }
        };
        $rules[] = function ($attribute, ?NestedValidationData $uuids, $fail) use ($messages) {
            if (null === $uuids) {
                return;
            }

            foreach ($uuids as $fileId) {
                try {
                    Uuid::fromString((string) base64_decode($fileId));
                } catch (InvalidUuidStringException $exception) {
                    $fail(\str_replace(['{ATTR}'], [$attribute], $messages->get('files.max')));
                }
            }
        };

        return $rules;
    }
}
