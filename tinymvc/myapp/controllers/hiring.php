<?php


use App\Filesystem\FilePathGenerator;
use App\Filesystem\FilePathGenerator as FilesystemFilePathGenerator;
use App\Filesystem\VacancyPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Bendiucov Tatiana
 *
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Hiring_Controller extends TinyMVC_Controller
{
    private $breadcrumbs = [];

     /**
     * Controller constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        /** @var FilesystemProviderInterface */
        $storageProvider = $container->get(FilesystemProviderInterface::class);

        $this->storage = $storageProvider->storage('public.storage');
        $this->tempStorage = $storageProvider->storage('temp.storage');
    }


    public function administration()
    {
        checkAdmin('manage_content');

        $this->_load_main(); // load main models

        $data = $this->session->getMessages();
        $data['hirings_list'] = $this->hiring->get_vacancies();

        $this->view->assign('title', 'Vacancies');
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/hiring/index_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_operations()
    {
        checkIsAjax();

        $this->_load_main();

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'create':
                checkAdminAjax('moderate_content');

                $validator_rules = [
                    [
                        'field' => 'country',
                        'label' => 'Country',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'office',
                        'label' => 'Office',
                        'rules' => ['min[1]' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'post',
                        'label' => 'Post vacancy',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'link',
                        'label' => 'Link',
                        'rules' => ['required' => '', 'valid_url' => ''],
                    ],
                    [
                        'field' => 'description',
                        'label' => 'Description vacancy',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'image',
                        'label' => 'Image',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'visible',
                        'label' => 'Visible',
                        'rules' => ['required' => '', 'integer' => '', 'min[0]' => '', 'max[1]' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $request = request()->request;

                $countryId = $request->get('country');
                if ('all' == $countryId) {
                    $countryId = 0;
                } else {
                    /** @var Country_Model $countryModel */
                    $countryModel = model(Country_Model::class);

                    if (empty($countryModel->get_country($countryId = (int) $countryId))) {
                        jsonResponse('Invalid country id');
                    }
                }

                /** @var Hiring_Model $hiringModel */
                $hiringModel = model(Hiring_Model::class);

                /** @var TinyMVC_Library_Cleanhtml $cleanhtmlLibrary */
                $cleanhtmlLibrary = library(TinyMVC_Library_Cleanhtml::class);

                $cleanhtmlLibrary->defaultTextarea();
                $cleanhtmlLibrary->addAdditionalTags('<img><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');

                $vacancyId = $hiringModel->set_vacancy([
                    'description_vacancy'   => $cleanhtmlLibrary->sanitize($request->get('description')),
                    'visible_vacancy'       => $request->getInt('visible'),
                    'post_vacancy'          => cleanInput($request->get('post')),
                    'link_vacancy'          => cleanInput($request->get('link')),
                    'id_country'            => $countryId,
                    'id_office'             => $request->getInt('office'),
                ]);

                $imageName = pathinfo($request->get('image'), PATHINFO_BASENAME);

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $tempDisk = $storageProvider->storage('temp.storage');

                $imagePath = request()->request->get('image');
                $imageName = pathinfo($imagePath, PATHINFO_BASENAME);
                $path = FilePathGenerator::uploadedFile($imageName);

                try {
                    $publicDisk->write(
                        VacancyPathGenerator::defaultUploadPath($vacancyId) . $imageName,
                        $tempDisk->read($path)
                    );
                } catch (\Throwable $th) {
                    try {
                        $publicDisk->deleteDirectory(VacancyPathGenerator::defaultUploadPath($vacancyId));
                    } catch (\Throwable $th) {

                    }

                    jsonResponse(translate('systmess_cannot_save_picture'));
                }

                $hiringModel->update_vacancy(
                    $vacancyId,
                    [
                        'photo' => $imageName,
                    ]
                );

                jsonResponse('The vacancy has been successfully inserted.', 'success');

            break;
            case 'update':
                checkAdminAjax('moderate_content');

                $validator_rules = [
                    [
                        'field' => 'country',
                        'label' => 'Country',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'office',
                        'label' => 'Office',
                        'rules' => ['min[1]' => '', 'integer' => ''],
                    ],
                    [
                        'field' => 'post',
                        'label' => 'Post vacancy',
                        'rules' => ['required' => '', 'max_len[255]' => ''],
                    ],
                    [
                        'field' => 'link',
                        'label' => 'Link',
                        'rules' => ['required' => '', 'valid_url' => ''],
                    ],
                    [
                        'field' => 'description',
                        'label' => 'Description vacancy',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'visible',
                        'label' => 'Visible',
                        'rules' => ['required' => '', 'integer' => '', 'min[0]' => '', 'max[1]' => ''],
                    ],
                ];

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                $request = request()->request;

                $countryId = $request->get('country');
                if ('all' == $countryId) {
                    $countryId = 0;
                } else {
                    /** @var Country_Model $countryModel */
                    $countryModel = model(Country_Model::class);

                    if (empty($countryModel->get_country($countryId = (int) $countryId))) {
                        jsonResponse('Invalid country id');
                    }
                }

                /** @var Hiring_Model $hiringModel */
                $hiringModel = model(Hiring_Model::class);

                if (empty($vacancyId = $request->getInt('id_vacancy')) || empty($vacancy = $hiringModel->get_vacancy($vacancyId))) {
                    jsonResponse('This vacancy does not exist.');
                }

                /** @var TinyMVC_Library_Cleanhtml $cleanhtmlLibrary */
                $cleanhtmlLibrary = library(TinyMVC_Library_Cleanhtml::class);

                $cleanhtmlLibrary->defaultTextarea();
                $cleanhtmlLibrary->addAdditionalTags('<img><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');

                if (empty($vacancy['photo'])) {
                    if (empty($tempImage = $request->get('image'))) {
                        jsonResponse('Image is required');
                    }

                    $imageName = pathinfo($request->get('image'), PATHINFO_BASENAME);

                    /** @var FilesystemProviderInterface */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDisk = $storageProvider->storage('public.storage');
                    $tempDisk = $storageProvider->storage('temp.storage');

                    $imagePath = request()->request->get('image');
                    $imageName = pathinfo($imagePath, PATHINFO_BASENAME);
                    $path = FilePathGenerator::uploadedFile($imageName);

                    try {
                        $publicDisk->write(
                            VacancyPathGenerator::defaultUploadPath($vacancyId) . $imageName,
                            $tempDisk->read($path)
                        );
                    } catch (\Throwable $th) {
                        try {
                            $publicDisk->deleteDirectory(VacancyPathGenerator::defaultUploadPath($vacancyId));
                        } catch (\Throwable $th) {
                            // NOTHIND TO DO
                        }

                        jsonResponse(translate('systmess_cannot_save_picture'));
                    }
                }

                $hiringModel->update_vacancy(
                    $vacancyId,
                    [
                        'description_vacancy'   => $cleanhtmlLibrary->sanitize($request->get('description')),
                        'visible_vacancy'       => $request->getInt('visible'),
                        'post_vacancy'          => cleanInput($request->get('post')),
                        'link_vacancy'          => cleanInput($request->get('link')),
                        'id_country'            => $countryId,
                        'id_office'             => $request->getInt('office'),
                        'photo'                 => $vacancy['photo'] ?: $imageName,
                    ]
                );

                jsonResponse('This vacancy has been successfully updated.', 'success');

            break;
            case 'delete':
                checkAdminAjax('moderate_content');

                $id_vacancy = (int) $_POST['vacancy'];

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');

                try {
                    $publicDisk->deleteDirectory(VacancyPathGenerator::defaultUploadPath($id_vacancy));
                } catch (\Throwable $th) {
                    // NOTHIND TO DO
                }

                $this->hiring->delete_vacancy($id_vacancy);
                jsonResponse('This vacancy has been successfully deleted.', 'success');

            break;
            case 'list':
                checkIsLoggedAjaxDT();
                checkPermisionAjaxDT('moderate_content');

                $params = [
                    'per_p'      => (int) $_POST['iDisplayLength'],
                    'start'      => (int) $_POST['iDisplayStart'],
                ];

                $sort_by = flat_dt_ordering($_POST, [
                    'dt_id'         => 'h.id_vacancy',
                    'dt_date'    => 'h.date_vacancy',
                ]);

                if (!empty($sort_by)) {
                    $params['sort_by'] = $sort_by;
                }

                if (isset($_POST['visible'])) {
                    $params['visible'] = (int) $_POST['visible'];
                }

                if (isset($_POST['id_office'])) {
                    $params['id_office'] = (int) $_POST['id_office'];
                }

                if (isset($_POST['id_country'])) {
                    $params['id_country'] = (int) $_POST['id_country'];
                }

                $records = model('hiring')->get_vacancies($params);
                $records_total = model('hiring')->count_vacancies($params);

                $output = [
                    'sEcho'                => intval($_POST['sEcho']),
                    'iTotalRecords'        => $records_total,
                    'iTotalDisplayRecords' => $records_total,
                    'aaData'               => [],
                ];

                if (empty($records)) {
                    jsonDTResponse('', $output, 'success');
                }

                foreach ($records as $record) {
                    $output['aaData'][] = [
                        'dt_id'         => $record['id_vacancy'],
                        'dt_office'     => 0 == $record['id_office'] ? 'N/A' : $record['name_office'],
                        'dt_name'       => $record['post_vacancy'],
                        'dt_link'       => '<a target="_blank" href="' . $record['link_vacancy'] . '">' . $record['link_vacancy'] . '</a>',
                        'dt_country'    => $record['id_country'] > 0 ? '<img width="24" height="24" src="' . getCountryFlag($record['country']) . '"/><br>' . $record['country'] : 'Worldwide',
                        'dt_visible'    => 1 == $record['visible_vacancy'] ? 'Yes' : 'No',
                        'dt_date'       => getDateFormat($record['date_vacancy'], 'Y-m-d H:i:s'),
                        'dt_actions'    => '<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModal" href="' . __SITE_URL . 'hiring/popup_forms/edit_vacancy/' . $record['id_vacancy'] . '" data-title="Edit vacancy" title="Edit vacancy"></a>
                                            <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-message="Are you sure you want to delete this vacancy?" data-callback="removeVacancy" data-vacancy="' . $record['id_vacancy'] . '" title="Delete vacancy"></a>',
                    ];
                }

                jsonResponse('', 'success', $output);

            break;
        }
    }

    public function popup_forms()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'add_vacancy':
                checkAdminAjaxModal('moderate_content');

                /** @var Offices_Model $officesModel */
                $officesModel = model(Offices_Model::class);

                /** @var Country_Model $countryModel */
                $countryModel = model(Country_Model::class);

                views(
                    'admin/hiring/form_view',
                    [
                        'offices'                   => $officesModel->get_offices(),
                        'countries'                 => $countryModel->get_countries(),
                        'fileupload_max_file_size'  => config('fileupload_max_file_size', 10000000),
                    ],
                );

            break;
            case 'edit_vacancy':
                checkAdminAjaxModal('moderate_content');

                /** @var Hiring_Model $hiringModel */
                $hiringModel = model(Hiring_Model::class);

                if (empty($vacancyId = (int) uri()->segment(4)) || empty($hiring = $hiringModel->get_vacancy($vacancyId))) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var Offices_Model $officesModel */
                $officesModel = model(Offices_Model::class);

                /** @var Country_Model $countryModel */
                $countryModel = model(Country_Model::class);

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $hiringUrl =  $publicDisk->url(VacancyPathGenerator::imageUploadPath($hiring['id_vacancy'], $hiring['photo']));

                views(
                    'admin/hiring/form_view',
                    [
                        'fileupload_max_file_size'  => config('fileupload_max_file_size', 10000000),
                        'countries'                 => $countryModel->get_countries(),
                        'offices'                   => $officesModel->get_offices(),
                        'hiring'                    => $hiring,
                        'hiringUrl'                 => $hiringUrl,
                    ],
                );

            break;
            // deleted on 2021.07.05
            /* case 'apply':
                $this->load->model('Hiring_Model', 'hiring');

                $id_vacancy = (int)$this->uri->segment(4);
                $data['vacancy'] = $this->hiring->get_vacancy($id_vacancy);

                if(empty($data['vacancy'])){
                    messageInModal('Error: The vacancy does not exist.');
                }

                if($data['vacancy']['visible_vacancy'] != 1){
                    messageInModal('Error: The vacancy does not exist.');
                }

                $this->view->display('hiring/apply_form_view', $data);
            break; */
        }
    }

    public function ajax_vacancy_upload_photo()
    {
        checkPermisionAjax('manage_content');

        if (empty($files = $_FILES['files'])) {
            jsonResponse('Error: Please select file to upload.');
        }

        if (!empty($vacancyId = (int) uri()->segment(3))) {
            /** @var Hiring_Model $hiringModel */
            $hiringModel = model(Hiring_Model::class);

            if (!empty($hiringModel->get_vacancy($vacancyId)['photo'])) {
                jsonResponse('This vacancy already has a photo. Before uploading a new photo, please remove the old photo first.');
            }
        }

        $imageConfigModule = 'hiring.main';

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

        $tempDisk->createDirectory(
            $uploadDirectory = dirname(FilePathGenerator::uploadedFile($imageName))
        );
        $path = $tempDiskPrefixer->prefixDirectoryPath($uploadDirectory);

        if (count($files['name']) > 1) {
            jsonResponse('You cannot upload more than 1 photo.');
        }

        /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);

        $images = $interventionImageLibrary->image_processing(
            ['tmp_name' => $uploadedFile->getRealPath(), 'name' => pathinfo($imageName, PATHINFO_FILENAME)],
            [
                'use_original_name' => true,
                'destination'   => $path,
                'rules'         => config("img.{$imageConfigModule }.rules"),
                'handlers'      => [
                    'resize'    => config("img.{$imageConfigModule}.resize"),
                ],
            ]
        );

        if (!empty($images['errors'])) {
            jsonResponse($images['errors']);
        }

        jsonResponse(
            '',
            'success',
            [
                'files' => [
                    [
                        'path'  =>  $tempDisk->url(FilePathGenerator::uploadedFile($imageName)),
                        'name'  => $images[0]['new_name'],
                    ],
                ],
            ],
        );
    }

    public function ajax_vacancy_delete_files()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate('systmess_error_should_be_logged'));
        }

        if (empty($_POST['file'])) {
            jsonResponse('Error: File name is not correct.');
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $imageName = request()->request->get('file');

        try {
            $tempDisk->delete(FilePathGenerator::uploadedFile($imageName));
        } catch (\Throwable $th) {
            jsonResponse(translate('validation_images_delete_fail'));
        }

        jsonResponse('', 'success');
    }

    public function ajax_vacancy_delete_db_photo()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate('systmess_error_should_be_logged'));
        }

        if (!have_right('manage_content')) {
            jsonResponse(translate('systmess_error_rights_perform_this_action'));
        }

        if (empty($_POST['file'])) {
            jsonResponse('Error: File name is not correct.');
        }

        $id_vacancy = intval($_POST['file']);
        $this->load->model('Hiring_Model', 'hiring');

        $vacancy = $this->hiring->get_vacancy($id_vacancy);
        if (empty($vacancy)) {
            jsonResponse('Error: Vacancy doeen\'t found.');
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');

        try {
            $publicDisk->delete(VacancyPathGenerator::defaultUploadPath($id_vacancy) . $vacancy['photo']);
        } catch (\Throwable $th) {
            jsonResponse(translate('validation_images_delete_fail'));
        }

        $this->hiring->update_vacancy($id_vacancy, ['photo' => '']);
        jsonResponse('Article image was deleted.', 'success');
    }

    private function _load_main()
    {
        $this->load->model('Category_Model', 'category');
        $this->load->model('Hiring_Model', 'hiring');
    }

    /**
     * @deprecated Alexei Tolmachinski [2022-06-07]
     */
    // private function get_temp_path()
    // {
    //     return 'var/temp/vacancies/' . id_session();
    // }
}
