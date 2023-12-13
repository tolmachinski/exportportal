<?php

use App\Common\Http\Request;
use App\Common\Validation\Legacy\ValidatorAdapter as LegacyValidatorAdapter;
use App\Common\Validation\ConstraintViolation;
use App\Common\Validators\SubscribeValidator;
use App\Email\ConfirmSubscription;
use App\Email\SubscribeToNewsletter;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 */
class Subscribe_Controller extends TinyMVC_Controller {

	function index() {
		$this->breadcrumbs[] = array(
			'link' => __SITE_URL . 'subscribe',
			'title' => 'Subscribe'
		);

		$this->view->display('new/header_view');
        $this->view->display('new/subscribe/index_view');
        $this->view->display('new/footer_view');
	}

	public function api_subscribe() {
		$token = $_POST["token"];
		$url = 'https://www.recaptcha.net/recaptcha/api/siteverify';
		$data = [
            'secret' => '6LcD84kUAAAAAFPLTnAfRD5SSu-hUZ4Qnv14KSrF',
            'response' => $token
		];
		$options = [
			'http' => [
			  'method' => 'POST',
			  'content' => http_build_query($data)
			]
		];
		$context  = stream_context_create($options);
		$verify = file_get_contents($url, false, $context);
		$captchaSuccess = json_decode($verify);
        $request = request();

		if ($captchaSuccess->success==false) {
			jsonResponse(translate('systmess_error_cannot_subscribe_now'));
		}

		$this->submitSubscribtion($request);
	}

    public function ajax_subscribe_operation()
    {
        checkIsAjax();

        $op = uri()->segment(3);

        switch ($op) {
            case 'subscribe':
                $this->submitSubscribtion(request());
                break;
        }
    }

    public function confirm_subscription()
    {
        $token = uri()->segment(3);

        if (empty($token)) {
            show_404();
        }

        /** @var Subscribe_Model $subscribeModel */
        $subscribeModel = model(Subscribe_Model::class);

        $subscriber = $subscribeModel->getSubscriberByToken($token);
        $redirectUrl = $subscriber['type'] !== 'general' && $subscriber['redirect_link'] ? $subscriber['redirect_link'] : __SITE_URL;

        if (empty($subscriber)) {
            show_404();
        } else {
            $subscribeModel->updateConfirmedStatus($subscriber['subscriber_email'], '1');

            /** @var MailerInterface $mailer */
            $mailer = $this->getContainer()->get(MailerInterface::class);
            $mailer->send(
                (new SubscribeToNewsletter())
                    ->to(new Address($subscriber['subscriber_email']))
            );

            cookies()->setCookieParam('_ep_subscriber_confirmed', true);

            if ('downloadable_materials' === $subscriber['type']) {
                cookies()->setCookieParam('_ep_success_subscribe_dm_message_key', 'js_dm_successfully_subscribe_message');
            } else {
                widgetPopupsSystemRemoveOneItem("subscribe");
            }

            headerRedirect($redirectUrl);
        }
    }

    private function submitSubscribtion(Request $request)
    {
        //region Validation
        $adapter = new LegacyValidatorAdapter(\library(TinyMVC_Library_validator::class));
        $validator = new SubscribeValidator($adapter);

        if (!$validator->validate($request->request->all())) {
            jsonResponse(
                \array_map(
                    function (ConstraintViolation $violation) {
                        return $violation->getMessage();
                    },
                    \iterator_to_array($validator->getViolations()->getIterator())
                )
            );
        }
        //endregion Validation

        /** @var Subscribe_Model $subscribeModel */
        $subscribeModel = model(Subscribe_Model::class);
        /** @var User_Model $userModel */
        $userModel = model(User_Model::class);

        $email = cleanInput($request->request->get('email'), true);

        //We check the email deliverability only on prod,  else email is assigned by default with status Bad
        if (config('env.APP_ENV') === 'prod' && 'Bad' === checkEmailDeliverability($email)) {
            jsonResponse(translate('systmess_error_undeliverable_email', ['[USER_EMAIL]' => $email]));
        }

        $subscriber = $subscribeModel->getSubscriberByEmail($email);
        $isRegisteredUser = $userModel->getSimpleUserByEmail($email);
        $idUser = (int) id_session();
        $token = getEmailHashToken($email);
        $isDownloadableMaterials = !empty($request->request->get('dm_page')) ? (bool) $request->request->get('dm_page') : null;

        if (!empty($subscriber) && $subscriber['confirmed']) {
            if ($idUser) {
                $userModel->updateUserMain($idUser, [
                    'subscription_email' => 1,
                    'notify_email'       => 1
                ]);

                session()->notify_email = 1;
                session()->subscription_email = 1;
            }

            if ($subscribeModel->check_unsubscriber_exists($subscriber['subscriber_email'].PHP_EOL)) {
                $unsubscriber = $subscribeModel->getUnsubscriberByEmail($subscriber['subscriber_email'].PHP_EOL);
                $subscribeModel->deleteUnsubscriber($unsubscriber['id']);
            }

            if ($isDownloadableMaterials) {
                jsonResponse(translate('dwn_successfully_subscribe_message'), 'success');
            } else {
                widgetPopupsSystemRemoveOneItem("subscribe");
                jsonResponse(translate('subscribe_popup_successfully_subscribed'), 'success');
            }
        } elseif (!empty($subscriber) && !$subscriber['confirmed']) {
            $this->sendEmailToConfirmSubscribe($email, $token);
        }

        if (!$subscribeModel->insertSubscriber([
            'subscriber_email' => $email,
            'token_email'      => $token,
            'confirmed'        => ($idUser && $email === email_session() || $isRegisteredUser) ? 1 : 0,
            'type'             => $isDownloadableMaterials ? 'downloadable_materials' : 'general',
            'redirect_link'    => $isDownloadableMaterials ? (string) $request->request->get('current_url') : null
        ])) {
            jsonResponse(translate('systmess_error_insert_subscriber_db_error'));
        }

        if ($isRegisteredUser) {
            if ($subscribeModel->check_unsubscriber_exists($subscriber['subscriber_email'].PHP_EOL)) {
                $unsubscriber = $subscribeModel->getUnsubscriberByEmail($subscriber['subscriber_email'].PHP_EOL);
                $subscribeModel->deleteUnsubscriber($unsubscriber['id']);
            }

            if ($isDownloadableMaterials) {
                jsonResponse(translate('dwn_successfully_subscribe_message'), 'success');
            } else {
                widgetPopupsSystemRemoveOneItem("subscribe");
                jsonResponse(translate('subscribe_popup_successfully_subscribed'), 'success');
            }
        } else {
            $this->sendEmailToConfirmSubscribe($email, $token);
        }
    }

    private function sendEmailToConfirmSubscribe($email, $token) {

        try {
            /** @var MailerInterface $mailer */
            $mailer = $this->getContainer()->get(MailerInterface::class);
            $mailer->send(
                (new ConfirmSubscription($token))
                    ->to(new Address($email))
            );
        } catch (\Throwable $th) {
            jsonResponse(translate('email_has_not_been_sent'));
        }

        jsonResponse(translate('systmess_info_please_confirm_email'), 'info', [
            'popupTitle' => translate('subscribe_popup_confirm_subscription'),
        ]);
	}
}
