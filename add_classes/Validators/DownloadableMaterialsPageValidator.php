<?php

declare(strict_types=1);

namespace App\Validators;

use Closure;
use Country_Model;
use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use Symfony\Component\HttpFoundation\ParameterBag;

class DownloadableMaterialsPageValidator extends Validator
{
    const TITLE_MAX_LENGTH = 250;
    const SHORT_DESCRIPTION_MAX_LENGTH = 500;

    private $uploadFolder = null;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        ?string $uploadFolder = null,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->uploadFolder = $uploadFolder;

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
                'rules' => $this->getTitleRules(static::TITLE_MAX_LENGTH, $messages),
            ],
            'shortDescription' => [
                'field' => $fields->get('shortDescription'),
                'label' => $labels->get('shortDescription'),
                'rules' => $this->getShortDescriptionRules(static::SHORT_DESCRIPTION_MAX_LENGTH, $messages),
            ],
            'content' => [
                'field' => $fields->get('content'),
                'label' => $labels->get('content'),
                'rules' => $this->getContentRules($messages),
            ],
            'cover' => [
                'field' => $fields->get('cover'),
                'label' => $labels->get('cover'),
                'rules' => $this->getCoverRules($messages),
            ],
            'file' => [
                'field' => $fields->get('file'),
                'label' => $labels->get('file'),
                'rules' => $this->getFileRules($messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'shortDescription'  => 'short_description',
            'content'           => 'content',
            'title'             => 'title',
            'cover'             => 'cover_image',
            'file'             => 'file',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'shortDescription'  => 'Short Description',
            'content'           => 'Content',
            'title'             => 'Title',
            'cover'             => 'Cover image',
            'file'              => 'PDF file',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'coverImage.invalidPath'    => 'The cover image contains an invalid path',
            'pdf.invalidPath'           => 'The pdf file contains an invalid path',
        ];
    }

    /**
     * Get the title field validation rules.
     */
    protected function getTitleRules(int $maxLength, ParameterBag $messages): array
    {
        return [
            'required'               => $messages->get('title.required') ?? '',
            "max_len[{$maxLength}]"  => $messages->get('title.maxLength') ?? '',
        ];
    }

    /**
     * Get the short description field validation rules.
     */
    protected function getShortDescriptionRules(int $maxLength, ParameterBag $messages): array
    {
        return [
            'required'               => $messages->get('shortDescription.required') ?? '',
            "max_len[{$maxLength}]"  => $messages->get('shortDescription.maxLength') ?? '',
        ];
    }

    /**
     * Get the content field validation rules.
     */
    protected function getContentRules(ParameterBag $messages): array
    {
        return [
            'required' => $messages->get('shortDescription.required') ?? '',
        ];
    }

    /**
     * Get the cover image field validation rules.
     */
    protected function getCoverRules(ParameterBag $messages): array
    {
        return [
            function (string $attr, $imagePath, callable $fail) use ($messages) {
                if (empty($imagePath)) {
                    return;
                }

                $tempImagePath = getTempImgPath('downloadable_materials.cover', ['{ENCRYPTED_FOLDER_NAME}' => $this->uploadFolder]);

                if (!startsWith($imagePath, $tempImagePath)) {
                    $fail(sprintf($messages->get('coverImage.invalidPath'), $attr));
                }
            },
        ];
    }

    /**
     * Get the pdf file field validation rules.
     */
    protected function getFileRules(ParameterBag $messages): array
    {
        return [
            function (string $attr, $fileName, callable $fail) use ($messages) {
                if (empty($fileName)) {
                    return;
                }

                $tempFilePath = str_replace('{ENCRYPTED_FOLDER_NAME}', $this->uploadFolder, (string) config('files.downloadable_materials.pdf.temp_folder_path')) . $fileName;

                if (!file_exists($tempFilePath)) {
                    $fail(sprintf($messages->get('pdf.invalidPath'), $attr));
                }
            },
        ];
    }
}
