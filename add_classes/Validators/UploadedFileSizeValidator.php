<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

final class UploadedFileSizeValidator extends Validator
{
    protected int $maxFileSize;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        int $maxFileSize = 10_485_760,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->maxFileSize = $maxFileSize;

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
            'status.sizeIncorrect' => \translate('systmess_error_file_size_large'),
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

                $uploadedSize = $uploadedFile->getSize();

                if ($uploadedSize > $this->maxFileSize) {
                    $fail($messages->get('status.sizeIncorrect'));

                    return;
                }
            },
        ];

        return $rules;
    }
}
