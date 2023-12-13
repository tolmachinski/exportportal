<?php

declare(strict_types=1);

use App\Common\Contracts\Group\GroupType;
use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Common\Traits\FileuploadOptionsAwareTrait;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\ConstraintViolationInterface;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Common\Validation\ValidationException;
use App\Filesystem\DashboardBannerPathGenerator;
use App\Filesystem\FilePathGenerator;
use App\Validators\DashboardBannerValidator;
use ExportPortal\Contracts\Filesystem\Exception\ReadException;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;
use TinyMVC_Library_Image_intervention as LegacyImageHandler;

/**
 * Controller Dashboard_banner.
 */
class Dashboard_banner_Controller extends TinyMVC_Controller
{
    use FileuploadOptionsAwareTrait {
        FileuploadOptionsAwareTrait::getFileuploadOptions as getFormattedFileuploadOptions;
    }

    /**
     * Index page.
     */
    public function administration(): void
    {
        if (!have_right('dashboard_banners_administration')) {
            show_404();
        }

        /** @var User_Groups_Model $userGroupsModel */
        $userGroupsModel = model(User_Groups_Model::class);

        views(
            [
                'admin/header_view',
                'admin/dashboard_banners/index_view',
                'admin/footer_view',
            ],
            [
                'title'              => 'Dashboard Banners',
                'userGroups'         => $userGroupsModel->findAllBy([
                    'conditions' => [
                        'aliases' => GroupType::from(GroupType::EP_CLIENTS)->aliases(),
                    ],
                ]),
            ]
        );
    }

    public function ajax_admin_all_dashboard_banners_dt(): void
    {
        checkIsAjax();
        checkPermisionAjaxDT('dashboard_banners_administration');

        /** @var Dashboard_Banners_Model $dashboardBannersModel */
        $dashboardBannersModel = model(Dashboard_Banners_Model::class);

        $request = request()->request;
        $queryOrderBy = dtOrdering(
            $request->all(),
            [
                'id'                => "`{$dashboardBannersModel->getTable()}`.`id`",
                'isVisible'         => "`{$dashboardBannersModel->getTable()}`.`is_visible`",
                'createdAt'         => "`{$dashboardBannersModel->getTable()}`.`date_created_at`",
                'updatedAt'         => "`{$dashboardBannersModel->getTable()}`.`date_updated_at`",
            ],
            fn ($ordering) => [$ordering['column'] => strtoupper($ordering['direction'])]
        ) ?: [["`{$dashboardBannersModel->getTable()}`.`id`" => 'DESC']];

        //region Filters

        $dashboardBannerConditions = dtConditions($request->all(), [
            ['as' => 'keywords',               'key' => 'keywords',             'type' => 'trim'],
            ['as' => 'createDateGte',          'key' => 'create_date_from',     'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'createDateLte',          'key' => 'create_date_to',       'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'isVisible',              'key' => 'is_visible',           'type' => fn ($onlyIsVisible) => in_array($onlyIsVisible, [0, 1]) ? (int) $onlyIsVisible : null],
            ['as' => 'userGroupIds',           'key' => 'user_groups',          'type' => fn ($userGroupIds) => empty($userGroupIds) ? null : array_map('intval', explode(',', (string) $userGroupIds))],
        ]);
        //endregion filters

        $dashboardBannersQueryParams = [
            'columns'       => [
                "`{$dashboardBannersModel->getTable()}`.`id`",
                "`{$dashboardBannersModel->getTable()}`.`subtitle`",
                "`{$dashboardBannersModel->getTable()}`.`title`",
                "`{$dashboardBannersModel->getTable()}`.`img`",
                "`{$dashboardBannersModel->getTable()}`.`url`",
                "`{$dashboardBannersModel->getTable()}`.`button_text`",
                "`{$dashboardBannersModel->getTable()}`.`is_visible`",
                "`{$dashboardBannersModel->getTable()}`.`date_created_at`",
                "`{$dashboardBannersModel->getTable()}`.`date_updated_at`",
            ],
            'conditions'    => $dashboardBannerConditions,
            'joins'         => array_filter([
                isset($dashboardBannerConditions['userGroupIds']) ? 'dashboardBannersRelation' : null,
            ]),
            'order'         => array_shift($queryOrderBy),
            'skip'          => abs($request->getInt('iDisplayStart')),
            'limit'         => abs($request->getInt('iDisplayLength', 10)),
            'group'         => [
                "`{$dashboardBannersModel->getTable()}`.`id`",
            ],
        ];

        //region DataTable Parameters
        $dashboardBannersCount = $dashboardBannersModel->countAllBy(array_diff_key($dashboardBannersQueryParams, array_flip(['skip', 'limit', 'order', 'group'])));

        $dashboardBanners = $dashboardBannersModel->findAllBy($dashboardBannersQueryParams);

        $output = [
            'sEcho'                     => $request->getInt('sEcho'),
            'iTotalRecords'             => $dashboardBannersCount,
            'iTotalDisplayRecords'      => $dashboardBannersCount,
            'aaData'                    => [],
        ];
        //endregion DataTable Parameters

        foreach ($dashboardBanners ?: [] as $dashboardBanner) {
            if ($dashboardBanner['is_visible']) {
                $publishReviewBtn = <<<PUBLISH
                    <a class="ep-icon ep-icon_ok txt-green confirm-dialog"
                        data-callback="visibleStatus"
                        data-message="Are you sure you want to un-publish this banner?"
                        title="Unpublish banner"
                        data-id="{$dashboardBanner['id']}"
                    ></a>
                PUBLISH;
            } else {
                $publishReviewBtn = <<<PUBLISH
                    <a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                        data-callback="visibleStatus"
                        data-message="Are you sure you want to publish this banner?"
                        title="Publish banner"
                        data-id="{$dashboardBanner['id']}"
                    ></a>
                PUBLISH;
            }

            $bannerDisplayImage = sprintf(
                <<<'DISPLAY'
                    <img class="mw-50 mh-50" src="%s" alt="">
                DISPLAY,
                asset(sprintf("public/storage%s", DashboardBannerPathGenerator::bannerImage($dashboardBanner['img'])), 'legacy'),
            );

            $actionEdit = sprintf(
                <<<'EDIT'
                    <li>
                        <a class="fancyboxValidateModalDT fancybox.ajax" href="%s" title="Edit" data-title="Edit">
                            <span class="ep-icon ep-icon_pencil"></span> Edit
                        </a>
                    </li>
                EDIT,
                __SITE_URL . 'dashboard_banner/popup_forms/edit_banner/' . $dashboardBanner['id']
            );

            $actionDelete = sprintf(
                <<<'DELETE'
                    <li>
                        <a class="confirm-dialog"
                            data-callback="deleteBanner"
                            data-message="Do you really want to delete the banner?"
                            data-id="%s"
                            title="Delete the banner">
                            <span class="ep-icon ep-icon_remove"></span> Delete
                        </a>
                    </li>
                DELETE,
                $dashboardBanner['id']
            );

            $bannerTableActions = sprintf(
                <<<ACTIONS
                <div class="dropdown">
                    <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                    {$actionEdit}
                    {$actionDelete}
                    </ul>
                </div>
                ACTIONS
            );

            $output['aaData'][] = [
                'id'                    => $dashboardBanner['id'],
                'subtitle'              => cleanOutput($dashboardBanner['subtitle']),
                'title'                 => cleanOutput($dashboardBanner['title']),
                'image'                 => $bannerDisplayImage,
                'url'                   => $dashboardBanner['url'],
                'buttonText'            => cleanOutput($dashboardBanner['button_text']),
                'isVisible'             => $publishReviewBtn,
                'createdAt'             => $dashboardBanner['date_created_at']->format(\App\Common\PUBLIC_DATETIME_FORMAT),
                'updatedAt'             => $dashboardBanner['date_updated_at']->format(\App\Common\PUBLIC_DATETIME_FORMAT),
                'dt_banner_actions'     => $bannerTableActions,
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
            case 'add_banner':
                checkIsAjax();
                checkPermisionAjax('dashboard_banners_administration');

                /** @var User_Groups_Model $userGroupsModel */
                $userGroupsModel = model(User_Groups_Model::class);

                views(
                    [
                        'admin/dashboard_banners/popup_forms/banner_form',
                    ],
                    [
                        'userGroups'         => $userGroupsModel->findAllBy([
                            'conditions' => [
                                'aliases' => GroupType::from(GroupType::EP_CLIENTS)->aliases(),
                            ],
                        ]),

                        'uploadOptions' => $this->getFormattedFileuploadOptions(
                            explode(',', config('img.dashboard_banner.main.rules.format', 'jpg,jpeg,png')),
                            1,
                            1,
                            (int) config('img.dashboard_banner.main.rules.size', 10 * 1024 * 1024),
                            config('img.dashboard_banner.main.rules.size_placeholder', '10MB'),
                            [
                                'width'  => config('img.dashboard_banner.main.rules.width'),
                                'height' => config('img.dashboard_banner.main.rules.height'),
                            ],
                            getUrlForGroup('dashboard_banner/ajax_dashboard_banner_upload_image')
                        ),
                    ]
                );

                break;
            case 'edit_banner':
                checkIsAjax();
                checkPermisionAjax('dashboard_banners_administration');

                $idBanner = (int) uri()->segment(4);

                /** @var Dashboard_Banners_Model $dashboardBannersModel */
                $dashboardBannersModel = model(Dashboard_Banners_Model::class);

                /** @var User_Groups_Model $userGroupsModel */
                $userGroupsModel = model(User_Groups_Model::class);

                if (!$dashboardBanner = $dashboardBannersModel->findOne($idBanner, [
                    'with' => ['userGroups'],
                ])) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $dashboardBanner['user_groups'] = array_column($dashboardBanner['user_groups']->toArray(), 'idgroup');

                /** @var FilesystemProviderInterface $filesystemProvider */
                $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $prefixer = $filesystemProvider->prefixer('public.storage');

                try {
                    $mimeType = (new MimeTypes())->guessMimeType(
                        $prefixer->prefixPath(
                            $relativePath = DashboardBannerPathGenerator::bannerImage($dashboardBanner['img'])
                        )
                    );
                } catch (\Throwable $e) {
                    //No image
                }

                $uploadedImage = [
                    'url'  => asset("/public/storage{$relativePath}"),
                    'name' => $dashboardBanner['img'],
                    'type' => $mimeType,
                ];

                views(
                    [
                        'admin/dashboard_banners/popup_forms/banner_form',
                    ],
                    [
                        'dashboardBanner'    => $dashboardBanner,
                        'userGroups'         => $userGroupsModel->findAllBy([
                            'conditions' => [
                                'aliases' => GroupType::from(GroupType::EP_CLIENTS)->aliases(),
                            ],
                        ]),
                        'uploadOptions' => $this->getFormattedFileuploadOptions(
                            explode(',', config('img.dashboard_banner.main.rules.format', 'jpg,jpeg,png')),
                            1,
                            1,
                            (int) config('img.dashboard_banner.main.rules.size', 10 * 1024 * 1024),
                            config('img.dashboard_banner.main.rules.size_placeholder', '10MB'),
                            [
                                'width'  => config('img.dashboard_banner.main.rules.width'),
                                'height' => config('img.dashboard_banner.main.rules.height'),
                            ],
                            getUrlForGroup('dashboard_banner/ajax_dashboard_banner_upload_image')
                        ),
                        'uploadedImages' => [$uploadedImage],
                    ]
                );

            break;
        }
    }

    //region image
    public function ajax_dashboard_banner_upload_image()
    {
        checkIsAjax();
        checkPermisionAjax('dashboard_banners_administration');

        $request = request();
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = current((array) ($request->files->get('files') ?? [])) ?: null;
        if (null === $uploadedFile) {
            jsonResponse(translate('validation_image_required'));
        }
        if (is_array($uploadedFile) || !$uploadedFile->isValid()) {
            jsonResponse(translate('validation_invalid_file_provided'));
        }

        /** @var LegacyImageHandler $imageHandler */
        $imageHandler = $this->getContainer()->get(LibraryLocator::class)->get(LegacyImageHandler::class);
        // Given that we need to validate the file and it would take a long time
        // to write the new proper validation we can only use what we have.
        try {
            $imageHandler->assertImageIsValid(
                $imageHandler->makeImageFromFile($uploadedFile),
                config('img.dashboard_banner.main.rules'),
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

        // Next we need to take our filesystem for temp directory
        /** @var FilesystemProviderInterface $filesystemProvider */
        $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempStorage = $filesystemProvider->storage('public.storage');
        // And write file there
        try {
            $tempStorage->write(
                FilePathGenerator::uploadedFile(
                    $fileName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), $uploadedFile->getClientOriginalExtension())
                ),
                $uploadedFile->getContent()
            );
        } catch (UnableToWriteFile $e) {
            jsonResponse(translate('validation_images_upload_fail'));
        }

        jsonResponse(null, 'success', ['files' => ['name' => $fileName]]);
    }

    //endregion image
    public function ajax_operations(): void
    {
        checkIsAjax();
        checkPermisionAjax('dashboard_banners_administration');

        $request = request()->request;

        switch (uri()->segment(3)) {
            case 'visible_status':
                checkIsAjax();
                checkPermisionAjax('dashboard_banners_administration');

                /** @var Dashboard_Banners_Model $dashboardBannersModel */
                $dashboardBannersModel = model(Dashboard_Banners_Model::class);

                if (
                    empty($dashboardBannerId = $request->getInt('id'))
                    || empty($dashboardBanner = $dashboardBannersModel->findOne($dashboardBannerId))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $dashboardBannersModel->updateMany(
                    [
                        'is_visible' => 0,
                    ],
                    [
                        'conditions'  => [
                            'isVisible' => 1,
                        ],
                    ]
                );

                if (!$dashboardBannersModel->updateOne($dashboardBannerId, [
                    'is_visible'  => $dashboardBanner['is_visible'] ? 0 : 1, ])) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                jsonResponse(translate('systmess_success_dashboard_banner_update_is_visible_status'), 'success');

                break;
            case 'delete':
                checkIsAjax();
                checkPermisionAjax('dashboard_banners_administration');

                /** @var Dashboard_Banners_Model $dashboardBannersModel */
                $dashboardBannersModel = model(Dashboard_Banners_Model::class);

                if (empty($dashboardBannerId = request()->request->getInt('id'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (!$dashboardBannersModel->deleteOne($dashboardBannerId)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                jsonResponse(translate('systmess_success_dashboard_banner_delete'), 'success');

                break;
            case 'add_banner':
                checkIsAjax();
                checkPermisionAjax('dashboard_banners_administration');

                //region Validate
                try {
                    $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
                    $validator = new DashboardBannerValidator($adapter);
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
                //endregion Validate

                if (empty($request->get('file'))) {
                    jsonResponse(translate('validation_dashboar_banner_popup_image_required'));
                }

                /** @var Dashboard_Banners_Model $dashboardBannersModel */
                $dashboardBannersModel = model(Dashboard_Banners_Model::class);

                if (empty($dashboardBannerId = $dashboardBannersModel->insertOne([
                    'title'               => $request->get('title'),
                    'subtitle'            => $request->get('subtitle'),
                    'url'                 => $request->get('link'),
                    'button_text'         => $request->get('button'),
                    'img'                 => $request->get('file'),
                ]))) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                /** @var Dashboard_Banners_Relation_Model $dashboardBannersModelRelation */
                $dashboardBannersModelRelation = model(Dashboard_Banners_Relation_Model::class);

                $banerGroupsRelations = [];
                foreach ((array) $request->get('user_groups') as $incomingId) {
                    $banerGroupsRelations[] = [
                        'banner_id' => $dashboardBannerId,
                        'group_id'  => $incomingId,
                    ];
                }

                if (!empty($banerGroupsRelations)) {
                    $dashboardBannersModelRelation->insertMany($banerGroupsRelations);
                }

                //region Image
                /** @var FilesystemProviderInterface $filesystemProvider */
                $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicStorage = $filesystemProvider->storage('public.storage');
                $tempStorage = $filesystemProvider->storage('public.storage');

                try {
                    $publicStorage->write(
                        DashboardBannerPathGenerator::bannerImage($request->get('file')),
                        $tempStorage->read(FilePathGenerator::uploadedFile($request->get('file')))
                    );
                } catch (Exception $e) {
                    $dashboardBannersModel->deleteOne($dashboardBannerId);
                    jsonResponse(translate('systmess_error_failed_loading_banner_image'));
                }
                //endregion Image

                jsonResponse(translate('systmess_success_dashboard_banner_create'), 'success');

                break;
            case 'edit_banner':
                checkIsAjax();
                checkPermisionAjax('dashboard_banners_administration');

                //region Validate

                try {
                    $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
                    $validator = new DashboardBannerValidator($adapter);
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

                //endregion Validate

                /** @var Dashboard_Banners_Model $dashboardBannersModel */
                $dashboardBannersModel = model(Dashboard_Banners_Model::class);
                $dashboardBannerId = $request->getInt('id');

                 /** @var Dashboard_Banners_Relation_Model $dashboardBannersModelRelation */
                 $dashboardBannersModelRelation = model(Dashboard_Banners_Relation_Model::class);

                if (empty($dashboardBannerId) || empty($dashboardBanner = $dashboardBannersModel->findOne($dashboardBannerId, ['with' => ['userGroups']]))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $alreadyExistsGroups = array_column($dashboardBanner['user_groups']->toArray(), 'idgroup');

                $incommingUserGroups = (array) $request->get('user_groups');

                $banerGroupsRelations = [];
                $newGroupsIds = array_diff($incommingUserGroups, $alreadyExistsGroups);
                foreach ($newGroupsIds as $newGroupsId) {
                    $banerGroupsRelations[] = [
                        'banner_id' => $dashboardBannerId,
                        'group_id'  => $newGroupsId,
                    ];
                }

                if (!empty($banerGroupsRelations)) {
                    $dashboardBannersModelRelation->insertMany($banerGroupsRelations);
                }

                $deletedGroups = array_diff($alreadyExistsGroups, $incommingUserGroups);
                if (!empty($deletedGroups)) {
                    $dashboardBannersModelRelation->deleteAllBy([
                        'conditions' => [
                            'bannerId'      => $dashboardBannerId,
                            'userGroupsIds' => $deletedGroups,
                        ],
                    ]);
                }
                //region Image

                if (empty($bannerImage = (string) $request->get('file'))) {
                    jsonResponse(translate('validation_dashboar_banner_popup_image_required'));
                }
                $newImageWasUploaded = $bannerImage !== $dashboardBanner['img'];

                if ($newImageWasUploaded) {
                    /** @var FilesystemProviderInterface $filesystemProvider */
                    $filesystemProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicStorage = $filesystemProvider->storage('public.storage');
                    $tempStorage = $filesystemProvider->storage('public.storage');

                    try {
                        $publicStorage->write(
                            DashboardBannerPathGenerator::bannerImage($bannerImage),
                            $tempStorage->read(FilePathGenerator::uploadedFile($bannerImage))
                        );
                    } catch (Exception $e) {
                        jsonResponse(translate('systmess_error_failed_loading_banner_image'));
                    }
                }

                $update = [
                    'title'         => $request->get('title'),
                    'subtitle'      => $request->get('subtitle'),
                    'url'           => $request->get('link'),
                    'button_text'   => $request->get('button'),
                    'img'           => $bannerImage,
                ];

                if (!$dashboardBannersModel->updateOne($dashboardBannerId, $update)) {
                    jsonResponse(translate('systmess_internal_server_error'));
                }

                if ($newImageWasUploaded) {
                    try {
                        if ($publicStorage->fileExists(DashboardBannerPathGenerator::bannerImage($dashboardBanner['img']))) {
                            $publicStorage->delete(DashboardBannerPathGenerator::bannerImage($dashboardBanner['img']));
                        }
                    } catch (Exception $e) {
                        jsonResponse(translate('systmess_error_dashboard_banner_deleting_old_image'));
                    }
                }
                //endregion Image

                jsonResponse(translate('systmess_success_dashboard_banner_update'), 'success');

                break;
        }
    }
}

// End of file dashboard_banner.php
// Location: /tinymvc/myapp/controllers/dashboard_banner.php
