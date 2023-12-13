<?php

declare(strict_types=1);

namespace App\Validators;

use App\Common\Validation\Legacy\Standalone\Validator;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\NestedValidationData;
use Symfony\Component\HttpFoundation\ParameterBag;

class ItemsCompilationValidator extends Validator
{
    protected $action;
    protected $itemsRepository;

    public function __construct(
        ValidatorAdapter $validatorAdapter,
        string $action = 'add_complain',
        $elasticsearchItemsModel,
        ?array $messages = null,
        ?array $labels = null,
        ?array $fields = null
    ) {
        $this->action = $action;
        $this->itemsRepository = $elasticsearchItemsModel;

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
        $itemsRepository = $this->itemsRepository;

        return [
            'title' => [
                'field' => $fields->get('title'),
                'label' => $labels->get('title'),
                'rules' => $this->getTitleRules(),
            ],
            'url' => [
                'field' => $fields->get('url'),
                'label' => $labels->get('url'),
                'rules' => $this->getUrlRules(),
            ],
            'itemsIds' => [
                'field' => $fields->get('itemsIds'),
                'label' => $labels->get('itemsIds'),
                'rules' => $this->getItemsIdsRules($messages, $itemsRepository),
            ],
            'upload_folder' => [
                'field' => $fields->get('uploadFolder'),
                'label' => $labels->get('uploadFolder'),
                'rules' => $this->getUploadFolderRules($messages),
            ],
            'tablet_image' => [
                'field' => $fields->get('tabletImage'),
                'label' => $labels->get('tabletImage'),
                'rules' => $this->getTabletImageRules($messages),
            ],
            'desktop_image' => [
                'field' => $fields->get('desktopImage'),
                'label' => $labels->get('desktopImage'),
                'rules' => $this->getDesktopImageRules($messages),
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fields(): array
    {
        return [
            'title'         => 'title',
            'url'           => 'url',
            'itemsIds'      => 'itemsIds',
            'tabletImage'   => 'tablet_image',
            'desktopImage'  => 'desktop_image',
            'uploadFolder'  => 'upload_folder',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function labels(): array
    {
        return [
            'title'         => 'Title',
            'url'           => 'URL',
            'itemsIds'      => 'Items',
            'tabletImage'   => 'Tablet image',
            'desktopImage'  => 'Desktop image',
            'uploadFolder'  => 'Upload Folder',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function messages(): array
    {
        return [
            'uploadFolder.wrongFormat'  => translate('systmess_error_invalid_data'),
            'uploadFolder.invalidName'  => translate('invalid_encrypted_folder_name'),
            'categories.wrongFormat'    => translate('systmess_error_invalid_data'),
            'desktopImage.notExist'     => 'Desktop image not found',
            'tabletImage.notExist'      => 'Tablet image not found',
            'items.fewCount'            => translate('systmess_error_validation_min_items'),
            'items.muchCount'           => translate('systmess_error_validation_max_items'),
            'items.countIsNull'         => translate('systmess_error_validation_null_items'),

        ];
    }

    protected function getTitleRules(): array
    {
        return [
            'required'      => '',
            'max_len[256]'  => '',
        ];
    }

    protected function getUrlRules(): array
    {
        return [
            'required'  => '',
            'valid_url' => '',
        ];
    }

    protected function getItemsIdsRules(ParameterBag $messages, $itemsRepository): array
    {
        return [
            'required' => '',
            function ($attribute, ?NestedValidationData $itemIds, $fail) use ($messages, $itemsRepository) {
                if ($itemIds === null) {
                    $fail(\sprintf($messages->get('items.countIsNull'), $attribute));

                    return;
                }

                $countIds = $itemIds->count();
                $itemIdsList = iterator_to_array($itemIds->getIterator());

                if ($countIds < 4) {
                    $fail(\sprintf($messages->get('items.fewCount'), $attribute));

                    return;
                }

                if ($countIds > 10) {
                    $fail(\sprintf($messages->get('items.muchCount'), $attribute));

                    return;
                }

                $items = $itemsRepository->get_items(['list_item' => $itemIdsList]);
                if ($countIds !== count($items)) {
                    $fail(\sprintf($messages->get('items.muchCount'), $attribute));

                    return;
                }
            }
        ];
    }

    protected function getUploadFolderRules(ParameterBag $messages): array
    {
        return [
            'required'  => '',
            function (string $attr, $uploadFolder, callable $fail) use ($messages) {
                if (!is_string($uploadFolder)) {
                    $fail($messages->get('uploadFolder.wrongFormat'));

                    return;
                }

                if (!checkEncriptedFolder($uploadFolder)) {
                    $fail($messages->get('uploadFolder.invalidName'));

                    return;
                }
            },
        ];
    }

    protected function getTabletImageRules(ParameterBag $messages): array
    {
        $rules = 'add_complain' === $this->action ? ['required' => ''] : [];
        $rules[] = function (string $attr, $imageName, callable $fail) use ($messages) {
            if (empty($imageName)) {
                return;
            }

            if (!file_exists(getTempImgPath('items_compilation.tablet', ['{ENCRYPTED_FOLDER_NAME}' => checkEncriptedFolder($this->getValidationData()->get('upload_folder'))]) . $imageName)) {
                $fail($messages->get('tabletImage.notExist'));

                return;
            }
        };

        return $rules;
    }

    protected function getDesktopImageRules(ParameterBag $messages): array
    {
        $rules = 'add_complain' === $this->action ? ['required' => ''] : [];
        $rules[] = function (string $attr, $imageName, callable $fail) use ($messages) {
            if (empty($imageName)) {
                return;
            }

            if (!file_exists(getTempImgPath('items_compilation.desktop', ['{ENCRYPTED_FOLDER_NAME}' => checkEncriptedFolder($this->getValidationData()->get('upload_folder'))]) . $imageName)) {
                $fail($messages->get('desktopImage.notExist'));

                return;
            }
        };

        return $rules;
    }
}
