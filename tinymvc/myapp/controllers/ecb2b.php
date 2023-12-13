<?php

use App\Email\ContactAdmin;
use App\Email\SubscribeToNewsletter;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
* @author Bendiucov Tatiana
* @todo Refactoring [15.12.2021]
* Controller Refactoring
*/
class Ecb2b_Controller extends TinyMVC_Controller {

	public function administration(){
		checkAdmin('manage_content');

		$this->view->assign('title', 'EC B2B');
		$this->view->display('admin/header_view');
		$this->view->display('admin/ecb2b/list_view');
		$this->view->display('admin/footer_view');
	}

	public function ajax_list_dt() {
        checkIsAjax();
        checkIsLoggedAjaxDT();

        $sortBy = flat_dt_ordering($_POST, [
            'dt_id'             => 'id',
            'dt_full_name'      => 'full_name',
            'dt_email'          => 'email',
            'dt_phone'          => 'phone',
            'dt_type'           => 'type',
            'dt_date_created'   => 'date_created',
            'dt_date_processed' => 'date_processed',
        ]);

        $userParams = array_merge(
            [
                'start_from'    => (int) $_POST['iDisplayStart'],
                'limit'         => (int) $_POST['iDisplayLength'],
            ],
            dtConditions($_POST, [
                ['as' => 'request_from',    'key' => 'request_from',    'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'request_to',      'key' => 'request_to',      'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'processed_from',  'key' => 'processed_from',  'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'processed_to',    'key' => 'processed_to',    'type' => 'getDateFormat:m/d/Y,Y-m-d'],
                ['as' => 'search',          'key' => 'search',          'type' => 'cleanInput'],
                ['as' => 'type',            'key' => 'type',            'type' => 'cleanInput']
            ])
        );

        if (!empty($sortBy)) {
            $sortingParts = explode('-', array_shift($sortBy));
            $userParams['sort_by'] = $sortingParts[0];
            $userParams['sort_by_type'] = $sortingParts[1];
        }

        /** @var Ec_B2b_Requests_Model $ecB2bRequestsModel */
        $ecB2bRequestsModel = model(Ec_B2b_Requests_Model::class);

		$tempData = $ecB2bRequestsModel->get_requests($userParams);
        $counter = $ecB2bRequestsModel->count_requests($userParams);
        $output = [
			"iTotalDisplayRecords"  => $counter,
			"iTotalRecords"         => $counter,
			"aaData"                => [],
			"sEcho"                 => (int) $_POST['sEcho'],
        ];

		if (empty($tempData)) {
            jsonResponse('', 'success', $output);
		}

		foreach ($tempData as $key => $item) {
		    $actions = array(
		        '<a class="ep-icon ep-icon_envelope fancyboxValidateModalDT fancybox.ajax" title="Send email" href="'. __SITE_URL . 'ecb2b/popups_send_email?id=' . $item['id'] . '" data-title="Answer to ' . $item['type'] . ' request"></a>'
            );

		    if ($item['is_viewed'] == 0) {
		        $actions[] = '<a class="ep-icon ep-icon_ok mark-ecb2b-viewed" data-id="' . $item['id'] . '" title="Mark viewed" href="#"></a>';
            }

			$output['aaData'][] = [
				'dt_date_processed' => empty($item['date_processed']) ? 'N/A' : getDateFormatIfNotEmpty($item['date_processed']),
				'dt_date_created'   => getDateFormat($item['date_created']),
				'dt_full_name'      => $item['full_name'],
				'dt_actions'        => implode('', $actions),
				'is_viewed'         => $item['is_viewed'],
				'dt_email'          => $item['email'],
				'dt_phone'          => $item['phone'],
				'dt_type'           => $item['type'],
				'dt_id'             => $item['id'],
            ];
		}

		jsonResponse('', 'success', $output);
	}

	function popups_send_email() {
	    if (empty($_GET['id'])) {
            jsonResponse('Please specify the ID');
        }

        $this->load->model('Ec_B2b_Requests_Model', 'ecb2b');

        $ecb2bRequest = $this->ecb2b->get_requests(array(
            'id' => (int)$_GET['id']
        ));

        if (empty($ecb2bRequest)) {
            jsonResponse('Request not found');
        }


        $files = array(
            Ec_B2b_Requests_Model::TYPE_SHORT_DECK => Ec_B2b_Requests_Model::DOC_SHORT_DECK,
            Ec_B2b_Requests_Model::TYPE_BUSINESS_PLAN => Ec_B2b_Requests_Model::DOC_BUSINESS_PLAN
        );

        $file = isset($files[$ecb2bRequest['type']]) ? $files[$ecb2bRequest['type']] : false;

        $this->view->display('admin/ecb2b/modal_send_email_view', array(
            'ecb2bRequest' => $ecb2bRequest,
            'file' => $file
        ));
    }

    public function api_request()
    {
        $this->validate_recaptcha();

        $validator_rules = array(
            array(
                'field' => 'type',
                'label' => 'Type',
                'rules' => array('required' => '')
            ), array(
                'field' => 'full_name',
                'label' => 'Full name',
                'rules' => array('required' => '')
            ), array(
                'field' => 'email',
                'label' => 'Email',
                'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => '')
            ), array(
                'field' => 'phone',
                'label' => 'Phone',
                'rules' => array('required' => '')
            )
        );

        $this->validator->set_rules($validator_rules);

        if(!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        $this->load->model('Ec_B2b_Requests_Model', 'ecb2b');

        $this->ecb2b->insert(array(
            'type' => cleanInput($_POST['type']),
            'full_name' => cleanInput($_POST['full_name']),
            'email' => cleanInput($_POST['email'], true),
            'phone' => cleanInput($_POST['phone'])
        ));

        jsonResponse('Request was successfully sent', 'success');
    }

    public function api_contact_admin()
    {
        $this->validate_recaptcha();

		$validator_rules = array(
			array(
				'field' => 'subject',
				'label' => 'Subject',
				'rules' => array('required' => '', 'max_len[100]' => '')
            ),
            array(
				'field' => 'from',
				'label' => 'From',
				'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => '')
            ),
            array(
				'field' => 'content',
				'label' => 'Content',
				'rules' => array('required' => '', 'max_len[500]' => '')
			)
		);

		$this->validator->set_rules($validator_rules);
		if (!$this->validator->validate()) {
			jsonResponse($this->validator->get_array_errors());
        }

        try {
            /** @var MailerInterface $mailer */
            $mailer = $this->getContainer()->get(MailerInterface::class);
            $mailer->send(
                (new ContactAdmin(cleanInput(request()->request->get('fname')) . ' ' . cleanInput(request()->request->get('lname')), request()->request->get('from'), cleanInput(request()->request->get('phone')), cleanInput(request()->request->get('content'))))
                    ->bcc(config('contact_us_bcc_emails'))
                    ->to(new Address(config('email_contact_us')))
                    ->subject(cleanInput(request()->request->get('subject')))

            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }

		jsonResponse('E-mail was sent successfully.', 'success');
    }

    public function api_subscribe()
    {
        $this->validate_recaptcha();

		$validator_rules = array(
			array(
				'field' => 'terms_cond',
				'label' => 'Terms and Conditions',
				'rules' => array('required' => '')
			),
			array(
				'field' => 'email',
				'label' => 'Email',
				'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => '', 'max_len[100]' => '')
			)
        );

		$this->validator->set_rules($validator_rules);
		if (!$this->validator->validate()){
            jsonResponse($this->validator->get_array_errors());
        }

		$email = cleanInput($_POST['email'], true);

		$this->load->model('Subscribe_Model', 'subscribe');
		if($this->subscribe->existSubscriber($email)){
			jsonResponse('You already subscribed before. Please choose another one!');
		}

        if(!$this->subscribe->insertSubscriber(['subscriber_email' => $email])){
            jsonResponse('You cannot subscribe now. Please try again later.');
        }

        try {
            /** @var MailerInterface $mailer */
            $mailer = $this->getContainer()->get(MailerInterface::class);
            $mailer->send(
                (new SubscribeToNewsletter())
                    ->to(new Address($email))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }

        jsonResponse('Thank you for your subscription.', 'success');
	}

    function send_email_action() {
        $validator_rules = array(
            array(
                'field' => 'id',
                'label' => 'ID',
                'rules' => array('required' => '')
            ), array(
                'field' => 'subject',
                'label' => 'Subject',
                'rules' => array('required' => '')
            ), array(
                'field' => 'message',
                'label' => 'Message',
                'rules' => array('required' => '')
            )
        );

        $this->validator->set_rules($validator_rules);

        if(!$this->validator->validate()) {
            jsonResponse($this->validator->get_array_errors());
        }

        $this->load->model('Ec_B2b_Requests_Model', 'ecb2b');

        $ecb2bRequest = $this->ecb2b->get_requests(array(
            'id' => (int)$_POST['id']
        ));

        if (empty($ecb2bRequest)) {
            jsonResponse('Request not found');
        }

        global $tmvc;

        $this->load->library('Sendgrid', 'sendgrid');
        $this->load->library('Html2Text', 'html2text');

        $this->sendgrid->initialize(array(
            'from_email' => $tmvc->my_config['noreply_email'],
            'from_name' => 'ExportPortal.com',
            'reply_to_email' => $tmvc->my_config['noreply_email'],
            'mail_subject' => 'Exportportal'
        ));


        $email_data = array(
            'to' => array('email' => $ecb2bRequest['email']),
            'subject' => cleanInput($_POST['subject']),
            'headers' => array(
                'List-Unsubscribe' => '<mailto:unsubscribe@exportportal.com>, <'.__SITE_URL . 'user/unsubscribe>'
            )
        );

        $message = cleanInput($_POST['message']);
        $text_plain = $this->html2text->convert($message)->get_text();
        $this->sendgrid->add_content($text_plain, 'text/plain');
        $this->sendgrid->add_content($message, 'text/html');
        $this->sendgrid->send_personilization($email_data);

        if (!empty($_POST['attach_document'])) {
            switch ($ecb2bRequest['type']) {
                case Ec_B2b_Requests_Model::TYPE_SHORT_DECK:
                    $file = Ec_B2b_Requests_Model::DOC_SHORT_DECK;
                    break;
                case Ec_B2b_Requests_Model::TYPE_BUSINESS_PLAN:
                    $file = Ec_B2b_Requests_Model::DOC_BUSINESS_PLAN;
                    break;
                default:
                    $file = false;
                    break;
            }

            if ($file !== false) {
                $this->sendgrid->add_attachment($file, mime_content_type($file));
            }
        }

        if ($this->sendgrid->send()) {
            $this->ecb2b->update($ecb2bRequest['id'], array(
                'date_processed' => date('Y-m-d H:i:s'),
                'is_processed' => 1,
                'is_viewed' => 1,
            ));

            jsonResponse('Message was successfully sent', 'success');
        } else {
            jsonResponse('Error: message was not sent');
        }
    }

    function mark_viewed() {
        if (empty($_POST['id'])) {
            jsonResponse('Please specify the ID');
        }

        $this->load->model('Ec_B2b_Requests_Model', 'ecb2b');

        $ecb2bRequest = $this->ecb2b->get_requests(array(
            'id' => (int)$_POST['id']
        ));

        if (empty($ecb2bRequest)) {
            jsonResponse('Request not found');
        }

        $this->ecb2b->update($ecb2bRequest['id'], array(
            'is_viewed' => 1,
        ));

        jsonResponse('Marked viewed', 'success');
    }

    private function validate_recaptcha()
    {
        $token = isset($_POST['token']) ? $_POST['token'] : null;
        if(empty($token)) {
            jsonResponse('Error: Cannot send email now. You did not pas bot check', 'error', array(
                'errors' => array(
                    array(
                        'title'  => "Token is empty",
                        "detail" => "Verification token recieved from request is empty",
                    )
                )
            ));
        }

        $url = 'https://www.recaptcha.net/recaptcha/api/siteverify';
		$verify = file_get_contents($url, false, stream_context_create(array(
			'http' => array(
		    	'method' => 'POST',
			    'content' => http_build_query(array(
                    'secret'   => '6LcD84kUAAAAAFPLTnAfRD5SSu-hUZ4Qnv14KSrF',
                    'response' => $token
                ))
            )
        )));
        if(false === $verify) {
            jsonResponse('Error: Cannot send email now. You did not pas bot check. Please try agan later.', 'error', array(
                'errors' => array(
                    array(
                        'title'  => "Verification server is unresponsive",
                        "detail" => "The capthca verification server failed to respond in time or returned empty response",
                    )
                )
            ));
        }
        $captcha_success = json_decode($verify);
        if(null === $captcha_success || json_last_error()) {
            jsonResponse('Error: Cannot send email now. You did not pas bot check. Please try agan later.', 'error', array(
                'errors' => array(
                    array(
                        'title'  => "Malformed response",
                        "detail" => "The captchs verification server returned malformed response",
                    )
                )
            ));
        }

		if (false === $captcha_success->success) {
			jsonResponse('Error: Cannot send email now. You did not pas bot check. Please try agan later.', 'error', array(
                'errors' => array(
                    array(
                        'title'  => "Bot-check failed",
                        "detail" => "The score in bot-check is too low",
                        'meta'   => array(
                            'score' => $captcha_success->score,
                            'codes' => $captcha_success->{"error-codes"},
                        )
                    )
                )
            ));
        }

        return true;
    }
}
