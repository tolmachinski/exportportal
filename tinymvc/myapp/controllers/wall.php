<?php

/**
 * Controller Wall
 *
 * @property \Country_Model             $countries
 * @property \Company_Model             $companies
 * @property \Seller_Wall_Model         $seller_wall
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 * @property \User_Model                $users
 * @property \UserGroup_Model           $groups
 * @property \TinyMVC_Library_Wall      $wall
 *
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Wall_Controller extends TinyMVC_Controller
{
    /**
     * Controller Wall index page
     */
    public function index()
    {
        show_404();
    }

    public function ajax_operations() {
        checkIsAjax();

        $this->load->model('User_Model', 'users');
        $this->load->model('Company_Model', 'companies');

        switch (cleanInput($this->uri->segment(3))) {
            case 'load':
                $this->show_wall();
            break;
            default:
                show_404();
            break;
		}
    }

    private function show_wall()
    {
        $this->load->model('UserGroup_Model', 'groups');
        $this->load->model('Country_Model', 'countries');
        $this->load->model('Seller_Wall_Model', 'seller_wall');
        $this->load->library('Wall', 'wall');

        $company_id = (int)$this->uri->segment(4);
        if(
            empty($company_id) ||
            empty($data['company'] = $this->companies->get_company(array('id_company' => $company_id)))
        ) {
            show_404();
        }

        if ( ! isset($_GET['offset'])) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $offset = (int) $_GET['offset'];
        $user_id = (int) $data['company']['id_user'];

        $is_blocked = (int) $data['company']['blocked'] > 0;
        $is_visible = (bool) filter_var($data['company']['visible_company'], FILTER_VALIDATE_BOOLEAN);
        if( ! (is_privileged('user', (int) $data['company']['id_user']) || have_right('moderate_content') || $is_visible && !$is_blocked)){
			show_blocked();
        }

        // $is_branch = (int) $company['parent_company'] !== 0; // not used
        jsonResponse(null, 'success', array(
            'hasMore'  => $this->wall->hasItemsBeyond($user_id, (int) config('seller_wall_items_limit', 10), $offset),
            'items'    => $this->wall->getItemsViews($user_id, $data, (int) config('seller_wall_items_limit', 10), $offset, false),
        ));
    }
}

/* End of file wall.php */
/* Location: /tinymvc/myapp/controllers/wall.php */
