<?php

use App\Filesystem\FilePathGenerator;
use App\Filesystem\PromoBannerPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\UnableToWriteFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Controller Promo Banners
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Promo_Banners_Controller extends TinyMVC_Controller
{
    /**
     * Index page
     */
    public function index(): void
    {
        headerRedirect(__SITE_URL."banners/administration");
    }

    public function administration()
    {
        checkPermision('moderate_content');

        views(
            [
                'admin/header_view',
                'admin/promo_banners/index_view',
                'admin/footer_view'
            ],
            [
                'byPages' => $this->getPagePosition()
            ]
        );
    }

    public function ajaxDtAdministration()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjaxDT('moderate_content');

        try {
            $paginator = $this->getTableContent();

            jsonResponse('', 'success', [
                'sEcho'                => request()->request->getInt('sEcho', 0),
                'iTotalRecords'        => $paginator['total'] ?? 0,
                'iTotalDisplayRecords' => $paginator['total'] ?? 0,
                'aaData'               => $paginator['data'] ?? [],
            ]);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getTableContent(): array
    {
        $request = request();
        $parameters = $request->request;
        $limit = $parameters->getInt('iDisplayLength', 10);
        $skip = $parameters->getInt('iDisplayStart', 0);
        $page = $skip / $limit + 1;
        $with = [];
        $joins = ['Page', 'pagePosition'];

        $conditions = dtConditions($parameters->all(), [
            ['as' => 'search',             'key' => 'keywords',          'type' => 'cleaninput|trim'],
            ['as' => 'id_banners_position','key' => 'page_position',     'type' => 'intval'],
            ['as' => 'id_page',            'key' => 'page_selection',    'type' => 'intval'],
            ['as' => 'added_to',           'key' => 'added_to',          'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'updated_to',         'key' => 'updated_to',        'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'added_from',         'key' => 'added_from',        'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'updated_from',       'key' => 'updated_from',      'type' => 'getDateFormat:m/d/Y,Y-m-d'],
        ]);

        /** @var Promo_Banners_Model $promoBannersRepository */
        $promoBannersRepository = model(Promo_Banners_Model::class);

        $bannersTableAlias = $promoBannersRepository->getTable();

        $order = array_column(dt_ordering($parameters->all(), [
            'dt_id'             => "`{$bannersTableAlias}`.`id_promo_banners`",
            'dt_title'          => "`{$bannersTableAlias}`.`title`",
            'dt_order_banner'   => "`{$bannersTableAlias}`.`order_banner`",
            'dt_visible'        => "`{$bannersTableAlias}`.`is_visible`",
            'dt_date_updated'   => "`{$bannersTableAlias}`.`date_updated`",
            'dt_date_added'     => "`{$bannersTableAlias}`.`date_added`",
        ]), 'direction', 'column');

        $banners = $promoBannersRepository->get_banners(compact('conditions', 'with', 'joins', 'limit', 'skip', 'order'));
        $countBanners = $promoBannersRepository->get_count_banners(compact('conditions', 'joins'));

        $response = [
            'total' => $countBanners,
            'data' => []
        ];

        if (null === $banners || $banners->isEmpty()) {
            return $response;
        }

        foreach ($banners as $bannersItem) {
            $actions = [];

            $editBannerUrl = __SITE_URL . 'promo_banners/popupForms/edit_banner/' . $bannersItem['id_promo_banners'];
            $actions[] = <<<BUTTON
                <a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax"
                    title="Edit banner"
                    href="{$editBannerUrl}"
                    data-title="Edit text block">
                </a>
                BUTTON;

            $actions[] = <<<BUTTON
                <a class="confirm-dialog"
                    data-callback="deleteBanner"
                    data-message="Do you really want to delete the banner?"
                    data-banner="{$bannersItem['id_promo_banners']}"
                    title="Delete the banner">
                    <span class="ep-icon ep-icon_remove txt-red"></span>
                </a>
                BUTTON;

            $actions[] = <<<BUTTON
                <a class="confirm-dialog"
                    data-callback="visibleBanner"
                    data-message="Do you really want to change visible banner?"
                    data-banner="{$bannersItem['id_promo_banners']}"
                    title="Change visible the banner">
                    <span class="ep-icon ep-icon_visible"></span>
                </a>
                BUTTON;

            $imagesBanner = json_decode($bannersItem['image'], true);
            $image = getDisplayImageLink(['{ID}' => $bannersItem['id_promo_banners'], '{FILE_NAME}' => $imagesBanner['desktop']], 'promo_banners.main');


            $response['data'][] = [
                'dt_id'                     => $bannersItem['id_promo_banners'],
                'dt_title'                  => $bannersItem['title'],
                'dt_link'                   => '<a href="' . $bannersItem['link'] . '" target="_blank">Banner link</a>',
                'dt_image'                  => '<div><img class="mw-100 mh-100" src="' . $image . '"></div>',
                'dt_page_position_name'     => '<div><span class="display-ib txt-bold mnw-70">Page name:</span> ' . $bannersItem['page_name'] . '</div> <div><span class="display-ib txt-bold mnw-70">Position:</span> ' . $bannersItem['position_name'] . '</div>',
                'dt_order_banner'           => $bannersItem['order_banner'],
                'dt_date_added'             => $bannersItem['date_added'],
                'dt_date_updated'           => $bannersItem['date_updated'],
                'dt_visible'                => ($bannersItem['is_visible']?'<span class="m-0 fs-20 txt-green ep-icon ep-icon_ok-circle2"></span>':'<span class="m-0 fs-20 txt-red ep-icon ep-icon_minus-circle"></span>'),
                'dt_actions'                => implode(' ', $actions)
            ];
        }

        return $response;
    }

    function popupForms(){
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse('Error: You must be logged in to perform this operation.');
        }

        $op = uri()->segment(3);

        switch ($op) {
            case 'add_banner':
                checkAdmin('manage_content');

                $byPages = $this->getPagePosition();
                $uploadFolder = encriptedFolderName();

                if(empty($uploadFolder)){
                    messageInModal('Error: Upload folder is not correct.', 'errors');
                }

                /** @var Promo_Banners_Page_Position_Model $pagePosition */
                $pagePosition = model(Promo_Banners_Page_Position_Model::class);
                $bannerPage = $pagePosition->get_banners_position();
                $bannerByPosition = [];

                foreach ($bannerPage as $bannerPageItem) {
                    $bannerByPosition[$bannerPageItem['id_promo_banners_page_position']] = json_decode($bannerPageItem['image_size'], true);
                }

                views(
                    [
                        'admin/promo_banners/form_view',
                    ],
                    [
                        'bannerByPosition' => $bannerByPosition,
                        'byPages' => $byPages,
                        'uploadFolder' => $uploadFolder,
                        'fileuploadMaxFileSize' => config('fileupload_max_file_size'),
                    ]
                );
                break;
            case 'edit_banner':
                checkAdmin('manage_content');

                $idBanner = (int) uri()->segment(4);

                try {
                    /** @var Promo_Banners_Model $promoBannersRepository */
                    $promoBannersRepository = model(Promo_Banners_Model::class);

                    $bannerInfo = $promoBannersRepository->get_banner($idBanner);
                } catch (Exception $e) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $byPages = $this->getPagePosition();
                $uploadFolder = encriptedFolderName();

                /** @var Promo_Banners_Page_Position_Model $pagePosition */
                $pagePosition = model(Promo_Banners_Page_Position_Model::class);
                $bannerPage = $pagePosition->get_banners_position();
                $bannerByPosition = [];

                foreach ($bannerPage as $bannerPageItem) {
                    $bannerByPosition[$bannerPageItem['id_promo_banners_page_position']] = json_decode($bannerPageItem['image_size'], true);
                }

                views(
                    [
                        'admin/promo_banners/form_view',
                    ],
                    [
                        'bannerByPosition' => $bannerByPosition,
                        'bannerInfo' => $bannerInfo,
                        'byPages' => $byPages,
                        'uploadFolder' => $uploadFolder,
                        'fileuploadMaxFileSize' => config('fileupload_max_file_size'),
                    ]
                );
                break;
        }
    }

    public function ajaxOperation() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse('Error: You should be logged in to perform this action.');
        }

        $op = uri()->segment(3);
        $request = request();
        $parameters = $request->request;

        switch ($op) {
            case 'add_banner':
                checkAdmin('manage_content');
                $validator = $this->validator;

                $validator_rules = [
                    [
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => ['required' => '', 'max_len[50]' => ''],
                    ],
                    [
                        'field' => 'link',
                        'label' => 'Link',
                        'rules' => ['max_len[250]' => ''],
                    ],
                    [
                        'field' => 'order_banner',
                        'label' => 'Order',
                        'rules' => ['required' => '', 'integer' => '', 'min[0]', 'max[99]'],
                    ],
                    [
                        'field' => 'id_page_position',
                        'label' => 'Page position',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'image',
                        'label' => 'Banner images',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'will_open_popup',
                        'label' => 'Will open popup?',
                        'rules' => ['required' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($validator->get_array_errors());
                }

                $idPagePosition = $parameters->getInt('id_page_position');

                /** @var Promo_Banners_Page_Position_Model $promoBannersPagePositionRepository */
                $promoBannersPagePositionRepository = model(Promo_Banners_Page_Position_Model::class);
                $positionInfo = $promoBannersPagePositionRepository->get_banner_page_by_id($idPagePosition);

                if (empty($positionInfo['image_size'])){
                    jsonResponse('The position images is empty.');
                }

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $tempDisk = $storageProvider->storage('temp.storage');
                $publicDiskPrefixer = $storageProvider->prefixer('public.storage');

                $imageSize = json_decode($positionInfo['image_size'], true);
                $image = $parameters->get('image');
                $cleanImages = [];
                foreach ($imageSize as $imageSizeKey => $imageSizeItem) {
                    $pathImage = $image[$imageSizeKey];
                    $imageName = pathinfo($pathImage, PATHINFO_BASENAME);

                    if (empty($pathImage) || !$tempDisk->fileExists(FilePathGenerator::uploadedFile($imageName))) {
                        jsonResponse('The position images is not correct.');
                        break;
                    }

                    $cleanImages[$imageSizeKey] = $pathImage;
                }

                /** @var Promo_Banners_Model $promoBannersRepository */
                $promoBannersRepository = model(Promo_Banners_Model::class);

                if (empty($idPromoBanner = $promoBannersRepository->add([
                    'title'               => cleanInput($parameters->get('title')),
                    'link'                => cleanInput($parameters->get('link')),
                    'id_page_position'    => $idPagePosition,
                    'order_banner'        => $parameters->getInt('order_banner'),
                    'will_open_popup'     => (int) $parameters->getInt('will_open_popup'),
                    'popup_action'        => cleanInput($parameters->get('popup_action')),
                    'popup_legacy_action' => cleanInput($parameters->get('popup_legacy_action')),
                    'popup_bg_path'       => cleanInput($parameters->get('popup_bg_path')),
                ]))) {
                    jsonResponse('Cannot insert now. Please try later.');
                }

                // Check folder with files
                if (empty(checkEncriptedFolder($parameters->get('upload_folder')))) {
                    $promoBannersRepository->deleteRecord(['conditions' => ['id_banners' => $idPromoBanner]]);
                    jsonResponse('File upload path is not correct.');
                }
                //endregion Directory check

                /** @var Image_optimization_Model $optimizeRepository */
                $optimizeRepository = model(Image_optimization_Model::class);
                $updateImages = [];

                foreach ($cleanImages as $imageSizeKey => $cleanImage) {
                    $imageName = pathinfo($cleanImage, PATHINFO_BASENAME);
                    $fullPublicPath = $publicDiskPrefixer->prefixPath(PromoBannerPathGenerator::publicPromoBannerPath(id_session(), $imageName));

                    try {
                        $publicDisk->write(
                            PromoBannerPathGenerator::publicPromoBannerPath($idPromoBanner, $imageName),
                            $tempDisk->read(FilePathGenerator::uploadedFile($imageName)));
                    } catch (UnableToWriteFile $e) {
                        jsonResponse(translate('systmess_error_promo_banner_wasnt_added'));
                    }

                    $updateImages[$imageSizeKey] = $imageName;
                    $optimizeRepository->insertOne([
                        'file_path'	=> $fullPublicPath,
                        'context'   => ['bannerId' => $idPromoBanner, 'type' => $imageSizeKey],
                        'type'      => 'promo_banner_image',
                    ]);
                }

                $promoBannersRepository->edit(
                    $idPromoBanner,
                    [
                        'image' => json_encode($updateImages),
                    ],
                );

                jsonResponse('The banner has been successfully inserted.', 'success');

            break;
            case 'edit_banner':
                checkAdmin('manage_content');
                $validator = $this->validator;

                $validator_rules = [
                    [
                        'field' => 'title',
                        'label' => 'Title',
                        'rules' => ['required' => '', 'max_len[50]' => ''],
                    ],
                    [
                        'field' => 'link',
                        'label' => 'Link',
                        'rules' => ['max_len[250]' => ''],
                    ],
                    [
                        'field' => 'order_banner',
                        'label' => 'Order',
                        'rules' => ['required' => '', 'integer' => '', 'min[0]', 'max[99]'],
                    ],
                    [
                        'field' => 'id_promo_banners',
                        'label' => 'Promo banner',
                        'rules' => ['required' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'will_open_popup',
                        'label' => 'Will open popup?',
                        'rules' => ['required' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$validator->validate()) {
                    jsonResponse($validator->get_array_errors());
                }

                $idBanner = $parameters->getInt('id_promo_banners');
                /** @var Promo_Banners_Model $promoBannersRepository */
                $promoBannersRepository = model(Promo_Banners_Model::class);

                if (empty($bannerInfo = $promoBannersRepository->get_banner($idBanner))) {
                    jsonResponse('Could not find the banner.');
                }

                $update = [
                    'title'               => cleanInput($parameters->get('title')),
                    'link'                => cleanInput($parameters->get('link')),
                    'order_banner'        => $parameters->getInt('order_banner'),
                    'will_open_popup'     => (int) $parameters->getInt('will_open_popup'),
                    'popup_action'        => cleanInput($parameters->get('popup_action')),
                    'popup_legacy_action' => cleanInput($parameters->get('popup_legacy_action')),
                    'popup_bg_path'       => cleanInput($parameters->get('popup_bg_path')),
                ];

                $image = (array) $parameters->get('image');
                if (!empty($image)) {
                    /** @var Promo_Banners_Page_Position_Model $promoBannersPagePositionRepository */
                    $promoBannersPagePositionRepository = model(Promo_Banners_Page_Position_Model::class);
                    $positionInfo = $promoBannersPagePositionRepository->get_banner_page_by_id($bannerInfo['id_page_position']);

                    /** @var FilesystemProviderInterface */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $tempDisk = $storageProvider->storage('temp.storage');
                    $publicDiskPrefixer = $storageProvider->prefixer('public.storage');

                    if (empty($positionInfo['image_size'])) {
                        jsonResponse('The position images is empty.');
                    }

                    $imageSize = json_decode($positionInfo['image_size'], true);
                    foreach ($image as $imageKey => $imageItem) {
                        $imageName = pathinfo($imageItem, PATHINFO_BASENAME);

                        if (empty($imageSize[$imageKey]) || !$tempDisk->fileExists(FilePathGenerator::uploadedFile($imageName))) {
                            jsonResponse('The position images is not correct.');

                            break;
                        }
                    }

                    /** @var Image_optimization_Model $optimizeRepository */
                    $optimizeRepository = model(Image_optimization_Model::class);
                    $uploadedImages = json_decode($bannerInfo['image'], true);

                    foreach ($image as $imageKey => $imageItem) {
                        $imageName = pathinfo($imageItem, PATHINFO_BASENAME);
                        $fullPublicPath = $publicDiskPrefixer->prefixDirectoryPath(PromoBannerPathGenerator::publicPromoBannerPath($idBanner, $imageName));

                        if(isset($uploadedImages[$imageKey])) {
                            $oldFileName = $uploadedImages[$imageKey];
                            try {
                                $publicDisk->delete(PromoBannerPathGenerator::publicPromoBannerPath($idBanner, $oldFileName));
                            } catch (\Throwable $th) {
                                //throw $th;
                            }
                        }

                        try {
                            $publicDisk->write(
                                PromoBannerPathGenerator::publicPromoBannerPath($idBanner, $imageName),
                                $tempDisk->read(FilePathGenerator::uploadedFile($imageName))
                            );
                        } catch (UnableToWriteFile $e) {
                            jsonResponse(translate('systmess_error_promo_banner_wasnt_updated'));
                        }

                        $uploadedImages[$imageKey] = $imageName;
                        $optimizeRepository->insertOne([
                            'file_path'	=> $fullPublicPath,
                            'context'   => ['bannerId' => $idBanner, 'type' => $imageKey],
                            'type'	     => 'promo_banner_image',
                        ]);
                    }

                    $update['image'] = json_encode($uploadedImages);
                }

				if (!$promoBannersRepository->edit($idBanner, $update)) {
                    jsonResponse('Cannot update this banner now Please try later.');
                }

                jsonResponse('The banner has been successfully updated.', 'success');

            break;
            case 'delete':
                checkAdmin('manage_content');

                $idBanner = $parameters->getInt('banner');

                /** @var Promo_Banners_Model $promoBannersRepository */
                $promoBannersRepository = model(Promo_Banners_Model::class);

                $bannerInfo = $promoBannersRepository->get_banner($idBanner);

                if(empty($bannerInfo)) {
                    jsonResponse('Could not find the banner.');
                }

                if($promoBannersRepository->deleteRecord(['conditions' => ['id_banners' => $idBanner]])) {
                    /** @var FilesystemProviderInterface */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');

                    try {
                        $publicDisk->deleteDirectory(PromoBannerPathGenerator::relativePromoBannerPath($idBanner));
                    } catch (\Throwable $th) {
                        //throw $th;
                    }

                    jsonResponse('The banner has been successfully deleted.', 'success');
                }

                jsonResponse('Error: Cannot delete this banner now. Please try later.');
            break;
            case 'visible':
                checkAdmin('manage_content');

                $idBanner = $parameters->getInt('banner');

                /** @var Promo_Banners_Model $promoBannersRepository */
                $promoBannersRepository = model(Promo_Banners_Model::class);

                $bannerInfo = $promoBannersRepository->get_banner($idBanner);

                if(empty($bannerInfo)) {
                    jsonResponse('Could not find the banner.');
                }

                $isVisible = (int) $bannerInfo['is_visible'];

                $update = [
                    'is_visible'  => !$isVisible,
                ];

                if($promoBannersRepository->edit($idBanner, $update)) {
                    jsonResponse('The banner has been successfully change.', 'success');
                }

                jsonResponse('Error: Cannot change visible this banner now. Please try later.');
            break;
        }
    }

    private function getPagePosition() {
        /** @var Promo_Banners_Page_Position_Model $promoBannersPagePositionRepository */
        $promoBannersPagePositionRepository = model(Promo_Banners_Page_Position_Model::class);
        $with = [];
        $joins = ['page'];
        $bannerPage = $promoBannersPagePositionRepository->get_banners_position(compact('with', 'joins'));
        $byPages = [];

        foreach ($bannerPage as $bannerPageItem) {
            if (!isset($byPages[$bannerPageItem['id_page']]['name'])) {
                $byPages[$bannerPageItem['id_page']]['page_name'] = $bannerPageItem['page_name'];
                $byPages[$bannerPageItem['id_page']]['id_page'] = $bannerPageItem['id_page'];
            }

            $byPages[$bannerPageItem['id_page']]['positions'][$bannerPageItem['id_promo_banners_page_position']] = [
                'id_page_position'  => $bannerPageItem['id_promo_banners_page_position'],
                'position_name'     => $bannerPageItem['position_name'],
                'image_size'        => $bannerPageItem['image_size'],
            ];
        }

        return $byPages;
    }

    function ajax_banner_upload_photo() {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('manage_content');

		if (empty($files = $_FILES['files'])) {
			jsonResponse('Please select file to upload.');
        }

        $pagePosition = (int) uri()->segment(4);
        $typeMedia = cleanInput(uri()->segment(5));
		if (!($uploadFolder = checkEncriptedFolder(uri()->segment(3)))) {
			jsonResponse('File upload path is not correct.');
        }

        if (!$pagePosition || empty($typeMedia)) {
            jsonResponse('The position is not correct.');
        }

        /** @var Promo_Banners_Page_Position_Model $promoBannersPagePositionRepository */
        $promoBannersPagePositionRepository = model(Promo_Banners_Page_Position_Model::class);
        $positionInfo = $promoBannersPagePositionRepository->get_banner_page_by_id($pagePosition);

        if (empty($positionInfo['image_size'])) {
            jsonResponse('The position is not correct.');
        }

        $imageSize = json_decode($positionInfo['image_size'], true);
        $imageMedia = $imageSize[$typeMedia];
        if (empty($imageMedia)) {
            jsonResponse('The position is not correct.');
        }
        /**
         *
         * @todo Filesystem temp upload
         *
        */
        /** @var null|UploadedFile */
        $uploadedFile = ((array) request()->files->get('files', []))[0] ?? null;
        if (empty($uploadedFile) || !$uploadedFile->isValid()) {
            jsonResponse('Please select file to upload.');
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $tempDiskPrefixer = $storageProvider->prefixer('temp.storage');

        $imageName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');
        $uploadFileName = FilePathGenerator::uploadedFile($imageName);
        $tempDisk->createDirectory($uploadDirectory = dirname($uploadFileName));
        $path = $tempDiskPrefixer->prefixDirectoryPath($uploadDirectory);

        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
        $images = $interventionImageLibrary->image_processing(
            ['tmp_name' => $uploadedFile->getRealPath(), 'name' => pathinfo($uploadFileName, PATHINFO_FILENAME)],
            [
                'convert'           => 'jpg',
                'quality'           => 100,
                'destination'       => $path,
                'use_original_name' => true,
                'rules'             => [
                    'size'          => config('fileupload_max_file_size'),
                    'height'        => $imageMedia['size']['h'],
                    'width'         => $imageMedia['size']['w']
                ],
            ]
        );

        if (!empty($images['errors'])) {
            jsonResponse($images['errors']);
        }

		jsonResponse(
            'Main photo was successfully uploaded.',
            'success',
            [
                'files' => [
                    [
                        'url'  => $tempDisk->url($imagePath = "{$uploadDirectory}/{$images[0]['new_name']}"),
			            'name' => $images[0]['new_name'],
                        'path' => $imagePath,
                    ],
                ],
            ],
        );
    }

/**
 * @todo Refactoring of Delete functional
 *
*/
    function ajax_banner_delete_files() {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('manage_content');

        if (empty($imageName = request()->request->get('file'))) {
            jsonResponse('File name is not correct.');
        }

		$uploadFolder = uri()->segment(3);
		if(!($uploadFolder = checkEncriptedFolder($uploadFolder))){
			jsonResponse('Error: File upload path is not correct.');
		}

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $path = FilePathGenerator::uploadedFile($imageName);

        try {
            $tempDisk->delete($path);
        } catch (\Throwable $th) {
            jsonResponse(translate('validation_images_delete_fail'));
        }

        jsonResponse('','success');
    }
}

/* End of file promo_banners.php */
/* Location: /tinymvc/myapp/controllers/promo_banners.php */
