<?php

use App\Common\Buttons\ChatButton;
use ExportPortal\Bridge\Notifier\Notification\SystemNotification;
use ExportPortal\Bridge\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\NotifierInterface;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Followers_Controller extends TinyMVC_Controller {

    private $breadcrumbs = array();
    private $followers_per_page = 15;

    private $followers_statuses = array(
        'followers' => array(
            'icon' => 'followers',
            'title' => 'Followers'
        ),
        'followed' => array(
            'icon' => 'reply-right-empty',
            'title' => 'Following'
        )
    );

    private function _load_main() {
        $this->load->model('Category_Model', 'category');
        $this->load->model('User_Model', 'user');
        $this->load->model("Followers_model", 'followers');
    }

    function my() {
		if(user_type('users_staff'))
			headerRedirect(__SITE_URL);

        if(!have_right('follow_user'))
            show_404();

		checkGroupExpire();

        $this->_load_main();
        $id_user = id_session();
        $data['status_select'] = 'followers';
        $data['followers_per_page'] = $this->followers_per_page;
        $followers = $this->followers->get_user_followers($id_user, array('limit' => '0,' . $this->followers_per_page));
        $data['followers_count'] = $data['status_select_count'] = $this->followers->get_count_user_followers($id_user);

        $data['followers'] = [];
        if (!empty($followers) && logged_in()) {
            $data['followers'] = array_map(
                function ($userFollower) {
                    $chatBtn = new ChatButton(['recipient' => $userFollower['idu'], 'recipientStatus' => $userFollower['status']]);
                    $userFollower['btnChat'] = $chatBtn->button();
                    return $userFollower;
                },
                $followers
            );
        }elseif(!empty($followers)){
			$data['followers'] = $followers;
		}

        $data['followed_count'] = $this->followers->get_count_user_followed($id_user);
        $data['id_user'] = $id_user;
        $data['followers_statuses'] = $this->followers_statuses;

        $this->view->assign($data);
        $this->view->display('new/header_view');
        $this->view->display('new/followers/index_view');
        $this->view->display('new/footer_view');
    }

    function popup_followers() {
        if (!isAjaxRequest()) {
            headerRedirect();
        }

		checkGroupExpire('modal');

        $type = $this->uri->segment(3);

        $id_user = intVal($this->uri->segment(4));
        if (!$id_user) {
            messageInModal(translate('systmess_error_invalid_data'));
        }

        $this->_load_main();

        switch ($type) {
            case 'follow_user':
                checkPermisionAjaxModal('follow_user');

                if (is_my($id_user)) {
                    messageInModal(translate('systmess_error_cannot_follow_yourself'));
                }

                $data['id_user'] = $id_user;

                $this->view->display('new/followers/popup_follow_user_view', $data);
            break;
			case 'followers':
                $followers = $this->followers->get_user_followers($id_user, array('limit' => '0,10'));
                $data['followers_count'] = $this->followers->get_count_user_followers($id_user);
                $data['type'] = $type;
                $data['id_user'] = $id_user;

                $data['followers'] = [];
                if (!empty($followers) && logged_in()) {
                    $data['followers'] = array_map(
                        function ($userFollower) {
                            $chatBtn = new ChatButton(['recipient' => $userFollower['idu'], 'recipientStatus' => $userFollower['status']]);
                            $userFollower['btnChat'] = $chatBtn->button();
                            return $userFollower;
                        },
                        $followers
                    );
                }elseif(!empty($followers)){
                    $data['followers'] = $followers;
                }

                $this->view->assign($data);
                $this->view->display('new/followers/followers_view', $data);
            break;
			case 'followed':
                $followers = $this->followers->get_users_followed($id_user, array('limit' => '0,10'));
                $data['followers_count'] = $this->followers->get_count_user_followed($id_user);
                $data['type'] = $type;
                $data['id_user'] = $id_user;

                $data['followers'] = [];
                if (!empty($followers) && logged_in()) {
                    $data['followers'] = array_map(
                        function ($userFollower) {
                            $chatBtn = new ChatButton(['recipient' => $userFollower['id_user'], 'recipientStatus' => user_status()]);
                            $userFollower['btnChat'] = $chatBtn->button();
                            return $userFollower;
                        },
                        $followers
                    );
                }elseif(!empty($followers)){
                    $data['followers'] = $followers;
                }

                $this->view->assign($data);
                $this->view->display('new/followers/followers_view', $data);
            break;
        }
    }

    function ajax_followers_info() {
        if (!isAjaxRequest())
            headerRedirect();

        $this->_load_main();
        $id_user = privileged_user_id();

        switch ($_POST['type']) {
            case 'followers_list':
                // CALCULATE LIMIT - FOR DASHBOARD PAGINATION
                global $tmvc;
                $per_page = $this->followers_per_page;
                $page = 1;
                if (!empty($_POST['page']) && intVal($_POST['page']) > 1)
                    $page = intVal($_POST['page']);

                $start_from = ($page == 1) ? 0 : ($page * $per_page) - $per_page;

                $conditions['limit'] = $start_from . ", " . $per_page;

                $followers = $this->followers->get_user_followers($id_user, $conditions);
                $total_followers_by_status = $this->followers->get_count_user_followers($id_user);

                if (empty($followers)) {
                    jsonResponse('0 followers found by this search.', 'info', array('total_followers_by_status' => 0));
                }

                $data['followers'] = [];
                if (logged_in()) {
                    $data['followers'] = array_map(
                        function ($userFollower) {
                            $chatBtn = new ChatButton(['recipient' => $userFollower['idu'], 'recipientStatus' => $userFollower['status']]);
                            $userFollower['btnChat'] = $chatBtn->button();
                            return $userFollower;
                        },
                        $followers
                    );
                }else{
                    $data['followers'] = $followers;
                }

                $data['type'] = 'followers';
                $followers_list = $this->view->fetch('new/followers/follower_item_view', $data);

                jsonResponse('', 'success', array('followers_list' => $followers_list, 'total_followers_by_status' => $total_followers_by_status));
            break;
            case 'followed_list':
                // CALCULATE LIMIT - FOR DASHBOARD PAGINATION
                global $tmvc;
                $per_page = $this->followers_per_page;
                $page = 1;
                if (!empty($_POST['page']) && intVal($_POST['page']) > 1)
                    $page = intVal($_POST['page']);

                $start_from = ($page == 1) ? 0 : ($page * $per_page) - $per_page;

                $conditions['limit'] = $start_from . ", " . $per_page;
                $followers = $this->followers->get_users_followed($id_user, $conditions);
                $total_followers_by_status = $this->followers->get_count_user_followed($id_user);

                if (empty($followers)) {
                    jsonResponse('0 followers found by this search.', 'info', array('total_followers_by_status' => 0));
                }

                $data['followers'] = [];
                if (logged_in()) {
                    $data['followers'] = array_map(
                        function ($userFollower) {
                            $chatBtn = new ChatButton(['recipient' => $userFollower['id_user'], 'recipientStatus' => user_status()]);
                            $userFollower['btnChat'] = $chatBtn->button();
                            return $userFollower;
                        },
                        $followers
                    );
                }else{
                    $data['followers'] = $followers;
                }

                $followers_list = $this->view->fetch('new/followers/follower_item_view', $data);

                jsonResponse('', 'success', array('followers_list' => $followers_list, 'total_followers_by_status' => $total_followers_by_status));
            break;
            case 'search_followers':
                $keywords = cleanInput(cut_str($_POST['keywords']));
                if ($keywords == '')
                    jsonResponse('Error: Search keywords is required.');

                global $tmvc;
                $per_page = $this->followers_per_page;
                $page = 1;
                if (!empty($_POST['page']) && intVal($_POST['page']) > 1)
                    $page = intVal($_POST['page']);

                $start_from = ($page == 1) ? 0 : ($page * $per_page) - $per_page;

                $search_filter = cleanInput($_POST['search_filter']);
                $conditions = array('keywords' => $keywords);

                $conditions['limit'] = $start_from . ", " . $per_page;

                if($search_filter == 'followed'){
                    $followers = $this->followers->get_users_followed($id_user, $conditions);
                    $total_followers_by_status = $this->followers->get_count_user_followed($id_user, $conditions);

                    if (empty($followers)) {
                        $data['followers'] = [];
                        if (logged_in()) {
                            $data['followers'] = array_map(
                                function ($userFollower) {
                                    $chatBtn = new ChatButton(['recipient' => $userFollower['id_user'], 'recipientStatus' => user_status()]);
                                    $userFollower['btnChat'] = $chatBtn->button();
                                    return $userFollower;
                                },
                                $followers
                            );
                        }else{
                            $data['followers'] = $followers;
                        }
                    }
                }else{
                    $followers = $this->followers->get_user_followers($id_user, $conditions);
                    $total_followers_by_status = $this->followers->get_count_user_followers($id_user, $conditions);
                    $data['type'] = 'followers';

                    if (empty($followers)) {
                        $data['followers'] = [];
                        if (logged_in()) {
                            $data['followers'] = array_map(
                                function ($userFollower) {
                                    $chatBtn = new ChatButton(['recipient' => $userFollower['idu'], 'recipientStatus' => $userFollower['status']]);
                                    $userFollower['btnChat'] = $chatBtn->button();
                                    return $userFollower;
                                },
                                $followers
                            );
                        }else{
                            $data['followers'] = $followers;
                        }
                    }
                }

                if (empty($followers)) {
                    jsonResponse('0 ollowers found by this search.', 'info');
                }

                $followers_list = $this->view->fetch('new/followers/follower_item_view', $data);

                jsonResponse('', 'success', array('followers_list' => $followers_list, 'total_followers_by_status' => $total_followers_by_status));
            break;
        }
    }

    public function ajax_followers_operation() {
        checkIsAjax();
        checkIsLoggedAjax();
		checkGroupExpire('ajax');

        $request = request()->request;
        $this->_load_main();
        $userId = id_session();

        switch (uri()->segment(3)) {
            case 'update_sidebar_counters':
                checkPermisionAjax('follow_user');

                // GET COUNTERS
                $followers_count = $this->followers->get_count_user_followers($userId);
                $followed_count = $this->followers->get_count_user_followed($userId);

                $statuses_counters = array(
                    'followers' => array('counter' => $followers_count),
                    'followed' => array('counter' => $followed_count)
                );

                // RETURN RESPONCE
                jsonResponse('', 'success', array('counters' => $statuses_counters));
            break;
            case 'follow_user':
                checkPermisionAjax('follow_user');

                if (empty($followedUserId = $request->getInt('user'))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                if (is_my($followedUserId)) {
                    jsonResponse(translate('systmess_error_cannot_follow_yourself'));
                }

                $this->validator->set_rules([
                    [
                        'field' => 'message',
                        'label' => translate('follow_user_message_label'),
                        'rules' => ['required' => '', 'max_len[500]' => ''],
                    ],
                ]);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var User_Followers_Model $userFollowersModel */
                $userFollowersModel = model(User_Followers_Model::class);
                if (!empty($userFollowersModel->findOneBy([
                    'scopes' => [
                        'followedUser'  => $followedUserId,
                        'user'          => $userId,
                    ],
                ]))) {
                    jsonResponse(translate('systmess_error_already_following_this_user'));
                }

                /** @var Elasticsearch_Users_Model $elasticsearchUsersModel */
                $elasticsearchUsersModel = model(Elasticsearch_Users_Model::class);
                $users = $elasticsearchUsersModel->getUsers(['id' => $followedUserId]);
                if (empty(array_shift($users))) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                /** @var User_Followers_Model $userFollowersModel */
                $userFollowersModel = model(User_Followers_Model::class);
                $userFollowersModel->insertOne([
                    'id_user_follower' => $userId,
                    'id_user'          => $followedUserId,
                    'message_follower' => $request->get('message'),
                ]);

                model(User_Statistic_Model::class)->set_users_statistic([
                    $followedUserId => ['followers_user' => 1],
                    $userId         => ['follow_users' => 1],
                ]);

                //WARNING: id_user need be string - as in DB
                session()->__push('followed', (string) $followedUserId);

                $notifier = $this->getContainer()->get(NotifierInterface::class);
                $notifier->send(
                    new SystemNotification('follow_user', [
                        '[USER_NAME]'    => cleanOutput(user_name_session()),
						'[USER_LINK]'    => getMyProfileLink(),
						'[USER_MESSAGE]' => cleanOutput($request->get('message'))
                    ]),
                    new Recipient($followedUserId)
                );

                jsonResponse(
                    translate('systmess_success_following_user'),
                    'success',
                    [
                        'user'            => $followedUserId,
                        'followers_count' => $userFollowersModel->countAllBy([
                            'scopes' => [
                                'followedUser' => $userId,
                            ],
                        ]),
                        'followed_count'  => $userFollowersModel->countAllBy([
                            'scopes' => [
                                'user' => $userId
                            ],
                        ]),
                    ]
                );
			break;
            case 'delete_follow_user':
                if (!have_right('follow_user')) {
                    jsonResponse(translate('systmess_error_permission_not_granted'));
                }

                $id_followed_user = intVal($_POST['user']);

                if (!in_array($id_followed_user, $this->session->followed)) {
                    jsonResponse(translate('systmess_error_you_are_not_following_this_user'));
                }

                $this->followers->delete_followed($userId, $id_followed_user);
				$this->load->model('User_Statistic_Model', 'statistic');
				$this->statistic->set_users_statistic(array(
					$this->session->id => array(
						'follow_users' => -1
					),
					$id_followed_user => array(
						'followers_user' => -1
					)
				));
                $this->session->clear_val('followed', $id_followed_user);

				$return = array('user' => $id_followed_user);
				$return['followers_count'] = $this->followers->get_count_user_followers($userId);
 				$return['followed_count'] = $this->followers->get_count_user_followed($userId);

                jsonResponse(translate('systmess_success_unfollow_user'), 'success', $return);
            break;
        }
    }

    function ajax_followers_load() {
        $this->_load_main();
        $type = $this->uri->segment(3);
        $view = $this->uri->segment(4);

        if(isset($_POST['id'])){
            $id_user = intVal($_POST['id']);
        }else{
            $id_user = id_session();
        }

        $start = intVal($_POST['start']);
        $upKey = ($start % 2 == 0) ? 0 : 2;

        switch ($type) {
            case'followers':
                $followers = $this->followers->get_user_followers($id_user, array('limit' => $start . ',10'));
                $followers_count = $this->followers->get_count_user_followers($id_user);

                $data['followers'] = [];
                if (!empty($followers) && logged_in()) {
                    $data['followers'] = array_map(
                        function ($userFollower) {
                            $chatBtn = new ChatButton(['recipient' => $userFollower['idu'], 'recipientStatus' => $userFollower['status']]);
                            $userFollower['btnChat'] = $chatBtn->button();
                            return $userFollower;
                        },
                        $followers
                    );
                }elseif(!empty($followers)){
                    $data['followers'] = $followers;
                }
            break;
            case'followed':
                $followers = $this->followers->get_users_followed($id_user, array('limit' => $start . ',10'));
                $followers_count = $this->followers->get_count_user_followed($id_user);

                $data['followers'] = [];
                if (!empty($followers) && logged_in()) {
                    $data['followers'] = array_map(
                        function ($userFollower) {
                            $chatBtn = new ChatButton(['recipient' => $userFollower['id_user'], 'recipientStatus' => user_status()]);
                            $userFollower['btnChat'] = $chatBtn->button();
                            return $userFollower;
                        },
                        $followers
                    );
                }elseif(!empty($followers)){
                    $data['followers'] = $followers;
                }
            break;
        }

        $data['type'] = $type;
        $data['up_key'] = $upKey;

        $display = $this->view->fetch('new/followers/follower_item_view', $data);

        echo json_encode(array('count' => $followers_count, 'html' => $display));
    }

}

?>
