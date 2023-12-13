<?php

use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Email\DownloadableMaterialsData;
use App\Email\DownloadableMaterialsShare;
use App\Services\PhoneCodesService;
use App\Validators\DownloadableMaterialsPageValidator;
use App\Validators\DownloadableMaterialsValidator;
use App\Validators\EmailValidator;
use App\Validators\PhoneValidator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Jaybizzle\CrawlerDetect\CrawlerDetect;
use League\Flysystem\UnableToWriteFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use const App\Common\ROOT_PATH;

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Downloadable_materials_Controller extends TinyMVC_Controller
{
    private $storageDirectory = \App\Common\VAR_PATH . '/app/public/downloadable_materials/';

    /**
     * Index page.
     */
    public function index()
    {
        show_404();
    }

    public function details()
    {
        if (empty($slug = uri()->segment(3))) {
            show_404();
        }

        /** @var Downloadable_Materials_Model $materialsModel */
        $materialsModel = model(Downloadable_Materials_Model::class);

        $materialsContent = $materialsModel->findOneBy([
            'conditions' => [
                'slug' => $slug,
            ],
        ]);

        if (empty($materialsContent)) {
            show_404();
        }

        $metaParams = ['[TITLE]' => cleanOutput($materialsContent['title'])];
        $coverPath = getImgPath('downloadable_materials.cover', ['{ID}' => $materialsContent['id']]) . $materialsContent['cover'];

        if (is_file($coverPath)) {
            $metaParams['[IMAGE]'] = __IMG_URL . $coverPath;
        }

        if (!empty($signature = uri()->segment(4))) {
            /** @var Users_Downloadable_Materials_Model $usersDownloadableMaterialsModel */
            $usersDownloadableMaterialsModel = model(Users_Downloadable_Materials_Model::class);

            $subscriberData = $usersDownloadableMaterialsModel->findOneBy([
                'conditions' => [
                    'signature' => $signature,
                ],
            ]);

            if (!empty($subscriberData)) {
                if (!cookies()->exist_cookie('_ep_material_downloaded')) {
                    cookies()->setCookieParam('_ep_material_downloaded', $signature, 6048000);
                }
                $autoDownloadMaterial = true;
            }
        }

        $breadcrumbs = [
            [
                'link' 	=> __SITE_URL . "downloadable_materials/details/{$slug}",
                'title'	=> 'Educational Information',
            ],
        ];

        $materialsContent['share_url'] = __SITE_URL . "downloadable_materials/ajaxShareAdministration/view/{$materialsContent['slug']}";
        $materialsContent['form_url'] = __SITE_URL . "downloadable_materials/ajaxFormAdministration/view/{$materialsContent['slug']}";
        $materialsContent['cover_path'] = getDownloadableMaterialsCoverPath((int) $materialsContent['id'], (string) $materialsContent['cover']);
        $materialsContent['post_url'] = __SITE_URL . "downloadable_materials/details/{$slug}";
        $materialsContent['signature'] = $signature;

        $recommendations = $materialsModel->findAllBy([
            'conditions'    => ['recommended' => $slug],
            'order'         => ['RAND()'],
            'limit'         => 2,
        ]);

        if (!empty($recommendations)) {
            $recommendations = array_map(
                function ($recommendation) {
                    $recommendation['cover_path'] = getDownloadableMaterialsCoverPath((int) $recommendation['id'], (string) $recommendation['cover']);
                    $recommendation['article_url'] = __SITE_URL . "downloadable_materials/details/{$recommendation['slug']}";

                    $thumbName = (string) getTempImgThumb('downloadable_materials.cover', 0, $recommendation['cover']);
                    $recommendation['thumb_0_path'] = getDownloadableMaterialsCoverPath((int) $recommendation['id'], $thumbName);

                    return $recommendation;
                },
                $recommendations
            );
        }

        $data = [
            'breadcrumbs'           => $breadcrumbs,
            'materials_content'     => $materialsContent,
            'recommended_content'   => $recommendations,
            'autoDownload'          => $autoDownloadMaterial ?? false,
            'meta_params'           => $metaParams,
            'isDwnMPage'            => true,
            'templateViews'         => [
                'headerOutContent'  => 'downloadable_materials/header_view',
                'mainOutContent'    => 'downloadable_materials/index_view',
                'footerOutContent'  => 'about/bottom_who_we_are_view',
            ],
        ];

        if (!logged_in()) {
            $data['webpackData'] = [
                'pageConnect'   => 'downloadable_materials_page',
                'styleCritical' => 'downloadable_materials',
            ];

            $data['templateViews']['customEncoreLinks'] = true;
        }

        views()->display_template($data);
    }

    public function download(): Response
    {
        $id = (int) uri()->segment(3);
        /** @var Downloadable_Materials_Model $materialsModel */
        $materialsModel = model(Downloadable_Materials_Model::class);
        if (empty($materialsPage = $materialsModel->findOne($id))) {
            return new RedirectResponse('/404');
        }

        try {
            $file = new File(
                sprintf(
                    '%s/%s/%s',
                    ROOT_PATH,
                    rtrim(str_replace('{ID}', $id, (string) config('files.downloadable_materials.pdf.folder_path')), '/'),
                    $materialsPage['file']
                )
            );
        } catch (FileNotFoundException $e) {
            // If file is not found then we will redirect to the 404 page.
            return new RedirectResponse('/404');
        } finally {
            $this->saveStatistics($id);
        }

        return (new BinaryFileResponse($file, 200, ['Content-Type' => 'application/pdf'], true))
            ->setContentDisposition('attachment', sprintf('%s.pdf', strForUrl($materialsPage['title'], ' ')))
            ->prepare(request())
        ;
    }

    public function administration(): void
    {
        checkIsLogged();
        checkPermision('downloadable_materials_administration');

        views(['admin/header_view', 'admin/downloadable_materials/index_view', 'admin/footer_view']);
    }

    public function ajaxDtAdministration(): void
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjaxDT('downloadable_materials_administration');

        $request = request()->request;

        $dtFilters = dtConditions($request->all(), [
            ['as' => 'created_from',   'key' => 'created_from',   'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'created_to',     'key' => 'created_to',     'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'title',          'key' => 'title',          'type' => 'cut_str:200|trim'],
        ]);

        $perPage = $request->getInt('iDisplayLength', 10);
        $page = $request->getInt('iDisplayStart', 0) / $perPage + 1;

        try {
            /** @var Downloadable_Materials_Model $materialsModel */
            $materialsModel = model(Downloadable_Materials_Model::class);

            $paginator = $materialsModel->paginate(
                [
                    'columns' => [
                        '`id`',
                        '`title`',
                        '`short_description`',
                        '`file`',
                        '`cover`',
                        '`slug`',
                        'IFNULL(`downloads`.`counts`, 0) AS `downloads`',
                    ],
                    'conditions' => array_merge($dtFilters, ['downloadCounts']),
                    'order'      => \array_column(
                        \dtOrdering(
                            request()->request->all(),
                            [
                                'dt_id'        => "`{$materialsModel->getTable()}`.`{$materialsModel->getPrimaryKey()}`",
                                'dt_downloads' => '`downloads`',
                            ]
                        ),
                        'direction',
                        'column'
                    ),
                ],
                $perPage,
                $page
            );

            foreach ($paginator['data'] as $row) {
                $editButton = '<a href="' . __SITE_URL . 'downloadable_materials/ajaxPopupAdministration/edit/' . $row['id'] . '"
                                    class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax"
                                    title="' . translate('dwn_edit_material_dialog') . '"
                                    data-title=" ' . translate('dwn_edit_material_dialog') . '">
                                </a>';

                $statButton = '<a href="' . __SITE_URL . 'downloadable_materials/ajaxPopupAdministration/statistics/' . $row['id'] . '"
                                    class="ep-icon ep-icon_statistic fancyboxValidateModalDT fancybox.ajax"
                                    title="' . translate('dwn_view_stat_material_dialog') . '"
                                    data-title=" ' . translate('dwn_view_stat_material_dialog') . '">
                                </a>';

                $deleteButton = '<a href="#"
                                    class="ep-icon ep-icon_remove txt-red confirm-dialog"
                                    title="Delete the record"
                                    data-delete-link="' . __SITE_URL . 'downloadable_materials/ajaxPopupAdministration/delete/' . $row['id'] . '"
                                    data-callback="delete_record"
                                    data-message="' . translate('dwn_material_delete_question') . '">
                                </a>';

                $viewButton = '<a class="ep-icon txt-blue ep-icon_visible"
                                    target="_blank"
                                    title="' . translate('dwn_view_material_page') . '"
                                    href="' . __SITE_URL . 'downloadable_materials/details/' . $row['slug'] . '">
                                </a>';

                $aaData[] = [
                    'dt_id'                => $row['id'],
                    'dt_title'             => cleanOutput($row['title']),
                    'dt_short_description' => cleanOutput($row['short_description']),
                    'dt_cover'             => '<img src="' . getFileExits(getDownloadableMaterialsCoverPath((int) $row['id'], (string) $row['cover']), 'public/img/no_image/no-image-125x90.png') . '" class="w-100">',
                    'dt_downloads'         => (int) $row['downloads'] ?? null,
                    'dt_actions'           => $viewButton . $editButton . $deleteButton . $statButton,
                ];
            }
        } catch (\Throwable $th) {
            $aaData = [];
        }

        jsonResponse('', 'success', [
            'sEcho'                => $request->getInt('sEcho', 0),
            'iTotalRecords'        => $paginator['total'] ?? 0,
            'iTotalDisplayRecords' => $paginator['total'] ?? 0,
            'aaData'               => $aaData ?? [],
        ]);
    }

    public function ajaxUsersDtAdministration(): void
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjaxDT('downloadable_materials_administration');

        $request = request()->request;

        $id = (int) uri()->segment(3);
        if (empty($id)) {
            jsonDTResponse('No id');
        }

        $dtFilters = ['id_material' => $id];
        $perPage = $request->getInt('iDisplayLength', 10);
        $page = $request->getInt('iDisplayStart', 0) / $perPage + 1;

        try {
            /** @var Users_Downloadable_Materials_Model $usersDownloadableMaterialsModel */
            $usersDownloadableMaterialsModel = model(Users_Downloadable_Materials_Model::class);

            $tableName = $usersDownloadableMaterialsModel->getTable();
            $paginator = $usersDownloadableMaterialsModel->paginate(
                [
                    'columns' => [
                        "{$tableName}.`{$usersDownloadableMaterialsModel->getPrimaryKey()}`",
                        "CONCAT_WS(' ', `fname`, `lname`) as user_name",
                        '`email`',
                        '`id_user`',
                        '`id_material`',
                        '`count`',
                        '`phone`',
                        '`code`',
                        "{$tableName}.`country`",
                        '`updated`',
                        '`referral`',
                        "{$usersDownloadableMaterialsModel->portCountryTableAlias}.`country_name`",
                    ],
                    'conditions' => $dtFilters,
                    'joins'      => ['countries'],
                    'order'      => \array_column(
                        \dtOrdering(
                            request()->request->all(),
                            [
                                'dt_id'      => "`{$tableName}`.`id`",
                                'dt_updated' => "`{$tableName}`.`updated`",
                            ]
                        ),
                        'direction',
                        'column'
                    ),
                ],
                $perPage,
                $page
            );

            foreach ($paginator['data'] as $row) {
                $userLink = $row['id_user'] ? "<br/><a class='ep-icon ep-icon_user' title='View personal page of " . $row['user_name'] . "' target='_blank' href='" . __SITE_URL . 'usr/' . strForURL($row['user_name']) . '-' . $row['id_user'] . "'></a>" : '';

                $aaData[] = [
                    'dt_user'                => $row['user_name'] . $userLink,
                    'dt_email'               => cleanOutput($row['email']),
                    'dt_phone'               => $row['phone_code'] . ' ' . $row['phone'],
                    'dt_country'             => $row['country_name'],
                    'dt_downloads'           => (int) $row['count'],
                    'dt_updated'             => getDateFormat($row['updated']),
                    'dt_referral'            => $row['referral'],
                ];
            }
        } catch (\Throwable $th) {
            dump($th);
            $aaData = [];
        }

        jsonResponse('', 'success', [
            'sEcho'                => $request->getInt('sEcho', 0),
            'iTotalRecords'        => $paginator['total'] ?? 0,
            'iTotalDisplayRecords' => $paginator['total'] ?? 0,
            'aaData'               => $aaData ?? [],
        ]);
    }

    public function ajaxPopupAdministration(): void
    {
        checkIsAjax();
        checkIsLoggedAjax();

        /** @var Downloadable_Materials_Model $materialsModel */
        $materialsModel = model(Downloadable_Materials_Model::class);

        $request = request();
        $id = (int) uri()->segment(4);

        switch (uri()->segment(3)) {
            case 'add':
                views()->display('admin/downloadable_materials/modal_view', [
                    'coverImageRules'   => config('img.downloadable_materials.cover.rules', []),
                    'pdfRules'          => config('files.downloadable_materials.pdf.rules', []),
                    'uploadFolder'      => encriptedFolderName(),
                ]);

                break;
            case 'edit':
                if (empty($row = $materialsModel->findOne($id))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                views()->display('admin/downloadable_materials/modal_view', [
                    'downloadableMaterials' => $row,
                    'coverImageRules'       => config('img.downloadable_materials.cover.rules', []),
                    'pdfRules'              => config('files.downloadable_materials.pdf.rules', []),
                    'uploadFolder'          => encriptedFolderName(),
                ]);

                break;
            case 'statistics':
                /** @var Users_Downloadable_Materials_Model $usersDownloadableMaterialsModel */
                $usersDownloadableMaterialsModel = model(Users_Downloadable_Materials_Model::class);

                if (empty($stats = $usersDownloadableMaterialsModel->getStatistics($id))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                views()->display('admin/downloadable_materials/statistics_view', [
                    'idMaterial' => $id,
                    'statistics' => $stats,
                ]);

                break;
            case 'create':
                //region Validation
                $encryptedFolderName = checkEncriptedFolder($request->get('upload_folder') ?? '');
                if (empty($encryptedFolderName)) {
                    jsonResponse(translate('invalid_encrypted_folder_name'));
                }

                $validator = new DownloadableMaterialsPageValidator(new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class)), $encryptedFolderName);

                if (!$validator->validate($request->request->all())) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolation $violation) {
                                return $violation->getMessage();
                            },
                            \iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }

                if (empty($cover = $request->request->get('cover_image'))) {
                    jsonResponse('Cover image is required for upload');
                }

                if (empty($pdfFileName = $request->request->get('file'))) {
                    jsonResponse('PDF file is required for upload');
                }
                //endregion Validation

                $coverPathDetails = pathinfo($cover);

                if (empty($materialsPageId = $materialsModel->insertOne(
                    [
                        'short_description' => $request->request->get('short_description'),
                        'content'           => $request->request->get('content'),
                        'title'             => $request->request->get('title'),
                        'cover'             => $coverPathDetails['basename'],
                        'file'              => $pdfFileName,
                    ]
                ))) {
                    jsonResponse(translate('dwn_database_write_fail'));
                }

                if (!$materialsModel->updateOne($materialsPageId, [
                    'slug'  => strForUrl($request->request->get('title') . ' ' . $materialsPageId),
                ])) {
                    jsonResponse(translate('dwn_record_not_updated'), 'error', []);
                }

                //region of uploading cover image
                $coverImageFolderPath = getImgPath('downloadable_materials.cover', ['{ID}' => $materialsPageId]);
                $coverImageFullPath = $coverImageFolderPath . $coverPathDetails['basename'];

                create_dir($coverImageFolderPath);

                if (!rename($cover, $coverImageFullPath)) {
                    $materialsModel->deleteOne($materialsPageId);

                    jsonResponse(translate('systmess_internal_server_error'));
                }

                //region copy thumbs
                $thumbs = config('img.downloadable_materials.cover.thumbs');

                if (!empty($thumbs)) {
                    $tempFolderPath = getTempImgPath('downloadable_materials.cover', ['{ENCRYPTED_FOLDER_NAME}' => $encryptedFolderName]);

                    foreach ($thumbs as $thumb) {
                        $thumbName = str_replace('{THUMB_NAME}', $coverPathDetails['basename'], $thumb['name']);

                        if (!rename($tempFolderPath . $thumbName, $coverImageFolderPath . $thumbName)) {
                            $materialsModel->deleteOne($materialsPageId);

                            $imagePath = getImgSrc('downloadable_materials.cover', 'original', ['{ID}' => $materialsPageId, '{FILE_NAME}' => $coverPathDetails['basename']]);
                            $imagePathGlob = getImgSrc('downloadable_materials.cover', 'original', ['{ID}' => $materialsPageId, '{FILE_NAME}' => '*' . pathinfo($coverPathDetails['basename'], PATHINFO_FILENAME) . '.*']);
                            removeFileByPatternIfExists($imagePath, $imagePathGlob);

                            jsonResponse(translate('systmess_internal_server_error'));
                        }
                    }
                }
                //endregion copy thumbs

                $imagesToOptimization[] = [
                    'file_path'	=> getcwd() . DS . $coverImageFullPath,
                    'context'	  => ['id' => $materialsPageId],
                    'type'		    => 'downloadable_materials_cover',
                ];
                //endregion of uploading cover image

                //region of uploading pdf file
                $pdfFileTempFolder = str_replace('{ENCRYPTED_FOLDER_NAME}', $encryptedFolderName, (string) config('files.downloadable_materials.pdf.temp_folder_path'));
                $pdfFilePublicFolder = str_replace('{ID}', $materialsPageId, (string) config('files.downloadable_materials.pdf.folder_path'));

                if (!rename($pdfFileTempFolder . $pdfFileName, $pdfFilePublicFolder . $pdfFileName)) {
                    $materialsModel->deleteOne($materialsPageId);

                    $imagePath = getImgSrc('downloadable_materials.cover', 'original', ['{ID}' => $materialsPageId, '{FILE_NAME}' => $coverPathDetails['basename']]);
                    $imagePathGlob = getImgSrc('downloadable_materials.cover', 'original', ['{ID}' => $materialsPageId, '{FILE_NAME}' => '*' . pathinfo($coverPathDetails['basename'], PATHINFO_FILENAME) . '.*']);
                    removeFileByPatternIfExists($imagePath, $imagePathGlob);

                    jsonResponse(translate('systmess_internal_server_error'));
                }
                //endregion of uploading pdf file

                /** @var Image_optimization_Model $optimizationImagesModel */
                $optimizationImagesModel = model(Image_optimization_Model::class);

                $optimizationImagesModel->insertMany($imagesToOptimization);

                jsonResponse(translate('dwn_record_created'), 'success');

            break;
            case 'update':
                if (empty($materialsPage = $materialsModel->findOne($id))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                //region Validation
                $encryptedFolderName = checkEncriptedFolder($request->get('upload_folder') ?? '');
                if (empty($encryptedFolderName)) {
                    jsonResponse(translate('invalid_encrypted_folder_name'));
                }

                $validator = new DownloadableMaterialsPageValidator(new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class)), $encryptedFolderName);

                if (!$validator->validate($request->request->all())) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolation $violation) {
                                return $violation->getMessage();
                            },
                            \iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }
                //endregion Validation

                //region of uploading cover image
                if (!empty($cover = $request->request->get('cover_image'))) {
                    $coverPathDetails = pathinfo($cover);
                    $coverImageFolderPath = getImgPath('downloadable_materials.cover', ['{ID}' => $id]);
                    $coverImageFullPath = $coverImageFolderPath . $coverPathDetails['basename'];

                    create_dir($coverImageFolderPath);

                    if (!rename($cover, $coverImageFullPath)) {
                        jsonResponse(translate('systmess_internal_server_error'));
                    }

                    //region copy thumbs
                    $thumbs = config('img.downloadable_materials.cover.thumbs');

                    if (!empty($thumbs)) {
                        $tempFolderPath = getTempImgPath('downloadable_materials.cover', ['{ENCRYPTED_FOLDER_NAME}' => $encryptedFolderName]);

                        foreach ($thumbs as $thumb) {
                            $thumbName = str_replace('{THUMB_NAME}', $coverPathDetails['basename'], $thumb['name']);

                            if (!rename($tempFolderPath . $thumbName, $coverImageFolderPath . $thumbName)) {
                                jsonResponse(translate('systmess_internal_server_error'));
                            }
                        }
                    }
                    //endregion copy thumbs

                    $imagesToOptimization[] = [
                        'file_path'	=> getcwd() . DS . $coverImageFullPath,
                        'context'	  => ['id' => $id],
                        'type'		    => 'downloadable_materials_cover',
                    ];
                }
                //endregion of uploading cover image

                //region of uploading pdf file
                if (!empty($pdfFileName = $request->request->get('file'))) {
                    $pdfFileTempFolder = str_replace('{ENCRYPTED_FOLDER_NAME}', $encryptedFolderName, (string) config('files.downloadable_materials.pdf.temp_folder_path'));
                    $pdfFilePublicFolder = str_replace('{ID}', $id, (string) config('files.downloadable_materials.pdf.folder_path'));

                    if (!rename($pdfFileTempFolder . $pdfFileName, $pdfFilePublicFolder . $pdfFileName)) {
                        jsonResponse(translate('systmess_internal_server_error'));
                    }
                }
                //endregion of uploading pdf file

                if (!$materialsModel->updateOne($id, [
                    'short_description' => $request->request->get('short_description'),
                    'content'           => $request->request->get('content'),
                    'title'             => $request->request->get('title'),
                    'cover'             => $coverPathDetails['basename'] ?? $materialsPage['cover'],
                    'slug'              => strForUrl($request->request->get('title') . ' ' . $id),
                    'file'              => $pdfFileName ?: $materialsPage['file'],
                ])) {
                    jsonResponse(translate('dwn_record_not_updated'), 'error', []);
                }

                if (!empty($pdfFileName)) {
                    unlink($pdfFilePublicFolder . $materialsPage['file']);
                }

                if (!empty($cover)) {
                    $imagePath = getImgSrc('downloadable_materials.cover', 'original', ['{ID}' => $id, '{FILE_NAME}' => $materialsPage['cover']]);
                    $imagePathGlob = getImgSrc('downloadable_materials.cover', 'original', ['{ID}' => $id, '{FILE_NAME}' => '*' . pathinfo($materialsPage['cover'], PATHINFO_FILENAME) . '.*']);

                    removeFileByPatternIfExists($imagePath, $imagePathGlob);
                }

                if (!empty($imagesToOptimization)) {
                    /** @var Image_optimization_Model $optimizationImagesModel */
                    $optimizationImagesModel = model(Image_optimization_Model::class);

                    $optimizationImagesModel->insertMany($imagesToOptimization);
                }

                jsonResponse(translate('dwn_record_updated'), 'success');

            break;
            case 'delete':
                if (!$materialsModel->deleteOne($id)) {
                    jsonResponse(translate('dwn_record_not_removed'), 'error');
                }

                remove_dir(str_replace('{ID}', $id, (string) config('files.downloadable_materials.pdf.folder_path')));

                jsonResponse(translate('dwn_record_removed'), 'success');

            break;
        }
    }

    /**
     * This method is used for upload cover image on server in the temp folder.
     */
    public function uploadTempCoverImage(): void
    {
        checkIsAjax();
        checkIsLoggedAjax();

        if (!$uploadFolder = checkEncriptedFolder((string) uri()->segment(3))) {
            jsonResponse(translate('invalid_encrypted_folder_name'));
        }

        if (empty($_FILES['cover'])) {
            jsonResponse(translate('systmess_error_select_file_to_upload'));
        }

        $path = getTempImgPath('downloadable_materials.cover', ['{ENCRYPTED_FOLDER_NAME}' => $uploadFolder]);
        create_dir($path);

        // Count number of files in this folder, to prevent upload more files than image limit
        $fi = new FilesystemIterator($path, FilesystemIterator::SKIP_DOTS);
        $countTempImages = iterator_count($fi);

        if ($countTempImages >= 1 || count($_FILES['cover']['name']) > 1) {
            jsonResponse(translate('systmess_error_cannot_upload_more_than_1_image'));
        }

        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);

        $result = $interventionImageLibrary->image_processing($_FILES['cover'], [
            'destination'   => $path,
            'rules'         => config('img.downloadable_materials.cover.rules'),
            'handlers'      => [
                'create_thumbs' => config('img.downloadable_materials.cover.thumbs'),
            ],
        ]);

        if (!empty($result['errors'])) {
            jsonResponse($result['errors']);
        }

        $response = ['files' => []];
        foreach ($result as $resultByFile) {
            $response['files'][] = [
                'path'  => $path . $resultByFile['new_name'],
                'name'  => $resultByFile['new_name'],
            ];
        }

        jsonResponse('', 'success', $response);
    }

    public function removeTempCoverImage(): void
    {
        if (!$uploadFolder = checkEncriptedFolder((string) uri()->segment(3))) {
            jsonResponse(translate('invalid_encrypted_folder_name'));
        }

        if (empty($imageName = request()->request->get('file'))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $path = getTempImgPath('downloadable_materials.cover', ['{ENCRYPTED_FOLDER_NAME}' => $uploadFolder]);
        $filePath = $path . $imageName;

        if (!file_exists($filePath)) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $image_path_glob = $path . '*' . pathinfo($imageName, PATHINFO_FILENAME) . '.*';

        removeFileByPatternIfExists($filePath, $image_path_glob);

        jsonResponse('', 'success');
    }

    public function ajaxUploadTmpFiles(): void
    {
        checkIsAjax();
        checkIsLoggedAjax();

        if (empty($_FILES['file'])) {
            jsonResponse('The uploaded file not found.');
        }

        $uploadFolder = checkEncriptedFolder((string) uri()->segment(3));
        $tempPath = str_replace('{ENCRYPTED_FOLDER_NAME}', $uploadFolder, (string) config('files.downloadable_materials.pdf.temp_folder_path'));

        $fileData = $this->upload->upload_files_new([
            'data_files' => $_FILES['file'],
            'path'       => $tempPath,
            'rules'      => config('files.downloadable_materials.pdf.rules'),
        ]);

        if (!empty($fileData['errors'])) {
            $result['result'] = implode(', ', $fileData['errors']);
            $result['resultcode'] = 'failed';
            jsonResponse($fileData['errors'], 'error', $result);
        }

        $fileData = array_shift($fileData);
        $fileData['file_path'] = $tempPath . '/' . $fileData['new_name'];

        jsonResponse('', 'success', ['data' => $fileData]);
    }

    public function ajaxFormAdministration(): void
    {
        checkIsAjax();

        $request = request();

        switch ($this->uri->segment(3)) {
            case 'view':
                $slug = $this->uri->segment(4);
                $materialsModel = model(Downloadable_Materials_Model::class);
                $phone_codes_service = new PhoneCodesService(model('country'));
                $phone_codes = $phone_codes_service->getCountryCodes();
                $countries = model(Country_Model::class)->get_countries();

                $materialsContent = $materialsModel->findOneBy([
                    'conditions' => ['slug' => $slug],
                ]);

                views()->display(
                    'new/downloadable_materials/form_view',
                    [
                        'phone_codes' => $phone_codes,
                        'countries'   => $countries,
                        'content'     => $materialsContent,
                    ]
                );

            break;
            /**
             * @author Usinevici Alexandr
             * @todo Remove [24.05.2022]
             * Reason: not used
             */
            // case 'store':
            //     $materialsModel = model(Downloadable_Materials_Model::class);

            //     $materialRequest = [
            //         'title'             => $request->request->get('title') ?: null,
            //         'content'           => $request->request->get('content') ?: null,
            //         'cover'             => $request->request->get('cover') ?: null,
            //         'file'              => $request->request->get('file') ?: null,
            //         'slug'              => strForUrl($request->request->get('title')) ?: null,
            //         'short_description' => $request->request->get('short_description') ?: null,
            //     ];

            //     try {
            //         $recordId = $materialsModel->insertOne($materialRequest);

            //         if (!($recordId)) {
            //             throw new RuntimeException(translate('dwn_database_write_fail'));
            //         }

            //         $this->proceedFiles($recordId, $materialRequest['cover'], $materialRequest['file']);
            //     } catch (\Throwable $th) {
            //         throw $th;
            //     }

            //     jsonResponse(translate('dwn_record_created'), 'success');

            // break;
            case 'create':
                if (!ajax_validate_google_recaptcha()) {
                    jsonResponse(translate('systmess_error_you_didnt_pass_bot_check'));
                }

                $adapter = new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class));
                $validators = [
                    new DownloadableMaterialsValidator($adapter),
                    new PhoneValidator(
                        $adapter,
                        [
                            'code.invalid'       => translate('register_label_unknown_value', ['[COLUMN_NAME]' => translate('register_label_country_code')]),
                            'phone.invalid'      => translate('register_error_invalid_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                            'phone.unacceptable' => translate('register_error_country_unacceptable_phone', ['[COLUMN_NAME]' => translate('register_label_phone')]),
                        ],
                        ['phone' => 'Phone', 'code' => 'Phone code'],
                        ['phone' => 'phone', 'code' => 'code'],
                    ),
                ];

                $validator = new AggregateValidator($validators);
                if (!$validator->validate($request->request->all())) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolation $violation) {
                                return $violation->getMessage();
                            },
                            \iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }

                $usersModel = model(Users_Downloadable_Materials_Model::class);

                $title = $request->request->get('title');
                $slug = $request->request->get('slug');

                $materialsContent = model(Downloadable_Materials_Model::class)->findOneBy([
                    'conditions' => ['slug' => $slug],
                ]);
                if (empty($materialsContent)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

				$formRequest = [
					'fname'       => $request->request->get('fname') ?: null,
					'lname'       => $request->request->get('lname') ?: null,
					'email'       => cleanInput($request->request->get('email'), true) ?: null,
					'code'        => $request->request->get('code') ?: null,
					'phone'       => $request->request->get('phone') ?: null,
					'country'     => $request->request->get('country') ?: null,
					'id_material' => $materialsContent['id'],
					'referral'    => $request->server->get('HTTP_REFERER') ?? null
				];

                $formRequest['signature'] = hash('sha512', $formRequest['email']);

                $existCondition = $usersModel->findOneBy([
                    'conditions' => ['email' => $formRequest['email']],
                ]);

                if (is_null($existCondition)) {
                    try {
                        $recordId = $usersModel->insertOne($formRequest);

                        if (!($recordId)) {
                            throw new RuntimeException(translate('dwn_database_write_fail'));
                        }
                    } catch (\Throwable $th) {
                        jsonResponse(translate('dwn_database_write_fail'));
                    }

                    if ('prod' === config('env.APP_ENV')) {
                        /** @var TinyMVC_Library_Zoho_crm $crmLibrary */
                        $crmLibrary = library(TinyMVC_Library_Zoho_crm::class);

                        $crmLibrary->createLead([
                            'first_name'  => $formRequest['fname'],
                            'last_name'   => $formRequest['lname'],
                            'email'       => $formRequest['email'],
                            'phone'       => "{$formRequest['code']}-{$formRequest['phone']}",
                            'lead_type'   => 'Downloadable Materials',
                            'lead_source' => 'ExportPortal API',
                        ]);
                    }
                }

                /** @var MailerInterface $mailer */
                $mailer = $this->getContainer()->get(MailerInterface::class);
                $mailer->send(
                    (new DownloadableMaterialsData(cleanOutput($formRequest['fname'] . ' ' . $formRequest['lname']), cleanOutput($materialsContent['title']), $materialsContent['slug'], $formRequest['signature']))
                        ->to(new Address($formRequest['email']))
                );

                jsonResponse(translate('dwn_form_submit_message', ['{{TITLE}}' => $title]), 'success');

            break;
        }
    }

    public function ajaxShareAdministration(): void
    {
        checkIsAjax();

        $request = request();

        switch ($this->uri->segment(3)) {
            case 'view':
                $slug = $this->uri->segment(4);

                $materialsModel = model(Downloadable_Materials_Model::class);

                $materialsContent = $materialsModel->findOneBy([
                    'conditions' => ['slug' => $slug],
                ]);

                $materialsContent['share_url'] = __SITE_URL . "downloadable_materials/details/{$materialsContent['slug']}";

                $view = views()->fetch('new/downloadable_materials/share_view', ['content' => $materialsContent]);

                jsonResponse('', 'success', ['content' => $view]);

            break;
            case 'create':
                $validator = new EmailValidator(
                    new LegacyValidatorAdapter(library(TinyMVC_Library_validator::class))
                );

                if (!$validator->validate($request->request->all())) {
                    jsonResponse(
                        \array_map(
                            function (ConstraintViolation $violation) {
                                return $violation->getMessage();
                            },
                            \iterator_to_array($validator->getViolations()->getIterator())
                        )
                    );
                }

                $slug  = $request->request->get('slug');
                $email = cleanInput($request->request->get('email'), true);

                $formRequest = [
                    'email' => $email ?: null,
                ];

                $materialsContent = model(Downloadable_Materials_Model::class)->findOneBy([
                    'conditions' => ['slug' => $slug],
                ]);

                if (empty($materialsContent)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var MailerInterface $mailer */
                $mailer = $this->getContainer()->get(MailerInterface::class);
                $mailer->send(
                    (new DownloadableMaterialsShare(cleanOutput($materialsContent['title']), $materialsContent['slug']))
                        ->to(new Address($formRequest['email']))
                );

                jsonResponse(translate('dwn_shared_email_send'), 'success');

            break;
        }
    }

    public function export_statistics()
    {
        $id = (int) uri()->segment(3);
        if (empty($id)) {
            exit();
        }

        /** @var Users_Downloadable_Materials_Model $usersDownloadableMaterialsModel */
        $usersDownloadableMaterialsModel = model(Users_Downloadable_Materials_Model::class);

        $tableName = $usersDownloadableMaterialsModel->getTable();
        $data = $usersDownloadableMaterialsModel->findAllBy([
            'columns' => [
                "{$tableName}.`{$usersDownloadableMaterialsModel->getPrimaryKey()}`",
                "CONCAT_WS(' ',{$tableName}.`fname`, {$tableName}.`lname`) as user_name",
                '`email`',
                '`id_user`',
                '`id_material`',
                '`count`',
                '`phone`',
                '`code`',
                "{$tableName}.`country`",
                '`updated`',
                '`referral`',
                '`is_registered`',
                "{$usersDownloadableMaterialsModel->portCountryTableAlias}.`country_name`",
            ],
            'conditions' => ['id_material' => $id],
            'joins'      => ['countries'],
            'order'      => ['updated' => 'DESC'],
        ]);

        if (empty($data)) {
            exit();
        }
        $now = date('Y-m-d-H_i');
        $this->returnReport($data, "downloadable_stat_{$now}.xlsx");
    }

    /**
     * @author Usinevici Alexandr
     * @todo Remove [24.05.2022]
     * Reason: not used
     */
    // private function proceedFiles($id, $cover, $file, $oldCover = null, $oldFile = null): void
    // {
    //     /** @var FilesystemProviderInterface */
    //     $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
    //     $disk = $storageProvider->storage('public.storage');
    //     $tempDisk = $storageProvider->storage('temp.legacy.storage');
    //     $tempPath = 'downloadable_materials/' . id_session();
    //     $targetPath = "downloadable_materials/{$id}";
    //     $fileCondition = (!empty($file) && !empty($oldFile)) && ($file != $oldFile);
    //     $coverCondition = (!empty($cover) && !empty($oldCover)) && ($cover != $oldCover);

    //     //----------------- Proceed cover -----------------//

    //     if (!empty($cover)) {
    //         try {
    //             $disk->writeStream("{$targetPath}/{$cover}", $tempDisk->readStream("{$tempPath}/{$cover}"));
    //         } catch (UnableToWriteFile $e) {
    //             jsonResponse(translate('dwn_error_cover_uploading'));
    //         }

    //         if ($coverCondition && $disk->fileExists("{$targetPath}/{$oldCover}")) {
    //             $disk->delete("{$targetPath}/{$oldCover}");
    //         }
    //     }

    //     //----------------- Proceed file -----------------//

    //     if (!empty($file)) {
    //         try {
    //             $disk->writeStream("{$targetPath}/{$file}", $tempDisk->readStream("{$tempPath}/{$file}"));
    //         } catch (UnableToWriteFile $e) {
    //             jsonResponse(translate('dwn_error_file_uploading'));
    //         }

    //         if ($fileCondition && $disk->fileExists("{$targetPath}/{$oldFile}")) {
    //             $disk->delete("{$targetPath}/{$oldFile}");
    //         }
    //     }
    // }

    /**
     * Get report.
     *
     * @param array  $data     - log data
     * @param string $fileName - name of the file with extension
     */
    private function returnReport($data, $fileName = 'statistics.xlsx')
    {
        $excel = new Spreadsheet();
        $excel->setActiveSheetIndex(0);
        $activeSheet = $excel->getActiveSheet();
        $activeSheet->setTitle('User Activity');

        $headerColumns = [
            'A' => ['name' => 'Name',          'width' => 60],
            'B' => ['name' => 'Email',         'width' => 50],
            'C' => ['name' => 'Phone',         'width' => 50],
            'D' => ['name' => 'Country',       'width' => 40],
            'E' => ['name' => 'Downloads',     'width' => 20],
            'F' => ['name' => 'Last download', 'width' => 40],
            'G' => ['name' => 'Referral',      'width' => 90],
            'H' => ['name' => 'Is Registered', 'width' => 90],
        ];

        //region generate headings
        $rowIndex = 1;

        foreach ($headerColumns as $letter => $heading) {
            $activeSheet->getColumnDimension($letter)->setWidth($heading['width']);
            $activeSheet->getStyle($letter . $rowIndex)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $activeSheet->getStyle($letter . $rowIndex)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $activeSheet->setCellValue($letter . $rowIndex, $heading['name'])
                ->getStyle($letter . $rowIndex)
                ->getFont()
                ->setSize(14)
                ->setBold(true)
            ;
        }
        //endregion generate headings

        //region introduce data
        $rowIndex = 2;
        $excel->getDefaultStyle()->getAlignment()->setWrapText(true);
        foreach ($data as $one) {
            $activeSheet
                ->setCellValue("A{$rowIndex}", $one['user_name'])
                ->setCellValue("B{$rowIndex}", $one['email'])
                ->setCellValue("C{$rowIndex}", $one['phone_code'] . ' ' . $one['phone'])
                ->setCellValue("D{$rowIndex}", $one['country_name'])
                ->setCellValue("E{$rowIndex}", (int) $one['count'])
                ->setCellValue("F{$rowIndex}", getDateFormat($one['updated']))
                ->setCellValue("G{$rowIndex}", $one['referral'])
                ->setCellValue("H{$rowIndex}", 1 == (int) $one['is_registered'] ? 'Yes' : 'No')
            ;

            ++$rowIndex;
        }
        //endregion introduce data

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $objWriter = IOFactory::createWriter($excel, 'Xlsx');
        $objWriter->save('php://output');
    }

    /**
     * Save that user downloaded material.
     *
     * @param int $id = the id of the material
     */
    private function saveStatistics($id)
    {
        /** @var Users_Downloadable_Materials_Model $usersModel */
        $usersModel = model(Users_Downloadable_Materials_Model::class);

        $crawlerDetect = new CrawlerDetect();
        if ($crawlerDetect->isCrawler()) {
            return;
        }

        if (logged_in()) {
            //check if such record (with this user and this material) exists
            $exists = $usersModel->findOneBy([
                'conditions' => [
                    'id_user'       => id_session(),
                    'id_material'   => $id,
                ],
            ]);

            //if exists then update count else insert
            if ($exists) {
                $usersModel->updateOne($exists['id'], [
                    'count' => (int) $exists['count'] + 1,
                ]);
            } else {
                /** @var User_Model $userModel */
                $userModel = model(User_Model::class);

                $userData = $userModel->getSimpleUser(id_session());
                $usersModel->insertOne([
                    'id_user'       => id_session(),
                    'fname'         => $userData['fname'],
                    'lname'         => $userData['lname'],
                    'email'         => $userData['email'],
                    'country'       => $userData['country'],
                    'code'          => $userData['phone_code'],
                    'phone'         => $userData['phone'],
                    'id_material'   => $id,
                    'is_registered' => 1,
                    'count'         => 1,
                    'referral'      => request()->server->get('HTTP_REFERER') ?? null,
                ]);
            }
        } elseif (cookies()->exist_cookie('_ep_material_downloaded')) {
            //if cookie is set then check this hash and material already exists in the table
            $exists = $usersModel->findOneBy([
                'conditions' => [
                    'signature'   => cookies()->getCookieParam('_ep_material_downloaded'),
                    'id_material' => (int) $id,
                ],
            ]);

            //if exists for this material then update count
            if (!empty($exists)) {
                $usersModel->updateOne($exists['id'], [
                    'count' => (int) $exists['count'] + 1,
                ]);
            } else {
                //if hash exists (without id material) then copy the data from that record and insert with current material id
                $exists = $usersModel->findOneBy([
                    'conditions' => [
                        'signature'   => cookies()->getCookieParam('_ep_material_downloaded'),
                    ],
                ]);

                if (empty($exists)) {
                    return;
                }

                unset($exists['id']);

                $exists['id_material'] = $id;
                $exists['count'] = 1;
                $exists['referral'] = request()->server->get('HTTP_REFERER') ?? null;

                $usersModel->insertOne($exists);
            }
        }
    }
}

// End of file downloadable_materials.php
// Location: /tinymvc/myapp/controllers/downloadable_materials.php
