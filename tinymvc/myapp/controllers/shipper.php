<?php

use App\Email\EmailFriendAboutShipperCompany;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use const App\Logger\Activity\OperationTypes\DELETE_LOGO;
use const App\Logger\Activity\ResourceTypes\COMPANY;
use App\Common\Buttons\ChatButton;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Shipper_Controller extends TinyMVC_Controller {

	function company(){
		$this->load->model("Shippers_Model", 'shippers');
		$this->load->model("Shipper_Countries_Model", 'shipper_countries');
        $this->load->model("User_Model", 'user');
		$this->load->model("Country_model", 'country');

		$id_company_shipper = id_from_link($this->uri->segment(3));
		$data['shipper'] = $this->shippers->get_shipper_details($id_company_shipper);

        if (
            empty($data['shipper'])
            || (
                1 === (int) $data['shipper']['fake_user']
                && !(
                        is_privileged('user', (int) $data['shipper']['id_user'])
                        || have_right('moderate_content')
                    )
                )
            )
        {
            show_404();
        }

        if(1 === (int) $data['shipper']['fake_user']){
            header("X-Robots-Tag: noindex");
        }

        $isVisible = $data['shipper']['visible'] > 0;
        $isActiveAccount = 'active' === $data['shipper']['user_status'];
		if(
            !(
                $isVisible && $isActiveAccount
                || is_privileged('user', (int) $data['shipper']['id_user'])
                || have_right('moderate_content')
            )
        ){
			show_blocked();
        }

		$id_shipper = $data['shipper']['id_user'];
		$data['user'] = $this->user->getSimpleUser($data['shipper']['id_user']);
		$data['address'] = $this->country->get_country_city($data['shipper']['id_country'],$data['shipper']['id_city']);
		$data['shipper_pictures'] = $this->shippers->get_shipper_pictures($id_company_shipper);

		if(have_right('sell_item'))
			$data['partnership'] = $this->shippers->get_seller_shipper_partner(array('id_seller' => privileged_user_id(),'id_shipper' => $data['shipper']['id_user']));

		$data['countries_by_continents'] = array(
			'1' => array('name' => 'Asia', 'id' => '1', 'count' => '0'),
			'2' => array('name' => 'Africa', 'id' => '2', 'count' => '0'),
			'3' => array('name' => 'Antarctica', 'id' => '3', 'count' => '0'),
			'4' => array('name' => 'Europe', 'id' => '4', 'count' => '0'),
			'5' => array('name' => 'North America', 'id' => '5', 'count' => '0'),
			'6' => array('name' => 'Australia', 'id' => '6', 'count' => '0'),
			'7' => array('name' => 'South America', 'id' => '7', 'count' => '0'),
		);

		$data['worldwide'] = $this->shipper_countries->worldwide_shipper_countries($id_shipper);
		if(!$data['worldwide']){
			$shipper_countries = $this->shipper_countries->get_shipper_countries(array('id_user' => $id_shipper));

			foreach($shipper_countries as $country){
				$data['countries_by_continents'][$country['id_continent']]['countries'][] = $country;
				$data['countries_by_continents'][$country['id_continent']]['count'] += 1;
			}
		}

		$chatBtn = new ChatButton(['recipient' => $data['user']['idu'], 'recipientStatus' => $data['user']['status']], ['atas' => 'shipper_sidebar_more_actions_menu_contact']);
		$data['chatBtn'] = $chatBtn->button();

		$chatBtnShipper = new ChatButton(['recipient' => $data['shipper']['idu'], 'recipientStatus' => $data['shipper']['user_status']]);
		$data['shipper']['chatBtn'] = $chatBtnShipper->button();

		$data['meta_params']['[COMPANY_NAME]'] = $data['shipper']['co_name'];
		$data['meta_params']['[USER_NAME]'] = $data['user']['fname'].' '.$data['user']['lname'];

		$this->breadcrumbs[] = array(
			'link'	=> __SITE_URL.'shipper/'.strForUrl($data['shipper']['co_name']).'-'.$data['shipper']['id'],
			'title'	=> $data['shipper']['co_name']
		);

		$data['breadcrumbs'] = $this->breadcrumbs;
        $data['sidebar_left_content'] = 'new/shippers/sidebar_view';
        $data['main_content'] = 'new/shippers/index_view';
        $this->view->assign($data);
        $this->view->display('new/index_template_view');
	}

	function ajax_shipper_operation(){
		if (!isAjaxRequest()) {
			headerRedirect();
		}

		$op = $this->uri->segment(3);
		$this->load->model("Shippers_Model", 'shippers');

		switch($op) {
			case 'unlock_email':
				if(!logged_in()){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$id_shipper = intval($_POST['id']);

				if(empty($id_shipper))
					jsonResponse('Error: Incorrect data sent.');

				$email = $this->shippers->get_email_shipper($id_shipper);

				jsonResponse('','success',array('block_info' => '<span>'.$email.'</span>'));
			break;
			case 'unlock_phone':
				if(!logged_in()){
					jsonResponse(translate("systmess_error_rights_perform_this_action"));
				}

				$id_shipper = intval($_POST['id']);

				if(empty($id_shipper))
					jsonResponse('Error: Incorrect data sent.');

				$phone = $this->shippers->get_phone_shipper($id_shipper);

				jsonResponse('','success',array('block_info' => '<span>'.$phone.'</span>'));
			break;
			case 'remove_shipper_saved':
				if (!logged_in()) {
					jsonResponse(translate('systmess_error_should_be_logged_in'));
				}

				$id_shipper = (int) $_POST['company'];
				if (!in_array($id_shipper, $this->session->shippers_saved)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (!model('shippers')->delete_saved_shipper(id_session(), $id_shipper)) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$this->session->clear_val('shippers_saved', $id_shipper);
				jsonResponse(translate('systmess_success_remove_from_saved_shippers'), 'success');

			break;
			case 'add_shipper_saved':
				if (!logged_in()) {
					jsonResponse(translate('systmess_error_should_be_logged_in'));
				}

				$id_shipper = (int) $_POST['company'];
				if (in_array($id_shipper, $this->session->shippers_saved)) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				if (!model('shippers')->set_saved_shipper(array('id_user' => id_session(), 'id_shipper' => $id_shipper))) {
					jsonResponse(translate('systmess_internal_server_error'));
				}

				$this->session->__push('shippers_saved', $id_shipper);
				jsonResponse(translate('systmess_success_save_shipper_in_saved'), 'success');

			break;
		}
	}

	function popup_forms(){
		if (!isAjaxRequest()) {
			headerRedirect();
		}

		if (!logged_in()) {
			messageInModal(translate('systmess_error_should_be_logged_in'));
		}

		$op = $this->uri->segment(3);

		switch($op){
			case 'share_company':
				checkPermisionAjaxModal('share_this');

				$id_shipper = (int) $this->uri->segment(4);

				if (!$id_shipper || !model('shippers')->exist_shipper(array('id_shipper' => $id_shipper))) {
					messageInModal(translate('systmess_error_invalid_data'));
				}

				$data['id_company'] = $id_shipper;
				$this->view->assign($data);
				$this->view->display('new/shippers/popup_share_view');
			break;
			case 'email_company':
				checkPermisionAjaxModal('email_this');

				$id_shipper = (int) $this->uri->segment(4);

				if (!$id_shipper || !model('shippers')->exist_shipper(array('id_shipper' => $id_shipper))) {
					messageInModal(translate('systmess_error_invalid_data'));
				}

				$data['id_company'] = $id_shipper;
				$this->view->assign($data);
				$this->view->display('new/shippers/popup_email_view');
			break;
		}
	}

	public function ajax_send_email()
    {
		if (!isAjaxRequest()) {
			headerRedirect();
		}

		if (!logged_in()) {
			jsonResponse(translate('systmess_error_should_be_logged_in'));
		}

		is_allowed("freq_allowed_send_email_to_user");

		$op = $this->uri->segment(3);
		switch($op){
			case 'share':
				checkPermision('share_this');

				$validator_rules = array(
					array(
						'field' => 'message',
						'label' => translate('share_ff_company_form_message_label'),
						'rules' => array('required' => '', 'max_len[500]' => '')
					)

				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				if (!$idShipper = (int) $_POST['shipper']) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

                $filteredEmails = model(Followers_Model::class)->getFollowersEmails(id_session());

				if (empty($filteredEmails)) {
					jsonResponse(translate('systmess_error_share_company_no_followers'));
				}

				if (!model(Shippers_Model::class)->exist_shipper(array('id_shipper' => $idShipper))) {
					jsonResponse(translate('systmess_error_invalid_data'));
				}

				$shipper = model(Shippers_Model::class)->get_shipper_details($idShipper);
                $userName = user_name_session();

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new EmailFriendAboutShipperCompany($userName, cleanInput(request()->request->get('message')), $shipper))
                            ->to(...array_map(fn (int $id, string $to) => new RefAddress((string) $id, new Address($to)), array_column($filteredEmails, 'idu', 'idu'), array_column($filteredEmails, 'email', 'email')))
                            ->subjectContext(['[userName]' => $userName])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

				jsonResponse(translate('systmess_successfully_shared_company_information'), 'success');
			break;
			case 'email':
                checkPermisionAjax('email_this');

                // Get the request
                $request = request();

                //region Request validation
                $emailsLimit = config('email_this_max_email_count', 10);
                $validatorRules = [
                    [
                        'field' => 'message',
                        'label' => translate('company_email_popup_input_message_label'),
                        'rules' => ['required' => '', 'max_len[500]' => ''],
                    ],
                    [
                        'field' => 'emails',
                        'label' => translate('company_email_popup_input_email_label'),
                        'rules' => ['required' => '', 'no_whitespaces' => '', 'valid_emails' => '', 'max_emails_count[' . $emailsLimit . ']' => ''],
                    ],
                ];
                $this->validator->set_rules($validatorRules);
                if (!$this->validator->validate()) {
                    json(['message' => $this->validator->get_array_errors()], 400);
                }
                //endregion Request validation

                //region Shippers
                $rawShipperId = $request->request->get('shipper');
                if (null == $rawShipperId || !is_numeric($rawShipperId) || (string) ($shipperId = (int) $rawShipperId) !== $rawShipperId) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }
                /** @var Shippers_Model $shippersRepository */
                $shippersRepository = model(Shippers_Model::class);
                if (!$shipperId || !$shippersRepository->exist_shipper(['id_shipper' => $shipperId])) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }
                $shipper = $shippersRepository->get_shipper_details($shipperId);
                //endregion Shippers

                //region Send email
                // Get filtered emails
                $filteredEmails = filter_email($request->request->get('emails'));
                if (empty($filteredEmails)) {
                    jsonResponse(translate('systmess_error_invalid_data'));
                }

                $userName = user_name_session();

                /** @var MailerInterface $mailer */
                $mailer = $this->getContainer()->get(MailerInterface::class);
                $mailer->send(
                    (new EmailFriendAboutShipperCompany($userName, cleanInput(request()->request->get('message')), $shipper))
                        ->to(...array_map(fn (string $to) => new Address($to), $filteredEmails))
                        ->subjectContext(['[userName]' => $userName])
                );
                //endregion Send email

                jsonResponse(translate('systmess_successfully_sent_email'), 'success');

			break;
		}
	}
}
