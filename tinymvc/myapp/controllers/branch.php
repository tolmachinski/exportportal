<?php

use App\Common\Buttons\ChatButton;
use App\Filesystem\CompanyVideoFilePathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Branch_Controller extends TinyMVC_Controller {

	/* load main models*/
	private function _load_main(){
		$this->load->model('Category_Model', 'category');
		$this->load->model('Branch_Model', 'branch');
		$this->load->model("Company_model", 'company');
		$this->load->model('User_Model', 'user');
	}

	public function detail() {
		$this->_load_main(); // load main models
		$this->load->model('Company_Model', 'company');
		$this->load->model('UserGroup_Model', 'groups');
		$this->load->model('Country_Model', 'country');
		$this->load->model('B2b_Model', 'b2b');
		$this->load->model('UserGroup_Model', 'groups');
		$this->load->model("Followers_model", 'followers');

		$id_company = id_from_link($this->uri->segment(3));
		$main['company'] = $this->company->get_company(array('id_company' => $id_company, 'type_company'=>'branch'));
		if(empty($main['company'])){
			show_404();
		}

		$is_blocked = (int) $main['company']['blocked'] > 0;
		$is_visible = filter_var((int) $main['company']['visible_company'], FILTER_VALIDATE_BOOLEAN);
		if(
			($is_blocked || !$is_visible) && !(
				is_privileged('user', (int) $main['company']['id_user']) ||
				have_right('moderate_content')
			)
		){
			show_blocked();
		}

		if(!empty($main['company']['index_name']))
			headerRedirect(__SITE_URL . $main['company']['index_name']);

		$data = null;


		if(__CACHE_ENABLE){
			$this->load->model('Cache_Config_Model', 'cache_config');

			$c_config = $this->cache_config->get_cache_options('branch');

			if(!empty($c_config) && $c_config['enable']){
				$this->load->library('Cache', 'cache');
				$this->cache->init(array('securityKey'	=> $c_config['folder']));
				$data = $this->cache->get('branch'. $id_company);
			}
		}

		if($data == null) {
			$data = $main;
			$full_country_info = $this->country->get_country_city($data['company']['id_country'],$data['company']['id_city']);
			$data['company_main'] = $this->company->get_company(array('id_company' => $main['company']['parent_company']));

			$data['company']['country'] = $full_country_info['country'];
			$data['company']['city'] = $full_country_info['city'];
			$partners_cond = array(
				'companies_list' => $data['company']['id_company'],
				'group_by' =>true
			);
			$data['partners'] = $this->b2b->get_partners($partners_cond);

			$this->breadcrumbs[] = array(
				'link'	=> __SITE_URL.'branch/'.strForUrl($data['company']['name_company']).'-'.$data['company']['id_company'],
				'title'	=> $data['company']['name_company']
			);

			$id_user = $data['company']['id_user'];

			$data['user_main'] = $this->user->getUser($id_user);

			if (logged_in()) {
				$userMainChatBtn = new ChatButton(['recipient' => $data['user_main']['idu'], 'recipientStatus' => $data['user_main']['status']]);
				$data['user_main']['btnChat'] = $userMainChatBtn->button();
			}

			$data['user_rights'] = $this->groups->get_users_right($id_user);
			$branch_images = $this->branch->get_branch_images(array('id_company'=>$data['company']['id_company']));
			if(!empty($branch_images)){
				foreach($branch_images as $key => $image){
					$images[$key]['id_photo'] = $image['id_photo'];
					$images[$key]['photo_name'] = $image['path_photo'];
					$images[$key]['thumbs'] = unserialize($image['thumbs_photo']);
				}
				$data['company']['pictures'] = $images;
			}

			if (logged_in()) {
				$chatBtn = new ChatButton(['recipient' => $data['company']['id_user'], 'recipientStatus' => $data['company']['status']]);
				$data['company']['btnChat'] = $chatBtn->button();
			}

			// Google Map configuration for event
			// $thumbs = [];
			// $image = $thumbs['50x50'];

			// $marker = array(
			// 	array(
			// 		'lat' => $data['company']['latitude'],
			// 		'lng' => $data['company']['longitude'],
			// 		'type' => 'coords',
			// 		'content' => $this->view->fetch('user/seller/gm_marker_content_view', array('company' => $data['company'], 'image' =>$image)),
			// 		'title' => $data['company']['name_company']
			// 	)
			// );

			// $data['myMapConfig'] = array(
			// 	'zoom' => 13,
			// 	'markers' => json_encode($marker, JSON_FORCE_OBJECT),
			// 	'centerLat' => $data['company']['latitude'] + 0.007,
			// 	'centerLng' => $data['company']['longitude'] + 0.012
			// );
			// end google Map configuration for branch

			//seo
			$data['meta_params']['[COMPANY_NAME]'] = $data['company']['name_company'];
			$data['meta_params']['[GROUP_NAME]'] = $data['user_main']['gr_name'];
			$data['meta_params']['[USER_NAME]'] = $data['user_main']['fname'].' '.$data['user_main']['lname'];
			$data['meta_params']['[TYPE_NAME]'] = $data['company']['name_type'];

			$user_group = $this->groups->getGroups(array('fields' => 'gr_name, idgroup, stamp_pic'));
			foreach($user_group as $group){
				$data['user_group'][$group['idgroup']] = array(
					'gr_name' => $group['gr_name'],
					'stamp_pic' => $group['stamp_pic']);
			}
			$data['breadcrumbs'] = $this->breadcrumbs;

			if(__CACHE_ENABLE && $c_config['enable']){
				$this->cache->set('branch'. $id_company , $data, $c_config['cache_time']);
			}

		}
		$followed = i_follow();
		if(in_array($id_user,$followed)){
			$data['iFollow'] = true;
		}
        /** @var FilesystemProviderInterface  $storageProvider */
        $provider = $this->getContainer()->get(FilesystemProviderInterface::class);
        $storage = $provider->storage('public.storage');
        $data['videoImagePath'] = $storage->url(CompanyVideoFilePathGenerator::videoPath($data['company']['id_company'], $data['company']['video_company_image']));

        $data['sidebar_left_content'] = 'new/directory/branch/sidebar_view';
        $data['main_content'] = 'new/directory/branch/index_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
	}
}
