<?php

use App\Filesystem\FilePathGenerator;
use App\Filesystem\PartnersUploadsPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Partners_Controller extends TinyMVC_Controller {

    private FilesystemOperator $storage;
    private FilesystemOperator $tempStorage;

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

    private function _load_main() {
        $this->load->model('Category_Model', 'category');
        $this->load->model('Partners_Model', 'partners');
    }

    function administration() {
        checkAdmin('manage_content');

        $this->_load_main(); // load main models
        $this->load->model('Country_Model', 'country');

        $data['partners_list'] = $this->partners->get_partners();
        $data['country'] = $this->country->get_countries();
		$data['upload_folder'] = encriptedFolderName();

        $this->view->assign($data);
        $this->view->assign('title', 'Partners');
        $this->view->display('admin/header_view');

        $this->view->display('admin/partners/index_view');
        $this->view->display('admin/footer_view');
    }

	public function partner_popups() {
        if (!isAjaxRequest())
            headerRedirect();

        $this->_load_main();
        $data['errors'] = array();

        $op = $this->uri->segment(3);
        switch ($op) {
            case 'add_partner':
                if (!logged_in())
                    messageInModal(translate("systmess_error_should_be_logged"), $type = 'errors');

			 	$this->load->model('Country_Model', 'country');

				global $tmvc;
				$data['fileupload_max_file_size'] = $tmvc->my_config['fileupload_max_file_size'];
                $data['country'] = $this->country->fetch_port_country();
                $this->view->display('admin/partners/form_view', $data);
            break;
			case 'edit_partner':
                if (!logged_in())
                    messageInModal(translate("systmess_error_should_be_logged"), $type = 'errors');

                $this->load->model('Country_Model', 'country');

				global $tmvc;
				$data['fileupload_max_file_size'] = $tmvc->my_config['fileupload_max_file_size'];
                $id_partner = intVal($this->uri->segment(4));
                $data['partner'] = $this->partners->get_partner($id_partner);
                $data['country'] = $this->country->fetch_port_country();
                $data['partner']['imageUrl'] =  $this->storage->url(PartnersUploadsPathGenerator::publicPromoBannerPath($data['partner']['img_partner']));

                $this->view->display('admin/partners/form_view', $data);
            break;
        }
    }

	function ajax_partners_administration() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (!have_right('moderate_content'))
            jsonResponse(translate("systmess_error_page_permision"));

        $this->_load_main();
        $partners_list = $this->partners->get_partners();
		$partners_count = $this->partners->count_partners();

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $partners_count,
			"iTotalDisplayRecords" => $partners_count,
			'aaData' => array()
		);

		if(empty($partners_list))
			jsonResponse('', 'success', $output);

        foreach ($partners_list as $partner) {
            $publicBaseUrl = $this->storage->url(PartnersUploadsPathGenerator::publicPromoBannerPath($partner['img_partner']));
	    	$visible_partner = $on_home = '<span class="ep-icon ep-icon_remove txt-red"/>';

            if ($partner['visible_partner'] == 1)
                $visible_partner = '<span class="ep-icon ep-icon_ok txt-green"/>';

            if ($partner['on_home'] == 1)
                $on_home = '<span class="ep-icon ep-icon_ok txt-green"/>';

            $output['aaData'][] = array(
                'dt_id_partner' => $partner['id_partner'],
                'dt_logo' => '<img class="mw-150 mh-150" src="' . $publicBaseUrl . '" alt="' . $partner['name_partner'] . '" />',
                'dt_name' => '<a href="' . $partner['website_partner'] . '" target="_blank">' . $partner['name_partner'] . '</a>',
                'dt_country' => '<img width="24" height="24" src="' . getCountryFlag($partner['country']) . '" title="' . $partner['country'] . '" alt="' . $partner['country'] . '"/>',
            	'dt_visible' =>  $visible_partner,
                'dt_home' => $on_home,
				'dt_actions' =>
					'<a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_pencil" data-title="Edit partner" href="partners/partner_popups/edit_partner/'. $partner['id_partner'] . '" title="Edit this partner"></a>'
					.'<a class="confirm-dialog ep-icon ep-icon_remove txt-red" data-partner="'.$partner['id_partner'].'" data-callback="partnerRemove" data-message="Are you sure you want to delete this partner?" href="#" title="Delete"></a>'
            );
        }

        jsonResponse('', 'success', $output);
    }

    function ajax_partners_operation() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (!have_right('moderate_content'))
            jsonResponse(translate("systmess_error_page_permision"));

        $this->_load_main();
        $op = $this->uri->segment(3);

		switch ($op) {
            case 'delete_partner':
                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $publicDiskPrefixer = $storageProvider->prefixer('public.storage');
				$id_partner = intval($_POST['partner']);
				if(empty($id_partner))
					jsonResponse('The partner doesn\'t exist.');

				$partner_info = $this->partners->get_partner($id_partner);

				if(empty($partner_info))
					jsonResponse('The partner doesn\'t exist.');

				if(!empty($partner_info['img_partner'])){
                    try {
                       $publicDisk->delete(PartnersUploadsPathGenerator::publicPromoBannerPath($partner_info['img_partner']));
                    } catch (\Throwable $th) {
                        jsonResponse(translate('validation_images_delete_fail'));
                    }
				}

                if ($this->partners->delete_partner($id_partner))
                    jsonResponse('The partner was deleted successfully.', 'success');
                else
                    jsonResponse('Error: The partner wasn\'t deleted.');
            break;
			case 'create_partner':
				$validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'country',
						'label' => 'Country partners',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'name',
						'label' => 'Name partners',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'link',
						'label' => 'Website partners',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$validator->validate()) {
					jsonResponse($validator->get_array_errors());
				}

				if (empty($_FILES['croppedImage'])) {
					jsonResponse("Image is required");
				}

				$insert = array(
					'id_country' => intVal($_POST['country']),
					'name_partner' => cleanInput($_POST['name']),
					'website_partner' => cleanInput($_POST['link']),
					'description_partner' => cleanInput($_POST['description'])
				);

				if (isset($_POST['visible']) && !empty($_POST['visible']))
					$insert['visible_partner'] = 1;

				if (isset($_POST['on_home']) && !empty($_POST['on_home']))
					$insert['on_home'] = 1;

                /**
                 * @todo Refactoring Library [2022-06-02]
                 */
                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $publicDiskPrefixer = $storageProvider->prefixer('public.storage');
                $path = $publicDiskPrefixer->prefixDirectoryPath(PartnersUploadsPathGenerator::publicPromoBannerDirectory());

                $publicDisk->createDirectory(PartnersUploadsPathGenerator::publicPromoBannerDirectory());

				global $tmvc;
				$conditions = array(
					'files' => $_FILES['croppedImage'],
					'destination' => $path,
					'rules' => array(
						'size' => $tmvc->my_config['fileupload_max_file_size'],
						'min_height' => 170,
						'min_width' => 170
					)
				);
				$res = $this->upload->upload_images_new($conditions);

				if (!empty($res['errors']))
					jsonResponse($res['errors']);

				$insert['img_partner'] = $res[0]['new_name'];

				if ($this->partners->set_partner($insert))
					jsonResponse('The partner was added successfully.', 'success');
				else
					jsonResponse('Error: The partner wasn\'t added.');
			break;
			case 'update_partner':
				$validator = $this->validator;

				$validator_rules = array(
					array(
						'field' => 'country',
						'label' => 'Country partners',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'name',
						'label' => 'Name partners',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'link',
						'label' => 'Website partners',
						'rules' => array('required' => '')
					),
				);

				$this->validator->set_rules($validator_rules);

				if (!$validator->validate())
					jsonResponse($validator->get_array_errors());

				$partner = $this->partners->get_partner((int) $_POST['id']);
				if (empty($partner))
					jsonResponse('The partner doesn\'t exist.');

				$update = array(
					'id_country' => intVal($_POST['country']),
					'name_partner' => cleanInput($_POST['name']),
					'website_partner' => cleanInput($_POST['link']),
					'description_partner' => cleanInput($_POST['description']),
				);

				if (!empty($_FILES['croppedImage'])) {
					if(!empty($partner['img_partner'])){
						jsonResponse('Error: You cannot upload more than 1 photo(s).');
					}

                    /**
                    * @todo Refactoring Library [2022-06-02]
                    */
                    /** @var FilesystemProviderInterface */
                    $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                    $publicDiskPrefixer = $storageProvider->prefixer('public.storage');
                    $path = $publicDiskPrefixer->prefixDirectoryPath(PartnersUploadsPathGenerator::publicPromoBannerDirectory());
					// Count number of files in this folder, to prevent upload more files than photo limit

					global $tmvc;
					$conditions = array(
						'files' => $_FILES['croppedImage'],
						'destination' => $path,
						'rules' => array(
							'size' => $tmvc->my_config['fileupload_max_file_size'],
							'min_height' => 170,
							'min_width' => 170
						)
					);
					$res = $this->upload->upload_images_new($conditions);

					if (!empty($res['errors'])) {
						jsonResponse($res['errors']);
					}
					$update['img_partner'] = $res[0]['new_name'];
				}

				$update['visible_partner'] = isset($_POST['visible']) ? 1 : 0;
				$update['on_home'] = isset($_POST['on_home']) ? 1 : 0;


				if ($this->partners->update_partner(intval($_POST['id']), $update))
					jsonResponse('The information about the partner was updated succefully.', 'success');
				else
					jsonResponse('Error: The information about the partner wasn\'t updated.');
			break;
        }
    }

	function ajax_partners_upload_photo() {
        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"), 'error');

        if (!have_right('manage_content'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));

        if (empty($_FILES['files'])) {
			jsonResponse('Error: Please select file to upload.');
        }

        $id_partner = intVal($this->uri->segment(4));
        if($id_partner){
            $this->load->model('Partners_Model', 'partners');
            $partner_info = $this->partners->get_partner($id_partner);
            if(!empty($partner_info['img_partner'])){
                jsonResponse('This partner already has a photo. Before uploading a new photo, please remove the old photo first.');
            }
        }
        /** @var null|UploadedFile */
        $uploadedFile = ((array) request()->files->get('files', []))[0] ?? null;
        if (empty($uploadedFile) || !$uploadedFile->isValid()) {
            jsonResponse('Please select file to upload.');
        }

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $tempDisk = $storageProvider->storage('temp.storage');
        $tempDiskPrefixer = $storageProvider->prefixer('temp.storage');
        $imageName = $uploadedFile->getClientOriginalName();

        $tempDisk->createDirectory(
            $path = dirname(FilePathGenerator::uploadedFile($imageName))
        );

        /**
        * @todo Refactoring Library [2022-06-02]
        */
		global $tmvc;
		$conditions = array(
			'files' => $_FILES['files'],
			'destination' => $tempDiskPrefixer->prefixDirectoryPath($path),
			'resize' => 'Rx200',
			'rules' => array(
				'size' => $tmvc->my_config['fileupload_max_file_size'],
				'min_height' => 105,
				'min_width' => 200
			)
		);
		$res = $this->upload->upload_images_new($conditions);

        if (!empty($res['errors'])) {
			jsonResponse($res['errors']);
        } else {
			foreach($res as $item){
				$result['files'][] = array('path'=> $tempDisk->url(FilePathGenerator::uploadedFile($imageName)),'name' => $item['new_name']);
			}
			jsonResponse('', 'success', $result);
        }
    }

	function ajax_partners_delete_photo() {

        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (empty($_POST['file']))
            jsonResponse('Error: File name is not correct.');

		jsonResponse('','success');

    }

	function ajax_partners_delete_db_photo() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }
        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

		if (!have_right('manage_content'))
            jsonResponse(translate("systmess_error_rights_perform_this_action"));

		if (empty($_POST['file']))
            jsonResponse('Error: File name is not correct.');

        /** @var FilesystemProviderInterface */
        $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $publicDisk = $storageProvider->storage('public.storage');
        $id_partner = intVal($_POST['file']);
		$this->load->model('Partners_Model', 'partners');

        $partner = $this->partners->get_partner($id_partner);

        try {
            $publicDisk->delete(PartnersUploadsPathGenerator::publicPromoBannerPath($partner['img_partner']));
        } catch (\Throwable $th) {
            jsonResponse(translate('validation_images_delete_fail'));
        }

        $this->partners->update_partner($id_partner, array('img_partner' => ''));
        jsonResponse('Partner image was deleted.', 'success');
    }
}

?>
