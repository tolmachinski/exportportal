<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\NestedValidationData;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\ParameterBag;

class OrderEnvelopeFilesValidator extends Validator
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
                'field' => $fields->get('files'),
                'label' => $labels->get('files'),
                'rules' => $this->getFilesRules($messages),
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
            'files.moreThanOne' => 'The upload of more than one document file is not supported.',
        ];
    }

    /**
     * Get the document validation rule.
     */
    protected function getFilesRules(ParameterBag $messages): array
    {
        return [
            'not_empty' => $messages->get('files.notEmpty') ?? '',
            function ($attribute, ?NestedValidationData $files, $fail) use ($messages) {
                if (null === $files) {
                    return;
                }

                if ($files->count() > 1) {
                    $fail(\sprintf($messages->get('files.moreThanOne'), $attribute));
                }
            },
            function ($attribute, ?NestedValidationData $uuids, $fail) use ($messages) {
                if (null === $uuids) {
                    return;
                }

                foreach ($uuids as $fileId) {
                    try {
                        Uuid::fromString((string) base64_decode($fileId));
                    } catch (InvalidUuidStringException $exception) {
                        $fail(\sprintf($messages->get('files.invalid'), $attribute));
                    }
                }
            },
        ];
    }
}
