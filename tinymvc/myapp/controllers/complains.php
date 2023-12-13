<?php

use App\Common\Buttons\ChatButton;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validation\FlatValidationData;
use App\Common\Validation\Legacy\ValidatorAdapter;
use App\Common\Validation\Standalone\AggregateValidator;
use App\Validators\ComplainsOtherThemeValidator;
use App\Validators\ComplainsValidator;
use App\Validators\NoticeValidator;
use App\Validators\ThemeValidator;

use const App\Common\PUBLIC_DATETIME_FORMAT;

class Complains_Controller extends TinyMVC_Controller
{
	private $companyRelated = [4, 5, 6, 7, 8, 9, 13, 14, 21, 22, 23];

	public function administration()
	{
		checkAdmin('manage_content');

		views()->assign([
			'types' => model(Complains_Model::class)->getTypes(),
			'title' => 'Reports'
		]);

		views(['admin/header_view', 'admin/complains/index_view', 'admin/footer_view']);
	}

	public function ajax_complains_dt()
	{
		checkIsAjax();
		checkIsLoggedAjax();
		checkAdmin('manage_content');

		$conditions = array_merge(
			[
				'per_p'   => request()->request->getInt('iDisplayLength'),
				'start'   => request()->request->getInt('iDisplayStart'),
				'sort_by' => flat_dt_ordering(request()->request->all(), [
					'dt_id'        => 'id',
					'dt_type'      => 'type_compl',
					'dt_from'      => 'user_name',
					'dt_id_item'   => 'id_item',
					'dt_to'        => 'reported_user',
					'dt_date_time' => 'date_time',
					'dt_status'    => 'status'
				]),
			],
			dtConditions(request()->request->all(), [
				['as' => 'keywords',        'key' => 'search',              'type' => 'string'],
				['as' => 'status',          'key' => 'status',              'type' => 'string'],
				['as' => 'type',            'key' => 'type',                'type' => 'int'],
				['as' => 'online',          'key' => 'online',              'type' => 'int'],
				['as' => 'reported_online', 'key' => 'reported_online',     'type' => 'int'],
				['as' => 'date_from',       'key' => 'complains_date_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
				['as' => 'date_to',         'key' => 'complains_date_to',   'type' => 'getDateFormat:m/d/Y,Y-m-d'],
				['as' => 'user',            'key' => 'user',                'type' => 'int'],
				['as' => 'reported_user',   'key' => 'reported_user',       'type' => 'int']
			])
        );

        /** @var Complains_Model $complainsModel */
        $complainsModel = model(Complains_Model::class);

		$recordsTotal = $complainsModel->getCountComplains($conditions);
		$complains = $complainsModel->getComplains($conditions);

		$output = [
			'sEcho'                 => request()->request->getInt('sEcho'),
			'iTotalRecords'         => $recordsTotal,
			'iTotalDisplayRecords'  => $recordsTotal,
			'aaData'                => []
		];

		if (empty($complains)) {
			jsonResponse('', 'success', $output);
		}

		foreach ($complains as $complain) {
			$online = (($complain['user_logged']) ? 'online' : 'offline');
			$reported_online = (($complain['reported_user_logged']) ? 'online' : 'offline');

			$status = '';
			switch ($complain['status']) {
				case 'new':
					$status = 'New';
				break;
				case 'in_process':
					$status = 'In process';
				break;
				case 'confirmed':
					$status = 'Confirmed';
				break;
				case 'declined':
					$status = 'Declined';
				break;
			}

			$status = "<div class=\"pull-left\"><a
                        class=\"ep-icon ep-icon_filter txt-green dt_filter\"
                        data-title=\"Report status\"
                        title=\"Filter by $status\"
                        data-value-text=\"$status\"
                        data-value=\"" . $complain['status'] . "\"
                        data-name=\"status\"></a>
					</div>
					<div class=\"clearfix\"></div>
					<span>$status</span>";

			$output['aaData'][] = [
				'dt_id'         => $complain['id'] . '<br/><a class="mt-10 ep-icon ep-icon_plus" rel="complains_detail" title="View details"></a>',
				'dt_type'       => '<div class="tal"><a
                                        class="ep-icon ep-icon_filter txt-green dt_filter"
                                        data-title="Report type"
                                        title="Filter by ' . $complain['type_compl'] . '"
                                        data-value-text="' . $complain['type_compl'] . '"
                                        data-value="' . $complain['id_type'] . '"
                                        data-name="type"></a>
                                    </div>
                                    <div>' . $complain['type_compl'] . '</div>',
				'dt_id_item'    => '<div class="tal"><a
                                        class="ep-icon ep-icon_visible"
                                        target="_blank"
                                        href="' . normalize_url((($complain['type_compl'] == 'Blog') ? __BLOG_URL : __SITE_URL) . parse_url($complain['link'], PHP_URL_PATH)) . '"
                                        title="Go to page"></a>
                                    </div>
                                    <div>' . idNumber($complain['id_item']) . '</div>',
				'dt_from'       => $this->get_user_from_dt_text($complain, $online),
				'dt_to'         => $this->get_user_to_dt_text($complain, $reported_online),
				'dt_text'       => '<div class="h-50 hidden-b">' . $complain['text'] . '</div>',
				'dt_status'     => $status,
				'dt_theme'      => $complain['theme'],
				'dt_date_time'  => getDateFormat($complain['date_time'], 'Y-m-d H:i:s', PUBLIC_DATETIME_FORMAT),
				'dt_actions'    => '<a class="ep-icon ep-icon_notice fancyboxValidateModalDT fancybox.ajax"
                                       title="Add notice"
                                       href="complains/popup_forms/update_complain/' . $complain['id'] . '"
                                       data-title="Add notice"></a>
                                    <a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                                       data-callback="remove_complain"
                                       data-message="Are you sure you want to delete this report?"
                                       title="Delete complain"
                                       data-complain="' . $complain['id'] . '"></a>'
			];
		}

		jsonResponse('', 'success', $output);
	}

	public function ajax_complains_operations()
	{
		checkIsAjax();
		checkIsLoggedAjax();

		$idUser = id_session();
		$op = $this->uri->segment(3);

		switch ($op) {
			case 'remove_complain':
				checkAdmin('manage_content');

				$idComplain = request()->request->getInt('id_complain');

				if (model(Complains_Model::class)->deleteComplain($idComplain)) {
					jsonResponse('The report has been successfully removed.', 'success');
				}

				jsonResponse('Error: You cannot remove this report now. Please try again later.');
			break;
			case 'add_complain':
				is_allowed('freq_allowed_add_complain');

				//region validation
				$adapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
				$validators = [new ComplainsValidator(
					$adapter,
					null,
					[
						'theme' => translate('report_company_popup_form_theme_label'),
						'text'  => translate('report_company_popup_form_message_label')
					]
				)];

				$idTheme = request()->request->getInt('id_theme');
				if ($idTheme == 0) {
					$validators[] = new ComplainsOtherThemeValidator($adapter);
				}

				$validator = new AggregateValidator($validators);
				if (!$validator->validate(request()->request->all())) {
					\jsonResponse(
						\array_map(
							fn (ConstraintViolation $violation): ?string => $violation->getMessage(),
							\iterator_to_array($validator->getViolations()->getIterator())
						)
					);
				}
				$idType = request()->request->getInt('id_type');
				$idItem = request()->request->getInt('id_item');
				$idTo = request()->request->getInt('id_to');
				$idCompany = request()->request->getInt('id_company');

				if (empty($idType) || empty($idItem) || empty($idTo)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}
				//endregion validation

				if ($idTheme != 0 && !model(Complains_Model::class)->existThemeByType($idType, $idTheme)) {
					jsonResponse(translate('systmess_theme_not_exist_message'));
				}

				if (!model(Complains_Model::class)->existTypes($idType)) {
					jsonResponse(translate('systmess_report_type_not_exist_message'));
				}

				if (!model(User_Model::class)->exist_user($idTo)) {
					jsonResponse(translate('systmess_user_type_not_exist'));
				}

				if ($idType == 14) {
					$infoFeedback = model(UserFeedback_Model::class)->get_feedback_details(['id_feedback' => $idItem]);

					if (empty($infoFeedback)) {
						jsonResponse(translate('systmess_feedback_not_exist'));
					}

					if (is_privileged('user', $infoFeedback['id_poster'])) {
						jsonResponse(translate('systmess_error_cannot_report_yourself'));
					}
				}

				$reportedUser = model(User_Model::class)->getSimpleUser($idTo, 'CONCAT(users.fname," ",users.lname) as user_name');

				if ($idTheme != 0) {
					$theme = model(Complains_Model::class)->getTheme($idTheme);
				} else {
					$theme['theme'] = cleanInput(request()->get('theme'));
				}

				$text = cleanInput(request()->get('text'));

				$for_link = [
					'id_type'    => $idType,
					'id_item'    => $idItem,
					'id_to'      => $idTo,
					'id_company' => $idCompany,
					'referer'    => cleanInput($_SERVER['HTTP_REFERER'])
				];

                $insert = $for_link + [
					'link'          => $this->generate_link($for_link),
					'text'          => $text,
					'id_from'       => $idUser,
					'id_theme'      => $idTheme,
					'theme'         => $theme['theme'],
					'search_info'   => $reportedUser['user_name'] . ' ' . $text . ' ' . $theme['theme'],
					'date_time'     => date('Y-m-d H:i:s'),
					'notice'        => json_encode(
						[
							'status'        => 'new',
							'add_date'      => date('Y-m-d H:i:s'),
							'add_by'        => user_name_session(),
							'notice'        => 'The report was created'
						]
					)
				];
				unset($insert['referer'], $insert['id_company']);

				if (!model(Complains_Model::class)->insertComplain($insert)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				jsonResponse(translate('systmess_successfully_sent_report'), 'success');
			break;
			case 'add_notice':
				checkAdmin('manage_content');

				//region validation
				$adapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
				$validator = new NoticeValidator($adapter);

				$validator = new AggregateValidator([$validator]);
				if (!$validator->validate(new FlatValidationData(request()->request->all() ?? []))) {
					\jsonResponse(
						\array_map(
							fn (ConstraintViolation $violation): ?string => $violation->getMessage(),
							\iterator_to_array($validator->getViolations()->getIterator())
						)
					);
				}
				//endregion validation

				$id = (int) (request()->get('complain'));

				if (model(Complains_Model::class)->updateComplain($id, ['status' => cleanInput(request()->get('status'))])) {
					$notice = [
						'status'    => cleanInput(request()->get('status')),
						'add_date'  => date('Y-m-d H:i:s'),
						'add_by'    => $this->session->fname . ' ' . $this->session->lname,
						'notice'    => cleanInput(request()->get('message'))
					];

					model(Complains_Model::class)->setNotice($id, json_encode($notice));
					jsonResponse('The report has been successfully updated.', 'success');
				} else {
					jsonResponse('Error: You cannot update this report now. Please try again later.');
				}
			break;
			case 'add_theme':
				checkAdmin('manage_content');

				//region validation
				$adapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
				$validators = [new ThemeValidator($adapter)];

				$validator = new AggregateValidator($validators);
				if (!$validator->validate(new FlatValidationData(request()->request->all() ?? []))) {
					\jsonResponse(
						\array_map(
							fn (ConstraintViolation $violation): ?string => $violation->getMessage(),
							\iterator_to_array($validator->getViolations()->getIterator())
						)
					);
				}
				//endregion validation

				$this->load->model('Complains_Model', 'complains');

				$idTheme = model(Complains_Model::class)->addComplainTheme([
					'theme' => cleanInput(request()->get('theme'))
				]);

				if (!empty($idTheme)) {
					if ($this->setThemeTypeRelations(request()->get('types'), $idTheme)) {
						jsonResponse('The theme has been successfully added.', 'success');
					}

					jsonResponse('Cannot set relationship with theme.');
				}

				jsonResponse('Error: You cannot add this theme now. Please try again later.');

			break;
			case 'edit_theme':
				checkAdmin('manage_content');

				//region validation
				$adapter = new ValidatorAdapter(library(TinyMVC_Library_validator::class));
				$validator = new ThemeValidator($adapter);

				$validator = new AggregateValidator([$validator]);
				if (!$validator->validate(new FlatValidationData(request()->request->all() ?? []))) {
					\jsonResponse(
						\array_map(
							fn (ConstraintViolation $violation): ?string => $violation->getMessage(),
							\iterator_to_array($validator->getViolations()->getIterator())
						)
					);
				}
				//endregion validation

				$idTheme = request()->request->getInt('id');

				$updateData = ['theme' => cleanInput(request()->get('theme'))];
				if (model(Complains_Model::class)->editComplainTheme($idTheme, $updateData)) {
					model(Complains_Model::class)->clearRelations($idTheme);

					if ($this->setThemeTypeRelations(request()->get('types'), $idTheme)) {
						jsonResponse('The theme has been successfully added.', 'success');
					}

					jsonResponse('Cannot set relationship with theme.');
				}

				jsonResponse('Error: You cannot add this theme now. Please try again later.');

			break;
			case 'delete_theme':
				checkAdmin('manage_content');

				$this->load->model('Complains_Model', 'complains');

				$idTheme = request()->request->getInt('id_theme');
				if (model(Complains_Model::class)->deleteTheme($idTheme)) {
					model(Complains_Model::class)->clearRelations($idTheme);
					jsonResponse('The theme has been successfully removed.', 'success');
				}

				jsonResponse('Error: You cannot remove this theme now. Please try again later.');
			break;
		}
	}

	public function popup_forms()
	{
		checkIsAjax();

		$idUser = id_session();

		$op = $this->uri->segment(3);

		switch ($op) {
			case 'update_complain':
				checkIsLoggedAjaxModal();
				checkAdmin('manage_content');

				$id = $this->uri->segment(4);

				$data = [
					'user'      => $idUser,
					'complain'  => model(Complains_Model::class)->getDetails($id)
				];

				$data['complain']['notice'] = json_decode('[' . $data['complain']['notice'] . ']', true);

				views('admin/complains/add_notice_form_view', $data);
			break;
			case 'add_complain':
				checkIsLoggedAjaxModal();

				$idTo = (int) $this->uri->segment(6);
				$idCompany = (int) $this->uri->segment(7);

				if ($idTo == id_session()) {
					messageInModal(translate('systmess_error_cannot_report_yourself'));
				}

				$type = model(Complains_Model::class)->getTypeByKey(cleanInput($this->uri->segment(4)));
				if (empty($type)) {
					messageInModal(translate('systmess_error_invalid_data'));
				}

                $webpackData = "webpack" === request()->headers->get("X-Script-Mode", "legacy");

				$type += [
					'id_item'     => (int) $this->uri->segment(5),
					'id_to'       => $idTo,
					'id_company'  => $idCompany,
					'link'        => $_SERVER['HTTP_REFERER'],
					'themes'      => model(Complains_Model::class)->getThemesByType($type['id_type']),
                    'webpackData' => $webpackData,
				];

				$this->view->display('new/complains/complain_form_view', $type);
			break;
			case 'update_complain_theme':
				checkIsLoggedAjaxModal();
				checkAdmin('manage_content');

				$id = $this->uri->segment(4);
				$data = [
					'types'             => model(Complains_Model::class)->getTypes(),
					'complain_theme'    => model(Complains_Model::class)->getTheme($id, true)
				];

				$data['complain_theme']['types'] = explode(',', $data['complain_theme']['types']);

				views('admin/complains/complains_themes_form_view', $data);
			break;
			case 'add_complain_theme':
				checkIsLoggedAjaxModal();
				checkAdmin('manage_content');

				views('admin/complains/complains_themes_form_view', ['types' => model(Complains_Model::class)->getTypes()]);
			break;
		}
	}

	public function types_themes_administration()
	{
		checkAdmin('manage_content');

		views()->assign([
			'title'     => 'Reports types themes',
			'types'     => model(Complains_Model::class)->getTypes(),
			'themes'    => model(Complains_Model::class)->getThemes()
		]);

		views(['admin/header_view', 'admin/complains/themes_index_view', 'admin/footer_view']);
	}

	public function ajax_complains_themes_dt()
	{
		checkIsAjax();
		checkIsLoggedAjax();
		checkAdmin('manage_content');

		$conditions = array_merge(
			[
				'joins'        => true,
				'per_p'        => request()->request->getInt('iDisplayLength'),
				'start'        => request()->request->getInt('iDisplayStart'),
				'sort_by'      => flat_dt_ordering(request()->request->all(), [
					'dt_id'        => 'id_theme',
					'dt_type'      => 'type',
					'dt_theme'     => 'theme'
				]),
			],
			dtConditions(request()->request->all(), [
				['as' => 'keywords',  'key' => 'search',    'type' => 'string'],
				['as' => 'type',      'key' => 'type',      'type' => 'int'],
				['as' => 'theme',     'key' => 'theme',     'type' => 'int']
			])
		);

		$themes = model(Complains_Model::class)->getComplainsThemes($conditions);
		$records_total = model(Complains_Model::class)->getCountComplainsThemes($conditions);

		$output = [
			'sEcho'                 => request()->request->getInt('sEcho'),
			'iTotalRecords'         => $records_total,
			'iTotalDisplayRecords'  => $records_total,
			'aaData'                => []
		];

		if (empty($themes)) {
			jsonResponse('', 'success', $output);
		}

		$allTypes = model(Complains_Model::class)->getTypes();
		$typesArray = [];
		foreach ($allTypes as $oneType) {
			$typesArray[$oneType['id_type']] = $oneType['type'] . '
                <a class="ep-icon ep-icon_filter txt-green dt_filter"
                data-title="Type"
                title="Filter by ' . $oneType['type'] . '"
                data-value-text="' . $oneType['type'] . '"
                data-value="' . $oneType['id_type'] . '"
                data-name="type"></a>';
		}

		foreach ($themes as $theme) {
			$tempTypes = explode(',', $theme['type']);
			$types = [];
			foreach ($tempTypes as $type) {
				$types[] = $typesArray[$type];
			}
			$output['aaData'][] = [
				'dt_id'         => $theme['id_theme'],
				'dt_type'       => implode(', ', $types),
				'dt_theme'      => $theme['theme'],
				'dt_actions'    => '<a
                    class="ep-icon ep-icon_pencil fancyboxValidateModal fancybox.ajax"
                    title="Update report theme"
                    href="complains/popup_forms/update_complain_theme/' . $theme['id_theme'] . '"
                    data-title="Edit report theme"></a>' .
				'<a class="ep-icon ep-icon_remove txt-red confirm-dialog"
                    data-callback="remove_complain_theme"
                    data-message="Are you sure you want to delete this theme?"
                    title="Delete report theme"
                    data-theme="' . $theme['id_theme'] . '"></a>'
			];
		}

		jsonResponse('', 'success', $output);
	}

	private function setThemeTypeRelations($types, $idTheme)
	{
		$insertBatch = [];
		foreach ($types as $type) {
			$insertBatch[] = [
				'id_type'  => (int) $type,
				'id_theme' => $idTheme
			];
		}

		return (bool) (model(Complains_Model::class)->addRelComplainsTypes($insertBatch));
	}

	private function generate_link($data)
	{
		$link = '';
		extract(arrayCamelizeAssocKeys($data));
		if (in_array($idType, $this->companyRelated)) {
			$indexName = '';
			$companyDetails = model(Company_Model::class)->getSimpleCompanyByIdUser($idTo, 'index_name,type_company,name_company,id_company', !empty($idCompany) ? $idCompany : false);
            $link = getCompanyURL($companyDetails, false);
		}

		switch ($idType) {
			case 1:
			case 2:
			case 11:
				$link = 'item/' . $idItem;
			break;
			case 3:
				$link = 'usr/' . $idTo;
			break;
			case 5:
				$link .= '/view_news/' . $idItem;
			break;
			case 8:
				$link .= '/updates/';
			break;
			case 6:
			case 22:
				$link = (($indexName) ? $indexName . '/picture/' . $idItem : '');
			break;
			case 7:
				$link .= '/videos';
			break;
			case 10:
			case 12:
			case 20:
				$link = get_static_url('questions/index');
			break;
			case 13:
				$link = !empty($idCompany) ? 'usr/' . $idCompany . '#reviews' : $referer;
			break;
			case 14:
				$link  = !empty($idCompany) ? 'usr/' . $idCompany . '#feedbacks' : $referer;
			break;
			case 15:
                /** @var Blogs_Model $blogsModel */
                $blogsModel = model(Blogs_Model::class);
                $blogs = $blogsModel->findOne($idItem, ['with' => ['category']]);

                $link = getBlogUrl([
                    'category_url'  => $blogs['category']['url'],
                    'title_slug'    => $blogs['title_slug'],
                    'id'            => $blogs['id'],
                ]);
			break;
			case 16:
			case 21:
				$link = $referer;
			break;
			case 17:
				$link = 'messages';
			break;
			case 18:
				$link = 'order/admin_assigned';
			break;
			case 23:
				$link = (($indexName) ? $indexName . '/video/' . $idItem : '');
			break;
			case 25:
				$link = '/shipper/' . $idItem;
			break;
		}

		return $link;
	}

	private function get_user_from_dt_text($complain, $online)
	{

        //TODO: admin chat hidden
        $btnChatFrom = new ChatButton(['hide' => true, 'recipient' => $complain['id_from'], 'recipientStatus' => 'active', 'module' => 13, 'item' => $complain['id']], ['classes' => 'btn-chat-now', 'text' => '']);
        $btnChatFromView = $btnChatFrom->button();

		return '<div class="tal">
                    <a class="ep-icon ep-icon_filter txt-green dt_filter"
                        data-title="User"
                        title="Filter by ' . $complain['user_name'] . '"
                        data-value-text="' . $complain['user_name'] . '"
                        data-value="' . $complain['id_from'] . '"
                        data-name="user"></a>' .
					'<a class="ep-icon ep-icon_onoff ' . (($complain['user_logged']) ? 'txt-green' : 'txt-red') . ' dt_filter"
                        title="Filter just ' . $online . '"
                        data-value="' . $complain['user_logged'] . '"
                        data-value-text="' . $online . '"
                        data-name="online"
                        data-title="OnLine/OffLine"></a>' .
					'<a class="ep-icon ep-icon_user"
                        title="View personal page of ' . $complain['user_name'] . '"
                        target="_blank"
                        href="' . __SITE_URL . 'usr/' . strForURL($complain['user_name']) . '-' . $complain['id_from'] . '"></a>' .
                    $btnChatFromView .
					'</div>
				<div>' . $complain['user_name'] . '</div>';
	}

	private function get_user_to_dt_text($complain, $reported_online)
	{

        //TODO: admin chat hidden
        $btnChatTo = new ChatButton(['hide' => true, 'recipient' => $complain['id_to'], 'recipientStatus' => 'active', 'module' => 13, 'item' => $complain['id']], ['classes' => 'btn-chat-now', 'text' => '']);
        $btnChatToView = $btnChatTo->button();

		return '<div class="pull-left">
                    <a class="ep-icon ep-icon_filter txt-green dt_filter"
                        data-title="Reported user"
                        title="Filter by ' . $complain['reported_user'] . '"
                        data-value-text="' . $complain['reported_user'] . '"
                        data-value="' . $complain['id_to'] . '"
                        data-name="reported_user"></a>' .
					'<a class="ep-icon ep-icon_onoff ' . (($complain['reported_user_logged']) ? 'txt-green' : 'txt-red') . ' dt_filter"
                        title="Filter just reported ' . $reported_online . '"
                        data-value="' . $complain['reported_user_logged'] . '"
                        data-value-text="' . $reported_online . '"
                        data-name="reported_online"
                        data-title="Reported user OnLine/OffLine"></a>' .
					'<a class="ep-icon ep-icon_user"
                        title="View personal page of ' . $complain['reported_user'] . '"
                        target="_blank"
                        href="' . __SITE_URL . 'usr/' . strForURL($complain['reported_user']) . '-' . $complain['id_to'] . '"></a>' .
                    $btnChatToView .
					'</div>
                <div class="clearfix"></div>
				<div class="pull-left">' . $complain['reported_user'] . '</div>';
	}
}
