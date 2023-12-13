<?php

use App\Filesystem\FilePathGenerator;
use App\Filesystem\OurTeamFilesPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use League\Flysystem\PathPrefixer;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Our_team_Controller extends TinyMVC_Controller {

    private FilesystemOperator $storage;

    private FilesystemOperator $tempStorage;

    private PathPrefixer $prefixer;

    private PathPrefixer $tempPrefixer;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        /** @var FilesystemProviderInterface */
        $storageProvider = $container->get(FilesystemProviderInterface::class);

        $this->storage = $storageProvider->storage('public.storage');
        $this->tempStorage = $storageProvider->storage('temp.storage');

        $this->prefixer = $storageProvider->prefixer('public.storage');
        $this->tempPrefixer = $storageProvider->prefixer('temp.storage');
    }

	private function _load_main(){
        $this->load->model('Category_Model', 'category');
		$this->load->model('Our_team_Model', 'our_team');
		$this->load->model('Offices_Model', 'offices');
    }

	function index() {
		$this->administration();
	}

	function administration() {
		checkAdmin('manage_content');

        $this->_load_main();

		$data['upload_folder'] = encriptedFolderName();

        $this->view->assign('title', 'Our team');
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/our_team/index_view');
        $this->view->display('admin/footer_view');
    }

	function ajax_ourteam_administration() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (!have_right('moderate_content'))
            jsonResponse(translate("systmess_error_page_permision"));

        $this->_load_main();
        $our_team_list = $this->our_team->get_persons();
        $our_team_count = $this->our_team->count_persons();

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $our_team_count,
			"iTotalDisplayRecords" => $our_team_count,
			'aaData' => array()
		);

		if(empty($our_team_list))
			jsonResponse('', 'success', $output);

        $upload_folder = $this->uri->segment(3);

        foreach ($our_team_list as $team) {
            $imageUrl = $this->storage->url(OurTeamFilesPathGenerator::defaultPublicImagePath($team['img_person']));
            $output['aaData'][] = [
                'dt_id_team' => $team['id_person'],
                'dt_logo' => '<img class="mw-150 mh-150" src="'. $imageUrl .'" alt="' . $team['name_person'] . '" />',
                'dt_name' => $team['name_person'],
                'dt_post' => $team['post_person'],
                'dt_tel' => $team['tel_person'],
                'dt_email' => $team['email_person'],
                'dt_office' => '<img class="mr-5" width="24" height="24" src="' . getCountryFlag($team['country']) . '" alt="flag"/> <span class="display-ib mt-5">'.$team['name_office'].'</span>',
                'dt_actions' => '<a class="fancyboxValidateModal fancybox.ajax ep-icon ep-icon_pencil" data-title="Edit team" href="our_team/ourteam_popups/edit_team/' .$upload_folder.'/'. $team['id_person'] . '" title="Edit this team"></a>'
						.'<a class="confirm-dialog ep-icon ep-icon_remove txt-red" data-team="'.$team['id_person'].'" data-callback="teamRemove" data-message="Are you sure you want to delete this team?" href="#" title="Delete"></a>'
            ];
        }

        jsonResponse('', 'success', $output);
    }

	public function ourteam_popups() {
        if (!isAjaxRequest())
            headerRedirect();

        $this->_load_main();
        $data['errors'] = array();
        $id_user = $this->session->id;

        $op = $this->uri->segment(3);
        switch ($op) {
			case 'contact_person':
				$data['id_person'] = intval($this->uri->segment(4));

				if(!$this->our_team->exist_person($data['id_person']))
					messageInModal('Error: This contact does not exist.', 'errors');

				$data['person'] = $this->our_team->get_person($data['id_person']);
                $data['person']['imageUrl'] = $this->storage->url(OurTeamFilesPathGenerator::defaultPublicImagePath($data['person']['img_person']));

				$this->view->assign($data);

				$view_name = 'new/about/executive_team/modals/get_person_view';

				$this->view->display($view_name);
			break;
			case 'email':
                if (!have_right('email_this')) {
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

				$id_person = intval($this->uri->segment(4));
                $this->view->assign('id_person', $id_person);
                $this->view->display('new/about/executive_team/modals/email_view');
			break;
            case 'add_team':
                if (!logged_in())
                    messageInModal(translate("systmess_error_should_be_logged"), $type = 'errors');

				$upload_folder = $this->uri->segment(4);
				if(empty($upload_folder))
                    messageInModal('Error: Upload folder is not correct.', $type = 'errors');

				$data['office'] = $this->offices->get_offices();
				$data['upload_folder'] = $upload_folder;

				$upload_folder=checkEncriptedFolder($upload_folder);
				$path = 'temp/our_team/' . id_session().'/' . $upload_folder;
				create_dir($path);

				global $tmvc;
				$data['fileupload_max_file_size'] = $tmvc->my_config['fileupload_max_file_size'];
                $this->view->display('admin/our_team/form_view', $data);
            break;
			case 'edit_team':
                if (!logged_in())
                    messageInModal(translate("systmess_error_should_be_logged"), $type = 'errors');

                $upload_folder = $this->uri->segment(4);
				if(empty($upload_folder))
                    messageInModal('Error: Upload folder is not correct.', $type = 'errors');

                $this->load->model('Country_Model', 'country');
				$data['upload_folder'] = $upload_folder;

				$upload_folder=checkEncriptedFolder($upload_folder);
				$path = 'temp/our_team/' . id_session().'/' . $upload_folder;
				create_dir($path);

				global $tmvc;
				$data['fileupload_max_file_size'] = $tmvc->my_config['fileupload_max_file_size'];
                $id_person = intVal($this->uri->segment(5));
                $data['person_info'] = $this->our_team->get_person($id_person);
                $data['person_info']['imageUrl'] = $this->storage->url(OurTeamFilesPathGenerator::defaultPublicImagePath($data['person_info']['img_person']));
                $data['office'] = $this->offices->get_offices();
                $this->view->display('admin/our_team/form_view', $data);
            break;
        }
    }

	function ajax_our_team_operation(){
        if (!isAjaxRequest()){
            headerRedirect();
		}

        $this->_load_main();
        $op = $this->uri->segment(3);

        switch($op){
			case 'delete_team':
				if (!logged_in()){
					jsonResponse(translate("systmess_error_should_be_logged"));
				}

				if (!have_right('moderate_content'))
					jsonResponse(translate("systmess_error_page_permision"));

				$id_person = intval($_POST['team']);
				if(empty($id_person))
					jsonResponse('The team doesn\'t exist.');

				$person_info = $this->our_team->get_person($id_person);

				if(empty($person_info))
					jsonResponse('The team doesn\'t exist.');

				if(!empty($person_info['img_person'])){

                    try {
                        $this->storage->delete(OurTeamFilesPathGenerator::defaultPublicImagePath($person_info['img_person']));
                    } catch (\Throwable $th) {
                        jsonResponse(translate('systmess_error_image_delete_fail'));
                    }
				}

                if ($this->our_team->delete_person($id_person))
                    jsonResponse('The team was deleted successfully.', 'success');
                else
                    jsonResponse('Error: The team wasn\'t deleted.');
            break;
			case 'create_team':
				if (!logged_in()){
					jsonResponse(translate("systmess_error_should_be_logged"));
				}

				if (!have_right('moderate_content'))
					jsonResponse(translate("systmess_error_page_permision"));

				$validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'name',
						'label' => 'Name',
						'rules' => array('required' => '', 'valid_user_name' => '')
					),
					array(
						'field' => 'post',
						'label' => 'Post',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'tel',
						'label' => 'Phone',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'email',
						'label' => 'Email',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'office',
						'label' => 'Office name',
						'rules' => array('required' => '')
					),
                    array(
                        'field' => 'description',
                        'label' => 'Description',
                        'rules' => array('required' => '')
                    )
				);

				$this->validator->set_rules($validator_rules);

				if (!$validator->validate()) {
					jsonResponse($validator->get_array_errors());
				}

				$insert = array(
					'name_person' => cleanInput($_POST['name']),
					'post_person' => cleanInput($_POST['post']),
					'tel_person' => cleanInput($_POST['tel']),
					'email_person' => cleanInput($_POST['email'], true),
					'id_office' => cleanInput($_POST['office']),
					'description' => cleanInput($_POST['description']),
				);

                $imageName = pathinfo(request()->request->get('image'), PATHINFO_BASENAME);
                $uploadDirectory = dirname(OurTeamFilesPathGenerator::defaultPublicImagePath($imageName));
                $imagePath = $this->tempPrefixer->prefixPath(FilePathGenerator::uploadedFile($imageName));

                /**
                 * @deprecated Refactoring Library
                 */

				$path = $this->prefixer->prefixPath($uploadDirectory);

				if (!$this->storage->fileExists(OurTeamFilesPathGenerator::defaultPublicImagePath($imageName))) {
                    $this->storage->createDirectory($uploadDirectory);
                }

				$conditions = array(
					'images' => [$imagePath],
					'destination' => $path,
					'resize' => '200x200'
				);
				$res = $this->upload->copy_images_new($conditions);

				if (count($res['errors']))
					jsonResponse($res['errors']);

				$insert['img_person'] = $res[0]['new_name'];

				if ($this->our_team->set_person($insert))
					jsonResponse('The team was added successfully.', 'success');
				else
					jsonResponse('Error: The team wasn\'t added.');
			break;
			case 'update_team':
				if (!logged_in()){
					jsonResponse(translate("systmess_error_should_be_logged"));
				}

				if (!have_right('moderate_content'))
					jsonResponse(translate("systmess_error_page_permision"));

				$validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'name',
						'label' => 'Name',
						'rules' => array('required' => '', 'valid_user_name' => '')
					),
					array(
						'field' => 'post',
						'label' => 'Post',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'tel',
						'label' => 'Telephone',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'email',
						'label' => 'Email',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'office',
						'label' => 'Office name',
						'rules' => array('required' => '')
					),
                    array(
                        'field' => 'description',
                        'label' => 'Description',
                        'rules' => array('required' => '')
                    )
				);

				$this->validator->set_rules($validator_rules);

				if (!$validator->validate())
					jsonResponse($validator->get_array_errors());

                /** @var Our_team_Model $ourTeamModel */
                $ourTeamModel = model(Our_team_Model::class);

                if (!empty($ourTeamModel->get_person($_POST['person'])['img_person'])) {
                    jsonResponse('This team already has a photo. Before uploading a new photo, please remove the old photo first.');
                }

				if (!$this->our_team->exist_person(intval($_POST['person'])))
					jsonResponse('The team doesn\'t exist.');

				$update = array(
					'name_person' => cleanInput($_POST['name']),
					'post_person' => cleanInput($_POST['post']),
					'tel_person' => cleanInput($_POST['tel']),
					'email_person' => cleanInput($_POST['email'], true),
					'id_office' => cleanInput($_POST['office']),
                    'description' => cleanInput($_POST['description']),
				);

                $imageName = pathinfo(request()->request->get('image'), PATHINFO_BASENAME);
                $uploadDirectory = dirname(OurTeamFilesPathGenerator::defaultPublicImagePath($imageName));
                $imagePath = $this->tempPrefixer->prefixPath(FilePathGenerator::uploadedFile($imageName));

                /**
                 * @deprecated Refactoring Library
                 */

				if (!empty($imagePath)) {
					$path = $this->prefixer->prefixPath($uploadDirectory);

                    if (!$this->storage->fileExists(OurTeamFilesPathGenerator::defaultPublicImagePath($imageName))) {
                        $this->storage->createDirectory($uploadDirectory);
                    }

					$conditions = array(
						'images' => [$imagePath],
						'destination' => $path,
						'resize' => '200x200'
					);
					$res = $this->upload->copy_images_new($conditions);

					if (count($res['errors']))
						jsonResponse($res['errors']);

					$update['img_person'] = $res[0]['new_name'];
				}

				if ($this->our_team->update_person(intval($_POST['person']), $update))
					jsonResponse('The information about the team was updated succefully.', 'success');
				else
					jsonResponse('Error: The information about the team wasn\'t updated.');
			break;
        }
    }

	function ajax_ourteam_upload_photo() {
        checkPermisionAjax('manage_content');

        /** @var null|UploadedFile */
        $uploadedFile = ((array) request()->files->get('files', []))[0] ?? null;
        if (empty($uploadedFile) || !$uploadedFile->isValid()) {
            jsonResponse('Please select file to upload.');
        }

        if (!empty($personId = (int) uri()->segment(4))) {
            /** @var Our_team_Model $ourTeamModel */
            $ourTeamModel = model(Our_team_Model::class);

            if (!empty($ourTeamModel->get_person($personId)['img_person'])) {
                jsonResponse('This team already has a photo. Before uploading a new photo, please remove the old photo first.');
            }
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $tempDiskPrefixer = $storageProvider->prefixer('temp.storage');
        $imageName = sprintf('%s.%s', \bin2hex(\random_bytes(16)), 'jpg');

        $tempDisk->createDirectory(
            $uploadDirectory = dirname(FilePathGenerator::uploadedFile($imageName))
        );

         /** @var TinyMVC_Library_Image_intervention $interventionImageLibrary */
        $interventionImageLibrary = library(TinyMVC_Library_Image_intervention::class);
        $res = $interventionImageLibrary->image_processing(
            ['tmp_name' => $uploadedFile->getRealPath(), 'name' => pathinfo($imageName, PATHINFO_FILENAME)],
            [
                'use_original_name' => true,
                'destination'       => $tempDiskPrefixer->prefixDirectoryPath($uploadDirectory),
                'rules' => [
                    'size' => config('fileupload_max_file_size'),
				    'min_height' => 200,
				    'min_width'  => 200,
                ],
                'handlers'          => [
                    'resize'        => [
                        'width'     => 200,
                        'height'    => 'R',
                    ],
                ],
            ]
        );

        if (count($res['errors'])) {
            $result['result'] = implode(', ', $res['errors']);
            $result['resultcode'] = 'failed';
			jsonResponse($res['errors']);
        } else {
			foreach($res as $item){
				$result['files'][] = array('path' => $this->tempStorage->url(FilePathGenerator::uploadedFile($item['new_name'])),'name' => $item['new_name']);
			}
			jsonResponse('', 'success', $result);
        }
    }

	function ajax_ourteam_delete_photo() {

        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (empty($name = request()->request->get('file')))
            jsonResponse('Error: File name is not correct.');

		$upload_folder = $this->uri->segment(3);
		if(!($upload_folder=checkEncriptedFolder($upload_folder))){
			jsonResponse('Error: File upload path is not correct.');
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $path = FilePathGenerator::uploadedFile($name);

        if (!$tempDisk->fileExists($path))
            jsonResponse('Error: Upload path is not correct.');
        try {
            $tempDisk->delete($path);
        } catch (\Throwable $th) {
            jsonResponse(translate('systmess_error_image_delete_fail'));
        }

		jsonResponse('','success');

    }

	function ajax_ourteam_delete_db_photo() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }
        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

		if (!have_right('manage_content'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));

		if (empty(request()->request->get('file')))
            jsonResponse('Error: File name is not correct.');

        $id_person = intVal(request()->request->get('file'));
        $this->load->model('Our_team_Model', 'our_team');

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $person_info = $this->our_team->get_person($id_person);

        try {
            $publicDisk->delete(OurTeamFilesPathGenerator::defaultPublicImagePath($person_info['img_person']));
        } catch (\Throwable $th) {
            jsonResponse(translate('systmess_error_image_delete_fail'));
        }

        $this->our_team->update_person($id_person, array('img_person' => ''));
        jsonResponse('Team image was deleted.', 'success');
    }

}
