<?php

declare(strict_types=1);

use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\Traits\FileuploadOptionsAwareTrait;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Common\Validation\ValidationException;
use App\Filesystem\FilePathGenerator;
use App\Filesystem\ShippingMethodsPathGenerator;
use App\Validators\ShippingMethodValidator;
use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;
use TinyMVC_Library_Filesystem as LegacyFilesystemProvider;
use TinyMVC_Library_Image_intervention as LegacyImageHandler;

/**
 * Controller Shipping_methods.
 */
class Shipping_methods_Controller extends TinyMVC_Controller
{
    use FileuploadOptionsAwareTrait {
        FileuploadOptionsAwareTrait::getFileuploadOptions as getFormattedFileuploadOptions;
    }

    /**
     * Index page.
     */
    public function administration(): void
    {
        if (!have_right('shipping_methods_administration')) {
            show_404();
        }

        /** @var Shipping_Types_Model $shippingTypesModel */
        $shippingTypesModel = model(Shipping_Types_Model::class);

        views(
            [
                'admin/header_view',
                'admin/shipping_methods/index_view',
                'admin/footer_view',
            ],
            [
                'title'         => 'Shipping Types',
                'shippingTypes' => $shippingTypesModel->findAll(),
            ]
        );
    }

    public function ajax_admin_all_shipping_methods_dt(): void
    {
        checkIsAjax();
        checkPermisionAjaxDT('shipping_methods_administration');

        /** @var Shipping_Types_Model $shippingTypesModel */
        $shippingTypesModel = model(Shipping_Types_Model::class);

        $request = request()->request;
        $queryOrderBy = dtOrdering(
            $request->all(),
            [
                'id'        => "`{$shippingTypesModel->getTable()}`.`id_type`",
                'isVisible' => "`{$shippingTypesModel->getTable()}`.`is_visible`",
                'createdAt' => "`{$shippingTypesModel->getTable()}`.`created_at`",
                'updatedAt' => "`{$shippingTypesModel->getTable()}`.`updated_at`",
            ],
            fn ($ordering) => [$ordering['column'] => strtoupper($ordering['direction'])]
        ) ?: [["`{$shippingTypesModel->getTable()}`.`id_type`" => 'DESC']];

        $shippingMethodsConditions = dtConditions($request->all(), [
            ['as' => 'keywords',               'key' => 'keywords',             'type' => 'trim'],
            ['as' => 'createDateGte',          'key' => 'create_date_from',     'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'createDateLte',          'key' => 'create_date_to',       'type' => 'getDateFormat:m/d/Y,Y-m-d'],
        ]);

        $shippingMethodsQueryParams = [
            'columns'       => [
                "`{$shippingTypesModel->getTable()}`.`id_type`",
                "`{$shippingTypesModel->getTable()}`.`type_name`",
                "`{$shippingTypesModel->getTable()}`.`type_description`",
                "`{$shippingTypesModel->getTable()}`.`full_description`",
                "`{$shippingTypesModel->getTable()}`.`image`",
                "`{$shippingTypesModel->getTable()}`.`is_visible`",
                "`{$shippingTypesModel->getTable()}`.`created_at`",
                "`{$shippingTypesModel->getTable()}`.`updated_at`",
            ],
            'conditions' => $shippingMethodsConditions,
            'order'      => array_shift($queryOrderBy),
            'skip'       => abs($request->getInt('iDisplayStart')),
            'limit'      => abs($request->getInt('iDisplayLength', 10)),
            'group'      => [
                "`{$shippingTypesModel->getTable()}`.`id_type`",
            ],
        ];

        // region DataTable Parameters
        $shippingMethodsCount = $shippingTypesModel->countAllBy(array_diff_key($shippingMethodsQueryParams, array_flip(['skip', 'limit', 'order', 'group'])));

        $shippingTypes = $shippingTypesModel->findAllBy($shippingMethodsQueryParams);

        $output = [
            'sEcho'                => $request->getInt('sEcho'),
            'iTotalRecords'        => $shippingMethodsCount,
            'iTotalDisplayRecords' => $shippingMethodsCount,
            'aaData'               => [],
        ];
        // endregion DataTable Parameters

        foreach ($shippingTypes ?: [] as $shippingType) {
            if ($shippingType['is_visible']) {
                $publishReviewBtn = <<<PUBLISH
                    <a class="ep-icon ep-icon_ok txt-green confirm-dialog"
                        data-callback="visibleStatus"
                        data-message="Are you sure you want to un-publish this method?"
                        title="Unpublish banner"
                        data-id="{$shippingType['id_type']}"
                    ></a>
                PUBLISH;
            } else {
                $publishReviewBtn = <<<PUBLISH
                    <a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                        data-callback="visibleStatus"
                        data-message="Are you sure you want to publish this method?"
                        title="Publish banner"
                        data-id="{$shippingType['id_type']}"
                    ></a>
                PUBLISH;
            }

            $methodDisplayImage = sprintf(
                <<<'DISPLAY'
                    <img class="mw-50 mh-50" src="%s" alt="">
                DISPLAY,
                getDisplayImageLink(['{FILE_NAME}' => $shippingType['image']], 'shipping_methods.main')
            );

            $editAction = sprintf(
                <<<'EDIT'
                    <li>
                        <a class="fancyboxValidateModalDT fancybox.ajax" href="%s" title="Edit" data-title="Edit">
                            <span class="ep-icon ep-icon_pencil"></span> Edit
                        </a>
                    </li>
                EDIT,
                __SITE_URL . 'shipping_methods/popup_forms/edit/' . $shippingType['id_type']
            );

            $deleteAction = sprintf(
                <<<'DELETE'
                    <li>
                        <a class="confirm-dialog"
                            data-callback="deleteShippingMethod"
                            data-message="Do you really want to delete the method?"
                            data-id="%s"
                            title="Delete shipping method">
                            <span class="ep-icon ep-icon_remove"></span> Delete
                        </a>
                    </li>
                DELETE,
                $shippingType['id_type']
            );

            /** @var TinyMVC_Library_CleanHtml $cleanHtmlLibrary */
            $cleanHtmlLibrary = library(TinyMVC_Library_CleanHtml::class);

            $output['aaData'][] = [
                'id'                => $shippingType['id_type'],
                'name'              => cleanOutput($shippingType['type_name']),
                'small_description' => cleanOutput($shippingType['type_description']),
                'full_description'  => cleanOutput($cleanHtmlLibrary->sanitize($shippingType['full_description'])),
                'image'             => $methodDisplayImage,
                'is_visible'        => $publishReviewBtn,
                'createdAt'         => $shippingType['created_at']->format(\App\Common\PUBLIC_DATETIME_FORMAT),
                'updatedAt'         => $shippingType['updated_at']->format(\App\Common\PUBLIC_DATETIME_FORMAT),
                'dt_actions'        => sprintf(
                    <<<ACTIONS
                    <div class="dropdown">
                        <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                           {$editAction}
                           {$deleteAction}
                        </ul>
                    </div>
                    ACTIONS
                ),
            ];
        }
        jsonResponse('', 'success', $output);
    }

    public function popup_forms()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate('systmess_error_should_be_logged'));
        }

        switch (uri()->segment(3)) {
            case 'add':
            checkIsAjax();
            checkPermisionAjaxDT('shipping_methods_administration');

                views(
                    [
                        'admin/shipping_methods/popup_forms/form_view',
                    ],
                    [
                        'uploadOptions' => $this->getFormattedFileuploadOptions(
                            explode(',', config('img.shipping_methods.main.rules.format', 'jpg,jpeg,png')),
                            1,
                            1,
                            (int) config('img.shipping_methods.main.rules.size', 10 * 1024 * 1024),
                            config('img.shipping_methods.main.rules.size_placeholder', '10MB'),
                            [
                                'width'  => config('img.shipping_methods.main.rules.width'),
                                'height' => config('img.shipping_methods.main.rules.height'),
                            ],
                            getUrlForGroup('shipping_methods/ajax_shipping_method_upload_image')
                        ),
                    ]
                );

                break;
            case 'edit':
                checkIsAjax();
                checkPermisionAjax('shipping_methods_administration');

                $idShippingMethod = (int) uri()->segment(4);

                /** @var Shipping_Types_Model $shippingTypesModel */
                $shippingTypesModel = model(Shipping_Types_Model::class);

                if (!$shippingType = $shippingTypesModel->findOne($idShippingMethod)) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var FilesystemProviderInterface */
                $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $storage = $filesystemProvider->storage('public.storage');
                $prefixer = $filesystemProvider->prefixer('public.storage');

                try {
                    $mimeType = (new MimeTypes())->guessMimeType(
                        $prefixer->prefixPath(
                            $filePath = ShippingMethodsPathGenerator::methodImage($shippingType['image'])
                        )
                    );
                } catch (\Throwable $th) {
                    // No image
                }

                $uploadedImage = [
                    'url'  => $storage->url($filePath),
                    'name' => $shippingType['image'],
                    'type' => $mimeType,
                ];

                views(
                    [
                        'admin/shipping_methods/popup_forms/form_view',
                    ],
                    [
                        'shippingType'  => $shippingType,
                        'uploadOptions' => $this->getFormattedFileuploadOptions(
                            explode(',', config('img.shipping_methods.main.rules.format', 'jpg,jpeg,png')),
                            1,
                            1,
                            (int) config('img.shipping_methods.main.rules.size', 10 * 1024 * 1024),
                            config('img.shipping_methods.main.rules.size_placeholder', '10MB'),
                            [
                                'width'  => config('img.shipping_methods.main.rules.width'),
                                'height' => config('img.shipping_methods.main.rules.height'),
                            ],
                            getUrlForGroup('shipping_methods/ajax_shipping_method_upload_image')
                        ),
                        'uploadedImages' => [$uploadedImage],
                    ]
                );

                break;
        }
    }

    // region image
    public function ajax_shipping_method_upload_image()
    {
        checkIsAjax();
        checkPermisionAjax('shipping_methods_administration');

        $request = request();
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = current((array) ($request->files->get('files') ?? [])) ?: null;
        if (null === $uploadedFile) {
            jsonResponse(translate('validation_image_required'));
        }
        if (is_array($uploadedFile) || !$uploadedFile->isValid()) {
            jsonResponse(translate('validation_invalid_file_provided'));
        }

        // Next we need to take our filesystem for temp directory
        /** @var LegacyFilesystemProvider $filesystemProvider */
        $filesystemProvider = $this->getContainer()->get(LibraryLocator::class)->get(LegacyFilesystemProvider::class);
        $tempDisk = $filesystemProvider->disk('temp.storage');

        /** @var LegacyImageHandler $imageHandler */
        $imageHandler = $this->getContainer()->get(LibraryLocator::class)->get(LegacyImageHandler::class);
        // Given that we need to validate the file and it would take a long time
        // to write the new proper validation we can only use what we have.
        try {
            $imageHandler->assertImageIsValid(
                $imageHandler->makeImageFromFile($uploadedFile),
                config('img.shipping_methods.main.rules'),
                $uploadedFile->getClientOriginalName()
            );
        } catch (ValidationException $e) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolationInterface $violation) { return $violation->getMessage(); },
                    \iterator_to_array($e->getValidationErrors()->getIterator())
                )
            );
        } catch (ReadException $e) {
            jsonResponse(translate('validation_images_upload_fail'), 'error', withDebugInformation(
                [],
                ['exception' => throwableToArray($e)]
            ));
        }

        // And write file there
        try {
            $tempDisk->write(
                // But first we need to get the full path to the file
                FilePathGenerator::uploadedFile($fileName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), $uploadedFile->getClientOriginalExtension())),
                $uploadedFile->getContent()
            );
        } catch (UnableToWriteFile $e) {
            jsonResponse(translate('validation_images_upload_fail'));
        }

        jsonResponse(null, 'success', ['files' => ['name' => $fileName]]);
    }
    // endregion image

    public function ajax_operations(): void
    {
        checkIsAjax();
        checkPermisionAjax('shipping_methods_administration');

        $request = request()->request;

        switch (uri()->segment(3)) {
            case 'visible_status':
                checkIsAjax();
                checkPermisionAjax('shipping_methods_administration');

                /** @var Shipping_Types_Model $shippingTypesModel */
                $shippingTypesModel = model(Shipping_Types_Model::class);

                if (
                    empty($shippingTypesId = $request->getInt('id'))
                    || empty($shippingType = $shippingTypesModel->findOne($shippingTypesId))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (!$shippingTypesModel->updateOne($shippingTypesId, [
                    'is_visible'  => $shippingType['is_visible'] ? 0 : 1, ])) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                jsonResponse(translate('systmess_success_shipping_methods_update_is_visible_status'), 'success');

                break;
            case 'delete':
                checkIsAjax();
                checkPermisionAjax('shipping_methods_administration');

                /** @var Shipping_Types_Model $shippingTypesModel */
                $shippingTypesModel = model(Shipping_Types_Model::class);

                if (empty($shippingTypesId = $request->getInt('id'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (!$shippingTypesModel->deleteOne($shippingTypesId)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                jsonResponse(translate('systmess_success_shipping_method_delete'), 'success');

            break;
            case 'add':
                checkIsAjax();
                checkPermisionAjax('shipping_methods_administration');

                // region Validate
                try {
                    $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
                    $validator = new ShippingMethodValidator($adapter);
                    if (!$validator->validate($request->all())) {
                        throw new ValidationException('Validation failed', 0, null, $validator->getViolations());
                    }
                } catch (ValidationException $e) {
                    jsonResponse(
                        \array_merge(
                            \array_map(
                                fn (ConstraintViolation $violation) => $violation->getMessage(),
                                \iterator_to_array($e->getValidationErrors()->getIterator())
                            ),
                        ),
                        'error',
                        !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
                    );
                }
                // endregion Validate

                if (empty($request->get('file'))) {
                    jsonResponse(translate('validation_image_required'));
                }

                /** @var Shipping_Types_Model $shippingTypesModel */
                $shippingTypesModel = model(Shipping_Types_Model::class);

                $typeAlias = preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($request->get('alias')));

                if (empty($shippingTypesId = $shippingTypesModel->insertOne([
                    'type_name'        => $request->get('name'),
                    'type_alias'       => $typeAlias,
                    'type_description' => $request->get('short_desc'),
                    'full_description' => $request->get('full_desc'),
                    'image'            => $request->get('file'),
                ]))) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                // //region Image
                /** @var FilesystemProviderInterface */
                $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $tempStorage = $filesystemProvider->storage('temp.storage');
                $storage = $filesystemProvider->storage('public.storage');

                try {
                    $storage->write(
                        ShippingMethodsPathGenerator::methodImage($request->get('file')),
                        $tempStorage->read(
                            FilePathGenerator::uploadedFile($request->get('file'))
                        )
                    );
                } catch (Exception $e) {
                    $shippingTypesModel->deleteOne($shippingTypesId);
                    jsonResponse(translate('validation_images_upload_fail'));
                }
                // //endregion Image

                jsonResponse(translate('systmess_success_shipping_methods_create'), 'success');

                break;
                case 'edit':
                    checkIsAjax();
                    checkPermisionAjax('shipping_methods_administration');

                    // region Validate
                    try {
                        $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
                        $validator = new ShippingMethodValidator($adapter, false);
                        if (!$validator->validate($request->all())) {
                            throw new ValidationException('Validation failed', 0, null, $validator->getViolations());
                        }
                    } catch (ValidationException $e) {
                        jsonResponse(
                            \array_merge(
                                \array_map(
                                    fn (ConstraintViolation $violation) => $violation->getMessage(),
                                    \iterator_to_array($e->getValidationErrors()->getIterator())
                                ),
                            ),
                            'error',
                            !DEBUG_MODE ? [] : ['errors' => [throwableToArray($e)]]
                        );
                    }
                    // endregion Validate

                    /** @var Shipping_Types_Model $shippingTypesModel */
                    $shippingTypesModel = model(Shipping_Types_Model::class);

                    $shippingTypesId = $request->getInt('id');

                    if (empty($shippingTypesId) || empty($shippingType = $shippingTypesModel->findOne($shippingTypesId))) {
                        jsonResponse(translate('systmess_error_invalid_data'));
                    }

                    // region Image
                    if (empty($shippingTypesImage = (string) $request->get('file'))) {
                        jsonResponse(translate('validation_image_required'));
                    }
                    $newImageWasUploaded = $shippingTypesImage !== $shippingType['image'];

                    if ($newImageWasUploaded) {
                        /** @var FilesystemProviderInterface */
                        $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                        $tempStorage = $filesystemProvider->storage('temp.storage');
                        $storage = $filesystemProvider->storage('public.storage');

                        try {
                            $storage->write(
                                ShippingMethodsPathGenerator::methodImage($shippingTypesImage),
                                $tempStorage->read(
                                    FilePathGenerator::uploadedFile($shippingTypesImage)
                                )
                            );
                        } catch (Exception $e) {
                            jsonResponse(translate('validation_images_upload_fail'));
                        }
                    }

                    $update = [
                        'type_name'        => $request->get('name'),
                        'type_description' => $request->get('short_desc'),
                        'full_description' => $request->get('full_desc'),
                        'image'            => $shippingTypesImage,
                    ];

                    if (!$shippingTypesModel->updateOne($shippingTypesId, $update)) {
                        jsonResponse(translate('systmess_internal_server_error'));
                    }

                    if ($newImageWasUploaded) {
                        try {
                            if ($storage->fileExists($shippingType['image'])) {
                                $storage->delete($shippingType['image']);
                            }
                        } catch (Exception $e) {
                            jsonResponse(translate('validation_images_upload_fail'));
                        }
                    }
                    // endregion Image

                    jsonResponse(translate('systmess_success_shipping_methods_update'), 'success');

                    break;
        }
    }
}

// End of file shipping_methods.php
// Location: /tinymvc/myapp/controllers/shipping_methods.php
