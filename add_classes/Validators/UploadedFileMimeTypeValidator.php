<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

final class UploadedFileMimeTypeValidator extends Validator
{
    protected array $mimeTypes;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        array $mimeTypes = [],
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->mimeTypes = $mimeTypes;

        parent::__construct($validatorAdapter, $messages, $labels, $fields);
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'file' => 'file',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'file' => 'File',
        ];
    }

    /**
    * {@inheritdoc}
    */
    protected function messages(): array
    {
        return [
            'status.formatIncorrect' => \translate('systmess_error_incorrect_file_format'),
        ];
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
                'field' => $fields->get('file'),
                'label' => $labels->get('file'),
                'rules' => $this->getFileRules($messages),
            ],
        ];
    }

    /**
     * Get the file validation rules.
     */
    protected function getFileRules(ParameterBag $messages): array
    {
        $rules = [
            'not_empty' => '',
            function (string $attr, $uploadedFile, \Closure $fail) use ($messages) {
                if (null === $uploadedFile) {
                    return;
                }

                $uploadedMime = $uploadedFile->getMimeType();

                if (!in_array($uploadedMime, $this->mimeTypes)) {
                    $fail($messages->get('status.formatIncorrect'));

                    return;
                }
            },
        ];

        return $rules;
    }
}
