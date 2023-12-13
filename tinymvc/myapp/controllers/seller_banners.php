<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Seller_Banners_Controller extends TinyMVC_Controller {

    private $breadcrumbs = array();

    /** deleted on 2021.07.05 */
    /* public function my() {
        show_404();
        if (!logged_in()) {
            jsonDTResponse(translate("systmess_error_should_be_logged"));
        }

        if (!have_right('have_company')) {
            jsonDTResponse(translate("systmess_error_rights_perform_this_action"));
        }


        $id_user = id_session();
        $this->breadcrumbs[] = array(
            'link'	=> __SITE_URL . 'usr/' . strForUrl(user_name_session()) . '-' . $id_user,
            'title'	=> user_name_session()
        );
        $this->breadcrumbs[] = array(
            'link'	=> __SITE_URL . 'seller_banners/my',
            'title'	=> 'Banners'
        );

        $data['breadcrumbs'] = $this->breadcrumbs;

        $this->view->assign($data);
        $this->view->display('dashboard/header_view');
        $this->view->display('admin/seller_banners/index_view');
        $this->view->display('dashboard/footer_view');
    } */

    function remove_banner() {
        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        if (!have_right('have_company')) {
            jsonResponse(translate("systmess_error_rights_perform_this_action"));
        }

        if (empty($_POST['id'])) {
            jsonResponse('Please provide banner id');
        }


        $this->load->model('Seller_Banners_Model', 'seller_banners');
        $id_user = id_session();

        $this->seller_banners->remove($id_user, intval($_POST['id']));

        jsonResponse('Banner was removed', 'success');
    }


    function form_banner_popup() {
        if (!logged_in()) {
            jsonDTResponse(translate("systmess_error_should_be_logged"));
        }

        if (!have_right('have_company')) {
            jsonDTResponse(translate("systmess_error_rights_perform_this_action"));
        }



        if (!empty($_GET['id'])) {
            $this->load->model('Seller_Banners_Model', 'seller_banners');
            $id_user = id_session();
            $banner = $this->seller_banners->get_seller_banner(intval($_GET['id']), $id_user);
        } else {
            $banner = array(
                'link' => '',
                'image' => '',
                'page' => ''
            );
        }


        $this->view->assign(array(
            'banner' => $banner,
            'path' => $this->seller_banners->path_to_images
        ));
        $this->view->display('admin/seller_banners/form_banner_popup');
    }


    function form_banner_save() {
        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"));
        }

        if (!have_right('have_company')) {
            jsonResponse(translate("systmess_error_rights_perform_this_action"));
        }


        $this->validator->set_rules(array(
            array(
                'field' => 'link',
                'label' => 'Link',
                'rules' => array('required' => '', 'valid_url' => '')
            ),
            array(
                'field' => 'image',
                'label' => 'Image',
                'rules' => array('required' => '')
            ),
            array(
                'field' => 'page',
                'label' => 'Page',
                'rules' => array('required' => '')
            )
        ));

        $domain = str_replace(array('https://', 'http://', 'www.', '/'), '', __SITE_URL);
        if (preg_match('/^((https?:\/\/)?(www\.)?)' . $domain . '/', $_POST['link']) === false) {
            jsonResponse('Only Export Portal links are allowed');
        }

        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }


        $this->load->model('Seller_Banners_Model', 'seller_banners');
        $id_user = id_session();
        $id = empty($_POST['id']) ? null : intval($_POST['id']);
        $_POST['id_user'] = $id_user;
        $target_path = $this->seller_banners->path_to_images;
        $temp_path = $this->seller_banners->path_to_temp;

        if ($id) {
            $banner = $this->seller_banners->get_seller_banner($id, $id_user);
            if (empty($banner)) {
                jsonResponse('Banner not found');
            }

            $this->seller_banners->update($_POST, $id_user, $id);

            if ($banner['image'] != $_POST['image']) {
                @unlink("$target_path/{$banner['image']}");

                if (!is_file("$temp_path/{$_POST['image']}")) {
                    jsonResponse('Missing uploaded image');
                }

                rename("$temp_path/{$_POST['image']}", "$target_path/{$_POST['image']}");
            }
        } else {
            $this->seller_banners->insert($_POST);

            if (!is_file("$temp_path/{$_POST['image']}")) {
                jsonResponse('Missing uploaded image');
            }

            rename("$temp_path/{$_POST['image']}", "$target_path/{$_POST['image']}");
        }

        jsonResponse('Banner saved', 'success');
    }

    /** deleted on 2021.07.05 */
    /* function ajax_my_banners() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonDTResponse(translate("systmess_error_should_be_logged"));
        }

        if (!have_right('have_company')) {
            jsonDTResponse(translate("systmess_error_rights_perform_this_action"));
        }

        $this->load->model('Seller_Banners_Model', 'seller_banners');

        $params = array(
            'page' => 0,
            'per_p' => isset($_POST['iDisplayLength']) ? $_POST['iDisplayLength'] : 20,
            'start' => $_POST['iDisplayStart'],
            'length' => $_POST['iDisplayLength'],
            'order_by' => array('id DESC')
        );

        if ($_POST['iSortingCols'] > 0) {
            $params['order_by'] = array();
            $sortFields = array('dt_image', 'link', 'date_added');
            for ($i = 0; $i < $_POST['iSortingCols']; $i++) {
                array_push($params['order_by'], "{$sortFields[$_POST['iSortCol_' . $i]]} {$_POST['sSortDir_' . $i]}");
            }
        }

        if (!empty($_POST['sSearch'])) {
            $params['keywords'] = cleanInput($_POST['sSearch']);
        }

        $id_user = id_session();
        $banners = $this->seller_banners->get_banners($id_user, $params);
        $banners_count = $this->seller_banners->count_banners($id_user, $params);

        $images_path = $this->seller_banners->path_to_images;

        $data = array();
        foreach ($banners as $row) {
            $data[] = array(
                'dt_image' => '<img class="seller-banner-image-item" src="' . __SITE_URL . $images_path . '/' . $row['image'] . '">',
                'dt_link' => $row['link'],
                'dt_added_date' => formatDate($row['date_added']),
                'dt_page' => ucfirst($row['page']) . ($row['page'] == 'both' ? ' pages' : ' page'),
                'dt_actions' => '
                    <div>
                        <a class="ep-icon ep-icon_pencil txt-blue fancyboxValidateModalDT fancybox.ajax" title="Edit" data-title="Edit widget" href="' . __SITE_URL . 'seller_banners/form_banner_popup?id=' . $row['id'] . '"></a>
                        <a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-message="Are you sure you want to remove this banner?" data-callback="remove_banner" data-id="' . $row['id'] . '" title="Remove banner"></a>
                    </div>
                '
            );
        }



        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $banners_count,
            "iTotalDisplayRecords" => $banners_count,
            'aaData' => $data
        );

        jsonResponse('', 'success', $output);
    } */

    function ajax_upload_image() {
        if (!logged_in()) {
            jsonResponse(translate("systmess_error_should_be_logged"), 'error');
        }

        if (empty($_FILES['file']['name'])) {
            jsonResponse('Error: Please select file to upload.');
        }

        if (!have_right('have_company')) {
            jsonDTResponse(translate("systmess_error_rights_perform_this_action"));
        }

        $this->load->model('Seller_Banners_Model', 'seller_banners');
        $path = $this->seller_banners->path_to_temp;
        global $tmvc;
        $conditions = array(
            'files' => $_FILES['file'],
            'destination' => $path,
            'resize' => '870xR',
            'rules' => array(
                'size' => $tmvc->my_config['fileupload_max_file_size'],
                'min_height' => 430,
                'min_width' => 870
            )
        );
        $result = $this->upload->upload_images_new($conditions);

        if (empty($result['errors'])) {
            jsonResponse('Uploaded', 'success', array(
                'path' => $path,
                'name' => $result[0]['new_name']
            ));
        } else {
            jsonResponse($result['errors']);
        }
    }
}

