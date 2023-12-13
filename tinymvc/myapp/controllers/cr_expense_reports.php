<?php

use App\Common\Buttons\ChatButton;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Cr_expense_reports_Controller extends TinyMVC_Controller {

    /*statuses*/
    const STATUS_INIT = "init";
    const STATUS_IN_PROGRESS = "in_progress";
    const STATUS_PROCESSED = "processed";
    const STATUS_DECLINED = "declined";

    private $icon_statuses = array(
        "init" => array(
            "icon" => "ep-icon_new-stroke txt-gray",
            "title" => "New"
        ),
        "in_progress" => array(
            "icon" => "ep-icon_hourglass-processing txt-orange",
            "title" => "Processing"
        ),
        "processed" => array(
            "icon" => "ep-icon_ok-circle txt-green",
            "title" => "Processed"
        ),
        "declined" => array(
            "icon" => "ep-icon_minus-circle txt-red",
            "title" => "Declined"
        ),
    );

	public function my() {
        checkPermision('manage_cr_expense_reports');

		$data = array(
            'title' => 'Expense reports',
            'ereports_statuses' => $this->icon_statuses
		);

        $this->view->assign($data);
        $this->view->display('new/header_view');
        $this->view->display('new/cr/my/expense_reports/index_view');
        $this->view->display('new/footer_view');
    }

	function administration() {
		checkAdmin("cr_expense_reports_administration");

		$data["title"] = "Expense reports administration";

		$this->view->assign($data);
		$this->view->display("admin/header_view");
		$this->view->display("admin/cr/expense_reports/index_view");
		$this->view->display("admin/footer_view");
	}

	function popup_forms() {
		if (!isAjaxRequest()) {
			headerRedirect();
        }

		$this->load->model("Cr_Expense_Reports_Model", "cr_ereports");
		$op = $this->uri->segment(3);

		switch ($op) {
            // DONE
			case "add":
                checkPermisionAjaxModal('manage_cr_expense_reports');

                // Prepare rule for allowed file types
                $formats = explode(',', config('fileupload_image_formats', 'jpg,jpeg,png,gif,bmp'));
                $mimetypes = array_filter(array_unique(array_map(
                    function ($extension) {
                        return Hoa\Mime\Mime::getMimeFromExtension($extension);
                    },
                    $formats
                )));
                $accept = implode(', ', $mimetypes);
                $formats = implode('|', $formats);
                $mimetypes = implode('|', array_map(function ($type) { return preg_quote($type); }, $mimetypes));
                $data['fileupload_limits'] = array(
                    'amount'              => 10,
                    'accept'              => $accept,
                    'formats'             => $formats,
                    'mimetypes'           => $mimetypes,
                    'image_size'          => config('fileupload_max_file_size', 1024 * 1024 * 10),
                    'image_size_readable' => config('fileupload_max_file_size_placeholder', '10MB')
                );

                $data['upload_folder'] = encriptedFolderName();

				$this->view->assign($data);
                $this->view->display("new/cr/my/expense_reports/form_view");
            break;
            // DONE
            case "edit":
                checkPermisionAjaxModal('manage_cr_expense_reports,cr_expense_reports_administration');

                $id_ereport = (int) $this->uri->segment(4);
                $data["ereport"] = $this->cr_ereports->get_report($id_ereport);
                if(empty($data["ereport"])){
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                // Prepare rule for allowed file types
                $formats = explode(',', config('fileupload_image_formats', 'jpg,jpeg,png,gif,bmp'));
                $mimetypes = array_filter(array_unique(array_map(
                    function ($extension) {
                        return Hoa\Mime\Mime::getMimeFromExtension($extension);
                    },
                    $formats
                )));
                $accept = implode(', ', $mimetypes);
                $formats = implode('|', $formats);
                $mimetypes = implode('|', array_map(function ($type) { return preg_quote($type); }, $mimetypes));
                $data['fileupload_limits'] = array(
                    'amount'              => 10,
                    'accept'              => $accept,
                    'formats'             => $formats,
                    'mimetypes'           => $mimetypes,
                    'image_size'          => config('fileupload_max_file_size', 1024 * 1024 * 10),
                    'image_size_readable' => config('fileupload_max_file_size_placeholder', '10MB')
                );

                $data['upload_folder'] = encriptedFolderName();

				$this->view->assign($data);

				if (have_right('cr_expense_reports_administration')) {
					$this->view->display("admin/cr/expense_reports/expense_report_form_view");
				} else{
					$this->view->display("new/cr/my/expense_reports/form_view");
				}
            break;
            // DONE
			case "decline":
                checkAdminAjaxModal("cr_expense_reports_administration");

                $data = array(
                    "id_ereport" => (int) $this->uri->segment(4)
                );

                $this->view->assign($data);
				$this->view->display("admin/cr/expense_reports/decline_expense_report_view");
            break;
            // DONE
            case 'details':
                checkAdminAjaxModal("manage_cr_expense_reports");

                $id_ereport = (int) $this->uri->segment(4);
                $data["ereport"] = $this->cr_ereports->get_report($id_ereport);
                if(empty($data["ereport"])){
                    messageInModal(translate('systmess_error_invalid_data'));
                }

                $this->view->assign($data);
                $this->view->display("new/cr/my/expense_reports/details_view");
            break;
		}
	}

	function ajax_operations() {
		if (!isAjaxRequest()) {
			headerRedirect();
        }

		$this->load->model("Cr_Expense_Reports_Model", "cr_ereports");

		$type = $this->uri->segment(3);
		switch ($type) {
            // DONE
            case 'add':
                checkPermisionAjax('manage_cr_expense_reports');

                $validator_rules = array(
                    array(
                        "field" => "title",
                        "label" => "Title",
                        "rules" => array("required" => "", "max_len[255]" => "")
                    ),
                    array(
                        "field" => "description",
                        "label" => "Description",
                        "rules" => array("required" => "", "max_len[1000]" => "")
                    ),
                    array(
                        "field" => "refund_amount",
                        "label" => "Refund amount",
                        "rules" => array("required" => "", "positive_number" => "")
                    ),
                );

                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()){
                    jsonResponse($this->validator->get_array_errors());
                }

                $this->load->library("Cleanhtml", "clean");
                $insert = array(
                    "ereport_title" => cleanInput($_POST["title"]),
                    "ereport_description" => cleanInput($_POST["description"]),
                    "ereport_refund_amount" => get_price($_POST["refund_amount"], false),
                    "id_user" => privileged_user_id(),
                );

                $id_ereport = $this->cr_ereports->insert_report($insert);
                if(!$id_ereport){
                    jsonResponse('The expense report can not be added now. Please try again later.');
                }

                if(!empty($_POST["images"])) {
                    $path = "public/expense_reports/{$id_ereport}";
                    create_dir($path);

                    $images = array();
                    foreach($_POST["images"] as $image_path) {
                        $parts = explode("/", $image_path);
                        $image_name = end($parts);

                        $parts = explode(".", $image_name);
                        $image_extension = end($parts);

                        if(file_exists($image_path) && copy($image_path, "{$path}/{$image_name}")) {
                            $images[$image_name] = array(
                                "name" => $image_name,
                                "type" => $image_extension
                            );
                        }
                    }

                    if(!empty($images)) {
                        $update = array(
                            "ereport_photos" => json_encode($images)
                        );

                        $this->cr_ereports->update_report($id_ereport, $update);
                    }
                }

                jsonResponse("Your expense report has been successfully saved!", "success");
			break;
            // DONE
			case 'edit':
				checkPermisionAjax('manage_cr_expense_reports,cr_expense_reports_administration');

				$_is_admin = have_right('cr_expense_reports_administration');

				$validator_rules = array(
					array(
						"field" => "title",
						"label" => "Title",
						"rules" => array("required" => "", "max_len[255]" => "")
					),
					array(
						"field" => "description",
						"label" => "Description",
                        "rules" => array("required" => "", "max_len[1000]" => "")
					),
					array(
						"field" => "refund_amount",
						"label" => "Refund price",
                        "rules" => array("required" => "", "positive_number" => "")
					),
					array(
						"field" => "id",
						"label" => "Report info",
						"rules" => array("required" => "", "is_natural_no_zero" => "")
					)
				);

				if($_is_admin){
					$validator_rules[] = array(
                        "field" => "status",
                        "label" => "Status",
                        "rules" => array("required" => "")
                    );
				}

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
				}

				$id_ereport = (int) $_POST["id"];
				$ereport = $this->cr_ereports->get_report($id_ereport);

				if (empty($ereport)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$_edit_statuses = array(self::STATUS_INIT);
				if($_is_admin){
					$_edit_statuses[] = self::STATUS_IN_PROGRESS;
				}

				if(!in_array($ereport['ereport_status'], $_edit_statuses)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

				$update = array(
					"ereport_title" => cleanInput($_POST["title"]),
                    "ereport_description" => cleanInput($_POST["description"]),
					"ereport_refund_amount" => get_price($_POST["refund_amount"], false)
				);

				$ereport_status = cleanInput($_POST["status"]);
				if($_is_admin && in_array($ereport_status, array(self::STATUS_INIT, self::STATUS_IN_PROGRESS, self::STATUS_PROCESSED, self::STATUS_DECLINED))){
					$update["ereport_status"] = $ereport_status;
				}

                if(!empty($_POST["images"])) {
                    $path = "public/expense_reports/{$id_ereport}";
                    create_dir($path);

					$images = json_decode($ereport['ereport_photos'], true);
					if(empty($images)){
						$images = array();
					}

                    foreach($_POST["images"] as $image_path) {
                        $parts = explode("/", $image_path);
                        $image_name = end($parts);

                        $parts = explode(".", $image_name);
                        $image_extension = end($parts);

                        if(file_exists($image_path) && copy($image_path, "{$path}/{$image_name}")) {
                            $images[$image_name] = array(
                                "name" => $image_name,
                                "type" => $image_extension
                            );
                        }
                    }

                    if(!empty($images)) {
                        $update["ereport_photos"] = json_encode($images);
                    }
                }

				$this->cr_ereports->update_report($id_ereport, $update);
				jsonResponse("Your expense report has been successfully updated!", "success");
            break;
            // DONE
            case 'delete':
                checkPermisionAjax('manage_cr_expense_reports');

                $id_ereport = (int) $_POST["expense_report"];
                $ereport = $this->cr_ereports->get_report($id_ereport);
				if (empty($ereport)) {
					jsonResponse(translate('systmess_error_invalid_data'));
                }

                if($ereport["ereport_status"] == self::STATUS_INIT) {
                    //DELETE THE RECORD
                    $this->cr_ereports->delete_report($id_ereport);
                    $photos = json_decode($ereport["ereport_photos"], true);
                    foreach($photos as $photoname => $photo) {
                        @unlink( "public/expense_reports/" . $ereport["id_ereport"] . "/" . $photoname);
                    }
                } else {
                    //DELETE LOGICALLY
                    $update = array("ereport_removed" => 1);
                    $this->cr_ereports->update_report($id_ereport, $update);
                }

                jsonResponse("The expense report has been deleted", "success");
            break;
            // DONE
            case "my_dt":
                checkPermisionAjaxDT('manage_cr_expense_reports');

                $id_user = id_session();
                $params = array(
                    'per_p' => (int) $_POST["iDisplayLength"],
                    'start' => (int) $_POST["iDisplayStart"],
                    'id_user' => $id_user,
                    'removed' => 0
                );

                if (!empty($_POST['start_from']) && validateDate($_POST['start_from'], 'm/d/Y')) {
                    $params['created_from'] = getDateFormat($_POST['start_from'], 'm/d/Y', 'Y-m-d 00:00:00');
                }

                if (!empty($_POST['start_to']) && validateDate($_POST['start_to'], 'm/d/Y')) {
                    $params['created_to'] = getDateFormat($_POST['start_to'], 'm/d/Y', 'Y-m-d 23:59:59');
                }

                if (!empty($_POST['update_from']) && validateDate($_POST['update_from'], 'm/d/Y')) {
                    $params['updated_from'] = getDateFormat($_POST['update_from'], 'm/d/Y', 'Y-m-d 00:00:00');
                }

                if (!empty($_POST['update_to']) && validateDate($_POST['update_to'], 'm/d/Y')) {
                    $params['updated_to'] = getDateFormat($_POST['update_to'], 'm/d/Y', 'Y-m-d 23:59:59');
                }

                if (!empty($_POST["refund_amount_from"])) {
                    $params["refund_amount_from"] = get_price($_POST["refund_amount_from"], false);
                }

                if (!empty($_POST["refund_amount_to"])) {
                    $params["refund_amount_to"] = get_price($_POST["refund_amount_to"], false);
                }

                if (!empty($_POST["status_filter"])) {
                    $status_filter = cleanInput($_POST["status_filter"]);
                    if(isset($this->icon_statuses[$status_filter])){
                        $params["status_filter"] = $status_filter;
                    }
                }

                if (!empty($_POST["keywords"])) {
                    $params["keywords"] = cleanInput($_POST["keywords"]);
                }

                $sort_by = flat_dt_ordering($_POST, array(
                    'dt_refund_amount' => 'cer.ereport_refund_amount',
                    'dt_status' => 'cer.ereport_status',
                    'dt_created' => 'cer.ereport_date',
                    'dt_updated' => 'cer.ereport_updated'
                ));

                if(!empty($sort_by)){
                    $params['sort_by'] = $sort_by;
                }

                $ereports_list = $this->cr_ereports->get_reports($params);
                $records_total = $this->cr_ereports->count_reports($params);

                $output = array(
                    "sEcho" => intval($_POST["sEcho"]),
                    "iTotalRecords" => $records_total,
                    "iTotalDisplayRecords" => $records_total,
                    "aaData" => array()
                );

                if(empty($ereports_list)) {
                    jsonResponse("", "success", $output);
                }

                $output['aaData'] = $this->_dt_my_new($ereports_list);

                jsonResponse("", "success", $output);
            break;
            // DONE
            case "upload_files":
                checkPermisionAjax('manage_cr_expense_reports,cr_expense_reports_administration');

                if(empty($_FILES["files"])){
                    jsonResponse("Error: Please select file to upload.");
                }

                $upload_folder = checkEncriptedFolder($this->uri->segment(4));
                if($upload_folder === false){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $path = config("default_temp_expense_report", $this->ereports->default_temp_folder)."/" . id_session()."/" . $upload_folder;
                create_dir($path);

                $conditions = array(
                    "data_files" => $_FILES["files"],
                    "path" => $path,
                    "rules" => array(
                        'size'       => config('fileupload_max_file_size', 1024 * 1024 * 10),
                        'format'     => config('fileupload_image_formats', 'jpg,jpeg,png,gif,bmp')
                    )
                );
                $upload_results = $this->upload->upload_files_new($conditions);

                if (!empty($upload_results["errors"])) {
                    jsonResponse($upload_results["errors"]);
                }

                foreach($upload_results as $upload_result){
                    $result["files"][] = array(
                        "path"=> $path . "/" . $upload_result["new_name"],
                        "name" => $upload_result["new_name"]
                    );
                }
                jsonResponse("", "success", $result);
            break;
            // DONE
            case 'delete_file':
                checkPermisionAjax('manage_cr_expense_reports,cr_expense_reports_administration');

                if (empty($_POST["file"])){
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $filename = $_POST["file"];
                $upload_folder = checkEncriptedFolder($this->uri->segment(4));
                if($upload_folder === false){
                    $id_ereport = (int) $this->uri->segment(4);
                    $ereport = $this->cr_ereports->get_report($id_ereport);

                    if(empty($ereport)){
                        jsonResponse(translate('systmess_error_invalid_data'));
                    }

                    if(have_right('manage_cr_expense_reports') && !is_privileged('user', $ereport['id_user'])){
                        jsonResponse(translate('systmess_error_invalid_data'));
                    }

                    $photos = json_decode($ereport["ereport_photos"], true);
                    if(isset($photos[$filename])) {
                        unset($photos[$filename]);
                        $update = array(
                            "ereport_photos" => json_encode($photos)
                        );
                        $this->cr_ereports->update_report($id_ereport, $update);
                    }

                    $path = "public/expense_reports/{$id_ereport}";
                } else{
                    $path = config("default_temp_expense_report", $this->ereports->default_temp_folder) . "/" . id_session() . "/" . $upload_folder;
                }

                @unlink("{$path}/{$filename}");

                jsonResponse("","success");
			break;
            // DONE
			case "change_status":
				checkPermisionAjax("cr_expense_reports_administration");

				$id_ereport = (int) $_POST["id_ereport"];
                $ereport = $this->cr_ereports->get_report($id_ereport);
				if (empty($ereport)) {
					jsonResponse(translate('systmess_error_invalid_data'));
                }

                $update = array();
                $update_status = cleanInput($_POST['status']);
                switch ($update_status) {
                    case 'processed':
                        if (!in_array($ereport['ereport_status'], array(self::STATUS_INIT, self::STATUS_IN_PROGRESS))) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        $update['ereport_status'] = self::STATUS_PROCESSED;
                    break;
                    case 'in_progress':
                        if ($ereport["ereport_status"] != self::STATUS_INIT) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        $update['ereport_status'] = self::STATUS_IN_PROGRESS;
                    break;
                    case 'declined':
                        $validator_rules = array(
                            array(
                                "field" => "declined_reason",
                                "label" => "Declined reason",
                                "rules" => array("required" => "", "max_len[255]" => "")
                            )
                        );

                        $this->validator->set_rules($validator_rules);
                        if (!$this->validator->validate()){
                            jsonResponse($this->validator->get_array_errors());
                        }

                        if (in_array($ereport["ereport_status"],  array(self::STATUS_PROCESSED, self::STATUS_DECLINED))) {
                            jsonResponse(translate('systmess_error_invalid_data'));
                        }

                        $this->load->library("Cleanhtml", "clean");
                        $update['ereport_declined_reason'] = $this->clean->sanitizeUserInput($_POST["declined_reason"]);
                        $update['ereport_status'] = self::STATUS_DECLINED;
                    break;
                    default:
                        jsonResponse(translate('systmess_error_invalid_data'));
                    break;
                }

				$this->cr_ereports->update_report($id_ereport, $update);
                jsonResponse("The status has been changed.", "success");
            break;
            // DONE
            case 'admin_dt':
                checkPermisionAjaxDT('cr_expense_reports_administration');

                $conditions = array(
                    'per_p' => (int) $_POST["iDisplayLength"],
                    'start' => (int) $_POST["iDisplayStart"],
                    'removed' => 0
                );

                if (!empty($_POST['start_from']) && validateDate($_POST['start_from'], 'm/d/Y')) {
                    $conditions['created_from'] = getDateFormat($_POST['start_from'], 'm/d/Y', 'Y-m-d 00:00:00');
                }

                if (!empty($_POST['start_to']) && validateDate($_POST['start_to'], 'm/d/Y')) {
                    $conditions['created_to'] = getDateFormat($_POST['start_to'], 'm/d/Y', 'Y-m-d 23:59:59');
                }

                if (!empty($_POST["refund_amount_to"])) {
                    $conditions["refund_amount_to"] = get_price($_POST["refund_amount_to"], false);
                }

                if (!empty($_POST["refund_amount_from"])) {
                    $conditions["refund_amount_from"] = get_price($_POST["refund_amount_from"], false);
                }

                if (!empty($_POST["status_filter"])) {
                    $status_filter = cleanInput($_POST["status_filter"]);
                    if(isset($this->icon_statuses[$status_filter])){
                        $conditions["status_filter"] = $status_filter;
                    }

                    if ($ereport["ereport_status"] == self::STATUS_DECLINED && !empty($ereport["ereport_declined_reason"])) {
                        $ereport_details[] = '<tr>
                                                <td class="w-100">Declined reason</td>
                                                <td>'.$ereport["ereport_declined_reason"].'</td>
                                            </tr>';
                    }

                    $actions = array();
                    if($ereport["ereport_status"] == self::STATUS_INIT || $ereport["ereport_status"] == self::STATUS_IN_PROGRESS) {
                        $actions[] = '<li>
                                        <a class="fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'cr_expense_reports/popup_forms/edit/'.$ereport["id_ereport"].'" data-title="Edit expense report">
                                            <span class="ep-icon ep-icon_pencil"></span> Edit
                                        </a>
                                    </li>';
                    }

                    if($ereport["ereport_status"] == self::STATUS_INIT) {
                        $actions[] = '<li>
                                        <a class="confirm-dialog" href="#" data-callback="change_status" data-message="Are you sure you want to change status to in progress?" data-status="'.self::STATUS_IN_PROGRESS.'" data-id_ereport="' . $ereport["id_ereport"] . '">
                                            <span class="ep-icon ep-icon_hourglass-plus txt-orange"></span> Change to in progress
                                        </a>
                                    </li>';
                    }

                    if(in_array($ereport["ereport_status"], array(self::STATUS_INIT, self::STATUS_IN_PROGRESS))) {
                        $actions[] = '<li>
                                        <a class="confirm-dialog" href="#" data-callback="change_status" data-message="Are you sure you want to change status to processed?" data-status="'.self::STATUS_PROCESSED.'" data-id_ereport="' . $ereport["id_ereport"] . '">
                                            <span class="ep-icon ep-icon_ok-circle txt-green"></span> Change to processed
                                        </a>
                                    </li>
                                    <li>
                                        <a class="fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'cr_expense_reports/popup_forms/decline/'.$ereport["id_ereport"].'" data-title="Decline expense report">
                                            <span class="ep-icon ep-icon_minus-circle txt-red"></span> Decline
                                        </a>
                                    </li>';
                    }

                    //TODO: admin chat hidden
                    $btnChatUser = new ChatButton(['hide' => true, 'recipient' => $ereport["id_user"], 'recipientStatus' => 'active', 'module' => 31, 'item' => $ereport["id_ereport"]], ['classes' => 'btn-chat-now', 'text' => '']);
                    $btnChatUserView = $btnChatUser->button();

                    $output["aaData"][] = array(
                        "dt_id"             => $ereport["id_ereport"] . '<br><a title="View details" class="ep-icon ep-icon_plus call-function" data-callback="toggle_details" href="#"></a>',
                        "dt_userinfo"       => '<div class="tal">
                                                    <a class="ep-icon ep-icon_onoff '. (($ereport["logged"]) ? 'txt-green' : 'txt-red') . ' dt_filter" title="Filter just ' . $online . '" data-value="' . $ereport["logged"] . '" data-name="online"></a>
                                                    <a class="ep-icon ep-icon_user" title="View personal page of '.$ereport["fname"].'" target="_blank" href="'.__SITE_URL.'country_representative/'.strForURL($ereport['fname'].' '.$ereport["lname"]).'-'.$ereport["id_user"].'"></a>
                                                    '.$btnChatUserView.'
                                                    <a class="ep-icon ep-icon_envelope-send fancyboxValidateModal fancybox.ajax" href="' . __SITE_URL . 'contact/popup_forms/email_user/'.$ereport["id_user"].'" title="Email '. $ereport["fname"] . ' ' . $ereport["lname"] .'" data-title="Email '. $ereport["fname"] . " " . $ereport["lname"] .'"></a>
                                                </div>
                                                <div>' . $ereport["fname"] . ' ' . $ereport["lname"] . '</div>',
                        "dt_title"          => $ereport["ereport_title"],
                        "dt_useravatar"     => '<img class="mw-50 mh-50" src="' . getDisplayImageLink(array('{ID}' => $ereport["id_user"], '{FILE_NAME}' => $ereport["user_photo"]), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $ereport["user_group"] )) . '"/>',
                        "dt_status"         => '<a class="dt_filter" data-title="Status" data-name="status_filter" data-value="'.$ereport["ereport_status"].'" data-value-text="'.$this->icon_statuses[$ereport["ereport_status"]]["title"].'"><i class="ep-icon ' . $this->icon_statuses[$ereport["ereport_status"]]["icon"] . ' fs-30"></i><br> ' . $this->icon_statuses[$ereport["ereport_status"]]["title"] . '</a>',
                        "dt_details"        => implode("", $ereport_details),
                        "dt_created"        => getDateFormat($ereport["ereport_date"], 'Y-m-d H:i:s'),
                        "dt_updated"        => getDateFormat($ereport["ereport_updated"], 'Y-m-d H:i:s'),
                        "dt_refund_amount"  => get_price($ereport["ereport_refund_amount"], false),
                        "dt_actions"        => !empty($actions)?'<div class="dropup">
                                                    <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                                                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                                        '.implode('', $actions).'
                                                    </ul>
                                                </div>':''
                    );
                }

                if (!empty($_POST["keywords"])) {
                    $conditions["keywords"] = cleanInput($_POST["keywords"]);
                }

                if (isset($_POST['online'])) {
                    $conditions['logged'] = intval($_POST['online']);
                }

                $sort_by = flat_dt_ordering($_POST, array(
                    'dt_refund_amount' => 'cer.ereport_refund_amount',
                    'dt_status' => 'cer.ereport_status',
                    'dt_created' => 'cer.ereport_date',
                    'dt_updated' => 'cer.ereport_updated'
                ));

                if(!empty($sort_by)){
                    $conditions['sort_by'] = $sort_by;
                }

                $ereports_list = $this->cr_ereports->get_reports($conditions);
                $records_total = $this->cr_ereports->count_reports($conditions);

                $output = array(
                    "sEcho" => intval($_POST["sEcho"]),
                    "iTotalRecords" => $records_total,
                    "iTotalDisplayRecords" => $records_total,
                    "aaData" => array()
                );

                if(empty($ereports_list)) {
                    jsonResponse("", "success", $output);
                }

                foreach ($ereports_list as $ereport) {
                    $online = ($user["logged"]) ? "online" : "offline";

                    $ereport_details = array();
                    $ereport_details[] = '<tr>
                                            <td class="w-100">Description</td>
                                            <td>'.$ereport["ereport_description"].'</td>
                                        </tr>';

                    if (!empty($ereport["user_country"])) {
                        $ereport_details[] = '<tr>
                                                <td class="w-100">Country</td>
                                                <td class="vam">
                                                    <img width="24" height="24" src="' . getCountryFlag($ereport['user_country']) . '"><span class="display-ib mt-5 ml-5">' . $ereport['user_country'].'</span>
                                                </td>
                                            </tr>';
                    }

                    $ereport_details[] = '<tr>
                                            <td class="w-100">Address</td>
                                            <td>'.$ereport["address"] . ', ' . $ereport["zip"] . ', ' . $ereport["user_city"] . ', ' . $ereport["user_country"] .'</td>
                                        </tr>
                                        <tr>
                                            <td class="w-100">Email</td>
                                            <td>'.$ereport["email"].'</td>
                                        </tr>';

                    if (!empty($ereport["phone"])){
                        $ereport_details[] = '<tr>
                                                <td class="w-100">Phone</td>
                                                <td>'.$ereport["phone_code"].' '.$ereport["phone"].'</td>
                                            </tr>';
                    }

                    if (!empty($ereport["fax"])){
                        $ereport_details[] = '<tr>
                                                <td class="w-100">Fax</td>
                                                <td>'.$ereport["fax_code"].' '.$ereport["fax"].'</td>
                                            </tr>';
                    }

                    $ereport_photos = json_decode($ereport["ereport_photos"], true);
                    if (!empty($ereport_photos)) {
                        $files_list = array();
                        foreach ($ereport_photos as $file) {
                            $files_list[] = '<a class="pull-left w-50 fancyboxGallery" rel="gallery-ereport_'.$ereport['id_ereport'].'" data-title="View document" href="'.__IMG_URL.'cr_expense_reports/view_file/'.$ereport['id_ereport'].'?file='.$file['name'] .'">
                                                <div class="img-b h-50 icon-files-'.$file['type'].'-middle"></div>
                                            </a>';
                        }

                        $ereport_details[] = '<tr>
                                                <td class="w-100">Files</td>
                                                <td>'.implode("", $files_list).'</td>
                                            </tr>';
                    }

                    if ($ereport["ereport_status"] == self::STATUS_DECLINED && !empty($ereport["ereport_declined_reason"])) {
                        $ereport_details[] = '<tr>
                                                <td class="w-100">Declined reason</td>
                                                <td>'.$ereport["ereport_declined_reason"].'</td>
                                            </tr>';
                    }

                    $actions = array();
                    if($ereport["ereport_status"] == self::STATUS_INIT || $ereport["ereport_status"] == self::STATUS_IN_PROGRESS) {
                        $actions[] = '<li>
                                        <a class="fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'cr_expense_reports/popup_forms/edit/'.$ereport["id_ereport"].'" data-title="Edit expense report">
                                            <span class="ep-icon ep-icon_pencil"></span> Edit
                                        </a>
                                    </li>';
                    }

                    if($ereport["ereport_status"] == self::STATUS_INIT) {
                        $actions[] = '<li>
                                        <a class="confirm-dialog" href="#" data-callback="change_status" data-message="Are you sure you want to change status to in progress?" data-status="'.self::STATUS_IN_PROGRESS.'" data-id_ereport="' . $ereport["id_ereport"] . '">
                                            <span class="ep-icon ep-icon_hourglass-plus txt-orange"></span> Change to in progress
                                        </a>
                                    </li>';
                    }

                    if(in_array($ereport["ereport_status"], array(self::STATUS_INIT, self::STATUS_IN_PROGRESS))) {
                        $actions[] = '<li>
                                        <a class="confirm-dialog" href="#" data-callback="change_status" data-message="Are you sure you want to change status to processed?" data-status="'.self::STATUS_PROCESSED.'" data-id_ereport="' . $ereport["id_ereport"] . '">
                                            <span class="ep-icon ep-icon_ok-circle txt-green"></span> Change to processed
                                        </a>
                                    </li>
                                    <li>
                                        <a class="fancyboxValidateModalDT fancybox.ajax" href="'.__SITE_URL.'cr_expense_reports/popup_forms/decline/'.$ereport["id_ereport"].'" data-title="Decline expense report">
                                            <span class="ep-icon ep-icon_minus-circle txt-red"></span> Decline
                                        </a>
                                    </li>';
                    }

                    //TODO: admin chat hidden
                    $btnChatUser2 = new ChatButton(['hide' => true, 'recipient' => $ereport["id_user"], 'recipientStatus' => 'active', 'module' => 31, 'item' => $ereport["id_ereport"]], ['classes' => 'btn-chat-now', 'text' => '']);
                    $btnChatUser2View = $btnChatUser2->button();

                    $output["aaData"][] = array(
                        "dt_id"             => $ereport["id_ereport"] . '<br><a title="View details" class="ep-icon ep-icon_plus call-function" data-callback="toggle_details" href="#"></a>',
                        "dt_userinfo"       => '<div class="tal">
                                                    <a class="ep-icon ep-icon_onoff '. (($ereport["logged"]) ? 'txt-green' : 'txt-red') . ' dt_filter" title="Filter just ' . $online . '" data-value="' . $ereport["logged"] . '" data-name="online"></a>
                                                    <a class="ep-icon ep-icon_user" title="View personal page of '.$ereport["fname"].'" target="_blank" href="'.__SITE_URL.'country_representative/'.strForURL($ereport['fname'].' '.$ereport["lname"]).'-'.$ereport["id_user"].'"></a>
                                                    '.$btnChatUser2View.'
                                                    <a class="ep-icon ep-icon_envelope-send fancyboxValidateModal fancybox.ajax" href="' . __SITE_URL . 'contact/popup_forms/email_user/'.$ereport["id_user"].'" title="Email '. $ereport["fname"] . ' ' . $ereport["lname"] .'" data-title="Email '. $ereport["fname"] . " " . $ereport["lname"] .'"></a>
                                                </div>
                                                <div>' . $ereport["fname"] . ' ' . $ereport["lname"] . '</div>',
                        "dt_title"          => $ereport["ereport_title"],
                        "dt_useravatar"     => '<img class="mw-50 mh-50" src="' . getDisplayImageLink(array('{ID}' => $ereport["id_user"], '{FILE_NAME}' => $ereport["user_photo"]), 'users.main', array( 'thumb_size' => 0, 'no_image_group' => $ereport["user_group"] )) . '"/>',
                        "dt_status"         => '<a class="dt_filter" data-title="Status" data-name="status_filter" data-value="'.$ereport["ereport_status"].'" data-value-text="'.$this->icon_statuses[$ereport["ereport_status"]]["title"].'"><i class="ep-icon ' . $this->icon_statuses[$ereport["ereport_status"]]["icon"] . ' fs-30"></i><br> ' . $this->icon_statuses[$ereport["ereport_status"]]["title"] . '</a>',
                        "dt_details"        => implode("", $ereport_details),
                        "dt_created"        => getDateFormat($ereport["ereport_date"], 'Y-m-d H:i:s'),
                        "dt_updated"        => getDateFormat($ereport["ereport_updated"], 'Y-m-d H:i:s'),
                        "dt_refund_amount"  => get_price($ereport["ereport_refund_amount"], false),
                        "dt_actions"        => !empty($actions)?'<div class="dropup">
                                                    <a class="ep-icon ep-icon_menu-circles dropdown-toggle" type="button" data-toggle="dropdown"></a>
                                                    <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
                                                        '.implode('', $actions).'
                                                    </ul>
                                                </div>':''
                    );
                }

                jsonResponse("", "success", $output);
            break;
		}
	}

    private function _dt_my_new($records = array()){
        $aaData = array();
        foreach ($records as $record) {
            $actions = array();
            if($record["ereport_status"] == self::STATUS_INIT) {
                $actions[] = '<a class="dropdown-item fancybox.ajax fancyboxValidateModalDT" data-title="'.translate('general_button_edit_text').'" title="'.translate('general_button_edit_text').'" href="' . __SITE_URL . 'cr_expense_reports/popup_forms/edit/' . $record['id_ereport'].'">
                                <i class="ep-icon ep-icon_pencil"></i>
                                <span>'.translate('general_button_edit_text').'</span>
                            </a>';
            }

            if(in_array($record["ereport_status"], array(self::STATUS_INIT, self::STATUS_DECLINED))) {
                $actions[] = '<a class="dropdown-item confirm-dialog" data-callback="delete_ereport" title="'.translate('general_button_delete_text').'" data-message="Are you sure you want to delete this expense report?" data-expense-report="' . $record['id_ereport'] . '" href="#">
                                <i class="ep-icon ep-icon_trash-stroke"></i>
                                <span>'.translate('general_button_delete_text').'</span>
                            </a>';
            }

            $aaData[] = array(
                "dt_title"          => $record["ereport_title"],
                "dt_status"         => '<span class="tac"><i class="ep-icon ' . $this->icon_statuses[$record["ereport_status"]]["icon"] . ' fs-30"></i><br> ' . $this->icon_statuses[$record["ereport_status"]]["title"] . '</span>',
                "dt_created"        => getDateFormat($record["ereport_date"], 'Y-m-d H:i:s'),
                "dt_updated"        => getDateFormat($record["ereport_updated"], 'Y-m-d H:i:s'),
                "dt_refund_amount"  => get_price($record["ereport_refund_amount"], false) ,
                "dt_actions"        => '<div class="dropdown">
                                            <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="ep-icon ep-icon_menu-circles"></i>
                                            </a>

                                            <div class="dropdown-menu dropdown-menu-right">
                                                '.implode("", $actions).'
                                                <a class="dropdown-item fancybox.ajax fancyboxValidateModal" data-title="'.translate('general_button_details_text').'" title="'.translate('general_button_details_text').'" href="' . __SITE_URL . 'cr_expense_reports/popup_forms/details/' . $record['id_ereport'].'">
                                                    <i class="ep-icon ep-icon_info-stroke"></i>
                                                    <span>'.translate('general_button_details_text').'</span>
                                                </a>
                                            </div>
                                        </div>'
            );
        }

        return $aaData;
    }

    function view_file() {
        checkPermision('cr_expense_reports_administration');

		$id_ereport = intVal($this->uri->segment(3));
		$filename = cleanInput($_GET['file']);

		$this->load->model("Cr_Expense_Reports_Model", "cr_ereports");
        $ereport = $this->cr_ereports->get_report($id_ereport);
        if(empty($ereport)) {
            return false;
        }

        $ereport_photos = json_decode($ereport["ereport_photos"], true);
        if(empty($ereport_photos)) {
            return false;
        }

        if(empty($ereport_photos[$filename])) {
            return false;
        }

        header("Content-type: image/".$ereport_photos[$filename]["type"]);
		readfile("public/expense_reports/{$ereport["id_ereport"]}/$filename");
    }
}
