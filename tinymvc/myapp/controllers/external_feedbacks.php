<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */

use App\Email\ConfirmFeedback;
use App\Email\ConfirmReview;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class External_feedbacks_Controller extends TinyMVC_Controller {

	private $external_invite_code_salt = 'code';

	function popup_feedback(){
		if (!isAjaxRequest()) {
			show_404();
		}

		$feedback_code = cleanInput($this->uri->segment(3));
		$company_id = (int) $this->uri->segment(4);

		$code = md5($company_id . $this->external_invite_code_salt);
		if ($feedback_code != $code) {
			messageInModal(translate('systmess_error_invalid_data'));
		}

		$data['company'] = model('Company')->get_company(array('id_company' => $company_id));
		$data['code'] = $code;

		$type = cleanInput($_GET['type']);
		switch ($type) {
			default:
			case 'feedback':
				$this->view->assign($data);
				$this->view->display("new/user/seller/invite_external_feedback_view");
			break;
			case 'review':
				$data['items'] = model('items')->get_items(array('seller' => $data['company']['id_user'], 'main_photo' => true));
				$this->view->assign($data);
				$this->view->display("new/user/seller/invite_external_review_view");
			break;
		}
	}

    /**
     * @author Vasile Cristel
     * @todo Remove [13.01.2022]
     * Reason: not used
     */
	// function ajax_external_feedbacks_operation(){
	// 	if(!isAjaxRequest()){
	// 		show_404();
	// 	}

	// 	$option = $this->uri->segment(3);

	// 	switch($option){
	// 		case 'save_feedback':
	// 			$validator_rules = array(
	// 				array(
	// 					'field' => 'full_name',
	// 					'label' => 'Full name',
	// 					'rules' => array('required' => '','max_len[150]' => '')
	// 				),
	// 				array(
	// 					'field' => 'email',
	// 					'label' => 'Email',
	// 					'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => '')
	// 				),
	// 				array(
	// 					'field' => 'feedback_raiting',
	// 					'label' => 'Feedback raiting',
	// 					'rules' => array('required' => '','natural' => '')
	// 				),
	// 				array(
	// 					'field' => 'description_feedback',
	// 					'label' => 'Text',
	// 					'rules' => array('required' => '','max_len[200]' => '')
	// 				),
	// 				array(
	// 					'field' => 'company',
	// 					'label' => translate("systmess_error_rights_perform_this_action"),
	// 					'rules' => array('required' => '','natural' => '')
	// 				),
	// 				array(
	// 					'field' => 'code',
	// 					'label' => translate("systmess_error_rights_perform_this_action"),
	// 					'rules' => array('required' => '')
	// 				)
	// 			);

	// 			if(!empty($_POST['description_review']) && intval($_POST['item']) && !empty($_POST['review_raiting'])){
	// 				$validator_rules[] = array(
	// 						'field' => 'review_raiting',
	// 						'label' => 'Review raiting',
	// 						'rules' => array('required' => '','natural' => '')
	// 					);

	// 				$validator_rules[] = array(
	// 						'field' => 'item',
	// 						'label' => 'Product',
	// 						'rules' => array('required' => '','integer' => '')
	// 					);

	// 				$validator_rules[] = array(
	// 						'field' => 'description_review',
	// 						'label' => 'Description review',
	// 						'rules' => array('required' => '','max_len[200]' => '')
	// 					);
	// 			}

	// 			$this->validator->set_rules($validator_rules);

	// 			if(!$this->validator->validate()){
	// 				jsonResponse($this->validator->get_array_errors());
	// 			}

	// 			$external_invite_code = md5(intval($_POST['company']).'code');

	// 			if($external_invite_code != cleanInput($_POST['code'])){
	// 				jsonResponse(translate("systmess_error_rights_perform_this_action"));
	// 			}

	// 			$this->load->model('External_feedbacks_Model', 'external_feedbacks');
	// 			$user_name = cleanInput($_POST['full_name']);
	// 			$userEmail = cleanInput($_POST['email'], true);
	// 			$idCompany = intval($_POST['company']);
	// 			$idItem = intval($_POST['item']);
	// 			$confirmCode = get_sha1_token($userEmail, false);

	// 			$this->load->model('Company_Model', 'company');
	// 			$companyInfo = $this->company->get_company(array('id_company' => $idCompany));

	// 			if(empty($companyInfo)){
	// 				jsonResponse('Error: This company does not exist.');
	// 			}

	// 			if(!empty($_POST['description_review']) && intval($_POST['item']) && !empty($_POST['review_raiting'])){
	// 				$insert_review = array(
	// 					'full_name' => $user_name,
	// 					'id_company' => $idCompany,
	// 					'id_item' => $idItem,
	// 					'email' => $userEmail,
	// 					'rating' => intval($_POST['review_raiting']),
	// 					'confirm_code' => $confirmCode,
	// 					'description' => cleanInput($_POST['description_review'])
	// 				);

	// 				$params_external_review = array('email' => $insert_review['email']);
	// 			}

	// 			$insert_feedback = array(
	// 				'full_name' => $user_name,
	// 				'id_company' => $idCompany,
	// 				'email' => $userEmail,
	// 				'rating' => intval($_POST['feedback_raiting']),
	// 				'confirm_code' => $confirmCode,
	// 				'description' => cleanInput($_POST['description_feedback'])
	// 			);

	// 			$params_external_feedback = array('id_company' => $companyInfo['id_company'], 'email' => $insert_feedback['email']);

	// 			if($this->external_feedbacks->exist_external_feedback($params_external_feedback)){
	// 				jsonResponse('Error: You already added external feedback.');
	// 			}

	// 			if (!$this->external_feedbacks->set_external_feedback($insert_feedback)) {
	// 				jsonResponse("Your feedback is not saved. Please try again later or contact administration.");
	// 			}

	// 			if(isset($insert_review) && !$this->external_feedbacks->exist_external_review($params_external_review)){
	// 				$this->external_feedbacks->set_external_review($insert_review);
	// 			}

	// 			$companyUrl = getCompanyURL($companyInfo);

    //             try {
    //                 /** @var MailerInterface $mailer */
    //                 $mailer = $this->getContainer()->get(MailerInterface::class);
    //                 $mailer->send(
    //                     (new ConfirmFeedback($user_name, $companyInfo['name_company'], $companyUrl, $confirmCode))
    //                         ->to(new Address($userEmail))
    //                 );
    //             } catch (\Throwable $th) {
    //                 jsonResponse(translate('email_has_not_been_sent'));
    //             }

    //             jsonResponse('You have successfully added the external feedback.', 'success');

	// 		break;
	// 	}
	// }

	function ajax_operations(){
		checkIsAjax();

		$option = $this->uri->segment(3);
		switch($option){
			case 'add_feedback':
				$validator_rules = array(
					array(
						'field' => 'feedback_raiting',
						'label' => translate('invite_external_feedback_form_rating_label'),
						'rules' => array('required' => '', 'natural' => '', 'max[5]' => '')
					),
					array(
						'field' => 'full_name',
						'label' => translate('invite_external_feedback_form_full_name_label'),
						'rules' => array('required' => '','max_len[150]' => '')
					),
					array(
						'field' => 'email',
						'label' => translate('invite_external_feedback_form_email_label'),
						'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => '')
					),
					array(
						'field' => 'description_feedback',
						'label' => translate('invite_external_feedback_form_message_label'),
						'rules' => array('required' => '','max_len[200]' => '')
					),
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				if (empty($_POST['company']) || empty($_POST['code'])) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$external_invite_code = md5(intval($_POST['company']) . $this->external_invite_code_salt);
				if ($external_invite_code != cleanInput($_POST['code'])) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$idCompany = intval($_POST['company']);
				$companyInfo = model('Company')->get_company(array('id_company' => $idCompany));
				if (empty($companyInfo)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$userEmail = cleanInput($_POST['email'], true);
				if (model('external_feedbacks')->exist_external_feedback(array('id_company' => $idCompany, 'email' => $userEmail))) {
					jsonResponse(translate('systmess_error_already_added_feedback'), 'info');
				}

                $userName = cleanInput(request()->request->get('full_name'));
                $confirmCode = get_sha1_token($userEmail, false);
				$description_feedback = cleanInput($_POST['description_feedback']);
				$id_feedback = model('external_feedbacks')->set_external_feedback(array(
					'full_name' => $userName,
					'id_company' => $idCompany,
					'email' => $userEmail,
					'rating' => (int) $_POST['feedback_raiting'],
					'confirm_code' => $confirmCode,
					'description' => $description_feedback,
					'has_bad_words' => model('elasticsearch_badwords')->is_clean($description_feedback) ? 0 : 1
				));

				$companyUrl = getCompanyURL($companyInfo);

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new ConfirmFeedback($userName, $companyInfo['name_company'], $companyUrl, $confirmCode))
                            ->to(new Address($userEmail))
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                $this->session->setMessages(translate('systmess_success_message_for_confirm_feedback', ['{{USER_EMAIL}}' => $userEmail]), 'success');
                jsonResponse('', 'success');

			break;
			case 'add_review':
				$validator_rules = array(
					array(
						'field' => 'review_rating',
						'label' => translate('invite_external_review_form_rating_label'),
						'rules' => array('required' => '', 'natural' => '', 'min[1]' => '', 'max[5]' => '')
					),
					array(
						'field' => 'full_name',
						'label' => translate('invite_external_review_form_full_name_label'),
						'rules' => array('required' => '','max_len[150]' => '')
					),
					array(
						'field' => 'email',
						'label' => translate('invite_external_review_form_email_label'),
						'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => '')
					),
					array(
						'field' => 'description_review',
						'label' => translate('invite_external_review_form_message_label'),
						'rules' => array('required' => '','max_len[200]' => '')
					),
					array(
						'field' => 'item',
						'label' => translate('invite_external_review_form_what_was_ordered'),
						'rules' => array('required' => '','natural' => '')
					)
				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				if (empty($_POST['company']) || empty($_POST['code'])) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$idCompany = (int) $_POST['company'];
                $external_invite_code = md5($idCompany.'code');

				if ($external_invite_code != cleanInput($_POST['code'])) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

                $companyInfo = model('Company')->get_company(['id_company' => $idCompany]);

				if (empty($companyInfo)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$idItem = (int) $_POST['item'];
                $itemInfo = model('items')->get_item_simple($idItem, "id, id_seller, title");

				if (empty($itemInfo) || $itemInfo['id_seller'] != $companyInfo['id_user']) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

                $userEmail = cleanInput($_POST['email'], true);

				if (model('external_feedbacks')->exist_external_review(array('id_item' => $idItem, 'email' => $userEmail))) {
					jsonResponse(translate('systmess_error_already_added_review'), 'info');
				}

				$confirmCode = get_sha1_token($userEmail, false);
				$description_review = cleanInput($_POST['description_review']);

                $userName = cleanInput($_POST['full_name']);
				model('external_feedbacks')->set_external_review(array(
					'full_name' => $userName,
					'id_company' => $idCompany,
					'id_item' => $idItem,
					'email' => $userEmail,
					'rating' => (int) $_POST['review_rating'],
					'confirm_code' => $confirmCode,
					'description' => $description_review,
					'has_bad_words' => model('elasticsearch_badwords')->is_clean($description_review) ? 0 : 1
				));

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new ConfirmReview($userName, $itemInfo, $companyInfo, $confirmCode))
                            ->to(new Address($userEmail))
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                $this->session->setMessages(translate('systmess_success_message_for_confirm_review', ['{{USER_EMAIL}}' => $userEmail]), 'success');
                jsonResponse('', 'success');

			break;
		}
	}
}
