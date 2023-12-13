<?php

declare(strict_types=1);

use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Validators\ItemsCompilationValidator;
/**
 * Controller Items_compilation
 */
class Items_compilation_Controller extends TinyMVC_Controller
{
    public function index(): void
    {
        show_404();
    }

    public function administration(): void
    {
        checkPermision('items_compilation_administration');

        views(
            [
                'admin/header_view',
                'admin/items_compilation/index_view',
                'admin/footer_view'
            ],
            [
                'title' => 'Items compilation',
            ]
        );
    }

    public function ajax_dt_administration(): void
    {
        checkIsAjax();
        checkPermisionAjaxDT('items_compilation_administration');

        $request = request()->request;

        /** @var Items_Compilation_Model $itemsCompilationModel */
        $itemsCompilationModel = model(Items_Compilation_Model::class);

        $queryParams = [
            'conditions' => dtConditions($request->all(), [
                ['as' => 'isPublished',     'key' => 'is_published',    'type' => 'int'],
            ]),
            'order' => array_column(
                dtOrdering($request->all(), ['id'   => 'id',]),
                'direction',
                'column'
            ),
            'limit' => abs($request->getInt('iDisplayLength')),
            'skip'  => abs($request->getInt('iDisplayStart')),
        ];

        $itemsCompilation = $itemsCompilationModel->findAllBy($queryParams);
        $itemsCompilationCount = $itemsCompilationModel->countBy(array_intersect_key($queryParams, ['conditions' => '']));

        $output = [
            'iTotalDisplayRecords'  => $itemsCompilationCount,
            'iTotalRecords'         => $itemsCompilationCount,
            'aaData'                => [],
            'sEcho'                 => request()->request->getInt('sEcho'),
        ];

        if (empty($itemsCompilation)) {
            jsonResponse('', 'success', $output);
        }

        foreach ($itemsCompilation as $compilation) {
            if ($compilation['is_published']) {
                $publishReviewBtn = <<<PUBLISH
                    <a class="ep-icon ep-icon_ok txt-green confirm-dialog"
                        data-callback="toglePublishStatus"
                        data-message="Are you sure you want to un-publish this items compilation?"
                        title="Unpublish compilation"
                        data-id="{$compilation['id']}"
                    ></a>
                PUBLISH;
            } else {
                $publishReviewBtn = <<<PUBLISH
                    <a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                        data-callback="toglePublishStatus"
                        data-message="Are you sure you want to publish this compilation?"
                        title="Publish compilation"
                        data-id="{$compilation['id']}"
                    ></a>
                PUBLISH;
            }

            $editCompilationUrl = __SITE_URL . 'items_compilation/popup_forms/edit_compilation/' . $compilation['id'];

            $output['aaData'][] = [
                'id'            => $compilation['id'],
                'title'         => cleanOutput($compilation['title']),
                'url'           => cleanOutput($compilation['url']),
                'isPublished'   => $publishReviewBtn,
                'actions'           => <<<ACTIONS
                    <div class="dropdown">
                        <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                            <li>
                                <a class="fancyboxValidateModalDT fancybox.ajax" href="{$editCompilationUrl}" title="Edit" data-title="Edit compilation #{$compilation['id']}" data-table="dtItemsCompilation">
                                    <span class="ep-icon ep-icon_pencil"></span> Edit a compilation
                                </a>
                            </li>
                            <li>
                                <a class="confirm-dialog" data-callback="deleteCompilation" data-message="Are you sure you want to delete this compilation?" title="Delete a compilation" data-id="{$compilation['id']}">
                                    <span class="ep-icon ep-icon_remove txt-red"></span> Delete a compilation
                                </a>
                            </li>
                        </ul>
                    </div>
                ACTIONS,
            ];
        }

        jsonResponse('', 'success', $output);
    }

    public function popup_forms(): void
    {
        checkIsAjax();

        switch (uri()->segment(3)) {
            case 'add_compilation':
                checkPermisionAjaxModal('items_compilation_administration');

                views(
                    [
                        'admin/items_compilation/form_view'
                    ],
                    [
                        'uploadFolder'  => encriptedFolderName(),
                    ]
                );

                break;
            case 'edit_compilation':
                checkPermisionAjaxModal('items_compilation_administration');

                /** @var Items_Compilation_Model $itemsCompilationModel */
                $itemsCompilationModel = model(Items_Compilation_Model::class);

                if (empty($compilationId = (int) uri()->segment(4))) {
                    messageInModal('Compilation id is expected.');
                }

                if (empty($compilation = $itemsCompilationModel->findOne($compilationId, ['with' => ['itemsRelations']]))) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var Item_Compilation_Relation_Model $itemCompilationRelationModel */
                $itemCompilationRelationModel = model(Item_Compilation_Relation_Model::class);

                /** @var Elasticsearch_Items_Model $elasticsearchItemsModel */
                $elasticsearchItemsModel = model(Elasticsearch_Items_Model::class);

                if (null !== $compilation['items_relations']) {
                    $items = $elasticsearchItemsModel->get_items([
                        'list_item'     => array_column($compilation['items_relations']->toArray(), 'id'),
                    ]);

                    $selectedItems = [];

                    foreach ($items as $item) {
                        $selectedItems[] = [
                            'id'       => $item['id'],
                            'title'    => $item['title'],
                            'photoUrl' => getDisplayImageLink(['{ID}' => $item['id'], '{FILE_NAME}' => $item['photo_name']], 'items.main', ['thumb_size' => 2]),
                            'itemUrl'  => makeItemUrl($item['id'], $item['title']),
                        ];
                    }
                }

                views(
                    [
                        'admin/items_compilation/form_view'
                    ],
                    [
                        'uploadFolder'    => encriptedFolderName(),
                        'compilation'     => $compilation,
                        'selectedItems'   => $selectedItems ?: [],
                    ]
                );
                break;
            default:
                messageInModal(translate('systmess_error_invalid_data'));

                break;
        }
    }

    public function ajax_search_items()
    {
        checkIsAjax();

        $request = request()->request;
        if (empty($keywords = $request->get('keywords'))) {
            jsonResponse('', 'success', ['items' => []]);
        }

        /** @var Elasticsearch_Items_Model $elasticsearchItemsModel */
        $elasticsearchItemsModel = model(Elasticsearch_Items_Model::class);

        $items = $elasticsearchItemsModel->get_items([
            'random_score'  => true,
            'keywords'      => $keywords,
            'per_p'         => $request->getInt('perPage') ?: 20,
        ]);

        $data = [];

        foreach ($items as $item) {
            $data[] = [
                'id'       => $item['id'],
                'title'    => $item['title'],
                'photoUrl' => getDisplayImageLink(['{ID}' => $item['id'], '{FILE_NAME}' => $item['photo_name']], 'items.main', ['thumb_size' => 2]),
                'itemUrl'  => makeItemUrl($item['id'], $item['title']),
            ];
        }

        jsonResponse('', 'success', ['items' => [$data]]);
    }

    public function ajax_operations(): void
    {
        checkIsAjax();

        switch (uri()->segment(3)) {
            case 'add_compilation':
                checkPermisionAjax('items_compilation_administration');

                $request = request()->request;

                $validator = new ItemsCompilationValidator(new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class)), 'add_complain', model(Elasticsearch_Items_Model::class));
                if (!$validator->validate($request->all())) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolation $violation) {
                                return $violation->getMessage();
                            },
                            \iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }

                $uploadFolder = checkEncriptedFolder($request->get('upload_folder'));

                $desktopImageName = $request->get('desktop_image');
                $desktopImagePath = getImgPath('items_compilation.desktop');
                create_dir($desktopImagePath);

                if (!rename(
                    getTempImgPath('items_compilation.desktop', ['{ENCRYPTED_FOLDER_NAME}' => $uploadFolder]) . $desktopImageName,
                    $desktopImagePath . $desktopImageName
                )) {
                    jsonResponse('Some errors occured while uploading desktop image.');
                }

                $tabletImageName = $request->get('tablet_image');
                $tabletImagePath = getImgPath('items_compilation.tablet');
                create_dir($tabletImagePath);

                if (!rename(
                    getTempImgPath('items_compilation.tablet', ['{ENCRYPTED_FOLDER_NAME}' => $uploadFolder]) . $tabletImageName,
                    $tabletImagePath . $tabletImageName
                )) {
                    jsonResponse('Some errors occured while uploading tablet image.');
                }

                $itemsCompilation = [
                    'background_images' => [
                        'tablet'    => $request->get('tablet_image'),
                        'desktop'   => $request->get('desktop_image'),
                    ],
                    'title'             => $request->get('title'),
                    'url'               => $request->get('url'),
                ];

                /** @var Items_Compilation_Model $itemsCompilationModel */
                $itemsCompilationModel = model(Items_Compilation_Model::class);

                if (empty($compilationId = $itemsCompilationModel->insertOne($itemsCompilation))) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                /** @var Item_Compilation_Relation_Model $itemCompilationRelationModel */
                $itemCompilationRelationModel = model(Item_Compilation_Relation_Model::class);

                $itemsIds = (array) $request->get('itemsIds');
                $newRelations = [];
                foreach ($itemsIds as $itemId) {
                    $newRelations[] = [
                        'item_id'        => $itemId,
                        'compilation_id' => $compilationId,
                    ];
                }

                $itemCompilationRelationModel->insertMany($newRelations);

                jsonResponse(translate('systmess_success_items_compilation_save'), 'success');

                break;
            case 'edit_compilation':
                checkPermisionAjax('items_compilation_administration');

                $request = request()->request;

                /** @var Items_Compilation_Model $itemsCompilationModel */
                $itemsCompilationModel = model(Items_Compilation_Model::class);

                if (
                    empty($compilationId = $request->getInt('id'))
                    || empty($compilation = $itemsCompilationModel->findOne($compilationId))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $validator = new ItemsCompilationValidator(new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class)), 'edit_compain', model(Elasticsearch_Items_Model::class));
                if (!$validator->validate($request->all())) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolation $violation) {
                                return $violation->getMessage();
                            },
                            \iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }

                $deletedImages = $imagesToOptimization = [];
                $uploadFolder = checkEncriptedFolder($request->get('upload_folder'));

                if (!empty($desktopImageName = $request->get('desktop_image'))) {
                    $desktopImagePath = getImgPath('items_compilation.desktop');
                    create_dir($desktopImagePath);

                    if (!rename(
                        getTempImgPath('items_compilation.desktop', ['{ENCRYPTED_FOLDER_NAME}' => $uploadFolder]) . $desktopImageName,
                        $desktopImagePath . $desktopImageName
                    )) {
                        jsonResponse('Some errors occured while uploading desktop image.');
                    }

                    $deletedImages[] = $desktopImagePath . $compilation['background_images']['desktop'];

                    $imagesToOptimization[] = [
                        'file_path'	=> getcwd() . DS . $desktopImagePath . $desktopImageName,
                        'context'	=> ['compilationId' => $compilationId],
                        'type'		=> 'items_compilation_desktop_image',
                    ];
                }

                if (!empty($tabletImageName = $request->get('tablet_image'))) {
                    $tabletImagePath = getImgPath('items_compilation.tablet');
                    create_dir($tabletImagePath);

                    if (!rename(
                        getTempImgPath('items_compilation.tablet', ['{ENCRYPTED_FOLDER_NAME}' => $uploadFolder]) . $tabletImageName,
                        $tabletImagePath . $tabletImageName
                    )) {
                        jsonResponse('Some errors occured while uploading tablet image.');
                    }

                    $deletedImages[] = $tabletImagePath . $compilation['background_images']['tablet'];

                    $imagesToOptimization[] = [
                        'file_path'	=> getcwd() . DS . $tabletImagePath . $tabletImageName,
                        'context'	=> ['compilationId' => $compilationId],
                        'type'		=> 'items_compilation_tablet_image',
                    ];
                }

                $compilationUpdates = [
                    'background_images' => [
                        'tablet'    => $request->get('tablet_image') ?: $compilation['background_images']['tablet'],
                        'desktop'   => $request->get('desktop_image') ?: $compilation['background_images']['desktop'],
                    ],
                    'title'             => $request->get('title'),
                    'url'               => $request->get('url'),
                ];

                /** @var Items_Compilation_Model $itemsCompilationModel */
                $itemsCompilationModel = model(Items_Compilation_Model::class);

                if (!$itemsCompilationModel->updateOne($compilationId, $compilationUpdates)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                if (!empty($deletedImages)) {
                    foreach ($deletedImages as $deletedImage) {
                        unlink($deletedImage);
                    }
                }

                if (!empty($imagesToOptimization)) {
                    /** @var Image_optimization_Model $optimizationImagesModel */
                    $optimizationImagesModel = model(Image_optimization_Model::class);

                    $optimizationImagesModel->insertMany($imagesToOptimization);
                }

                /** @var Item_Compilation_Relation_Model $itemCompilationRelationModel */
                $itemCompilationRelationModel = model(Item_Compilation_Relation_Model::class);

                $itemCompilationRelationModel->deleteAllBy([
                    'scopes' => [
                        'compilationId' => $compilationId
                    ],
                ]);

                $itemsIds = (array) $request->get('itemsIds');
                $newRelations = [];
                foreach ($itemsIds as $itemId) {
                    $newRelations[] = [
                        'item_id'        => $itemId,
                        'compilation_id' => $compilationId,
                    ];
                }

                $itemCompilationRelationModel->insertMany($newRelations);

                jsonResponse(translate('systmess_success_items_compilation_save'), 'success');

                break;
            case 'delete_compilation':
                checkPermisionAjax('items_compilation_administration');

                /** @var Items_Compilation_Model $itemsCompilationsModel */
                $itemsCompilationsModel = model(Items_Compilation_Model::class);

                if (
                    empty($compilationId = request()->request->getInt('id'))
                    || empty($compilation = $itemsCompilationsModel->findOne($compilationId))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (!$itemsCompilationsModel->deleteOne($compilationId)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                $tabletImagePath = getImgPath('items_compilation.tablet');
                removeFileByPatternIfExists(
                    $tabletImagePath . $compilation['background_images']['tablet'],
                    $tabletImagePath . '*' . pathinfo($compilation['background_images']['tablet'], PATHINFO_FILENAME) . '.*'
                );

                $desktopImagePath = getImgPath('items_compilation.desktop');
                removeFileByPatternIfExists(
                    $desktopImagePath . $compilation['background_images']['desktop'],
                    $desktopImagePath . '*' . pathinfo($compilation['background_images']['desktop'], PATHINFO_FILENAME) . '.*'
                );

                jsonResponse('Items compilation was successfully deleted.', 'success');

                break;
            case 'togle_published_status':
                checkPermisionAjax('items_compilation_administration');

                /** @var Items_Compilation_Model $itemsCompilationsModel */
                $itemsCompilationsModel = model(Items_Compilation_Model::class);

                if (
                    empty($compilationId = request()->request->getInt('id'))
                    || empty($compilation = $itemsCompilationsModel->findOne($compilationId))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $compilationUpdates = [
                    'is_published'  => $compilation['is_published'] ? 0 : 1
                ];

                if (!$itemsCompilationsModel->updateOne($compilationId, $compilationUpdates)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                jsonResponse('Items compilation was successfully ' . ($compilation['is_published'] ? 'un-published' : 'published') . '.', 'success');

                break;
            case 'upload_temp_image':
                checkPermisionAjax('items_compilation_administration');

                $imageType = uri()->segment(4);
                if (!in_array($imageType, ['tablet', 'desktop'])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (empty($files = $_FILES['image'])) {
                    jsonResponse(translate('validation_image_required'));
                }

                if (!($uploadFolder = checkEncriptedFolder(uri()->segment(5)))) {
                    jsonResponse(translate('invalid_encrypted_folder_name'));
                }

                $module = "items_compilation.{$imageType}";
                $path = getTempImgPath($module, ['{ENCRYPTED_FOLDER_NAME}' => $uploadFolder]);
                create_dir($path);

                if (iterator_count(new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS)) >= 1) {
                    jsonResponse('You can upload only one image.');
                }

                /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
                $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);

                $result = $interventionImageLibrary->image_processing($files, [
                    'destination'   => $path,
                    'rules'         => config("img.{$module}.rules"),
                ]);

                if (!empty($result['errors'])) {
                    jsonResponse($result['errors']);
                }

                jsonResponse(
                    'Image was successfully uploaded.',
                    'success',
                    [
                        'files' => [
                            [
                                'path' => $path . $result[0]['new_name'],
                                'name' => $result[0]['new_name'],
                            ]
                        ]
                    ]
                );

                break;
            case 'delete_temp_image':
                checkPermisionAjax('items_compilation_administration');

                $imageType = uri()->segment(4);
                if (!in_array($imageType, ['tablet', 'desktop'])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (!($uploadFolder = checkEncriptedFolder(uri()->segment(5)))) {
                    jsonResponse(translate('invalid_encrypted_folder_name'));
                }

                if (empty($imageName = request()->request->get('file'))) {
                    jsonResponse('The name of the image is expected.');
                }

                $filePath = getTempImgPath("items_compilation.{$imageType}", ['{ENCRYPTED_FOLDER_NAME}' => $uploadFolder]) . $imageName;
                if (!file_exists($filePath)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                @unlink($filePath);

                jsonResponse('', 'success');

                break;

            default:
                jsonResponse(translate('systmess_error_invalid_data'));

                break;
        }
    }
}

// End of file items_compilation.php
// Location: /tinymvc/myapp/controllers/items_compilation.php
