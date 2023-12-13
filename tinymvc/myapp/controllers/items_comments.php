<?php

use App\Common\Buttons\ChatButton;
use App\Filesystem\UserFilePathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;

/**
 * Item comments application controller.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @property \ItemComments_Model              $itemcomments
 * @property \TinyMVC_Load                    $load
 * @property \TinyMVC_View                    $view
 * @property \TinyMVC_Library_URI             $uri
 * @property \TinyMVC_Library_Session         $session
 * @property \TinyMVC_Library_Cookies         $cookies
 * @property \TinyMVC_Library_Upload          $upload
 * @property \TinyMVC_Library_validator       $validator
 *
 */
class Items_Comments_Controller extends TinyMVC_Controller
{
    public function my()
    {
        checkIsLogged();
        checkPermision('write_comments_on_item,manage_seller_item_comments');
        checkGroupExpire();

        views()->assign('filterByItem', uri()->uri_to_assoc()['item']);
        views()->display('new/header_view');
        views()->display('new/items_comments/my/index_view');
        views()->display('new/footer_view');
    }

    public function administration()
    {
		checkAdmin('moderate_content');

		$this->load_main();
		$this->load->model('Items_Model', 'items');
		$id_item = id_from_link($this->uri->segment(3));
		if($id_item) {
			$data['item'] = $this->items->get_item($id_item, '*');
        }
		$data['last_items_comments_id'] = $this->itemcomments->get_items_comments_last_id();

		$this->view->assign($data);
		$this->view->assign('title', 'Items comments');
		$this->view->display('admin/header_view');
		$this->view->display('admin/items_comments/index_view');
		$this->view->display('admin/footer_view');
	}

    public function ajax_list_my_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
		checkPermision('write_comments_on_item,manage_seller_item_comments');

		$is_buyer = have_right('write_comments_on_item');
		$is_seller = ! $is_buyer;

		$conditions = array(
			'per_p' => intVal($_POST['iDisplayLength']),
			'start' => intVal($_POST['iDisplayStart'])
		);

        if (!empty($_POST['create_from'])) {
			$start_date = cleanInput($_POST['create_from']);
			$conditions['date_from'] = date('Y-m-d', strtotime($start_date)) . ' 00:00:00';
		}

		if (!empty($_POST['crate_to'])) {
			$added_finish = cleanInput($_POST['crate_to']);
			$conditions['date_to'] = date('Y-m-d', strtotime($added_finish)) . ' 23:59:59';
        }

		if (isset($_POST['status'])){
			$conditions['status'] = cleanInput($_POST['status']);
		}

		if (isset($_POST['keywords'])) {
			$conditions['keywords'] = cleanInput(cut_str($_POST['keywords']));
		}

		if (isset($_POST['has_reply']) && in_array($_POST['has_reply'], array(0, 1))) {
			$conditions['has_replies'] = (bool) $_POST['has_reply'];
		}

        if (isset($_POST['item'])) {
            $conditions['item'] = (int) $_POST['item'];
        }

        $conditions['sort_by'] = flat_dt_ordering($_POST, array(
			'item'       => 'item_title',
			'created_at' => 'ic.comment_date'
		));

		if ($is_buyer) {
			$conditions['user'] = id_session();
		} else {
			$conditions['not_id_user'] = $conditions['id_seller'] = privileged_user_id();
		}

		$comments = model('itemcomments')->get_comments_my($conditions);
		$records_total = model('itemcomments')->get_comments_my_count($conditions);
		$output = array(
			"sEcho"                => intval($_POST['sEcho']),
			"iTotalRecords"        => $records_total,
			"iTotalDisplayRecords" => $records_total,
			'aaData'               => array()
		);

		if(empty($comments)) {
			jsonResponse('', 'success', $output);
		}

		$items_list = implode(',', array_column($comments, 'id_item', 'id_item'));
        $result_ratings = array_column(model('ItemsReview')->getRatingsByItems($items_list), 'raiting', 'id_item');

        foreach($comments as $row){
            $item_id = (int) $row['id_item'];
            $item_title = $row['item_title'];
            $comment_id = (int) $row['id_comm'];
            $has_replies = (bool) (int) $row['nr_replies'];

            //region Actions
            //region Delete button
            $delete_button = null;
            /* if ($is_buyer) {
                $delete_button_url = __SITE_URL . "items_comments/ajax_comment_operation/delete_comment";
				$delete_button_title = "Delete";
				$delete_button_message = "Are you sure you want to delete this comment?";
				if ($has_replies) {
					$delete_button = "
						<a class=\"dropdown-item disabled call-systmess\"
							data-message=\"The comment cannot be deleted if it has already a reply.\"
							data-type=\"info\">
							<i class=\"ep-icon ep-icon_trash-stroke\"></i>
							<span>{$delete_button_title}</span>
						</a>
					";
				} else {
					$delete_button = "
						<a class=\"dropdown-item confirm-dialog\"
							data-href=\"{$delete_button_url}\"
							data-message=\"{$delete_button_message}\"
							data-callback=\"deleteComment\"
							data-comment=\"{$comment_id}\"
							data-item=\"{$item_id}\">
							<i class=\"ep-icon ep-icon_trash-stroke\"></i>
							<span>{$delete_button_title}</span>
						</a>
					";
				}
            } */
            //endregion Delete button

            //region Edit button
            $edit_button = null;
            if ($is_buyer) {
                $edit_button_url = __SITE_URL . "items_comments/popup_forms/edit_main_comment/{$comment_id}";
				$edit_button_title = "Edit";
				$edit_class_listener = $has_replies ? 'disabled call-systmess' : 'fancybox.ajax fancyboxValidateModal';
				$edit_additional_attrs = $has_replies ? 'data-message="The comment cannot be edited if it has already a reply." data-type="info"' : '';
                $edit_button = "
                    <a rel=\"edit\"
                        class=\"dropdown-item {$edit_class_listener}\"
                        data-fancybox-href=\"{$edit_button_url}\"
                        data-title=\"{$edit_button_title}\" {$edit_additional_attrs}>
                        <i class=\"ep-icon ep-icon_pencil\"></i>
                        <span>{$edit_button_title}</span>
                    </a>
                ";
            }
            //endregion Edit button

			//region View button
			$class_listener = $has_replies ? 'fancybox.ajax fancyboxValidateModal' : 'disabled call-systmess';
			$additional_attrs = $has_replies ? '' : 'data-message="No replies" data-type="info"';
			$view_button_url = __SITE_URL . "items_comments/popup_forms/comment_details/{$comment_id}";
			$view_button_title="View replies";
			$view_button = "
				<a rel=\"replies\"
					class=\"dropdown-item {$class_listener}\"
					data-fancybox-href=\"{$view_button_url}\"
					data-title=\"{$view_button_title}\" {$additional_attrs}>
					<i class=\"ep-icon ep-icon_comments-stroke\"></i>
					<span>{$view_button_title}</span>
				</a>
			";
			//endregion View button

			//region Reply button
			$reply_button_url = __SITE_URL . 'items_comments/popup_forms/add_reply/' . $comment_id;
			$reply_button_title = 'Reply';
			$reply_button = "
				<a rel=\"reply\"
					class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
					data-fancybox-href=\"{$reply_button_url}\"
					data-title=\"{$reply_button_title}\">
					<i class=\"ep-icon ep-icon_reply-left-empty\"></i>
					<span>{$reply_button_title}</span>
				</a>
			";
			//endregion Reply button

			//region Report button
			$report_button = null;
			/* if ($is_seller) {
				$report_button_url = __SITE_URL . 'complains/popup_forms/add_complain/item_comment/' . $item_id . '/' . $row['id_user'];
				$report_button_title = 'Report this';
				$report_button = "
					<a rel=\"report\"
						class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
						data-fancybox-href=\"{$report_button_url}\"
						data-title=\"{$report_button_title}\">
						<i class=\"ep-icon ep-icon_warning-circle-stroke\"></i>
						<span>{$report_button_title}</span>
					</a>
				";
			} */
			//endrefion Report button

			$dropdown_delimiter = null;
			if ($is_buyer && (!empty($edit_button) || !empty($delete_button))) {
				$dropdown_delimiter = '<div class="dropdown-divider"></div>';
			}

            $actions = "
                <div class=\"dropdown\">
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                        <i class=\"ep-icon ep-icon_menu-circles\"></i>
                    </a>
					<div class=\"dropdown-menu dropdown-menu-right\">
						{$reply_button}
						{$view_button}
						{$report_button}
						{$dropdown_delimiter}
						{$edit_button}
						{$delete_button}
                        <a class=\"dropdown-item d-none d-md-block d-lg-block d-xl-none call-function\"
                            data-callback=\"dataTableAllInfo\"
                            href=\"#\"
                            target=\"_blank\">
                            <i class=\"ep-icon ep-icon_info-stroke\"></i>
                            <span>All info</span>
                        </a>
                    </div>
                </div>
            ";
            //endregion Actions

            //region Item
            //region Item rating
            $rating_number = empty($result_ratings) ? 0 : (int) $result_ratings[$row['id_item']];
            $rating = "
                <input class=\"rating-bootstrap\"
                    data-filled=\"ep-icon ep-icon_star txt-orange fs-16\"
                    data-empty=\"ep-icon ep-icon_star txt-gray-light fs-16\"
                    type=\"hidden\"
                    name=\"val\"
                    value=\"{$rating_number}\"
                    data-readonly>
            ";
            //endregion Item rating

            //region Categories
            $item_categories = json_decode("[{$row['item_breadcrumbs']}]", true);
            $item_category_breadcrumbs = array();
			if (!empty($item_categories)) {
				foreach ($item_categories as $category) {
                    foreach ($category as $category_id => $category_title)
                        $category_url = __SITE_URL . 'category/'. strForURL($category_title) . "/{$category_id}";
                        $item_category_breadcrumbs[] = "<a class=\"link\" href=\"{$category_url}\" target=\"_blank\">{$category_title}</a>";
				}
            }
            $item_category_breadcrumbs = implode('<span> / </span>', $item_category_breadcrumbs);
            //endregion categories

			$item_image_name = $row['photo_name'];
            $item_image_url = getDisplayImageLink(array('{ID}' => $item_id, '{FILE_NAME}' => $item_image_name), 'items.main', array( 'thumb_size' => 1 ));
    		$item_url = __SITE_URL . 'item/' . strForURL($item_title) . '-' . $item_id . '/comments#li-comment-' . $comment_id;
			$highlight_title_bg = $has_replies ? 'bg-green' : 'bg-orange';
			$highlight_title_text = $has_replies ? 'Replied' : 'Not replied';

			$item = "
				<div class=\"flex-card relative-b\">
					<div class=\"main-data-table__item-actions\">
						<div class=\"main-data-table__item-action {$highlight_title_bg}\">
							<div class=\"text\">
								{$highlight_title_text}
							</div>
						</div>
					</div>
                    <div class=\"flex-card__fixed main-data-table__item-img main-data-table__item-img--h-auto image-card3\">
                        <span class=\"link\">
                            <img class=\"image\" src=\"{$item_image_url}\" alt=\"{$item_title}\"/>
                        </span>
                    </div>
                    <div class=\"flex-card__float\">
                        <div class=\"main-data-table__item-ttl\">
                            <a href=\"{$item_url}\"
                                class=\"display-ib link-black txt-medium\"
                                title=\"View item\"
                                target=\"_blank\">
                                {$item_title}
                            </a>
                        </div>
                        <div>{$rating}</div>
                        <div class=\"links-black\">{$item_category_breadcrumbs}</div>
                    </div>
                </div>
            ";
            //endregion Item

            //region Comment
            $comment_text = $row['comment'];
            $comment = "
                <div class=\"grid-text\">
                    <div class=\"grid-text__item\">
                        <div>
                            {$comment_text}
                        </div>
                    </div>
                </div>
            ";
			//endregion Comment

			$dt_row = array(
				'item' 	     => $item,
				'comment'    => $comment,
				'created_at' => getDateFormatIfNotEmpty($row['comment_date']),
				'actions'    => $actions,
			);

			if ($is_seller) {
				$dt_row['author'] = '<a href="' . getUserLink($row['username'], $row['id_user'], 'buyer') . '">' . $row['username'] . '</a>';
			}

			$output['aaData'][] = $dt_row;
		}

		jsonResponse('', 'success', $output);
    }

    public function ajax_list_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('moderate_content');

		$this->load_main();

		$conditions = array();

		if (isset($_POST['iDisplayStart'])) {
			$from = intval(cleanInput($_POST['iDisplayStart']));
			$till = intval(cleanInput($_POST['iDisplayLength']));
			$conditions['limit'] = $from . ',' . $till;
		}

		if (!empty($_POST['start_date'])) {
			$start_date = cleanInput($_POST['start_date']);
			$conditions['added_after_time'] = date('Y-m-d', strtotime($start_date));
		}

		if (!empty($_POST['finish_date'])) {
			$added_finish = cleanInput($_POST['finish_date']);
			$conditions['added_before_time'] = date('Y-m-d', strtotime($added_finish));
		}

		if (!empty($_POST['id_user'])) {
			$conditions['user'] = intval($_POST['id_user']);
		}

		if (!empty($_POST['id_item'])) {
			$conditions['item'] = intval($_POST['id_item']);
		}

		// in the itemsmodel - c.status IN ( ".$status." )
		if (isset($_POST['moderated'])) {
			$conditions['status'] = "'new'";
			if($_POST['moderated'] == 1) {
			$conditions['status'] = "'moderated'";
			}
		}

		if (isset($_POST['keywords']))
			$conditions['keywords'] = cleanInput(cut_str($_POST['keywords']));

		$sort_col = $_POST["mDataProp_" . intval($_POST['iSortCol_0'])];
		switch ($sort_col) {
			case 'author': $column = 'username'; break;
			case 'item': $column = 'title'; break;
			case 'text': $column = 'comment'; break;
			case 'added': $column = 'comment_date'; break;
		}

		$conditions['order_by'] = $column . ' ' . $_POST['sSortDir_0'];
		$conditions['map_tree'] = false;

		$itemcomments = $this->itemcomments->get_comments($conditions);
		$records_total = $this->itemcomments->count_comments($conditions);

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $records_total,
			"iTotalDisplayRecords" => $records_total,
			'aaData' => array()
		);

		if(empty($itemcomments))
			jsonResponse('', 'success', $output);

		foreach ($itemcomments as $itemcomment){
			$moderate_btn = '<a class="confirm-dialog ep-icon ep-icon_sheild-nok txt-red" data-callback="moderate_comment" title="Moderate comment" data-comment="' . $itemcomment['id_comm'] . '" data-message="Are you sure want moderate this comment?"></a>';

			if ($itemcomment['status'] == "moderated")
				$moderate_btn = '<a class="ep-icon ep-icon_sheild-ok txt-green" title="Moderated comment"/>';

			$text_dots = "";
			if(strlen($itemcomment['comment']) > 150){
				$text_dots = "<a rel='item_comment_details' title='View details'><p class='tac'>...</p></a>";
			}

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $itemcomment['id_user'], 'recipientStatus' => $itemcomment['user_status']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatUserView = $btnChatUser->button();

			$output['aaData'][] = array(
				"checkboxes" => '<input type="checkbox" class="check-item-comment mr-5 pull-left" data-id-item-comment="' . $itemcomment['id_comm'] . '">' .
						$itemcomment['id_comm'] . "</br>" .
						"<a class='ep-icon ep-icon_plus' rel='item_comment_details' title='View details'></a>",
				"author" =>
					'<div class="pull-left">'
						. '<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by author" data-value-text="' . $itemcomment['username'] . '" data-value="' . $itemcomment['id_user'] . '" data-title="Author" data-name="id_user"></a>'
						. '<a class="ep-icon ep-icon_user" title="View user\'s profile" href="' . __SITE_URL . 'usr/' . strForURL($itemcomment['username']) .
						'-' . $itemcomment['id_user'] . '"' . '></a>'
						. $btnChatUserView
					. "</div>"
					. "<div class='clearfix'></div><span>" . $itemcomment['username'] . "</span>",
				"item" => '<div>'
						. '<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by item" data-value-text="' . $itemcomment['title'] . '" data-value="' . $itemcomment['id_item'] . '" data-title="Item" data-name="id_item"></a>'
						. '<a class="ep-icon ep-icon_item txt-orange" title="View item" href="' . __SITE_URL . 'item/' . strForURL($itemcomment['title']) .
						'-' . $itemcomment['id_item'] . '"' . '></a>' .
						"</div><div class='clearfix'></div><span>" . $itemcomment['title'] . "</span>",
				"text" => "<p class='h-50 hidden-b' title='" . $itemcomment['comment'] . "'>" . $itemcomment['comment'] . "</p>" . $text_dots,
				"full_text" =>  $itemcomment['comment'],
				"added" => formatDate($itemcomment['comment_date']),
				"actions" =>
					'<a class="ep-icon ep-icon_visible fancybox.ajax fancybox com-tree" href="items_comments/popup_forms/comments_tree/' . $itemcomment['id_item'] . '" title="View question together with answers and questions" data-title="View details" data-w="700" data-scroll-block="li-comment-' . $itemcomment['id_comm']. '"></a>'
					. $moderate_btn  .
					'<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" title="Edit comment" data-title="Edit comment" href="items_comments/popup_forms/edit_admin_comment/' .$itemcomment['id_comm'] . '"></a>
					<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="delete_comment_dt" data-message="Are you sure want to delete this comment?" title="Delete comment" data-comment="' . $itemcomment['id_comm'] . '"></a>'
			);
		}

		jsonResponse('', 'success', $output);
	}

    public function ajax_comments_administration_operation()
    {
		if (!isAjaxRequest())
			show_404();

		if (!logged_in())
			jsonResponse(translate("systmess_error_should_be_logged"));

		if (!have_right('moderate_content'))
			jsonResponse(translate("systmess_error_rights_perform_this_action"));

		$action = $this->uri->segment(3);
		$this->load_main();

		switch($action){
			case 'check_new':
				$lastId = $_POST['lastId'];
				$items_comments_count = $this->itemcomments->get_count_new_items_comments($lastId);

				if($items_comments_count){
					$last_items_comments_id = $this->itemcomments->get_items_comments_last_id();
					jsonResponse('','success', array('nr_new' => $items_comments_count,'lastId' => $last_items_comments_id));
				}else
					jsonResponse('New comments about items do not exist');
			break;
			case "moderate":
				$checked_comments = implode(',',$_POST['checked_comments']);

				if (empty($checked_comments))
					jsonResponse('There are no reviews to be moderated.');

				if ($this->itemcomments->moderate_comments($checked_comments))
					jsonResponse('Your changes have been saved and will be visible soon.', 'success');
				else
					jsonResponse('Error moderation');
			break;
			// REVIEW
			case "delete":
				$checked_comments = implode(',',$_POST['checked_comments']);

				if (empty($checked_comments))
					jsonResponse('There are no reviews to be deleted.');

				$list = $this->itemcomments->get_comments_to_delete($checked_comments, $checked_comments);
				$comments_owners = $this->itemcomments->get_comments_owners($list);

				if ($this->itemcomments->delete_comment($list)){
					$this->load->model('User_Statistic_Model', 'statistic');
					$statistic_array = $this->statistic->prepare_user_array(
						$comments_owners,
						'id_user',
						array('item_comments_wrote' => -1)
					);
					$this->statistic->set_users_statistic($statistic_array);

					jsonResponse('Comment(s) deleted', 'success');
				}else
					jsonResponse('The comment has not been deleted');
			break;
			case 'edit':
				$validator = $this->validator;
				$validator_rules = array(
					array(
						'field' => 'description',
						'label' => 'Description',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$data = array(
					'comment' => cleanInput($_POST['description'])
				);

				if ($this->itemcomments->updateComment(intVal($_POST['comment']), $data)) {
					jsonResponse("The comment has been successfully updated!", 'success');
				} else {
					jsonResponse("The comment wasn't updated. Please try again later.");
				}
            break;
            default:
                jsonResponse('The provided path is not found on this server');

            break;
		}
	}

    public function ajax_comment_operation()
    {
		if(!isAjaxRequest()) {
			headerRedirect();
        }

		$this->load_main();

        /** @var Itemcomments_Model $itemCommentsModel */
        $itemCommentsModel = model(Itemcomments_Model::class);

		$type = $this->uri->segment(3);
		switch($type){
			case 'add_main_comment':
				is_allowed("freq_allowed_add_comment");
				checkPermisionAjax('write_comments_on_item');

				$validator_rules = array(
					array(
						'field' => 'description',
						'label' => 'Comment text',
						'rules' => array('required' => '', 'max_len[500]' => '')
					),
					array(
						'field' => 'item',
						'label' => 'Item info',
						'rules' => array('required' => '')
					)
                );

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors() );
                }

                $prospective_id_item = id_from_link($_SERVER['HTTP_REFERER']);
                $incoming_id_item = (int) $_POST['item'];

                if ($prospective_id_item != $incoming_id_item || !model(Items_Model::class)->item_is_accessible($incoming_id_item)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

				$insert = array(
					'comment'		=> $_POST['description'],
					'id_user'		=> id_session(),
					'id_item'		=> (int) $_POST['item']
                );

                if (empty($id_comment = $itemCommentsModel->setComment($insert))) {
                    jsonResponse('The comment has not been added.');
                }

                model(User_Statistic_Model::class)->set_users_statistic(array($insert['id_user'] => array('item_comments_wrote' => 1)));

                $data = array(
                    'comments_user_info'    => true,
                    'comments'              => array($itemCommentsModel->getComment($id_comment))
                );

                views()->assign($data);
                $response = array(
                    'reply_to'  => 0,
                    'comment'   => views()->fetch('new/items_comments/my/item_main_comment_view')
                );

                jsonResponse('The comment has been successfully saved.', 'success', $response);
			break;
			case 'edit_main_comment':
				is_allowed("freq_allowed_add_comment");
				checkPermisionAjax('write_comments_on_item,moderate_content');

				$validator_rules = array(
					array(
						'field' => 'description',
						'label' => 'Comment text',
						'rules' => array('required' => '', 'max_len[500]' => '')
					),
					array(
						'field' => 'comment',
						'label' => 'Comment info',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse( $this->validator->get_array_errors());
                }

                $update = array('comment' => $_POST['description']);
				$id_comment = (int) $_POST['comment'];

				if (!have_right('moderate_content')){
					if (!$itemCommentsModel->isMyComment($id_comment, $this->session->id)) {
						jsonResponse('This comment is not yours.','error');
					}

					if ($itemCommentsModel->count_comments(array('reply_to_comm' => $id_comment))) {
						jsonResponse('You cannot edit this comment.');
                    }

                    $update['status'] = 'new';
				}

				if (!$itemCommentsModel->updateComment($id_comment, $update)) {
					jsonResponse('The changes have not been saved.');
                }

                $response = array(
                    'c_description' => $update['comment'],
                    'c_id'          => $id_comment
                );

                jsonResponse('All comment changes have been successfully saved.', 'success', $response);
			break;
			case 'add_reply':
				is_allowed("freq_allowed_add_comment");
				checkPermisionAjax('write_comments_on_item,manage_seller_item_comments');

				$validator_rules = [
					[
						'field' => 'description',
						'label' => 'Comment text',
						'rules' => ['required' => '', 'max_len[500]' => '']
                    ],
					[
						'field' => 'comment',
						'label' => 'Reply to',
						'rules' => ['required' => '']
                    ]
                ];

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors() );
				}

                $request = request()->request;
                $repliedCommentId = $request->getInt('comment');

				if (empty($comment = $itemCommentsModel->get_comment_simple($repliedCommentId))) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if ( ! have_right('write_comments_on_item')) {
                    /** @var Items_Model $itemsModel */
                    $itemsModel = model(Items_Model::class);

					$commentItem = $itemsModel->get_item_simple($comment['id_item'], 'id_seller');

					if ( ! is_privileged('user', $commentItem['id_seller'])) {
						messageInModal(translate('systmess_error_permission_not_granted'));
					}
				}

				if ($comment['level'] >= 9) {
					jsonResponse('This comment is last in the tree comments.');
				}

				$insert = [
					'reply_to_comm'	=> $repliedCommentId,
					'comment'		=> cleanInput($request->get('description')),
					'id_user'		=> id_session(),
					'id_item'		=> (int) $comment['id_item'],
					'level'			=> ++$comment['level']
                ];

				$insert['general_comment'] = $comment['general_comment'] ?: $repliedCommentId;

				if (empty($insertedCommentId = $itemCommentsModel->setComment($insert))) {
					jsonResponse('The comment has not been added.');
				}

                /** @var User_Statistic_Model $userStatisticsModel */
                $userStatisticsModel = model(User_Statistic_Model::class);
				$userStatisticsModel->set_users_statistic([$insert['id_user'] => ['item_comments_wrote' => 1]]);

				$resp = [
                    'reply_to'  => $repliedCommentId,
                    'comment'   => views()->fetch('new/items_comments/item_reply_view', [
                        'comments' => [$itemCommentsModel->getComment($insertedCommentId)]
                    ])
                ];

				jsonResponse('The comment has been successfully saved.','success', $resp);

			break;
			case 'edit_reply':
				is_allowed("freq_allowed_add_comment");
				checkPermisionAjax('write_comments_on_item,manage_seller_item_comments,moderate_content');

				$validator_rules = array(
					array(
						'field' => 'description',
						'label' => 'Comment text',
						'rules' => array('required' => '', 'max_len[500]' => '')
					),
					array(
						'field' => 'comment',
						'label' => 'Comment info',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if ( ! $this->validator->validate()) {
					jsonResponse( $this->validator->get_array_errors());
				}

				$id_comment = (int) $_POST['comment'];

				if ( ! $itemCommentsModel->is_comment_exists($id_comment)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if ( ! have_right('moderate_content')) {
					if ( ! $this->itemcomments->isMyComment($id_comment, privileged_user_id())) {
						jsonResponse(translate('systmess_error_invalid_data'));
					}

					if ($this->itemcomments->count_comments(array('reply_to_comm' => $id_comment))) {
						jsonResponse('You cannot edit the comment which has already a reply');
					}
				}

				$update = array(
					'comment' => cleanInput($_POST['description'])
				);

				if ( ! have_right('moderate_content')) {
					$update['status'] = 'new';
				}

				if ( ! $itemCommentsModel->updateComment($_POST['comment'], $update)) {
					jsonResponse('The changes have not been saved.');
				}

				$resp = array(
					'c_description' => $update['comment'],
					'c_id' => $id_comment
				);

				jsonResponse('All comment changes have been successfully saved.', 'success',$resp);

			break;
			case 'show':
				$pagination = (bool) $_POST['pagination'];
				$comment_params = array(
					'item' => intVal($_POST['comments']),
					'order' => cleanInput($_POST['order']),
					'parent' => 0
				);

				if($pagination){
					$comment_params['per_p'] = config('item_comments_per_p');
				}

				$comments = $itemCommentsModel->get_comments($comment_params);
				foreach ($comments as $comment) {
					$comments_list[] = $comment['id_comm'];
				}
				if(!empty($comments_list)){
					$comments_children_params = array(
						'item' => $comment_params['item'],
						'order' => 'date_asc',
						'general_comment' => implode(',', $comments_list),
						'map_tree' => false
					);

					$comments_children = $this->itemcomments->get_comments($comments_children_params);
				}

				$item_comments = array_merge($comments, $comments_children);
				$data['comments'] = $itemCommentsModel->comment_map($item_comments);

				$data['comments_user_info'] = true;

				$data['page_comments_all'] = 1;
                $this->view->assign($data);
                $list_comment = $this->view->fetch('new/items_comments/list_comments_view');

				jsonResponse('','success',array('html' => $list_comment, 'count' => count($data['comments'])));
			break;
			case 'moderate_comment':
				is_allowed("freq_allowed_add_comment");
				if(!have_right('moderate_content'))
					jsonResponse ('You have no permission to moderate comments.');

				$id_comment = $_POST['comment'];
				if($itemCommentsModel->updateComment($id_comment, array('status' => 'moderated'))){
					jsonResponse('The comment has been moderated', 'success');
				}else{
					jsonResponse('You cannot moderate the comment now. Please try again later.');
				}
			break;
            default:
                jsonResponse('The provided path is not found on this server');

            break;
		}
    }

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();

		$action = cleanInput($this->uri->segment(3));
		$id = (int) $this->uri->segment(4);
        $this->load_main();

		switch ($action) {
            case 'add_main_comment':
                checkPermisionAjaxModal('write_comments_on_item');

                if (empty($id) || !model('items')->item_exist($id)) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

				views()->assign(array(
                    'is_dialog'   => (bool) request()->query->getInt('dialog') ?: false,
                    'action'      => getUrlForGroup("/items_comments/ajax_comment_operation/add_main_comment"),
                    'item'        => $id,
                    'webpackData' => "webpack" === request()->headers->get("X-Script-Mode", "legacy"),
				));
				views()->display('new/items_comments/my/add_comment_form_view');

			break;
            case 'edit_main_comment':
				checkPermisionAjaxModal('write_comments_on_item,moderate_content');

                if (empty($id) || empty($comment_detail = model('itemcomments')->getComment($id))) {
                    messageInModal(translate('systmess_error_invalid_data'));
				}

				if ( ! have_right('moderate_content') && $comment_detail['id_user'] != privileged_user_id()) {
					messageInModal(translate('systmess_error_invalid_data'));
				}

                if ( ! have_right('moderate_content') && model('itemcomments')->count_comments(array('reply_to_comm' => $id))) {
                    messageInModal('You cannot edit the comment which has already a reply.');
                }

				views()->assign(array(
                    'comment_info' => $comment_detail,
                    'is_dialog'    => (bool) request()->query->getInt('dialog') ?: false,
                    'action'       => getUrlForGroup('/items_comments/ajax_comment_operation/edit_main_comment'),
				));
				views()->display('new/items_comments/my/edit_comment_form_view');

			break;
            case 'add_reply':
				checkPermisionAjaxModal('write_comments_on_item,manage_seller_item_comments');

                if (empty($id) || empty($comment = model('itemcomments')->getComment($id))) {
                    messageInModal(translate('systmess_error_invalid_data'));
				}

				if ( ! have_right('write_comments_on_item') && ! is_privileged('user', $comment['id_seller'])) {
					messageInModal(translate('systmess_error_permission_not_granted'));
				}

				if ($comment['level'] >= 9) {
					messageInModal('This comment is last in the tree comments.');
				}

                views()->assign(array(
                    'action'    =>  getUrlForGroup('/items_comments/ajax_comment_operation/add_reply'),
                    'is_dialog' => (bool) request()->query->getInt('dialog') ?: false,
                    'id_comm'   => $id,
				));
				views()->display('new/items_comments/my/add_comment_reply_form_view');

			break;
            case 'edit_reply':
				checkPermisionAjaxModal('write_comments_on_item,manage_seller_item_comments,moderate_content');

                if (empty($id) || !model('itemcomments')->is_comment_exists($id)) {
                    messageInModal(translate('systmess_error_invalid_data'));
				}

				if ( ! have_right('moderate_content')) {
					if ( ! model('itemcomments')->isMyComment($id, privileged_user_id())) {
						messageInModal(translate('systmess_error_permission_not_granted'));
					}

					if (model('itemcomments')->count_comments(array('reply_to_comm' => $id))) {
						messageInModal('You cannot edit the comment which has already a reply.');
					}
				}

                views()->assign(array(
                    'comment_info' => model('itemcomments')->getComment($id),
                    'is_dialog'    => (bool) request()->query->getInt('dialog') ?: false,
                    'action'       => getUrlForGroup('/items_comments/ajax_comment_operation/edit_reply'),
				));

				views()->display('new/items_comments/my/edit_comment_reply_form_view');

			break;
            case 'edit_admin_comment':
                checkPermisionAjaxModal('moderate_content');

                $comment_id = (int) $id;
                if (
                    empty($comment_id) ||
                    !model('itemcomments')->is_comment_exists($comment_id)
                ) {
                    messageInModal("This comment doesn't exist");
                }

				views()->display('admin/items_comments/comment_form_view', array(
                    'comment_info' => model('itemcomments')->getComment($comment_id),
                    'is_dialog'    => (bool) request()->query->getInt('dialog') ?: false,
                ));

			break;
			case 'comments_tree':
                $this->load->model('items_model', 'items');

                $item_id = (int) $id;
                if(
                    empty($item_id) ||
                    empty($item = model('items')->get_item($item_id))
                ) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                /** @var FilesystemProviderInterface */
                $storageProvider = $this->getContainer()->get(FilesystemProviderInterface::class);
                $publicDisk = $storageProvider->storage('public.storage');

                $this->view->assign([
                    'makeUserPhoto' => fn (int $commentId, string $userPhoto): string => $publicDisk->url(
                        UserFilePathGenerator::imagesThumbUploadFilePath($commentId, $userPhoto)
                    ),
                ]);

				$this->view->display("{$this->view_folder}admin/items_comments/popup_comments_all_view", array(
                    'is_modal'           => true,
                    'comments'           => model('itemcomments')->get_comments(array('item' => $item_id)),
                    'title_item'         => $item['title'],
                    'comments_user_info' => true,
                ));
			break;
			case 'comment_details':
                checkPermisionAjaxModal('write_comments_on_item,manage_seller_item_comments');

                $comment_id = (int) $this->uri->segment(4);
                if (empty($comment_id) || empty($comment = $this->itemcomments->getComment($comment_id))) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }
				$comment['replies'] = $this->itemcomments->get_comment_replies($comment_id);

                $this->view->assign('unwrap', true);
				$this->view->assign(array(
                    'is_dialog' => true,
					'comments'	=> array($comment),
					'item'		=> array(
						'id_item'	=> $comment['id_item'],
						'title'		=> $comment['title'],
						'id_seller'	=> $comment['id_seller'],
					)
				));

                $this->view->display('new/items_comments/my/comment_replies');

            break;
            default:
                messageInModal('The provided path is not found on this server');
            break;
		}
    }

    private function load_main()
    {
		$this->load->model('Itemcomments_Model', 'itemcomments');
	}
}
