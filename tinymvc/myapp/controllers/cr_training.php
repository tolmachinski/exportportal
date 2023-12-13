<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Cr_training_Controller extends TinyMVC_Controller {

    private $icon_types = array(
        "training" => array(
            "icon" => "ep-icon_training txt-green",
            "title" => "Training"
        ),
        "webinar" => array(
            "icon" => "ep-icon_webinar txt-orange",
            "title" => "Webinar"
        )
    );


	private function _load_main() {
		$this->load->model("Cr_Training_Model", "cr_training");
	}

	public function my() {
        if(!logged_in()){
            headerRedirect(__SITE_URL."login");
        }

        if(!have_right("manage_cr_trainings")){
            headerRedirect(__SITE_URL);
        }

        $this->_load_main();

        $this->view->assign("title", "My trainings");
        $this->view->display('new/header_view');
        $this->view->display('new/cr/my/index_view');
        $this->view->display('new/footer_view');
	}

	function ajax_my_trainings() {
		if (!isAjaxRequest()) {
			headerRedirect();
        }

        if(!logged_in()){
            jsonDTResponse(translate("systmess_error_should_be_logged"));
        }

        if(!have_right("manage_cr_trainings")){
            jsonDTResponse(translate("systmess_error_rights_perform_this_action"));
        }

        $this->_load_main();

        $conditions = array(
            'id_user' => id_session()
        );

        if (isset($_POST["iDisplayStart"]) && ($_POST["iDisplayLength"] != -1)) {
            $from = intval(cleanInput($_POST["iDisplayStart"]));
            $till = intval(cleanInput($_POST["iDisplayLength"]));
            $conditions["limit"] = $from . "," . $till;
        }

        $sort_by = flat_dt_ordering($_POST, array(
            'dt_id' => 'id_training',
            'dt_title' => 'training_title',
            'dt_type' => 'training_type',
            'dt_start_date' => 'training_start_date',
            'dt_finish_date' => 'training_finish_date',
        ));

        $conditions = array_merge(
            [
                'sort_by' => $sort_by
            ],
            dtConditions($_POST, [
                ['as' => 'date_to', 'key' => 'date_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d,H:i:s'],
                ['as' => 'date_from', 'key' => 'date_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d,H:i:s'],
                ['as' => 'start_date_to', 'key' => 'start_date_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'start_date_from', 'key' => 'start_date_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'finish_date_to', 'key' => 'finish_date_to', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'finish_date_from', 'key' => 'finish_date_from', 'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'type_filter', 'key' => 'type_filter', 'type' => 'cleanInput'],
                ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput']
            ])
        );

        $trainings_list = $this->cr_training->get_trainings($conditions);
        $records_total = $this->cr_training->count_trainings($conditions);

        $output = array(
            "sEcho" => intval($_POST["sEcho"]),
            "iTotalRecords" => $records_total,
            "iTotalDisplayRecords" => $records_total,
            "aaData" => array()
        );

        if(empty($trainings_list)) {
            jsonResponse("", "success", $output);
        }

        foreach ($trainings_list as $training) {
            $output['aaData'][] = array(
//                "dt_title" => "<a class=\"link-black\" rel=\"training_details\" title=\"View details\">{$training["training_title"]}</a>",
                "dt_title" => "<a class=\"link-black call-function\" data-callback=\"trainingInformationFancybox\" title=\"View training info\" href=\"#info-training\">{$training["training_title"]}</a>",
                // "dt_description" => $training["training_description"],
                "dt_start_date" => formatDate($training["training_start_date"], 'j M, Y H:i'),
                "dt_finish_date" => formatDate($training["training_finish_date"], 'j M, Y H:i'),
                "dt_type" => $this->icon_types[$training["training_type"]]["title"],
            );
        }

        jsonResponse("", "success", $output);
	}

	function administration() {

        checkAdmin("cr_training_administration");

		$this->_load_main();

		$data["title"] = "Comunity trainings administration";

		$this->view->assign($data);
		$this->view->display("admin/header_view");
		$this->view->display("admin/cr/trainings/index_view");
		$this->view->display("admin/footer_view");
	}

    function ajax_administration_dt() {

		if (!isAjaxRequest()) {
			headerRedirect();
        }

        checkAdminAjaxDT("cr_training_administration");

		$type = $this->uri->segment(3);
        $this->_load_main();

		switch ($type) {
			case "trainings":
                $conditions = array();

				if (isset($_POST["iDisplayStart"]) && ($_POST["iDisplayLength"] != -1)) {
					$from = intval(cleanInput($_POST["iDisplayStart"]));
					$till = intval(cleanInput($_POST["iDisplayLength"]));
					$conditions["limit"] = $from . "," . $till;
                }

                $conditions = array_merge(
                    [
                        'per_p' => intVal($_POST['iDisplayLength']),
                        'start' => intVal($_POST['iDisplayStart']),
                        'sort_by' => flat_dt_ordering($_POST, [
                            'dt_id' => 'id_training',
                            'dt_title' => 'training_title',
                            'dt_type' => 'training_type',
                            'dt_date' => 'training_date',
                            'dt_start_date' => 'training_start_date',
                            'dt_finish_date' => 'training_finish_date'
                        ])
                    ],
                    dtConditions($_POST, [
                        ['as' => 'date_to', 'key' => 'date_to', 'type' => 'formatDate:Y-m-d|concat: 23:59:59'],
                        ['as' => 'date_from', 'key' => 'date_from', 'type' => 'formatDate:Y-m-d|concat: 00:00:00'],
                        ['as' => 'start_date_to', 'key' => 'start_date_to', 'type' => 'formatDate:Y-m-d|concat: 23:59:59'],
                        ['as' => 'start_date_from', 'key' => 'start_date_from', 'type' => 'formatDate:Y-m-d|concat: 00:00:00'],
                        ['as' => 'finish_date_to', 'key' => 'finish_date_to', 'type' => 'formatDate:Y-m-d|concat: 23:59:59'],
                        ['as' => 'finish_date_from', 'key' => 'finish_date_from', 'type' => 'formatDate:Y-m-d|concat: 00:00:00'],
                        ['as' => 'type_filter', 'key' => 'type_filter', 'type' => 'cleanInput'],
                        ['as' => 'keywords', 'key' => 'keywords', 'type' => 'cleanInput']
                    ])
                );

				$trainings_list = $this->cr_training->get_trainings($conditions);
				$records_total = $this->cr_training->count_trainings($conditions);

				$output = array(
					"sEcho" => intval($_POST["sEcho"]),
					"iTotalRecords" => $records_total,
					"iTotalDisplayRecords" => $records_total,
					"aaData" => array()
				);

				if(empty($trainings_list)) {
					jsonResponse("", "success", $output);
                }

				foreach ($trainings_list as $training) {
					$output["aaData"][] = array(
						"dt_id" => $training["id_training"] . '<br><input type="checkbox" class="check-training mt-1" data-id-training="' .$training["id_training"] . '""> <a rel="training_details" title="View details" class="ep-icon ep-icon_plus"></a>',
						"dt_title" => $training["training_title"],
                        "dt_type" => "<a class='dt_filter' data-name='type_filter' data-value-text='".$this->icon_types[$training["training_type"]]["title"]."' data-title='".$this->icon_types[$training["training_type"]]["title"]."' data-value='".$training["training_type"]."'><span><i class='ep-icon " . $this->icon_types[$training["training_type"]]["icon"] . " fs-30'></i><br> " . $this->icon_types[$training["training_type"]]["title"] . "</span></a>",
                        "dt_description" => "<div class='p-10'>" . $training["training_description"] . "</div>",
						"dt_date" => formatDate($training["training_date"]),
						"dt_start_date" => formatDate($training["training_start_date"]),
						"dt_finish_date" => formatDate($training["training_finish_date"]),
                        //'dt_count_ambassadors' => '<span>' . $training["training_count_ambassadors"] . '</span>' . '&nbsp;&nbsp;&nbsp; <a class="ep-icon txt-blue ep-icon_user-plus fancyboxValidateModalDT fancybox.ajax" data-submit-callback="on_users_selected" data-table="dtTrainingsList" href="' .  __SITE_URL . 'cr_users/popup_forms/assign_users?type=training&id_item=' . $training['id_training'] . '" title="Assign ambassadors" data-title="Assign ambassadors"></a>',
						"dt_actions" =>
							'<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax m-2" title="Edit Training" href="'.__SITE_URL.'cr_training/popup_forms/edit_training_admin/' . $training["id_training"] . '" data-title="Edit training" id="training-' . $training["id_training"] . '"></a>
                            <a class="ep-icon ep-icon_remove txt-red confirm-dialog m-2" data-callback="delete_training" data-message="Are you sure want delete this training?" title="Delete training" data-training="'.$training["id_training"].'"></a>
                            <a class="ep-icon txt-blue ep-icon_user-plus fancyboxValidateModalDT fancybox.ajax m-2" data-submit-callback="on_users_selected" data-table="dtTrainingsList" href="' .  __SITE_URL . 'cr_users/popup_forms/assign_users?type=training&id_item=' . $training['id_training'] . '" title="Assign ambassadors" data-title="Assign ambassadors"></a>
                            '
					);
				}

				jsonResponse("", "success", $output);
			break;
		}
    }

	function ajax_trainings_operation() {
		if (!isAjaxRequest()) {
			headerRedirect();
        }

        $this->_load_main();
		$op = $this->uri->segment(3);
		switch ($op) {
			case "add_training":
                checkAdminAjax("cr_training_administration");

                $validator_rules = array(
                    array(
                        "field" => "title",
                        "label" => "Title",
                        "rules" => array("required" => "", "max_len[255]" => "")
                    ),
                    array(
                        "field" => "description",
                        "label" => "Description",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "start_date",
                        "label" => "Start date",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "finish_date",
                        "label" => "Finish date",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "type",
                        "label" => "Type",
                        "rules" => array("required" => "")
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
				}

				$this->load->library("Cleanhtml", "clean");
                $insert = array(
                    "training_title" => cleanInput($_POST["title"]),
                    "training_description" => $this->clean->sanitizeUserInput($_POST["description"]),
                    "training_start_date" => formatDate($_POST["start_date"], "Y-m-d H:i:s"),
                    "training_finish_date" => formatDate($_POST["finish_date"], "Y-m-d H:i:s"),
                    "training_type" => cleanInput($_POST["type"])
                );

                $this->cr_training->insert_training($insert);
                jsonResponse("Your training has been successfully saved!", "success");
                break;
			case "edit_training":
                checkAdminAjax("cr_training_administration");

                $validator_rules = array(
                    array(
                        "field" => "title",
                        "label" => "Title",
                        "rules" => array("required" => "", "max_len[255]" => "")
                    ),
                    array(
                        "field" => "description",
                        "label" => "Description",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "id",
                        "label" => "Training info",
                        "rules" => array("required" => "", "is_natural_no_zero" => "")
                    ),
                    array(
                        "field" => "start_date",
                        "label" => "Start date",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "finish_date",
                        "label" => "Finish date",
                        "rules" => array("required" => "")
                    ),
                    array(
                        "field" => "type",
                        "label" => "Type",
                        "rules" => array("required" => "")
                    )
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
				}

                $id = intVal($_POST["id"]);
				$training_info = $this->cr_training->get_training($id);

				if (empty($training_info)) {
                    jsonResponse("Error: The training does not exist.");
				}

				$this->load->library("Cleanhtml", "clean");
                $update = array(
                    "training_title" => cleanInput($_POST["title"]),
                    "training_description" => $this->clean->sanitizeUserInput($_POST["description"]),
                    "training_start_date" => formatDate($_POST["start_date"], "Y-m-d H:i:s"),
                    "training_finish_date" => formatDate($_POST["finish_date"], "Y-m-d H:i:s"),
                    "training_type" => cleanInput($_POST["type"])
                );

                $this->cr_training->update_training($id, $update);
                jsonResponse("Your training has been successfully updated!", "success");
                break;

			case "delete_trainings":
                checkAdminAjax("cr_training_administration");

				$checked_trainings = cleanInput($_POST["training"]);
				$checked_trainings = explode(",", $checked_trainings);
				$checked_trainings = array_filter($checked_trainings);

				if (empty($checked_trainings)) {
					jsonResponse("Error: There are no training(s) to be deleted.");
				}

                //todo delete and the relations
				$this->cr_training->delete_training(implode(",", $checked_trainings));

				jsonResponse("The training(s) has been deleted", "success");
			break;
        }
	}

	function popup_forms() {
		if (!isAjaxRequest()) {
			headerRedirect();
        }

		$this->_load_main();
		$op = $this->uri->segment(3);

		switch ($op) {
			case "add_training_admin":
                checkAdminAjaxModal("cr_training_administration");

				$this->view->display("admin/cr/trainings/training_form_view");
			break;
			case "edit_training_admin":
                checkAdminAjaxModal("cr_training_administration");

                $id = (int) $this->uri->segment(4);
				$data["training"] = $this->cr_training->get_training($id);
				if (empty($data["training"])){
					messageInModal("Error: This training does not exist.");
				}

				$this->view->assign($data);
				$this->view->display("admin/cr/trainings/training_form_view");
			break;
		}
	}
}
