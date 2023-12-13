<?php

use App\Filesystem\VideoThumbsPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Video_Controller extends TinyMVC_Controller {

    private function _load_main() {
        $this->load->model('Category_Model', 'category');
        $this->load->model('Video_Model', 'video');
    }

    function index() {
        checkAdmin('manage_content');

        $this->_load_main();
        $data['videos_list'] = $this->video->getVideos();
        $this->view->assign('title', 'Videos');
        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/video/index_view');
        $this->view->display('admin/footer_view');
    }

	function popup_forms(){
		if (!isAjaxRequest())
			headerRedirect();

		if (!logged_in())
			messageInModal(translate("systmess_error_should_be_logged"));

		$op = $this->uri->segment(3);

        switch($op){
			case 'edit_video':
				$this->load->model('Video_Model', 'video');
				$id_video = intval($this->uri->segment(4));
				$data['video_info'] = $this->video->getVideo($id_video);
                $this->view->display('admin/video/form_view', $data);
			break;
			case 'add_video':
				$this->view->display('admin/video/form_view');
			break;
			case 'view_video':
				$data = array(
					'type_video' => cleanInput($this->uri->segment(4)),
					'id_video' => cleanInput($this->uri->segment(5))
				);
				if(!$data['type_video'])
					messageInModal("Error: Video type is empty. Please select video type.");

				if(!$data['id_video'])
					messageInModal("Error: Video ID is empty. Please select a video.");

                $this->view->display('admin/video/view_video', $data);
			break;
		}
	}

    function ajax_video_operation() {
        if (!isAjaxRequest())
            headerRedirect();

        if (!logged_in())
            jsonResponse(translate("systmess_error_should_be_logged"));

        if (!have_right('moderate_content'))
            jsonResponse(translate("systmess_error_page_permision"));

        $this->_load_main();
        $op = $this->uri->segment(3);

        switch ($op) {
            case 'delete_video':
        		$id_video = intval($_POST['video']);
                $video = $this->video->getVideo($id_video);

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $filePath = VideoThumbsPathGenerator::publicVideoImageUploadPath($video['link_img']);

                try {
                    $publicDisk->delete($filePath);
                } catch (\Throwable $th) {
                    jsonResponse(translate('validation_images_delete_fail'));
                }

                if ($this->video->deleteVideo($id_video))
                    jsonResponse('Video has been successfully deleted.', 'success');
                else
                    jsonResponse('Error: This video doesn\'t exist.');
            break;
			case "create_video":
                $validator = $this->validator;

                $validator_rules = array(
                    array(
                        'field' => 'title_video',
                        'label' => 'Title',
                        'rules' => array('required' => '')
                    ),
					array(
                        'field' => 'short_name',
                        'label' => 'Short name',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'link_video',
                        'label' => 'Link video',
                        'rules' => array('required' => '', 'valid_url' => '')
                    ),
                    array(
                        'field' => 'src_video',
                        'label' => 'Source video',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);

                if (!$validator->validate()){
                    jsonResponse($validator->get_array_errors());
                }

				$this->load->library('videothumb');
                $video = $this->videothumb->process($_POST['link_video']);

                if (isset($video['error'])){
                    jsonResponse($video['error']);
                }

                $this->load->library('Cleanhtml', 'clean');
                $this->clean->defaultTextarea();
                $this->clean->addAdditionalTags('<a>');

                $title_video = cleanInput($_POST['title_video']);
                $short_name = cleanInput($_POST['short_name']);
                $description_video = $this->clean->sanitize($_POST['description_video']);
                $video_source = cleanInput($_POST['src_video']);

                $insert = array(
                    'title_video' => $title_video,
                    'short_name' => $short_name,
                    'description_video' => $description_video
                );

                $video_link = $this->videothumb->getVID(cleanInput($_POST['link_video']));

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDiskPrefixer = $storageProvider->prefixer('public.storage');

                $insert['link_video'] = $video['v_id'];
                $insert['src_video'] = $video_link['type'];
                if (!$id_video = $this->video->setVideo($insert)) {
                    jsonResponse('Error: Video wasn\'t  inserted.');
                }

                // @TODO this place is disabled due to issue with overriden assets
                // $insert['id_video'] = $id_video;
                // if (!empty($video['image'])) {
                //     $files[] = $video['image'];
				// 	$conditions = array(
                //         'images' => $files,
				// 		'destination' => $publicDiskPrefixer->prefixPath(VideoThumbsPathGenerator::publicUploadPath($id_video)),
				// 		'resize' => '200x150'
				// 	);
				// 	$res = $this->upload->copy_images_new($conditions);

                //     if(!count($res['errors'])) {
                //         $insert['link_img'] = pathinfo($res[0]['new_name'], PATHINFO_FILENAME);
                //     }
                // }

                jsonResponse('Video was successfully inserted.', 'success', array_merge($insert, ['id_video' => $id_video]));
            break;
			case "update_video":
                $validator = $this->validator;

                $validator_rules = array(
                    array(
                        'field' => 'title_video',
                        'label' => 'Title',
                        'rules' => array('required' => '')
                    ),
					array(
                        'field' => 'short_name',
                        'label' => 'Short name',
                        'rules' => array('required' => '')
                    ),
                    array(
                        'field' => 'link_video',
                        'label' => 'Link video',
                        'rules' => array('required' => '', 'valid_url' => '')
                    ),
                    array(
                        'field' => 'src_video',
                        'label' => 'Source video',
                        'rules' => array('required' => '')
                    )
                );

                $this->validator->set_rules($validator_rules);

                if (!$validator->validate()){
                    jsonResponse($validator->get_array_errors());
                }

				$this->load->library('videothumb');
				$id_video = intval($_POST['id_video']);
                if (!$this->video->existVideo($id_video)){
                    jsonResponse('Error: This video doesn\'t exist ' . $this->video->existVideo($id_video));
                }

                $this->load->library('Cleanhtml', 'clean');
                $this->clean->defaultTextarea();
                $this->clean->addAdditionalTags('<a>');

                $title_video = cleanInput($_POST['title_video']);
                $short_name = cleanInput($_POST['short_name']);
                $description_video = $this->clean->sanitize($_POST['description_video']);
                $video_source = cleanInput($_POST['src_video']);

                $update = array(
                    'title_video' => $title_video,
                    'short_name' => $short_name,
                    'description_video' => $description_video
                );

                $video_link = $this->videothumb->getVID($_POST['link_video']);

				$video_info = $this->video->getVideo($id_video);

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');
                $publicDiskPrefixer = $storageProvider->prefixer('public.storage');

                $filePath = VideoThumbsPathGenerator::publicVideoImageUploadPath($video_info['link_img']);

                if ($video_link['v_id'] != $video_info['link_video']) {
                    $video = $this->videothumb->process($_POST['link_video']);

                    if (isset($video['error']))
                        jsonResponse($video['error']);
                    try {
                        $publicDisk->delete($filePath);
                    } catch (\Throwable $th) {
                        jsonResponse(translate('validation_images_delete_fail'));
                    }

                    $update['link_video'] = $video['v_id'];
                    $update['src_video'] = $video_link['type'];
                }

                // @TODO this place is disabled due to issue with overriden assets
                // if (!empty($video['image'])) {
                //     $files[] = $video['image'];
				// 	$conditions = array(
				// 		'images' => $files,
				// 		'destination' => $publicDiskPrefixer->prefixPath(VideoThumbsPathGenerator::publicUploadPath($id_video)),
				// 		'resize' => '200x150'
				// 	);
				// 	$res = $this->upload->copy_images_new($conditions);
                //  if(!count($res['errors'])) {
                //      $update['link_img'] = pathinfo($res[0]['new_name'], PATHINFO_FILENAME);
                //  }
                // }

                if (!$this->video->updateVideo($id_video, $update)) {
                    jsonResponse('Error: The video has not been updated.');
                }

                jsonResponse('The video was successfully updated', 'success', array_merge($update, ['id_video' => $id_video]));
            break;
        }
    }
}
