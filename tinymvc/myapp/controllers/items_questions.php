<?php

use App\Common\Buttons\ChatButton;
use App\Email\AnswerItemQuestion;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * Item questions application controller.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 *
 * @property \ItemQuestions_Model             $itemquestions
 * @property \TinyMVC_Load                    $load
 * @property \TinyMVC_View                    $view
 * @property \TinyMVC_Library_URI             $uri
 * @property \TinyMVC_Library_Session         $session
 * @property \TinyMVC_Library_Cookies         $cookies
 * @property \TinyMVC_Library_Upload          $upload
 * @property \TinyMVC_Library_validator       $validator
 *
 */
Class Items_Questions_Controller extends TinyMVC_Controller
{
    public function my()
    {
        checkIsLogged();
        checkPermision('write_questions_on_item,reply_questions');
        checkGroupExpire();

        if (!have_right('buy_item')) {
            checkHaveCompany();
        }

        $uri = uri()->uri_to_assoc();

        views()->assign([
            'id_item'           => $uri['item'] ?: null,
            'id_question'       => $uri['question'] ?: null,
            'questionsDtUrl'    => __SITE_URL . '/items_questions/ajax_list_my_questions_dt' . (isBackstopEnabled() ? '?backstop=' . request()->query->getInt('backstop') : ''),
        ]);

        views(['new/header_view', 'new/items_questions/my/index_view', 'new/footer_view']);
    }

    public function administration()
    {
		checkAdmin('moderate_content');

		$this->load_main();
		$data['item'] = $this->items->get_item(id_from_link($this->uri->segment(3)), '*');
		$data['replied'] = $this->uri->segment(4);
		$data['last_items_questions_id'] = $this->itemquestions->get_items_questions_last_id();

		$this->view->assign($data);
		$this->view->assign('title', 'Items questions');
		$this->view->display('admin/header_view');
		$this->view->display('admin/items_questions/index_view');
		$this->view->display('admin/footer_view');
    }

    public function categories_administration()
    {
		checkAdmin('moderate_content');

		$this->load_main();

		$this->view->assign('title', 'Items categories questions');
		$this->view->display('admin/header_view');
		$this->view->display('admin/items_questions/categories/index_view');
		$this->view->display('admin/footer_view');
    }

    public function ajax_categories_list_admin_dt()
    {
		if (!isAjaxRequest())
			show_404();

		if (!logged_in())
			jsonDTResponse(translate("systmess_error_should_be_logged_in"));

		if (!have_right('moderate_content'))
			jsonDTResponse(translate("systmess_error_page_permision"));

		$this->load_main();

        $conditions = array();

        $sorting = [
            'limit' => intval(cleanInput($_POST['iDisplayStart'])) . ',' . intval(cleanInput($_POST['iDisplayLength'])),
            'sort_by' => flat_dt_ordering($_POST, [
                'id_dt'       => 'id_category',
                'category_dt' => 'name_category'
            ])
        ];

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["id_category-asc"] : $sorting['sort_by'];

        $conditions = array_merge($conditions, $sorting);

		$categories_list = $this->itemquestions->get_categories_question($conditions);
		$count_categories = $this->itemquestions->count_categories_question();

		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $count_categories,
			"iTotalDisplayRecords" => $count_categories,
			"aaData" => array()
		);

		if(empty($categories_list))
			jsonResponse('', 'success', $output);

		foreach ($categories_list as $category) {
			$output['aaData'][] = array(
				"id_dt" => $category['id_category'],
				"category_dt" => $category['name_category'],
				"actions_dt" =>
					'<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" href="items_questions/popup_forms/edit_question_category/' . $category['id_category'] . '" data-table="dtCategoriesQuestionsList" data-title="Edit category" title="Edit category"></a>
					<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="removeCategory" data-category="'.$category['id_category'].'" data-message="Are you sure you want to delete this category?" href="#" title="Delete category"></a>'
			);
		}

		jsonResponse('', 'success', $output);
    }

    public function ajax_list_my_questions_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('write_questions_on_item,reply_questions');
        checkGroupExpire('ajax');

        if (!have_right('buy_item')) {
            checkHaveCompanyAjaxDT();
        }

        $userId = privileged_user_id();
        $request = request()->request;

        $dtFilters = dtConditions($request->all(), [
            ['as' => 'added_start',         'key' => 'create_from',     'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'added_finish',        'key' => 'create_to',       'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'replied_start',       'key' => 'reply_from',      'type' => 'concat: 00:00:00|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'replied_finish',      'key' => 'reply_to',        'type' => 'concat: 23:59:59|getDateFormat:m/d/Y H:i:s,Y-m-d H:i:s'],
            ['as' => 'replied',             'key' => 'replied',         'type'  => fn ($filter) => in_array($filter, ['yes', 'no']) ? $filter : null],
            ['as' => 'item',                'key' => 'id_item',         'type'  => 'toId'],
            ['as' => 'questioner',          'key' => 'id_user',         'type'  => 'int'],
            ['as' => 'seller',              'key' => 'id_seller',       'type'  => 'int'],
            ['as' => 'status',              'key' => 'status',          'type'  => fn ($filter) => in_array($filter, ['new', 'moderated']) ? $filter : null],
            ['as' => 'question_number',     'key' => 'question_number', 'type'  => 'toId'],
            ['as' => 'keywords',            'key' => 'keywords',        'type'  => 'cleanInput|cut_str'],
        ]);

        $orderBy = dtOrdering($request->all(), [
            'item'       => 'title',
            'created_at' => 'question_date',
            'replied_at' => 'reply_date',
        ], fn ($ordering) => $ordering['column'] . '-' . $ordering['direction']);

        $questionsParams = array_merge(
            $dtFilters,
            array_filter(
                [
                    'my_list_details'   => true,
                    'questioner'        => have_right('write_questions_on_item') ? $userId : null,
                    'sort_by'           => $orderBy ?: null,
                    'seller'            => have_right('reply_questions') ? $userId : null,
                    'limit'             => ((int) $_POST['iDisplayStart']) . ', ' . ((int) $_POST['iDisplayLength']),
                ]
            ),
        );

        /** @var Itemquestions_Model $itemsQuestionsModel */
        $itemsQuestionsModel = model(Itemquestions_Model::class);

		$questions = $itemsQuestionsModel->get_questions($questionsParams);
		$recordsTotal = $itemsQuestionsModel->count_questions($questionsParams);

		$output = [
			"iTotalDisplayRecords"  => $recordsTotal,
			"iTotalRecords"         => $recordsTotal,
			"aaData"                => [],
			"sEcho"                 => $request->getInt('sEcho'),
        ];

		if (empty($questions)) {
			jsonResponse('', 'success', $output);
        }

        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);
        /** @var ItemsReview_Model $itemsReviewsModel */
        $itemsReviewsModel = model(ItemsReview_Model::class);

        $itemsIds = implode(',', array_column($questions, 'id_item'));
        $mainImages = $itemsModel->items_main_photo(array('main_photo' => 1, 'items_list' => $itemsIds));
        $ratings = array_column($itemsReviewsModel->getRatingsByItems($itemsIds), 'raiting', 'id_item');
        $isQuestioner = have_right('write_questions_on_item');
        $isRespondent = have_right('reply_questions');
        $deleteQuestionButtonAtas = addQaUniqueIdentifier('item-questions-my__table_dropdown-delete-btn');
		foreach ($questions as $question) {
            //region Delete button
            $deleteButton = null;
            if (
                $isQuestioner &&
                'moderated' !== $question['status'] &&
                empty($question['reply'])
            ) {
                $deleteButtonUrl = __SITE_URL . "items_questions/ajax_question_operation/delete";
                $deleteButton = <<<DELETE_BUTTON
                    <a rel="delete"
                        class="dropdown-item confirm-dialog"
                        {$deleteQuestionButtonAtas}
                        data-href="{$deleteButtonUrl}"
                        data-message="Are you sure you want to delete this question?"
                        data-callback="deleteQuestion"
                        data-question="{$question['id_q']}">
                        <i class="ep-icon ep-icon_trash-stroke"></i>
                        <span>Delete</span>
                    </a>
                DELETE_BUTTON;
            }
            //endregion Delete button

            //region Edit question button
            $editQuestionButton = null;
            $editQuestionButtonAtas = addQaUniqueIdentifier('item-questions-my__table_dropdown-edit-btn');
            if(
                $isQuestioner &&
                'moderated' !== $question['status'] &&
                empty($question['reply'])
            ) {
                $editButtonUrl = __SITE_URL . "items_questions/popup_forms/edit_question/{$question['id_q']}";
                $editQuestionButton = <<<EDIT_BUTTON
                    <a rel="edit"
                        class="dropdown-item fancybox.ajax fancyboxValidateModal"
                        {$editQuestionButtonAtas}
                        data-fancybox-href="{$editButtonUrl}"
                        data-title="Edit">
                        <i class="ep-icon ep-icon_pencil"></i>
                        <span>Edit</span>
                    </a>
                EDIT_BUTTON;
            }
            //endregion Edit question button

            //region Edit reply button
            $replyButton = null;
            if(
                $isRespondent &&
                $question['status'] !== 'moderated'
            ){
                $replyButtonUrl= __SITE_URL . "items_questions/popup_forms/leave_reply_to_item_question/{$question['id_q']}";
                $replyButtonTitle = empty($question['reply']) ? "Leave reply" : "Edit reply";
                $replyButtonAtas = addQaUniqueIdentifier('item-questions-my__table_dropdown-edit-reply-btn');
                $replyButton = <<<REPLY_BUTTON
                    <a rel="edit"
                        class="dropdown-item fancybox.ajax fancyboxValidateModal"
                        {$replyButtonAtas}
                        data-fancybox-href="{$replyButtonUrl}"
                        data-title="{$replyButtonTitle}">
                        <i class="ep-icon ep-icon_pencil"></i>
                        <span>{$replyButtonTitle}</span>
                    </a>
                REPLY_BUTTON;
            }
            //endregion Edit reply button

            //region View button
            $viewButtonUrl = __SITE_URL . "items_questions/popup_forms/question_details/{$question['id_q']}" . (isBackstopEnabled() ? "?backstop=" . request()->query->getInt('backstop') : "");
            $viewButtonAtas = addQaUniqueIdentifier('item-questions-my__table_dropdown-details-btn');
            $viewButton = <<<VIEW_BUTTON
                <a rel="item_question_details"
                    class="dropdown-item fancybox.ajax fancyboxValidateModal"
                    data-fancybox-href="{$viewButtonUrl}"
                    {$viewButtonAtas}
                    data-title="Details">
                    <i class="ep-icon ep-icon_visible"></i>
                    <span>Details</span>
                </a>
            VIEW_BUTTON;
            //endregion View button
            $dropdownAtas = addQaUniqueIdentifier('item-questions-my__table_dropdown-btn');
            $actions = <<<ACTION_BTNS
                <div class="dropdown">
                    <a class="dropdown-toggle" {$dropdownAtas} data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ep-icon ep-icon_menu-circles"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        {$viewButton}
                        {$editQuestionButton}
                        {$replyButton}
                        {$deleteButton}
                        <a class="dropdown-item d-none d-md-block d-lg-block d-xl-none call-function"
                            data-callback="dataTableAllInfo"
                            href="#"
                            target="_blank">
                            <i class="ep-icon ep-icon_info-stroke"></i>
                            <span>All info</span>
                        </a>
                    </div>
                </div>
            ACTION_BTNS;
            //endregion Actions

            //region Status
            $statusLabeltext = $question['status'] === 'new' ? 'New' : 'Moderated';
            $statusLabelIcon = $question['status'] === 'new' ? 'ep-icon_new' : 'ep-icon_sheild-ok';
            $statusLabel = <<<STATUS
                <span class="tac">
                    <i class="ep-icon {$statusLabelIcon} txt-green fs-30"></i>
                    <br>
                    {$statusLabeltext}
                </span>
            STATUS;
            //endregion Status

            //region Item
            //region Item rating
            $ratingNumber = (int) ($ratings[$question['id_item']] ?? 0);
            $ratingAtas = addQaUniqueIdentifier('item-questions-my__table_item-rating');
            $rating = <<<RATING
                <input class="rating-bootstrap"
                    data-filled="ep-icon ep-icon_star txt-orange fs-16"
                    data-empty="ep-icon ep-icon_star txt-gray-light fs-16"
                    type="hidden"
                    name="val"
                    {$ratingAtas}
                    value="{$ratingNumber}"
                    data-readonly>
            RATING;
            //endregion Item rating

            //region Categories
            $itemCategories = json_decode("[{$question['item_breadcrumbs']}]", true);
            $itemCategoryBreadcrumbs = [];
            $itemCategoryAtas = addQaUniqueIdentifier('item-questions-my__table_item-category');
			if (!empty($itemCategories)) {
				foreach ($itemCategories as $category) {
                    foreach ($category as $categoryId => $categoryTitle)
                        $categoryUrl = __SITE_URL . 'category/' . strForURL($categoryTitle) . '/' . $categoryId;
                        $itemCategoryBreadcrumbs[] = "<a class=\"link\" {$itemCategoryAtas} href=\"{$categoryUrl}\" target=\"_blank\">{$categoryTitle}</a>";
				}
            }

            $itemCategoryBreadcrumbs = implode('<span> / </span>', $itemCategoryBreadcrumbs);
            //endregion categories

            $itemImage = search_in_array($mainImages, 'sale_id', $question['id_item']);
            $itemImgUrl = getDisplayImageLink(['{ID}' => $question['id_item'], '{FILE_NAME}' => $itemImage['photo_name']], 'items.main', ['thumb_size' => 1]);
			$itemUrl = __SITE_URL . 'item/' . strForURL($question['title'] . ' ' . $question['id_item']);
            $itemAtas = addQaUniqueIdentifier('item-questions-my__table_item');
            $itemImageAtas = addQaUniqueIdentifier('item-questions-my__table_item-image');
            $itemTitleAtas = addQaUniqueIdentifier('item-questions-my__table_item-title');

            $item = <<<ITEM
                <div class="flex-card" {$itemAtas}>
                    <div class="flex-card__fixed main-data-table__item-img main-data-table__item-img--h-auto image-card3">
                        <span class="link">
                            <img class="image" {$itemImageAtas} src="{$itemImgUrl}" alt="{$question['title']}"/>
                        </span>
                    </div>
                    <div class="flex-card__float">
                        <div class="main-data-table__item-ttl">
                            <a href="{$itemUrl}"
                                class="display-ib link-black txt-medium"
                                {$itemTitleAtas}
                                title="View item"
                                target="_blank">
                                {$question['title']}
                            </a>
                        </div>
                        <div>{$rating}</div>
                        <div class="links-black">{$itemCategoryBreadcrumbs}</div>
                    </div>
                </div>
            ITEM;
            //endregion Item

            //region Question
            $questionTitleAtas = addQaUniqueIdentifier('item-questions-my__table-question-title');
            $questionTextAtas = addQaUniqueIdentifier('item-questions-my__table-question-text');
            $question = <<<QUESTION
                <div class="grid-text">
                    <div class="grid-text__item">
                        <div>
                            <div>
                                <strong {$questionTitleAtas}>{$question['title_question']}</strong>
                            </div>
                            <div {$questionTextAtas}>{$question['question']}</div>
                        </div>
                    </div>
                </div>
            QUESTION;
            //endregion Question

			$output['aaData'][] = [
				"created_at" => getDateFormatIfNotEmpty($question['question_date']),
				"replied_at" => getDateFormatIfNotEmpty($question['reply_date']),
				"question"   => $question,
				"actions"    => $actions,
				"status"     => $statusLabel,
				"item" 	     => $item,
            ];
		}

		jsonResponse('', 'success', $output);
    }

    public function ajax_list_admin_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('moderate_content');

		$this->load_main();

		$conditions = array();

        $sorting = [
            'my_list_details' => true,
            'limit' => (int) $_POST['iDisplayStart'] . ',' . (int) $_POST['iDisplayLength'],
            'sort_by' => flat_dt_ordering($_POST, [
                'item'       => 'title',
                'author'     => 'questionername',
                'title'      => 'title_question',
                'category'   => 'name_category',
                'text'       => 'question',
                'answer'     => 'reply',
                'ques_date'  => 'question_date',
                'created_at' => 'question_date',
                'replied_at' => 'reply_date',
                'status'     => 'status',
                'plus'       => 'count_plus',
                'minus'      => 'count_minus',
                'seller'     => 'sell_fullname',
            ])
        ];

        $filters = dtConditions($_POST, [
            ['as' => 'added_start',  'key' => 'start_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'added_finish',  'key' => 'finish_date', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
            ['as' => 'item', 'key' => 'id_item', 'type' => 'int'],
            ['as' => 'questioner', 'key' => 'id_user', 'type' => 'int'],
            ['as' => 'seller', 'key' => 'id_seller', 'type' => 'int'],
            ['as' => 'replied', 'key' => 'replied', 'type' => 'cleanInput'],
            ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput'],
            ['as' => 'status', 'key' => 'moderated', 'type' => 'cleanInput']
        ]);

        $sorting['sort_by'] = empty($sorting['sort_by']) ? ["title-asc"] : $sorting['sort_by'];

        $conditions = array_merge($sorting, $filters);

		$questions_list = $this->itemquestions->get_questions($conditions);
		$records_total = $this->itemquestions->count_questions($conditions);
		$output = array(
			"sEcho" => intval($_POST['sEcho']),
			"iTotalRecords" => $records_total,
			"iTotalDisplayRecords" => $records_total,
			"aaData" => array()
		);

		if (empty($questions_list))
			jsonResponse('', 'success', $output);

		foreach ($questions_list as $question) {
			$moderate_btn = '<a data-callback="moderate_question" class="ep-icon ep-icon_sheild-nok txt-red confirm-dialog" data-message="Are you sure want to moderate this question?" title="Moderate question" data-question="' . $question['id_q'] . '"></a>';
			$reply = "-";

			if ($question['status'] == "moderated")
				$moderate_btn = '<a class="ep-icon ep-icon_sheild-ok txt-green" title="Moderated question"/></a>';

			$title_dots = "";
			if (strlen($question['title_question']) > 70)
				$title_dots = "<a rel='review_details' title='View details'><p class='tac'>...</p></a>";

			$text_dots = "";
			if (strlen($question['question']) > 150)
				$text_dots = "<a rel='review_details' title='View details'><p class='tac'>...</p></a>";

			$reply_dots = "";
			if (strlen($question['reply']) > 100)
				$reply_dots = "<a rel='review_details' title='View details'><p class='tac'>...</p></a>";

			if(!empty($question['reply']))
				$reply = $question['reply'];

            //TODO: admin chat hidden
            $btnChatSeller = new ChatButton(['hide' => true, 'recipient' => $question['id_seller'], 'recipientStatus' => $question['sell_user_status']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatSellerView = $btnChatSeller->button();

            //TODO: admin chat hidden
            $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $question['id_questioner'], 'recipientStatus' => $question['user_status']], ['classes' => 'btn-chat-now', 'text' => '']);
            $btnChatUserView = $btnChatUser->button();

			$output['aaData'][] = array(
				'id' => $question['id_q'] . '<input type="checkbox" class="check-question pull-left" data-id-question="' .
					$question['id_q'] . '"> </br><a rel="review_details" title="View details" class="ep-icon ep-icon_plus"></a>',
				"item" => '<div class="pull-left">'
					. '<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by item" data-value-text="' . $question['title'] . '" data-value="' . $question['id_item'] . '" data-title="Item" data-name="id_item"></a>'
					. '<a class="ep-icon ep-icon_item txt-orange" title="View item" href="' . __SITE_URL . 'item/' . strForURL($question['title']) .
					'-' . $question['id_item'] . '"' . '></a>' .
					"</div><div class='clearfix'></div><span>" . $question['title'] . "</span>",
				"category" => $question['name_category'],
				"author" =>
						'<div class="pull-left">'
							. '<a data-value-text="' . $question['questionername'] . '" class="ep-icon ep-icon_filter txt-green dt_filter" data-value="' . $question['id_questioner'] . '" data-title="Author" data-name="id_user" title="Filter by user"></a>'
							. '<a class="ep-icon ep-icon_user" title="View user\'s profile" href="' . __SITE_URL . 'usr/' . strForURL($question['questionername'])
							.'-' . $question['id_questioner'] . '"' . '></a>'
							. $btnChatUserView
						. "</div>"
						. "<div class='clearfix'></div><span>" . $question['questionername'] . "</span>",
				"seller" =>
						'<div class="pull-left">'
							. '<a class="ep-icon ep-icon_filter txt-green dt_filter" title="Filter by seller" data-value-text="' . $question['sell_fullname'] . '" data-value="' . $question['id_seller'] . '" data-title="Seller" data-name="id_seller"></a>'
							. '<a class="ep-icon ep-icon_user" title="View seller\'s profile" href="' . __SITE_URL . 'usr/' . strForURL($question['sell_fullname'])
							.'-' . $question['id_seller'] . '"' . '></a>'
							.'<a class="ep-icon ep-icon_build" title="View company\'s profile" href="' . getCompanyURL($question). '"' . '></a>'
							. $btnChatSellerView
						."</div>"
						. "<div class='clearfix'></div><span>" . $question['sell_fullname'] . "</br> (" . $question['name_company'] . ")</span>",
				"title" => '<div class="h-50 hidden-b" title="' . $question['title_question'] . '">
								<div class="grid-text">
									<div class="grid-text__item">'
									. $question['title_question'] .
									'</div>
								</div>
							</div>'
							. $title_dots,
				"full_title" => $question['title_question'],
				"text" => '<div class="h-50 hidden-b" title="' . $question['question'] . '">
								<div class="grid-text">
									<div class="grid-text__item">'
										. $question['question'] .
									'</div>
								</div>
							</div>'
							. $text_dots,
				"full_text" => $question['question'],
				"ques_date" => formatDate($question['question_date']),
				"status" => $question['status'],
				"reply_date" => formatDate($question['reply_date']),
				"plus" => $question['count_plus'],
				"minus" => $question['count_minus'],
                "answer" => '<div class="h-50 hidden-b" title="' . $reply . '">
                            <div class="grid-text">
                                <div class="grid-text__item">'
                                    . $reply .
                                '</div>
                            </div>
                        </div>'
                        . $reply_dots,
				"full_reply" => $reply,
				"actions" =>
					$moderate_btn.
					'<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" data-title="Edit Question" title="Edit Question" href="'.__SITE_URL.'items_questions/popup_forms/question_admin_form/' . $question['id_q'] . '"></a>
					<a data-callback="delete_question" class="ep-icon ep-icon_remove txt-red confirm-dialog" data-message="Are you sure want delete this question?" title="Delete question" data-question="' . $question['id_q'] . '"></a>'
			);
		}

		jsonResponse('', 'success', $output);
	}

    public function ajax_question_operation()
    {
        checkIsAjax();

		$this->load_main();
		$this->load->model('Itemquestions_Model', 'itemquestions');

		$type = $this->uri->segment(3);

		switch($type){
			case 'check_new':
				$lastId = $_POST['lastId'];
				$items_questions_count = $this->itemquestions->get_count_new_items_questions($lastId);

				if($items_questions_count){
                    $last_items_questions_id = $this->itemquestions->get_items_questions_last_id();

					jsonResponse('', 'success', array('nr_new' => $items_questions_count, 'lastId' => $last_items_questions_id));
				} else {
					jsonResponse('Questions about new items do not exist');
                }
			break;
            case 'add_question':
                checkIsLoggedAjax();
                checkPermisionAjax('write_questions_on_item');

				$validator_rules = array(
					array(
						'field' => 'name_category',
						'label' => 'Category',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'title',
						'label' => 'Headline or title of your question',
						'rules' => array('required' => '', 'max_len[255]' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Question description',
						'rules' => array('required' => '', 'max_len[500]' => '')
					),
					array(
						'field' => 'item',
						'label' => 'Item info',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate()) {
					jsonResponse ($this->validator->get_array_errors());
                }

				$id_item = intVal($_POST['item']);
				$insert = array(
					'title_question' => cleanInput($_POST['title']),
					'question'	     => cleanInput($_POST['description']),
					'id_questioner'	 => $this->session->id,
					'id_category'	 => intVal($_POST['name_category']),
					'id_item'	     => $id_item
				);
				if(isset($_POST['answer'])) {
					$insert['notify'] = 1;
                }

				$question = $this->itemquestions->setQuestion($insert);
				if($question){
					$this->load->model('User_Statistic_Model', 'statistic');
                    $this->load->model('Notify_Model', 'notify');

					$item_info = $this->items->get_item($id_item, 'id_seller, title');

					$data_systmess = [
						'mess_code' => 'user_written_question_to_item',
						'id_item'   => $id_item,
						'id_users'  => [$item_info['id_seller']],
						'replace'   => [
							'[USER]'       => cleanOutput(user_name_session()),
							'[ITEM_TITLE]' => cleanOutput($item_info['title']),
							'[ITEM_LINK]'  => __SITE_URL . 'item/' . strForURL($item_info['title']) . '-' . $id_item,
							'[LINK]'       => __SITE_URL . 'item/' . strForURL($item_info['title']) . '-' . $id_item . '#questions-f'
						],
						'systmess'  => true,
					];


                    $this->statistic->set_users_statistic(array($insert['id_questioner'] => array('item_questions_wrote' => 1)));
					$this->notify->send_notify($data_systmess);

					$data['questions_user_info'] = true;
					$data['questions'][] = $this->itemquestions->getQuestion($question);

					$html = $this->view->fetch('new/items_questions/item_view', $data);

					jsonResponse('Question has been successfully saved.', 'success', array('question' => $html));
				} else {
					jsonResponse('Question has not been added.');
				}
			break;
			case 'edit_question':
                checkIsLoggedAjax();
                checkPermisionAjax('write_questions_on_item');

                $id_question = (int) $_POST['question'];
                if(
                    empty($id_question) ||
                    empty($question_info = $this->itemquestions->getQuestion($id_question))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

				if (!is_my($question_info['id_questioner'])) {
					jsonResponse(translate('systmess_error_invalid_data'));
                }

				if ('moderated' === $question_info['status']) {
					jsonResponse(translate('systmess_error_edit_question_already_moderated'));
				}

                if (!empty($question_info['reply'])) {
					jsonResponse(translate('systmess_error_edit_question_exist_reply'));
                }

				$validator_rules = array(
					array(
						'field' => 'name_category',
						'label' => translate('item_question_form_category_question_label'),
						'rules' => array('required' => '')
					),
					array(
						'field' => 'title',
						'label' => translate('item_question_form_title_question_label'),
						'rules' => array('required' => '', 'max_len[255]' => '')
					),
					array(
						'field' => 'description',
						'label' => translate('item_question_form_text_question_label'),
						'rules' => array('required' => '', 'max_len[500]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

				$update = array(
					'id_category'    => cleanInput($_POST['name_category']),
					'title_question' => cleanInput($_POST['title']),
                    'question'       => cleanInput($_POST['description']),
                    'notify'         => (int) isset($_POST['answer']),
				);

				if (!$this->itemquestions->updateQuestion($id_question, $update)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$question = $this->itemquestions->getQuestion($id_question);
				$resp = array(
					'q_title'       => $question['title_question'],
					'q_category'    => $question['name_category'],
					'q_description' => $question['question'],
					'q_li'          => 'question-'.$question['id_q'],
					'q_notify'      => $question['notify']
				);

				jsonResponse(translate('systmess_success_edited_item_question'), 'success', $resp);

			break;
            case 'add_question_reply':
                checkIsLoggedAjax();
                checkPermisionAjax('reply_questions');

				$idQuestion = (int)$_POST['question'];
                $idUser = privileged_user_id();
                /** @var Itemquestions_Model $itemquestions */
                $itemquestions = model(Itemquestions_Model::class);

				if (!$itemquestions->isQuestionForUser($idQuestion, $idUser)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$validator_rules = array(
					array(
						'field' => 'response',
						'label' => translate('item_question_reply_form_text_label'),
						'rules' => array('required' => '', 'max_len[500]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse ($this->validator->get_array_errors());
				}

				$update = [
					'reply_date' => date('Y-m-d H:i:s'),
					'status'	 => 'new',
					'reply' 	 => cleanInput($_POST['response']),
                ];

				if (!$itemquestions->updateQuestion($idQuestion, $update)){
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$dataQuestion = $itemquestions->getQuestionForReply($idQuestion);
				$data['question'] = $itemquestions->getQuestion($idQuestion);
				$data['question']['reply'] = $update['reply'];
				$data['question']['reply_date'] = $update['reply_date'];

				if ($dataQuestion['notify'] == 1) {
					$itemInfo = model(Items_Model::class)->get_item($data['question']['id_item'], 'id_seller, title');

					model(Notify_Model::class)->send_notify([
						'mess_code' => 'user_written_reply_to_item',
						'id_item' 	 => $data['question']['id_item'],
						'id_users' 	=> [$data['question']['id_questioner']],
						'systmess'  => true,
						'replace' 	 => [
							'[USER]' 	=> cleanOutput(user_name_session()),
							'[LINK]' 	=> __SITE_URL . 'item/' . strForURL($itemInfo['title']) . '-' . $data['question']['id_item'] . '#questions-f'
						],
					]);

                    $sellerName = cleanOutput(user_name_session());

                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new AnswerItemQuestion($dataQuestion['user_name'], $sellerName, $dataQuestion, $itemInfo['title'], $data['question']['id_item']))
                            ->to(new RefAddress((string) $dataQuestion['idu'], new Address($dataQuestion['email'])))
                            ->subjectContext([
                                '[seller]' => $sellerName,
                            ])
                    );
				}

				$this->load->model('User_Statistic_Model', 'statistic');
				$this->statistic->set_users_statistic(array($idUser => array('item_questions_answered' => 1)));

				$resp = array(
					'reply_to_question' => $this->view->fetch('new/items_questions/item_question_reply_response_view', $data),
					'id_question' 		=> $idQuestion,
					'reply_block' 		=> 'question-' . $idQuestion . '-reply-block'
				);

				jsonResponse(translate('systmess_success_item_question_reply_added'), 'success', $resp);
			break;
			case 'edit_question_reply':
                checkIsLoggedAjax();
                checkPermisionAjax('reply_questions');

				$validator_rules = array(
					array(
						'field' => 'response',
						'label' => translate('item_question_reply_form_text_label'),
						'rules' => array('required' => '', 'max_len[500]' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse ($this->validator->get_array_errors());
				}

				$id_question = (int) $_POST['question'];
				$id_user = privileged_user_id();

				if (!$id_question || !$this->itemquestions->isQuestionForUser($id_question, $id_user)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$question_info = $this->itemquestions->getQuestion($id_question);

				$update = array(
                    'reply'      	=> cleanInput($_POST['response']),
					'reply_date' 	=> date('Y-m-d H:i:s'),
					'status'		=> 'new'
				);

				if (!$this->itemquestions->updateQuestion($id_question, $update)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$resp = array(
					'reply_text' => cleanInput($_POST['response']),
					'reply_block' => 'li-question-reply-'.$id_question
				);

				jsonResponse(translate('systmess_success_item_question_reply_edited'), 'success', $resp);

			break;
			case 'show':
				$questions_params = array('item' => (int) $_POST['item'], 'order' => 'date_desc');

				$data['reply_full_width'] = true;

				$data['questions_user_info'] = true;
				$data['questions'] = $this->itemquestions->get_questions($questions_params);

				foreach($data['questions'] as $item)
					$array_id[] = $item['id_q'];

				if(!empty($data['questions']) && logged_in())
					$data['helpful'] = $this->itemquestions->get_helpful_by_question(implode(',', $array_id), id_session());

                $data['page_questions_all'] = 1;
                $this->view->assign($data);
                $list_questions = $this->view->fetch('new/items_questions/list_questions_view');

				jsonResponse('','success',array('html' => $list_questions, 'count' => count($data['questions'])));
			break;
			case 'help':
                checkIsLoggedAjax();

				$type = cleanInput($_POST['type']);
				if (!in_array($type, array('y', 'n'))) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$type = $type == 'y' ? 1 : 0;

				$id = (int) $_POST['id'];
				if (empty($id) || empty($question_info = $this->itemquestions->get_simple_question($id))) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$response_data = array(
					'counter_plus' => $question_info['count_plus'],
					'counter_minus' => $question_info['count_minus']
				);

				if (is_privileged('user',$question_info['id_seller'])) {
					jsonResponse(translate('systmess_error_helpful_vote_for_yourself'));
				}

				$id_user = privileged_user_id();
				$my_question_reply_helpful = $this->itemquestions->exist_helpful($id, $id_user);
				$action = $type ? 'plus' : 'minus';
				if (empty($my_question_reply_helpful['counter'])) {
					unset($my_question_reply_helpful);
				}

				// If this is the first vote for this feedback
				if (empty($my_question_reply_helpful)) {
					$insert = array(
						'id_question' 	=> $id,
						'id_user' 		=> $id_user,
						'help'			=> $type
					);

					$columns['count_' . $action] = '+';

					if (!$this->itemquestions->set_helpful($insert)) {
						jsonResponse(translate('systmess_internal_server_error'));
					}

					$this->itemquestions->modify_counter_helpfull($id, $columns);
					$response_data['counter_' . $action] = ++$response_data['counter_' . $action];
					$response_data['select_' . $action] = true;

					jsonResponse(translate('systmess_success_item_question_reply_helpful_vote_successfully_saved'), 'success', $response_data);
				}

				// If it is a vote cancellation
				if ($my_question_reply_helpful['help'] == $type) {
					$this->itemquestions->delete_user_helpful($id, $id_user);

					$columns['count_' . $action] = '-';
					$this->itemquestions->modify_counter_helpfull($id, $columns);

					$response_data['counter_' . $action] = --$response_data['counter_' . $action];
					$response_data['remove_' . $action] = true;

					jsonResponse(translate('systmess_success_item_question_reply_helpful_vote_successfully_saved'), 'success', $response_data);
				}

				// If a vote has been changed
				$update['help'] = $type;
				$columns = array(
					'count_plus' => $type ? '+' : '-',
					'count_minus' => $type ? '-' : '+'
				);

				if (!$this->itemquestions->update_helpful($id, $update, $id_user)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$this->itemquestions->modify_counter_helpfull($id, $columns);

				$opposite_action = $action == 'plus' ? 'minus' : 'plus';

				$response_data['counter_' . $action] = ++$response_data['counter_' . $action];
				$response_data['counter_' . $opposite_action] = --$response_data['counter_' . $opposite_action];
				$response_data['select_' . $action] = true;
				$response_data['remove_' . $opposite_action] = true;

				jsonResponse(translate('systmess_success_item_question_reply_helpful_vote_successfully_saved'), 'success', $response_data);

			break;
			case 'delete':
                checkIsLoggedAjax();

				$id_question = (int) $_POST['question'];
                if(
                    empty($id_question) ||
                    empty($question = $this->itemquestions->getQuestion($id_question))
                ) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

				if (!have_right('moderate_content') && !is_my($question['id_questioner'])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
				}

				if ('moderated' === $question['status']) {
                    jsonResponse(translate('systmess_error_delete_item_question_already_moderated'));
                }

                if (!empty($question['reply'])) {
                    jsonResponse(translate('systmess_error_delete_item_question_exist_reply'));
                }

				if (!$this->itemquestions->delete_questions($id_question)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$this->load->model('User_Statistic_Model', 'statistic');
				$this->statistic->set_users_statistic(array($question['id_questioner'] => array('item_questions_wrote' => -1)));
				$this->statistic->set_users_statistic(array($question['id_seller'] => array('item_questions_answered' => -1)));

				jsonResponse(translate('systmess_success_deleted_item_question'),'success');

			break;
            /** deleted on 2021.07.05 */
			/* case 'new_questions':
				$questions = $this->itemquestions->get_questions(array('item' => (int)$_POST['item'], 'added_after_time' => date('Y-m-d H:i:s', strtotime("-1 minutes"))));
				if(!empty($questions)){
					$qlist = array();
					foreach($questions as $item){
						if(!is_my($item['id_questioner']) || !logged_in()){
							$data = array();
							$item['new_question'] = true;
							$data['questions'][] = $item;
							$qlist[] = $this->view->fetch($this->view_folder.'items_questions/item_view', $data);
						}
					}
					if(!empty($qlist)){
						$resp = array(
							'not_empty' => true,
							'new_questions' => implode('',$qlist)
						);
						jsonResponse('','success', $resp);
					} else{
						$resp = array(
							'not_empty' => false
						);
						jsonResponse('','success', $resp);
					}
				} else{
					$resp = array(
						'not_empty' => false
					);
					jsonResponse('','success', $resp);
				}
			break; */
            case 'edit_question_admin':
                checkIsLoggedAjax();
                checkPermisionAjax('moderate_content');

				$validator_rules = array(
					array(
						'field' => 'name_category',
						'label' => 'Category',
						'rules' => array('required' => '')
					),
					array(
						'field' => 'title',
						'label' => 'Headline or title of your question',
						'rules' => array('required' => '', 'max_len[255]' => '')
					),
					array(
						'field' => 'description',
						'label' => 'Question description',
						'rules' => array('required' => '', 'max_len[500]' => '')
					),
					array(
						'field' => 'reply',
						'label' => 'Question reply',
						'rules' => array('max_len[5000]' => '')
					),
					array(
						'field' => 'question',
						'label' => 'Question info',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if(!$this->validator->validate())
					jsonResponse ($this->validator->get_array_errors());

				$update = array(
					'id_category'    => cleanInput($_POST['name_category']),
					'title_question' => cleanInput($_POST['title']),
					'question'       => cleanInput($_POST['description']),
                    'reply'          => cleanInput($_POST['reply']),
                    'reply_date'     => date('Y-m-d H:i:s'),
				);


				if($this->itemquestions->updateQuestion($_POST['question'], $update))
					jsonResponse ('Changes have been successfully saved', 'success');
				else
					jsonResponse ('Changes have not been saved.');

			break;
			case "create_category":
                checkIsLoggedAjax();
                checkPermisionAjax('moderate_content');

				$validator_rules = array(
					array(
						'field' => 'name_category',
						'label' => 'Category\'s name',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$insert = array(
					'name_category' => cleanInput($_POST['name_category'])
				);

				if($this->itemquestions->exist_category_question(array('name_category' => $insert['name_category'])))
					jsonResponse('This category already exists.');

				if($this->itemquestions->set_category_question($insert))
					jsonResponse('The category was added successfully.', 'success');
				else
					jsonResponse('The category has not been added.');
			break;
			case "edit_category":
                checkIsLoggedAjax();
                checkPermisionAjax('moderate_content');

				$validator_rules = array(
					array(
						'field' => 'name_category',
						'label' => 'Category\'s name',
						'rules' => array('required' => '')
					)
				);

				$this->validator->set_rules($validator_rules);

				if (!$this->validator->validate())
					jsonResponse($this->validator->get_array_errors());

				$id_category = intval($_POST['id_category']);

				$update = array(
					'name_category' => cleanInput($_POST['name_category'])
				);

				if (!$this->itemquestions->exist_category_question(array('id_category' => $id_category)))
					jsonResponse("This category doesn't exist");

				$exist_category = $this->itemquestions->exist_category_question(array('name_category' => $update['name_category']));
				if ( $exist_category && $id_category != $exist_category )
					jsonResponse('This category already exists');

				if ($this->itemquestions->update_category_question($id_category, $update))
					jsonResponse('Category was successfully updated.', 'success');
				else
					jsonResponse("The category wasn't updated.");
			break;
			case 'remove_category':
                checkIsLoggedAjax();
                checkPermisionAjax('moderate_content');

				$id_category = intval($_POST['category']);

				if ($this->itemquestions->delete_category_question($id_category))
					jsonResponse('Category was successfully deleted.', 'success');
				else
					jsonResponse("This category doesn't exist.");
            break;
            default:
                jsonResponse('The provided path is not found on this server');

            break;
		}
    }

    public function ajax_questions_administration_operation()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('moderate_content');

		$operation = $this->uri->segment(3);
		$this->load_main();

		switch ($operation) {
            case 'moderate':
            case 'moderate_questions':
                $is_multiple = empty($_POST['question']) && !empty($_POST['checked_questions']);
                if ($is_multiple) {
                    $id_questions = array_map(function($id) { return (int) $id; }, $_POST['checked_questions']);
                    if (!$this->itemquestions->exists_all($id_questions)) {
                        jsonResponse(translate('systmess_error_invalid_data'));
                    }
                } else {
                    $id_questions = (int) $_POST['question'];
                    if(!$this->itemquestions->exists($id_questions)) {
                        jsonResponse(translate('systmess_error_invalid_data'));
                    }
                }

                if (!$this->itemquestions->moderateQuestions($id_questions)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				jsonResponse(translate($is_multiple ? 'systmess_succes_moderated_item_questions' : 'systmess_succes_moderated_item_question'), 'success');

                break;
            case 'delete':
            case 'delete_questions':
                $is_multiple = empty($_POST['question']) && !empty($_POST['checked_questions']);
                if($is_multiple) {
                    $id_questions = array_map(function($id) { return (int) $id; }, $_POST['checked_questions']);
                    if(!$this->itemquestions->exists_all($id_questions)) {
                        jsonResponse('One or meore questions are not found on this server');
                    }
                } else {
                    $id_questions = (int) $_POST['question'];
                    if(!$this->itemquestions->exists($id_questions)) {
                        jsonResponse('The questions is not found on this server');
                    }
                    $id_questions = [$id_questions];
                }

                $users_questions = array();
                $questions_users = $this->itemquestions->questions_owner($id_questions);
				foreach($questions_users as $user){
                    $questioner = $user['id_questioner'];
                    $seller = $user['id_seller'];
					if(isset($users_questions[$questioner]['item_questions_wrote'])) {
						$users_questions[$questioner]['item_questions_wrote'] -= 1;
                    } else {
                        $users_questions[$questioner]['item_questions_wrote'] = -1;
                    }

					if(isset($users_questions[$seller]['item_questions_answered'])) {
						$users_questions[$seller]['item_questions_answered'] -= 1;
                    } else {
                        $users_questions[$seller]['item_questions_answered'] = -1;
                    }
				}

                if($this->itemquestions->delete_questions($id_questions)){
					$this->load->model('User_Statistic_Model', 'statistic');
					$this->statistic->set_users_statistic($users_questions);

					jsonResponse (
                        $is_multiple
                            ? 'The questions have been deleted'
                            : 'The question has been deleted',
                        'success'
                    );
				} else {
					jsonResponse(
                        $is_multiple
                            ? 'You cannot delete the questions now. Please try again later.'
                            : 'You cannot delete the question now. Please try again later.'
                    );
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

		$this->load_main();

		$op = $this->uri->segment(3);

		switch($op){
			case 'create_question_category':
				checkAdminAjaxModal('moderate_content');

				$this->view->display('admin/items_questions/categories/category_form_view');
			break;
			case 'edit_question_category':
				checkAdminAjaxModal('moderate_content');

				$this->load->model('ItemQuestions_Model', 'itemquestions');
				$id_category = intval($this->uri->segment(4));
				$data['category'] = $this->itemquestions->get_category_question($id_category);
				$this->view->display('admin/items_questions/categories/category_form_view', $data);
            break;
            case 'add_question':
                checkIsLoggedAjaxModal();
                checkPermisionAjaxModal('write_questions_on_item');

                $item_id = (int) $this->uri->segment(4);
                if(
                    empty($item_id) ||
                    !model('items')->item_exist($item_id)
                ) {
                    messageInModal('This item does not exist.');
                }

                $this->view->assign(array(
                    'action'              => __SITE_URL . 'items_questions/ajax_question_operation/add_question',
                    'question_categories' => model('itemquestions')->get_question_categories(),
                    'item'                => array(
                        'id' => $item_id,
                    )
                ));

				$this->view->display('new/items_questions/add_question_form_view');
            break;
            case 'edit_question':
                checkIsLoggedAjaxModal();
                checkPermisionAjaxModal('write_questions_on_item');

                $question_id = (int) $this->uri->segment(4);
                if(
                    empty($question_id) ||
                    empty($question = model('itemquestions')->getQuestion($question_id))
                ) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                if (!is_my($question['id_questioner'])) {
                    messageInModal(translate('systmess_error_invalid_data'));
				}

				if ('moderated' === $question['status']) {
                    messageInModal(translate('systmess_error_edit_question_already_moderated'));
                }

                if (!empty($question['reply'])) {
                    messageInModal(translate('systmess_error_edit_question_exist_reply'));
				}

                $this->view->assign(array(
                    'action'              => __SITE_URL . 'items_questions/ajax_question_operation/edit_question',
                    'question_categories' => model('itemquestions')->get_question_categories(),
                    'question_info'       => $question,
                    'item'                => array(
                        'id' => $question_id,
                    )
                ));

				$this->view->display('new/items_questions/edit_question_form_view');

            break;
			case 'leave_reply_to_item_question':
                checkIsLoggedAjaxModal();
                checkPermisionAjaxModal('reply_questions');

                $question_id = (int) $this->uri->segment(4);
                if(
                    empty($question_id) ||
                    empty($question = model('itemquestions')->getQuestion($question_id))
                ) {
                    messageInModal(translate('systmess_error_invalid_data'));
                }

				if (!is_privileged('user', $question['id_seller'], 'reply_questions')){
					messageInModal(translate('systmess_error_invalid_data'));
				}

                $action = __SITE_URL . 'items_questions/ajax_question_operation/add_question_reply';
                if (!empty($question['reply'])) {
                    $action = __SITE_URL . 'items_questions/ajax_question_operation/edit_question_reply';
                }

                $this->view->assign(array(
                    'action'              => $action,
                    'question_categories' => model('itemquestions')->get_question_categories(),
                    'question'            => $question,
                    'item'                => array(
                        'url' => __SITE_URL . "item/" . strForURL($question['title'] . ' ' . $question['id_item']),
                    )
                ));

                $this->view->display('new/items_questions/my/leave_question_reply_form_view');
			break;
			case 'question_admin_form':
				checkAdminAjaxModal('moderate_content');

				$id_question = intval($this->uri->segment(4));
				$data['question_info'] = $this->itemquestions->getQuestion($id_question);
				if(empty($data['question_info'])){
					messageInModal('The question does not exist.');
				}

				$data['question_categories'] = $this->itemquestions->get_question_categories();
				$this->view->assign($data);
				$this->view->display('admin/items_questions/edit_item_question_form');
            break;
            case 'question_details':
                if(!logged_in()){
                    messageInModal(translate("systmess_error_should_be_logged"));
                }

                if(!(have_right('reply_questions') || have_right('write_questions_on_item'))) {
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                $question_id = (int) $this->uri->segment(4);
                $question = $this->itemquestions->getQuestion($question_id);
                if(empty($question)) {
                    messageInModal('The question does not exist.');
                }

                $user_id = id_session();
                if(
                    !have_right('moderate_content') &&
                    !$this->itemquestions->isMyQuestion($question_id, $user_id) &&
                    !$this->itemquestions->isQuestionForUser($question_id, $user_id)
                ) {
                    messageInModal(translate("systmess_error_rights_perform_this_action"));
                }

                if (isBackstopEnabled()) {
                    $question['reply'] = 1 == request()->query->get('backstop') ? '' : 'not empty reply';
                }

                $this->view->display('new/items_questions/my/question_details', array(
                    'helpful'             => $this->itemquestions->get_helpful_by_question($question_id, $user_id),
                    'question'            => $question,
                    'questions_user_info' => true,
                ));
            break;
            default:
                messageInModal('The provided path is not found on this server');

            break;
		}
	}

    private function load_main()
    {
		$this->load->model('Category_Model', 'category');
		$this->load->model('Country_Model', 'country');
		$this->load->model('Questions_Model', 'questions');

		$this->load->model('ItemQuestions_Model', 'itemquestions');
		$this->load->model('Items_Model', 'items');
		$this->load->model('ItemsReview_Model', 'reviews');
	}

    private function my_questions(array $data = array())
    {
        /** @var Items_Model $itemsModel */
        $itemsModel = model(Items_Model::class);
        /** @var ItemsReview_Model $itemsReviewsModel */
        $itemsReviewsModel = model(ItemsReview_Model::class);

        $output = array();

        $items_list = implode(',', array_column($data['questions_list'], 'id_item'));
        $main_images = $itemsModel->items_main_photo(array('main_photo' => 1, 'items_list' => $items_list));
        $result_ratings = array_column($itemsReviewsModel->getRatingsByItems($items_list), 'raiting', 'id_item');
        $is_questioner = have_right('write_questions_on_item');
        $is_respondent = have_right('reply_questions');

		foreach($data['questions_list'] as $row){
            //region Actions

            //region Delete button
            $delete_button = null;
            if(
                $is_questioner &&
                'moderated' !== $row['status'] &&
                empty($row['reply'])
            ) {
                $delete_button_url = __SITE_URL . "items_questions/ajax_question_operation/delete";
                $delete_button_title = "Delete";
                $delete_button_message = "Are you sure you want to delete this question?";
                $delete_button = "
                    <a rel=\"delete\"
                        class=\"dropdown-item confirm-dialog\"
                        data-href=\"{$delete_button_url}\"
                        data-message=\"{$delete_button_message}\"
                        data-callback=\"deleteQuestion\"
                        data-question=\"{$row['id_q']}\">
                        <i class=\"ep-icon ep-icon_trash-stroke\"></i>
                        <span>{$delete_button_title}</span>
                    </a>
                ";
            }
            //endregion Delete button

            //region Edit question button
            $edit_question_button = null;
            if(
                $is_questioner &&
                'moderated' !== $row['status'] &&
                empty($row['reply'])
            ) {
                $edit_question_button_url = __SITE_URL . "items_questions/popup_forms/edit_question/{$row['id_q']}";
                $edit_question_button_title = "Edit";
                $edit_question_button = "
                    <a rel=\"edit\"
                        class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$edit_question_button_url}\"
                        data-title=\"{$edit_question_button_title}\">
                        <i class=\"ep-icon ep-icon_pencil\"></i>
                        <span>{$edit_question_button_title}</span>
                    </a>
                ";
            }
            //endregion Edit question button

            //region Edit reply button
            $edit_reply_button = null;
            if(
                $is_respondent &&
                $row['status'] !== 'moderated'
            ){
                $edit_reply_button_url = __SITE_URL . "items_questions/popup_forms/leave_reply_to_item_question/{$row['id_q']}";
                $edit_reply_button_title = empty($row['reply']) ? "Leave reply" : "Edit reply";
                $edit_reply_button = "
                    <a rel=\"edit\"
                        class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                        data-fancybox-href=\"{$edit_reply_button_url}\"
                        data-title=\"{$edit_reply_button_title}\">
                        <i class=\"ep-icon ep-icon_pencil\"></i>
                        <span>{$edit_reply_button_title}</span>
                    </a>
                ";
            }
            //endregion Edit reply button

            //region View button
            $view_button_url = __SITE_URL . "items_questions/popup_forms/question_details/{$row['id_q']}";
            $view_button_title="Details";
            $view_button = "
                <a rel=\"item_question_details\"
                    class=\"dropdown-item fancybox.ajax fancyboxValidateModal\"
                    data-fancybox-href=\"{$view_button_url}\"
                    data-title=\"{$view_button_title}\">
                    <i class=\"ep-icon ep-icon_visible\"></i>
                    <span>{$view_button_title}</span>
                </a>
            ";
            //endregion View button

            $actions = "
                <div class=\"dropdown\">
                    <a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                        <i class=\"ep-icon ep-icon_menu-circles\"></i>
                    </a>
                    <div class=\"dropdown-menu dropdown-menu-right\">
                        {$view_button}
                        {$edit_question_button}
                        {$edit_reply_button}
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

            //region Status
            $status_label_text = $row['status'] === 'new' ? 'New' : "Moderated";
            $status_label_icon = $row['status'] === 'new' ? 'ep-icon_new' : "ep-icon_sheild-ok";
            $status_label = "
                <span class=\"tac\">
                    <i class=\"ep-icon {$status_label_icon} txt-green fs-30\"></i>
                    <br>
                    {$status_label_text}
                </span>
            ";
            //endregion Status

            //region Item
            //region Item rating
            $rating_number = !empty($result_ratings) ? (int) $result_ratings[$row['id_item']] : 0;
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

            $item_id = $row['id_item'];
            $item_title = $row['title'];
            $item_image = search_in_array($main_images, 'sale_id', $item_id);
            $item_img_link = getDisplayImageLink(array('{ID}' => $item_id, '{FILE_NAME}' => $item_image['photo_name']), 'items.main', array( 'thumb_size' => 1 ));
			$item_url = __SITE_URL . 'item/' . strForURL($item_title) . "-{$item_id}";
            $item = "
                <div class=\"flex-card\">
                    <div class=\"flex-card__fixed main-data-table__item-img main-data-table__item-img--h-auto image-card3\">
                        <span class=\"link\">
                            <img class=\"image\" src=\"{$item_img_link}\" alt=\"{$item_title}\"/>
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

            //region Question
            $question_title = $row['title_question'];
            $question_text = $row['question'];
            $question = "
                <div class=\"grid-text\">
                    <div class=\"grid-text__item\">
                        <div>
                            <div>
                                <strong>{$question_title}</strong>
                            </div>
                            <div>{$question_text}</div>
                        </div>
                    </div>
                </div>
            ";
            //endregion Question

			$output[] = array(
				"item" 	     => $item,
				"question"   => $question,
				"created_at" => getDateFormatIfNotEmpty($row['question_date']),
				"replied_at" => getDateFormatIfNotEmpty($row['reply_date']),
				"status"     => $status_label,
				"actions"    => $actions,
			);
		}

		return $output;
	}
}
