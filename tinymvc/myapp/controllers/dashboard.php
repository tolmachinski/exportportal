<?php
/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */

use App\Common\Buttons\ChatButton;
use App\Common\Traits\PromotedEventProviderTrait;

class Dashboard_Controller extends TinyMVC_Controller
{
    use PromotedEventProviderTrait;

    private $breadcrumbs = [];

    public function index()
    {
        if (!logged_in()) {
            $this->session->setMessages(translate('systmess_error_should_be_logged'), 'errors');
            headerRedirect(__SITE_URL . 'login');
        }
        checkDomainForGroup();

        $this->load_main();
        $this->load->model('UserGroup_Model', 'user_group');

        $id_user = $this->session->id;

        $uri = $this->uri->uri_to_assoc();
        $params = [
            'per_p' => 1,
            'from'  => 0,
        ];
        $data['ep_updates'] = $this->ep_updates->get_list_ep_update_public($params);

        $id_user_statistic = privileged_user_id();
        if (user_type('users_staff')) {
            $this->load->model('User_Model', 'user');
            $seller = $this->user->getSimpleUser($id_user_statistic, 'users.user_group');
            $group = $seller['user_group'];
        } else {
            $group = group_session();
        }

        $this->load->model('User_Statistic_Model', 'statistic');

        $data['statistic'] = $this->statistic->get_user_statistic($id_user_statistic, $group);

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->dashboardEpl($data);
        } else {
            $this->dashboardAll($data);
        }
    }

    public function timeline()
    {
        if (!logged_in()) {
            $this->session->setMessages(translate('systmess_error_should_be_logged'), 'errors');
            headerRedirect(__SITE_URL . 'login');
        }

        //show_comming_soon();

        $this->load_main();
        $this->load->model('UserGroup_Model', 'user_group');
        $this->load->model('Followers_Model', 'followers');
        $this->load->model('Company_Model', 'company');

        $id_user = privileged_user_id();

        $data['counters_notification'] = $this->systmess->counter_user_notifications_by_type(['type' => 'all', 'user' => $id_user, 'status' => 'new']);

        $users_followers = $this->followers->get_user_followed($id_user);
        if (!empty($users_followers)) {
            $companies = $this->company->get_companies(['users_list' => $users_followers]);
            if (!empty($companies)) {
                $companies = arrayByKey($companies, 'id_user');

                if (logged_in()) {
                    $companies = array_map(
                        function ($companyItem) {
                            $chatBtn = new ChatButton(['recipient' => $companyItem['id_user'], 'recipientStatus' => $companyItem['status']]);
                            $companyItem['btnChat'] = $chatBtn->button();

                            return $companyItem;
                        },
                        $companies
                    );
                }
            }

            $data['wall_items'] = library('Wall', 'wally')->getItemsViews($users_followers, $companies, (int) config('seller_wall_items_limit', 10), 0, true);
            $data['companies'] = $companies;
        }

        $this->breadcrumbs[] = [
            'link'	 => __SITE_URL . 'timeline',
            'title'	=> 'Timeline',
        ];

        $data['breadcrumbs'] = $this->breadcrumbs;
        $this->view->assign($data);
        $data['main_content'] = 'new/user/dashboard/timeline/index_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
    }

    public function widgets()
    {
        if (!logged_in()) {
            $this->session->setMessages(translate('systmess_error_should_be_logged'), 'errors');
            headerRedirect(__SITE_URL . 'login');
        }

        if (!have_right('sell_item')) {
            $this->session->setMessages(translate('systmess_error_rights_perform_this_action'), 'errors');
            headerRedirect();
        }

        $this->view->display('new/header_view');
        $this->view->display('new/dashboard/widgets/index_view');
        $this->view->display('new/footer_view');
    }

    public function ajax_my_widgets()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('sell_item');

        $id_user = id_session();
        $request = request()->request;

        $sortBy = dtOrdering($request->all(), [
            'dt_height' => 'height',
            'dt_width'  => 'width',
            'dt_site'   => 'site',
        ], fn ($ordering) => $ordering['column'] . ' ' . $ordering['direction']);

        $widgetsConditions = array_filter([
            'keywords'  => empty($request->get('sSearch')) ? null : cleanInput($request->get('sSearch')),
            'order_by'  => $sortBy ?: ['id desc'],
            'per_p'     => $request->getInt('iDisplayLength') ?: 20,
            'start'     => $request->getInt('iDisplayStart'),
        ], fn ($value) => isset($value));

        /** @var Widgets_Model $widgetsModel */
        $widgetsModel = model(Widgets_Model::class);

        $widgets = $widgetsModel->get_widgets($id_user, $widgetsConditions);
        $widgetsCount = $widgetsModel->count_widgets($id_user, $widgetsConditions);

        $output = [
            'iTotalDisplayRecords'  => $widgetsCount,
            'iTotalRecords'         => $widgetsCount,
            'sEcho'                 => $request->getInt('sEcho'),
            'aaData'                => [],
        ];

        if (empty($widgets)) {
            jsonResponse('', 'success', $output);
        }

        foreach ($widgets as $widget) {
            $output['aaData'][] = [
                'dt_site'    => $widget['site'],
                'dt_width'   => $widget['width'],
                'dt_height'  => $widget['height'],
                'dt_actions' => '<div class="dropdown">
                        <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="ep-icon ep-icon_menu-circles"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item fancybox fancybox.ajax" title="Edit" data-title="Edit widget" data-mw="470" href="' . __SITE_URL . 'dashboard/add_widget_popup?id=' . $widget['id'] . '">
                                <i class="ep-icon ep-icon_pencil"></i><span class="txt">Edit</span>
                            </a>
                            <a class="dropdown-item fancybox fancybox.ajax" title="Get widget code" data-title="Widget code" href="' . __SITE_URL . 'dashboard/widget_code_popup?id=' . $widget['id'] . '">
                                <i class="ep-icon ep-icon_menu"></i><span class="txt">Get code</span>
                            </a>
                            <a class="dropdown-item confirm-dialog" data-message="Are you sure you want to remove this widget?" data-callback="remove_widget" data-id="' . $widget['id'] . '" title="Remove widget">
                                <i class="ep-icon ep-icon_trash-stroke"></i><span class="txt">Remove</span>
                            </a>
                        </div>
                    </div>',
            ];
        }

        jsonResponse('', 'success', $output);
    }

    public function widget_code_popup()
    {
        checkIsLoggedAjaxModal();
        checkPermisionAjaxModal('sell_item');

        if (empty($widgetId = request()->query->getInt('id'))) {
            messageInModal('Please provide widget id');
        }

        /** @var Widgets_Model $widgetsModel */
        $widgetsModel = model(Widgets_Model::class);

        if (empty($widget = $widgetsModel->get_widget($widgetId, id_session()))) {
            messageInModal('Widget not found');
        }

        views()->display('new/dashboard/widgets/code_popup_view', ['widget' => $widget]);
    }

    public function widget_script()
    {
        $content = $this->view->fetch('new/dashboard/widgets/widget_script_view');
        $length = mb_strlen($content);

        header('Content-Type: application/javascript; charset=UTF-8');
        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename="widget.js"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Content-Length: {$length}");
        echo $content;
    }

    public function seller_widget()
    {
        if (empty($_GET['key'])) {
            jsonResponse('Please provide widget key');
        }

        header_remove('X-Frame-Options');

        $this->view->display('new/dashboard/widgets/seller_widget_view', [
            'key'  => $_GET['key'],
            'site' => $_SERVER['HTTP_REFERER'],
        ]);
    }

    public function add_widget_popup()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse(translate('systmess_error_should_be_logged'));
        }

        if (!have_right('sell_item')) {
            jsonResponse(translate('systmess_error_rights_perform_this_action'));
        }

        $this->load->model('Items_Model', 'items');
        $this->load->model('Widgets_Model', 'widgets');

        $condition = [
            'seller'       => id_session(),
            'item_columns' => 'it.id, it.title',
            'visible'      => 1,
            'blocked'      => 0,
            'main_photo'   => 1,
            'limit_items'  => false,
            'order_by'     => 'it.id DESC',
        ];

        $sellerItems = $this->items->get_items($condition);
        $sellerItemsCount = $this->items->count_items($condition);

        $widget = false;
        if (!empty($_GET['id'])) {
            $widget = $this->widgets->get_widget($_GET['id'], id_session());
        }

        $widgetItems = array_fill(0, 3, [
            'id'    => 0,
            'title' => 'Item title',
            'link'  => '#',
            'image' => __IMG_URL . 'public/img/no_image/no-image-80x80.png',
        ]);

        $this->view->assign([
            'widget'           => $widget,
            'widgetItems'      => $widgetItems,
            'sellerItems'      => $sellerItems,
            'sellerItemsCount' => $sellerItemsCount,
            'withTemplate'     => true,
        ]);

        $this->view->display('new/dashboard/widgets/add_popup_view');
    }

    public function save_widget()
    {
        checkIsLoggedAjax();
        checkPermisionAjax('sell_item');

        $this->validator->set_rules([
            [
                'field' => 'targetSite',
                'label' => 'Target site',
                'rules' => ['required' => '', 'valid_url' => ''],
            ],
            [
                'field' => 'type',
                'label' => 'Widget type',
                'rules' => ['required' => '', 'integer' => '', 'min[1]' => '', 'max[2]' => ''],
            ],
            [
                'field' => 'width',
                'label' => 'Widget width',
                'rules' => ['required' => ''],
            ],
            [
                'field' => 'widgetHeight',
                'label' => 'Widget height',
                'rules' => ['required' => ''],
            ],
            [
                'field' => 'bodyHeight',
                'label' => 'Widget body height',
                'rules' => ['required' => ''],
            ],
            [
                'field' => 'items',
                'label' => 'Widget items',
                'rules' => ['required' => ''],
            ],
        ]);

        if (!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        $request = request()->request;

        $itemsList = explode(',', (string) $request->get('items'));
        $itemsList = array_filter(array_unique(array_map(
            fn ($itemId) => (int) $itemId,
            $itemsList
        )));

        if (empty($countSelectedItems = count($itemsList))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);

        $itemsConditions = [
            'item_columns'  => 'it.id, it.title',
            'limit_items'   => false,
            'main_photo'    => 1,
            'list_item'     => implode(',', $itemsList),
            'order_by'      => 'it.id DESC',
            'visible'       => 1,
            'blocked'       => 0,
            'seller'        => id_session(),
        ];

        if ($countSelectedItems != count($items = $itemsModel->get_items($itemsConditions))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        /** @var Widgets_Model $widgetsModel */
        $widgetsModel = model(Widgets_Model::class);

        if (!empty($widgetId = $request->getInt('id')) && empty($widget = $widgetsModel->get_widget($widgetId, id_session()))) {
            jsonResponse(translate('systmess_error_invalid_data'));
        }

        $key = $widget['widget_key'] ?? md5(id_session() . microtime());

        $widgetData = [
            'widget_type'   => $request->getInt('type'),
            'widget_key'    => $key,
            'id_user'       => id_session(),
            'height'        => $request->get('widgetHeight'),
            'width'         => $request->get('width'),
            'items'         => implode(',', $itemsList),
            'site'          => $request->get('targetSite'),
        ];

        if (empty($widget)) {
            $widgetId = $widgetsModel->insert($widgetData);
        } else {
            @unlink(sellerWidgetFilePath(id_session(), $key, $widget['site']));
            $widgetsModel->update($widgetData, id_session(), $widgetId);
        }

        $widgetItems = [];
        foreach ($items as $item) {
            $widgetItems[] = [
                'id'    => $item['id'],
                'title' => $item['title'],
                'image' => getDisplayImageLink(['{ID}' => $item['id'], '{FILE_NAME}' => $item['photo_name']], 'items.main', ['thumb_size' => 1]),
                'link'  => makeItemUrl($item['id'], $item['title']),
            ];
        }

        $viewParams = [
            'sellerItemsCount'  => $countSelectedItems,
            'withTemplate'      => false,
            'widgetHeight'      => $request->get('widgetHeight'),
            'widgetItems'       => $widgetItems,
            'bodyHeight'        => $request->get('bodyHeight'),
            'width'             => $request->get('width'),
        ];

        $widget = '';
        switch ($_POST['type']) {
            case 1:
                $widget = views()->fetch('new/dashboard/widgets/widget_1_view', $viewParams);

            break;
            case 2:
                $widget = views()->fetch('new/dashboard/widgets/widget_2_view', $viewParams);

            break;

            default:
                jsonResponse('Undefined widget type');

            break;
        }

        $sellerWidgetsDir = 'public/widgets/' . id_session();
        if (!is_dir($sellerWidgetsDir)) {
            create_dir($sellerWidgetsDir);
        }

        file_put_contents(sellerWidgetFilePath(id_session(), $key, $request->get('targetSite')), $widget);

        jsonResponse('Widget saved successfully', 'success', ['id' => $widgetId]);
    }

    public function remove_widget()
    {
        if (!logged_in()) {
            jsonResponse(translate('systmess_error_should_be_logged'));
        }

        if (!have_right('sell_item')) {
            jsonResponse(translate('systmess_error_rights_perform_this_action'));
        }

        if (empty($_POST['id'])) {
            jsonResponse('Please provide widget ID');
        }

        $this->load->model('Widgets_Model', 'widgets');

        $widget = $this->widgets->get_widget($_POST['id'], id_session());
        if (false === $widget) {
            jsonResponse('Widget not found');
        }

        $this->widgets->remove(id_session(), $_POST['id']);

        $filePath = sellerWidgetFilePath(id_session(), $widget['widget_key'], $widget['site']);
        @unlink($filePath);

        jsonResponse('Widget removed successfully', 'success');
    }

    public function customize_menu()
    {
        if (!logged_in()) {
            $this->session->setMessages(translate('systmess_error_should_be_logged'), 'errors');
            headerRedirect(__SITE_URL . 'login');
        }
        
        checkGroupExpire();
        
        $this->load_main();
        $this->load->model('UserGroup_Model', 'user_group');
        
        $data['custom_menu'] = $this->session->menu;

        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $this->customizeMenuEpl($data);
        } else {
            $this->customizeMenuAll($data);
        }
    }

    /** deleted on 2021.07.05 */
    /* function ajax_view_dashboard(){
        if(!isAjaxRequest())
            headerRedirect();

        if(!logged_in()){
            jsonResponse('Error: Your session has been expired. Please refresh this page and log in.');
        }

        $data['custom_header_menu'] = $this->session->menu;

        // $this->load->model('UserGroup_Model', 'user_group');
        // $custom_menu_array = json_decode($data['custom_header_menu']);

        // if(empty($custom_menu_array)){
        // 	$custom_menu = $this->user_group->getGroup(group_session(), 'menu');
        // 	$this->session->menu = $data['custom_header_menu'] = $custom_menu['menu'];
        // }

        $content = $this->view->fetch($this->view_folder.'header_dashboard_view', $data);
        jsonResponse('', 'success', array('menu_content' => $content));
    } */

    public function ajax_view_dashboard_mob()
    {
        checkIsAjax();

         /** @var Dashboard_Banners_Model $dashboardBannersModel */
         $dashboardBannersModel = model(Dashboard_Banners_Model::class);

        $viewVars['complete_profile'] = session()->__get('completeProfile');
        $viewVars['dashboardBanner'] = $dashboardBannersModel->findOneBy([
            'conditions' => [
                'userGroupIds' => [group_session()],
                'isVisible' => 1,
            ],
            'joins' => ['dashboardBannersRelation'],
        ]);
        //$viewVars['eventPromotion'] = $this->getPromotedEventDisplayInformation(); // Get promoted event
        //$viewVars['dashboardBanner'] = $this->getPromotedEventDisplayInformation();
        $viewVars['is_dashboard'] = true;
        $viewVars['currencyes'] = model('Currency')->get_all_cur();
        if ('webpackData' == cleanInput(uri()->segment(3))) {
            $viewVars['webpackData'] = true;
            // $data['custom_header_menu'] = $this->session->menu;
        }
        views()->assign($viewVars);
        jsonResponse('', 'success', [
            'html' => logged_in() ? views()->fetch('new/template_views/nav_mobile_dashboard_logged_view') : views()->fetch('new/template_views/nav_mobile_dashboard_view'),
        ]);
    }

    public function ajax_view_dashboard_new()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        /** @var Dashboard_Banners_Model $dashboardBannersModel */
        $dashboardBannersModel = model(Dashboard_Banners_Model::class);

        $viewVars = [
            'complete_profile'   => session()->__get('completeProfile'),
            'is_dashboard'       => true,
            'custom_header_menu' => $this->session->menu,
            'eventPromotion'     => $this->getPromotedEventDisplayInformation(), // Get promoted event
            'dashboardBanner'    => $dashboardBannersModel->findOneBy([
                'conditions' => [
                    'userGroupIds' => [group_session()],
                    'isVisible' => 1,
                ],
                'joins' => ['dashboardBannersRelation'],
            ]),
        ];

        if ('webpackData' == cleanInput(uri()->segment(3))) {
            $viewVars['webpackData'] = true;
        }

        $this->view->assign($viewVars);
        if (__CURRENT_SUB_DOMAIN === getSubDomains()['shippers']) {
            $content = views()->fetch('new/epl/template/header_dashboard_view');
        } else {
            $content = views()->fetch('new/header_dashboard_view');
        }

        jsonResponse('', 'success', ['menu_content' => $content]);
    }

    // function ajax_community_dashboard()
    // {
    //     checkIsAjax();
    //     checkIsLoggedAjax();

    //     $data = array(
    //         'complete_profile' => session()->__get('completeProfile'),
    //         'custom_header_menu' => $this->session->menu
    //     );
    //     $this->view->assign($data);
    //     $content = $this->view->fetch('new/questions/header_dashboard_view');

    // 	jsonResponse(null, 'success', array('menu_content' => $content));
    // }

    public function ajax_view_dashboard_tablet()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse('Error: Your session has been expired. Please refresh this page and log in.');
        }

        $data['complete_profile'] = session()->__get('completeProfile');
        $data['custom_header_menu'] = $this->session->menu;
        $data['is_dashboard'] = true;
        $content = $this->view->fetch($this->view_folder . 'header_dashboard_new_view', $data);
        jsonResponse('', 'success', ['menu_content' => $content]);
    }

    public function ajax_view_admin_dashboard()
    {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

        if (!logged_in()) {
            jsonResponse('Error: Your session has been expired. Please refresh this page and log in.');
        }

        $data['custom_header_menu'] = $this->session->menu;
        $content = $this->view->fetch('admin/header_dashboard_view', $data);
        jsonResponse('', 'success', ['menu_content' => $content]);
    }

    private function dashboardEpl($data)
    {
        $data['templateViews'] = [
            'mainOutContent'    => 'user/dashboard/index_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(['new/epl/template/index_view'], $data);
    }

    private function dashboardAll($data)
    {
        views(['new/header_view', 'new/user/dashboard/index_view', 'new/footer_view'], $data);
    }

    private function customizeMenuEpl($data)
    {
        $data['templateViews'] = [
            'mainOutContent'    => 'user/dashboard/customize_menu_view',
        ];
        $data['webpackData'] = [
            'dashboardOldPage' => true,
            // 'styleCritical' => 'epl_styles_home',
            // 'pageConnect' 	=> 'epl_index_page',
        ];

        views(['new/epl/template/index_view'], $data);
    }

    private function customizeMenuAll($data)
    {
        views(['new/header_view', 'new/user/dashboard/customize_menu_view', 'new/footer_view'], $data);
    }

    private function load_main()
    {
        $this->load->model('Widgets_Model', 'widgets');
        $this->load->model('Category_Model', 'category');
        $this->load->model('User_Model', 'user');
        $this->load->model('Ep_Updates_Model', 'ep_updates');
    }
}
