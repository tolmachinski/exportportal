<?php
/**
* @author Bendiucov Tatiana
* @todo Refactoring [15.12.2021]
* Controller Refactoring
*/

class Email_Message_Controller extends TinyMVC_Controller {
    private function load_main(){
        $this->load->model('Email_Message_Model', 'email_message');
    }

    private function modal_get_record($id_record){
        $record = $this->email_message->get_email_message(array('id_record' => $id_record));

        if(empty($record))
            messageInModal('Error: This email message doesn\'t found.');

        return $record;
    }

    public function my(){
        checkAdmin('manage_email_messages');
        $this->load_main();

        $data['list_email_account'] = $this->email_message->get_email_account();
        $data['title'] = 'The message list from email';

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/email_message/private/index_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_dt_user_staff(){
        checkAdminAjaxDT('manage_email_messages');

        $this->load_main();

        $params = array('per_p' => (int) $_POST['iDisplayLength'], 'start' => (int) $_POST['iDisplayStart']);

        $params['sort_by'] = flat_dt_ordering($_POST, [
            'dt_id_record'  => 'id_mess',
            'dt_email_from' => 'email_account',
            'dt_email_to'   => 'email_from'
        ]);

        $params['id_user_assign'] = $this->session->id;

        $params = array_merge($params,
            dtConditions($_POST, [
                ['as' => 'email_account', 'key' => 'email_account', 'type' => 'cleanInput'],
                ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
                ['as' => 'status_record', 'key' => 'status_record', 'type' => 'cleanInput'],
                ['as' => 'date_start', 'key' => 'date_start', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'date_end', 'key' => 'date_end', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ])
        );

        $emailMessages = $this->email_message->get_email_messages($params);
        $emailMessageCount = $this->email_message->get_email_message_count($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $emailMessageCount,
            "iTotalDisplayRecords" => $emailMessageCount,
			'aaData' => array()
        );

        if(empty($emailMessages))
			jsonResponse('', 'success', $output);

		foreach($emailMessages as $message){
			$contentMessage = '<a class="fs-12 ep-icon ep-icon_info" title="Please check email to more information"></a>';

			if (!empty($message['mess_text'])){
				$contentMessage = '<a class="fancybox fancybox.ajax fs-12 ep-icon ep-icon_info" href="' . __SITE_URL . 'email_message/popup_forms/view_text/' . $message['id_mess'] . '" data-title="View text message" title="View text message"></a>';
			}

			$email_account = $full_name = $attachments = '---';
			if (!empty($message['fname']))
				$full_name = $message['fname'] . ' ' . $message['lname'];

			$actions = '';
			$status  = $message['status_record'];
			if ($status !== 'resovled'){
				$actions = '<a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_ok" href="' . __SITE_URL . 'email_message/private_popap_form/resolved_problem/' . $message['id_mess'] . '" data-title="Problem resolved" title="Problem resolved"></a>
							<a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_request" href="' . __SITE_URL . 'email_message/private_popap_form/assign_another/' . $message['id_mess'] . '" data-title="Assign to another staff" title="Assign to another staff"></a>
							<a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_user-minus txt-red" href="' . __SITE_URL . 'email_message/private_popap_form/not_resolved_problem/' . $message['id_mess'] . '" data-title="Deallocation message" title="Deallocation message"></a>';
			}

			if (!empty($message['email_account']))
				$email_account = $message['email_account'];

			if (!empty($message['attachments']))
				$attachments = '<a class="fs-12 ep-icon ep-icon_info" title="The message contains a file, for more details access the email!></a>';

			$output['aaData'][] = array(
				'dt_id_record'  =>  $message['id_mess'],
				'dt_email_from' =>  $email_account,
				'dt_email_to'   =>  $message['email_from'],
				'dt_user_name'  =>  $full_name,
				'dt_subject'    =>  $message['mess_subject'],
				'dt_message'    =>  $contentMessage,
				'dt_date_time'  =>  formatDate($message['mess_time']),
				'dt_file_att'   =>  $attachments,
				'dt_status'     =>  ucfirst($status),
				'dt_actions'    =>  '<a class="fancybox fancybox.ajax ep-icon ep-icon_visible" href="' . __SITE_URL . 'email_message/popup_forms/view_html/' . $message['id_mess'] . '" data-title="View" title="View"></a>' .
									$actions,
			);
		}

        jsonResponse('', 'success', $output);
    }

    public function administration(){
        checkAdmin('manage_messages');
        $this->load_main();
        $this->load->model('Category_Support_Model', 'category_support');

        $data['list_category'] = $this->category_support->get_categories_name();
        $data['list_ep_staff' ]= $this->category_support->get_staff_ep();
        $data['list_email_account'] = $this->email_message->get_email_account();

        $data['isAdmin'] = false;

        if(have_right('admin_site'))
            $data['isAdmin'] = true;

        $data['title'] = 'The message list from email';

        $this->view->assign($data);
        $this->view->display('admin/header_view');
        $this->view->display('admin/email_message/index_view');
        $this->view->display('admin/footer_view');
    }

    public function ajax_dt_email_message(){
        checkAdminAjaxDT('manage_messages');

        $this->load_main();

        $isAdmin = false;

        if(have_right('admin_site'))
            $isAdmin = true;

        $params = array('per_p' => (int) $_POST['iDisplayLength'], 'start' => (int) $_POST['iDisplayStart']);

        $params['sort_by'] = flat_dt_ordering($_POST, [
            'dt_id_record'  => 'id_mess',
            'dt_email_from' => 'email_account',
            'dt_email_to'   => 'email_from',
            'dt_date_time'  => 'mess_time'
        ]);

        $time_sf = $time_st = false;

        $params = array_merge($params,
            dtConditions($_POST, [
                ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
                ['as' => 'id_cat_support', 'key' => 'category', 'type' => 'cleanInput'],
                ['as' => 'id_user_assign', 'key' => 'user_staff', 'type' => 'cleanInput'],
                ['as' => 'email_account', 'key' => 'email_account', 'type' => 'cleanInput']
            ])
        );

        if (isset($_POST['date_start'])){
            $params['date_start']= formatDate($_POST['date_start'],'Y-m-d');
            $time_st = true;
        }

        if (isset($_POST['date_end'])){
            $params['date_end']  = formatDate($_POST['date_end'],  'Y-m-d');
            $time_sf = true;
        }

        if(!$isAdmin)
            $params['status_record'] = 'new';

        if($time_st && $time_sf){
            if(formatDate($params['date_start'],'U') > formatDate($params['date_end'],'U'))
                jsonDTResponse(translate("systmess_error_start_date_more_than_final"));
        }


        if (isset($_POST['status_record'])){
            switch ($_POST["status_record"]) {
                case 0: $params['status_record'] = 'new';         break;
                case 1: $params['status_record'] = 'resovled';    break;
                case 2: $params['status_record'] = 'not resolved';break;
                case 3: $params['status_record'] = 'waiting';     break;
            }
        }

        $emailMessages = $this->email_message->get_email_messages($params);
        $emailMessageCount = $this->email_message->get_email_message_count($params);

        $output = array(
            "sEcho" => intval($_POST['sEcho']),
            "iTotalRecords" => $emailMessageCount,
            "iTotalDisplayRecords" => $emailMessageCount,
			'aaData' => array()
        );

        if(empty($emailMessages))
			jsonResponse('', 'success', $output);

		foreach($emailMessages as $message){
			$noticeMessage = $content = $removeMessage = $assignUser= "";
			$full_name = $user_group = $email_account = $category = $attachments = "---";

			$contentMessage = '<a class="fs-12 ep-icon ep-icon_info" title="Please check email to more information"></a>';

			if (!empty($message['mess_text'])){
//                    $contentMessage = '<a class="fs-12 ep-icon ep-icon_info" title="' . htmlspecialchars($message['mess_text']) . '"></a>';
				$contentMessage = '<a class="fancybox fancybox.ajax fs-12 ep-icon ep-icon_info" href="' . __SITE_URL . 'email_message/popup_forms/view_text/' . $message['id_mess'] . '" data-title="View text message" title="View text message"></a>';
			}

			if (!empty($message['fname']))
				$full_name = $message['fname'] . ' ' . $message['lname'];

			if (!empty($message['ep_fname']))
				$user_group= $message['gr_name'] . ' : <div>' . $message['ep_fname'] . ' ' . $message['ep_lname'] . '</div>';

			if (!empty($message['category']))
				$category  = $message['category'];

			if (!empty($message['notice_record']))
				$noticeMessage = '<a class="fancybox fancybox.ajax ep-icon ep-icon_notice" href="' . __SITE_URL . 'email_message/popup_forms/show_notice/' . $message['id_mess'] . '" data-title="Notice" title="Notice"></a>';

			$status = $message['status_record'];
			if ($isAdmin){
				if ($status != 'resovled'){
					$assignUser = '<a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_user-plus" href="' . __SITE_URL . 'email_message/popup_forms/select_category/' . $message['id_mess'] . '" data-title="Assign users" title="Assign users"></a>';
				}
				$removeMessage= '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_email_message" data-record="' . $message['id_mess'] . '" title="Remove this message" data-message="Are you sure you want to delete this message?" href="#" ></a>';
			}else{
				$assignUser   = '<a class="fancyboxValidateModalDT fancybox.ajax ep-icon ep-icon_user-plus" href="' . __SITE_URL . 'email_message/private_popap_form/select_category/' . $message['id_mess'] . '" data-title="Assign me" title="Assign me"></a>';
			}

			if (!empty($message['email_account']))
				$email_account = $message['email_account'];

			if (!empty($message['attachments']))
				$attachments = '<a class="fs-12 ep-icon ep-icon_info" title="The message contains a file, for more details access the email!"></a>';

			$output['aaData'][] = array(
				'dt_id_record'  =>  $message['id_mess'],
				'dt_email_from' =>  $email_account,
				'dt_email_to'   =>  $message['email_from'],
				'dt_user_name'  =>  $full_name,
				'dt_subject'    =>  $message['mess_subject'],
				'dt_message'    =>  $contentMessage,
				'dt_date_time'  =>  formatDate($message['mess_time']),
				'dt_file_att'   =>  $attachments,
				'dt_category'   =>  $category,
				'dt_ep_staff'   =>  $user_group,
				'dt_status'     =>  ucfirst($status),
				'dt_actions'    =>  '<a class="fancybox fancybox.ajax ep-icon ep-icon_visible" href="' . __SITE_URL . 'email_message/popup_forms/view_html/' . $message['id_mess'] . '" data-title="View" title="View"></a>
									 ' . $noticeMessage . $assignUser . $removeMessage,
			);
		}

        jsonResponse('', 'success', $output);
    }

    public function popup_forms(){
        checkAdminAjaxModal('manage_messages');

        $this->load_main();

        $form_action= $this->uri->segment(3);
        $id_record  = intVal($this->uri->segment(4));
        switch ($form_action) {
            case 'view_text':
                $record = $this->modal_get_record($id_record);

                $data['content'] = !empty($record['mess_text']) ? htmlspecialchars($record['mess_text']) : 'Please check email to more information';

                $this->view->display('admin/email_message/show_html_form_view', $data);
            break;

            case 'view_html':
                $record = $this->modal_get_record($id_record);

                $data['content'] = !empty($record['mess_html']) ? $record['mess_html'] : 'Please check email to more information';

                $this->view->display('admin/email_message/show_html_form_view', $data);
            break;

            case 'show_notice':
                $record = $this->modal_get_record($id_record);

                $data['notice'] = json_decode($record['notice_record'], true);

                $this->view->display('admin/email_message/show_notice_form_view', $data);
            break;

            case 'select_category':
                $record  = $this->modal_get_record($id_record);
                $id_spcat= 0;

                $data['record'] = $record;

                $this->load->model('Category_Support_Model', 'category_support');

                $data['list_category'] = arrayByKey($this->category_support->get_categories_name(), 'id_spcat');

                if(!empty($data['list_category'])){
                    $temp = array();
                    if(!empty($record['id_suppcat'])){
                        $id_spcat = $record['id_suppcat'];
                    }else{
                        $temp = array_keys($data['list_category']);
                        $id_spcat = $temp[0];
                    }

                    if(!empty($data['list_category'][$id_spcat]))
                        $data['list_user'] = $this->category_support->get_support_category(array('id_record'=>$data['list_category'][$id_spcat]['id_spcat']));
                }

                $this->view->display('admin/email_message/select_category_form_view', $data);
            break;
        }
    }

    public function private_popap_form(){
        checkAdminAjaxModal('manage_email_messages');

        $this->load_main();

        $form_action= $this->uri->segment(3);
        $id_record  = intVal($this->uri->segment(4));

        switch ($form_action) {
            case 'select_category':
                $id_user= $this->session->id;

                $record = $this->email_message->get_email_message(array('id_record' => $id_record));

                if (empty($record))
                    messageInModal('Error: This email message doesn\'t found.');

                if(!empty($record['attach_user']) && $record['attach_user'] != $id_user)
                    messageInModal('Error: This email message already assigned to another ep staff.');

                $this->load->model('Category_Support_Model', 'category_support');

                $data['list_category'] = $this->category_support->get_category_by_user(array('id_user'=>$id_user));

                if(empty($data['list_category']))
                    messageInModal('Error: You have no categories of support.');

                $data['id_record'] = $id_record;
                $this->view->display('admin/email_message/assign_form_view', $data);
            break;

            case 'resolved_problem':
                if(!$this->email_message->check_email_message(array('id_record' => $id_record, 'id_user_assign' => $this->session->id)))
                    messageInModal('Error: This email message doesn\'t found.');

                $data['id_record']= $id_record;
                $data['action']   = 'resovled_notice';
                $this->view->display('admin/email_message/private/add_notice_form_view', $data);
            break;

            case 'assign_another':
                if(!$this->email_message->check_email_message(array('id_record' => $id_record, 'id_user_assign' => $this->session->id)))
                    messageInModal('Error: This email message doesn\'t found.');

                $this->load->model('Category_Support_Model', 'category_support');

                $data['list_category'] = arrayByKey($this->category_support->get_categories_name(), 'id_spcat');

                if(!empty($data['list_category'])){
                    $temp = array();
                    $temp = array_keys($data['list_category']);

                    if(!empty($data['list_category'][$temp[0]]))
                        $data['list_user'] = $this->category_support->get_support_category(array('id_record'=>$data['list_category'][$temp[0]]['id_spcat']));
                }

                $data['record']['id_mess'] = $id_record;

                $this->view->display('admin/email_message/private/assign_another_form_view', $data);
            break;

            case 'not_resolved_problem':
                if(!$this->email_message->check_email_message(array('id_record' => $id_record, 'id_user_assign' => $this->session->id)))
                    messageInModal('Error: This email message doesn\'t found.');

                $data['id_record']= $id_record;
                $data['action']   = 'deallocated_notice';
                $this->view->display('admin/email_message/private/add_notice_form_view', $data);
            break;
        }
    }

    public function get_users_category(){
        checkAdminAjax('manage_messages');

        $id_category = intVal($_POST['id_category']);

        $this->load->model('Category_Support_Model', 'category_support');

        $categoryData= $this->category_support->get_support_category(array('id_record'=> $id_category));

        if(empty($categoryData))
            exit('<option value="">Not user</option>');

        $content = '';
        $usersId = $usersFullName = array();

        $usersId = explode(',', $categoryData['user_list']);
        $usersFullName = explode(',', $categoryData['full_name']);

        foreach($usersId as $i => $id){
            $content .= '<option value="' . $id . '">' . $usersFullName[$i] . '</option>';
        }

        exit($content);
    }

    public function ajax_emai_mess_operation(){
        checkAdminAjax('admin_site');

        $this->load_main();

        $operation = $this->uri->segment(3);

        switch($operation){
            case 'assign_user':
                $validator_rules = array(
                    array(
                        'field' => 'category_support',
                        'label' => 'Category of support',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                    array(
                        'field' => 'user_id',
                        'label' => 'User id',
                        'rules' => array('required' => '', 'integer' => '')
                    ),
                );
                $this->validator->set_rules($validator_rules);

                if(!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $id_record = intVal($_POST['id_record']);

                if(!$this->email_message->check_email_message(array('id_record' => $id_record)))
                    jsonResponse('Error: This email message doesn\'t found.');

                $update = array(
                    'id_suppcat'     => intVal($_POST['category_support']),
                    'attach_user'    => intVal($_POST['user_id']),
                    'status_record'  => 'waiting'
                );

                if($this->email_message->update_email_message($id_record, $update)){
                    jsonResponse('Category of support was changed successfully.', 'success');
                } else {
                    jsonResponse('Error: You cannot changed this category of support now. Please try later.');
                }
            break;

            case 'remove_email_message':
                $id_record = intVal($_POST['record']);

                if(!$this->email_message->check_email_message(array('id_record' => $id_record)))
                    jsonResponse('This email message doesn\'t found.');

                if($this->email_message->delete_email_message($id_record))
                    jsonResponse('The email message has been successfully removed.', 'success');
                else
                    jsonResponse('Error: You cannot remove this email message now. Please try again later.');
            break;
        }
    }

    public function private_ajax_operation(){
        checkAdminAjax('manage_email_messages');

        $this->load_main();

        $operation = $this->uri->segment(3);

        switch($operation){
            case 'assign_me_message':
                $id_record = intVal($_POST['id_record']);
                $id_user   = $this->session->id;

                $record = $this->email_message->get_email_message(array('id_record' => $id_record));

                if (empty($record))
                    jsonResponse('This email message doesn\'t found.');

                if(!empty($record['attach_user']) && $record['attach_user'] != $id_user)
                    jsonResponse('This email message already assigned to another ep staff.');

                $validator_rules = array(
                    array(
                        'field' => 'category_support',
                        'label' => 'Category of support',
                        'rules' => array('required' => '', 'integer' => '')
                    )
                );
                $this->validator->set_rules($validator_rules);

                if(!$this->validator->validate())
                    jsonResponse($this->validator->get_array_errors());

                $update = array(
                    'id_suppcat'     => intVal($_POST['category_support']),
                    'attach_user'    => $id_user,
                    'status_record'  => 'waiting'
                );

                if($this->email_message->update_email_message($id_record, $update)){
                    jsonResponse('Category of support was changed successfully.', 'success');
                } else {
                    jsonResponse('Error: You cannot changed this category of support now. Please try later.');
                }
            break;

            case 'resovled_notice':
                $id_record = intVal($_POST['id_record']);
                $id_user   = $this->session->id;

                $record = $this->email_message->get_email_message(array('id_record' => $id_record, 'id_user_assign' => $id_user));

                if(empty($record))
                    jsonResponse('This email message doesn\'t found.');

                $this->load->model('Category_Support_Model', 'category_support');

                $userInfo  = $this->category_support->get_staff(array('id_user'=> $id_user));

                $newNotice = array(
                    'add_date'      => date('Y-m-d H:i:s'),
                    'add_by'        => $userInfo['fname'] . ' ' . $userInfo['lname'] . ' - ' . $userInfo['gr_name'],
                    'id_user_from'  => $id_user,
                    'assign_user'   => '---',
                    'id_user_assign'=> '',
                    'notice'        => cleanInput($_POST['notice_message']),
                    'status'        => 'Resolved'
                );

                if(!empty($record['notice_record']))
                    $recordNotice = json_decode($record['notice_record'], true);

                $recordNotice[] = $newNotice;

                $update = array(
                    'notice_record' => json_encode($recordNotice),
                    'status_record'=> 'resovled'
                );

                if($this->email_message->update_email_message($id_record, $update)){
                    jsonResponse('The notification message has been successfully added', 'success');
                } else {
                    jsonResponse('Error: You cannot added this notification message now. Please try later.');
                }
            break;

            case 'assign_to_another':
                $id_record = intVal($_POST['id_record']);
                $id_user   = $this->session->id;
                $id_assign = intVal($_POST['user_id']);
                $id_category= intVal($_POST['category_support']);

                $record = $this->email_message->get_email_message(array('id_record' => $id_record, 'id_user_assign' => $id_user));

                if(empty($record))
                    jsonResponse('This email message doesn\'t found.');

                $this->load->model('Category_Support_Model', 'category_support');

                $userInfo  = arrayByKey($this->category_support->get_staff_ep(array('id_users'=> $id_user . ',' . $id_assign)), 'idu');

                $newNotice = array(
                    'add_date'      => date('Y-m-d H:i:s'),
                    'add_by'        => $userInfo[$id_user]['fname']  . ' ' . $userInfo[$id_user]['lname']  . ' - ' . $userInfo[$id_user]['gr_name'],
                    'id_user_from'  => $id_user,
                    'assign_user'   => $userInfo[$id_assign]['fname']. ' ' . $userInfo[$id_assign]['lname']. ' - ' . $userInfo[$id_assign]['gr_name'],
                    'id_user_assign'=> $id_assign,
                    'notice'        => cleanInput($_POST['notice_message']),
                    'status'        => 'Waiting',
                );

                if(!empty($record['notice_record']))
                    $recordNotice = json_decode($record['notice_record'], true);

                $recordNotice[] = $newNotice;

                $update = array(
                    'notice_record' => json_encode($recordNotice),
                    'status_record' => 'waiting',
                    'attach_user'   => $id_assign,
                    'id_suppcat'    => $id_category
                );

                if($this->email_message->update_email_message($id_record, $update)){
                    jsonResponse('The notification message has been successfully added', 'success');
                } else {
                    jsonResponse('Error: You cannot added this notification message now. Please try later.');
                }
            break;

            case 'deallocated_notice':
                $id_record = intVal($_POST['id_record']);
                $id_user   = $this->session->id;

                $record = $this->email_message->get_email_message(array('id_record' => $id_record, 'id_user_assign' => $id_user));

                if(empty($record))
                    jsonResponse('This email message doesn\'t found.');

                $this->load->model('Category_Support_Model', 'category_support');

                $userInfo  = $this->category_support->get_staff(array('id_user'=> $id_user));

                $newNotice = array(
                    'add_date'      => date('Y-m-d H:i:s'),
                    'add_by'        => $userInfo['fname']  . ' ' . $userInfo['lname']  . ' - ' . $userInfo['gr_name'],
                    'id_user_from'  => $id_user,
                    'assign_user'   => '---',
                    'id_user_assign'=> '',
                    'notice'        => cleanInput($_POST['notice_message']),
                    'status'        => 'Not resolved'
                );

                if(!empty($record['notice_record']))
                    $recordNotice = json_decode($record['notice_record'], true);

                $recordNotice[] = $newNotice;

                $update = array(
                    'notice_record' => json_encode($recordNotice),
                    'status_record' => 'not resolved',
                    'attach_user'   => 0,
                    'id_suppcat'    => 0
                );

                if($this->email_message->update_email_message($id_record, $update)){
                    jsonResponse('The notification message has been successfully added', 'success');
                } else {
                    jsonResponse('Error: You cannot added this notification message now. Please try later.');
                }
            break;
        }
    }

	public function user_view(){
		$key_link = cleanInput($this->uri->segment(3));

		if(empty($key_link)) {
            show_404();
        }

        /** @var Mail_Messages_Model $mailMessagesModel */
        $mailMessagesModel = model(Mail_Messages_Model::class);

        $email_message = $mailMessagesModel->findOneBy([
            'scopes' => [
                'key' => $key_link,
            ],
            'with' => ['content'],
        ]);

        if(empty($email_message)) {
			show_404();
        }

		echo $email_message['content']['message'];
	}
}
