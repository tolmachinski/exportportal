<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Contracts\Document\DocumentTypeCategory;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\NestedValidationData;
use Doctrine\DBAL\Query\QueryBuilder;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\ParameterBag;
use Validator as LegacyValidator;

/**
 * @author Anton Zencenco
 */
class EditRequestDocumentsValidator extends Validator
{
    /**
     * The min amount of files.
     */
    protected int $minFiles;

    /**
     * The max amount of files.
     */
    protected int $maxFiles;

    /**
     * The maximum comment length value.
     */
    private int $maxCommentLength;

    /**
     * The minimum subtitle length value.
     */
    private int $minSubtitleLength;

    /**
     * The maximum subtitle length value.
     */
    private int $maxSubtitleLength;

    /**
     * The documents reposiory.
     */
    private Model $documentsRepository;

    /**
     * THe user ID value.
     */
    private int $userId;

    /**
     * The list of document types.
     */
    private array $types;

    /**
     * Creates the validator.
     */
    public function __construct(
        ValidatorAdapter $validator,
        Model $documentsRepository,
        int $userId,
        ?array $types = null,
        int $maxCommentLength = 500,
        int $minSubtitleLength = 3,
        int $maxSubtitleLength = 500,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->userId = $userId;
        $this->types = $types ?? DocumentTypeCategory::cases();
        $this->documentsRepository = $documentsRepository;
        $this->maxCommentLength = $maxCommentLength;
        $this->minSubtitleLength = $minSubtitleLength;
        $this->maxSubtitleLength = $maxSubtitleLength;

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
                'field' => $fields->get('documents'),
                'label' => $labels->get('documents'),
                'rules' => $this->getDocumentsRules(
                    $messages,
                    $labels,
                    $this->maxCommentLength,
                    $this->minSubtitleLength,
                    $this->maxSubtitleLength
                ),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'documents' => 'documents',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'documentSubtitle' => 'Document #%s subtitle',
            'documentComment'  => 'Document #%s comment',
            'document'         => 'Document #%s',
            'documents'        => 'Documents',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'documents.notEmpty'      => 'The document file is required.',
            'documents.invalid'       => 'The document file is not uploaded or uploaded with errors.',
            'documents.uuid.required' => 'The document file is not uploaded or uploaded with errors.',
            'documents.uuid.invalid'  => 'The document file is not uploaded or uploaded with errors.',
            'documents.min'           => 'The amount of the documents must be at least {MIN}.',
            'documents.max'           => 'The amount of the documents must be at most {MAX}.',
        ];
    }

    /**
     * Get the document validation rule.
     */
    protected function getDocumentsRules(
        ParameterBag $messages,
        ParameterBag $labels,
        int $maxCommentLength,
        int $minSubtitleLength,
        int $maxSubtitleLength
    ): iterable {
        $minFiles = $this->getMinAmount();
        $maxFiles = $this->getMaxAmount();
        $allowEmpty = 0 === $this->getMinAmount();
        $rules = [];
        if (!$allowEmpty) {
            $rules['not_empty'] = $messages->get('documents.notEmpty') ?? '';
        }
        if (0 !== $minFiles) {
            $rules[] = function ($attribute, ?NestedValidationData $files, $fail) use ($messages, $minFiles) {
                if (null === $files) {
                    return;
                }

                if ($files->count() < $minFiles) {
                    $fail(\str_replace(['{ATTR}', '{MIN}'], [$attribute, $minFiles], $messages->get('documents.min')));
                }
            };
        }
        $rules[] = function ($attribute, ?NestedValidationData $files, $fail) use ($messages, $maxFiles) {
            if (null === $files) {
                return;
            }

            if ($files->count() > $maxFiles) {
                $fail(\str_replace(['{ATTR}', '{MAX}'], [$attribute, $maxFiles], $messages->get('documents.max')));
            }
        };
        // Add rules for document IDs
        $rules[] = function ($attribute, ?NestedValidationData $documents, $fail, LegacyValidator $validator) use ($messages, $labels) {
            if (null === $documents) {
                return;
            }

            foreach ($documents as $index => $document) {
                $documentId = $document['id'] ?? null;
                if (!$validator->not_blank($documentId)) {
                    $fail(\sprintf(
                        $messages->get('documents.id.notBlank') ?? $validator->get_rule_message('not_blank'),
                        \sprintf($labels->get('document'), $index + 1),
                    ));

                    continue;
                }
                if (!$validator->integer($documentId)) {
                    $fail(\sprintf(
                        $messages->get('documents.id.integer') ?? $validator->get_rule_message('integer'),
                        \sprintf($labels->get('document'), $index + 1),
                    ));

                    continue;
                }
            }
        };
        // Add rule for the document UUIDs
        $rules[] = function ($attribute, ?NestedValidationData $documents, $fail) use ($messages) {
            if (null === $documents) {
                return;
            }

            foreach ($documents as $index => $document) {
                $documentId = $document['document'] ?? null;
                if (null === $documentId) {
                    $fail(\str_replace(['{ATTR}', '{INDEX}'], [$attribute, $index], $messages->get('documents.uuid.required')));

                    continue;
                }

                try {
                    Uuid::fromString((string) base64_decode($documentId));
                } catch (InvalidUuidStringException $exception) {
                    $fail(\str_replace(['{ATTR}', '{INDEX}'], [$attribute, $index], $messages->get('documents.uuid.valid')));
                }
            }
        };
        // Add rules for document comments
        $rules[] = function ($attribute, ?NestedValidationData $documents, $fail, LegacyValidator $validator) use ($messages, $labels, $maxCommentLength) {
            if (null === $documents) {
                return;
            }

            foreach ($documents as $index => $document) {
                $comment = $document['comment'] ?? null;
                if (null === $comment) {
                    return;
                }

                if (!$validator->max_len($comment, $maxCommentLength)) {
                    $fail(\sprintf(
                        $messages->get('documents.comment.maxLength') ?? $validator->get_rule_message('max_len'),
                        \sprintf($labels->get('documentComment'), $index + 1),
                        $maxCommentLength,
                    ));

                    continue;
                }
            }
        };
        // Add rule for document subtitles
        $rules[] = function ($attribute, ?NestedValidationData $documents, $fail, LegacyValidator $validator) use (
            $messages,
            $labels,
            $minSubtitleLength,
            $maxSubtitleLength
        ) {
            if (null === $documents) {
                return;
            }

            foreach ($documents as $index => $document) {
                $subtitle = $document['subtitle'] ?? null;
                if (null === $subtitle) {
                    continue;
                }

                if (!$validator->min_len($subtitle, $minSubtitleLength)) {
                    $fail(\sprintf(
                        $messages->get('documents.subtitle.minLength') ?? $validator->get_rule_message('min_len'),
                        \sprintf($labels->get('documentSubtitle'), $index + 1),
                        $minSubtitleLength,
                    ));

                    continue;
                }
                if (!$validator->max_len($subtitle, $maxSubtitleLength)) {
                    $fail(\sprintf(
                        $messages->get('documents.subtitle.maxLength') ?? $validator->get_rule_message('max_len'),
                        \sprintf($labels->get('documentSubtitle'), $index + 1),
                        $maxSubtitleLength,
                    ));

                    continue;
                }
            }
        };

        yield from $rules;
    }

    /**
     * Get minimum amount of documents for rules.
     */
    protected function getMinAmount(): int
    {
        $this->resolveCountersForDocuments();

        return $this->minFiles;
    }

    /**
     * Get maximum amount of documents for rules.
     */
    protected function getMaxAmount(): int
    {
        $this->resolveCountersForDocuments();

        return $this->maxFiles;
    }

    /**
     * Resolves counters for documents.
     */
    private function resolveCountersForDocuments(): void
    {
        if (isset($this->minFiles, $this->maxFiles)) {
            return;
        }

        $exists = [];
        if (!empty($this->types)) {
            $exists[] = $this->documentsRepository
                ->getRelationsRuleBuilder()
                ->whereHas('type', function (QueryBuilder $builder, RelationInterface $relation) {
                    $relation->getRelated()->getScope('categories')->call($relation->getRelated(), $builder, $this->types);
                })
            ;
        }

        $this->minFiles = $this->maxFiles = $this->documentsRepository->countAllBy([
            'scopes' => ['user' => $this->userId],
            'exists' => $exists,
        ]);
    }
}
