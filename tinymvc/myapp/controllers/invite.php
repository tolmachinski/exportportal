<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */

use App\Email\FriendInvite;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

class Invite_Controller extends TinyMVC_Controller
{
    public function popup_forms(){
		if (!isAjaxRequest()) {
			headerRedirect();
		}

		$op = $this->uri->segment(3);
		switch($op){
            case 'invite':
                $id_user = id_session();
				checkPermisionAjaxModal('invite_friend');
				$user_group_type = strtolower(user_group_type());
				if(!in_array($user_group_type, array('seller', 'buyer', 'shipper'))){
					messageInModal(translate('systmess_error_invalid_data'));
				}

                $data['invite_messages'] = json_encode($this->_get_messages($user_group_type, $id_user));
                $data['id_user'] = $id_user;
				$this->view->display('new/invite/popup_invite_view', $data);
			break;
			case 'invite_by_email':
				checkPermisionAjaxModal('invite_friend');

                $id_user = id_session();
				$user_group_type = strtolower(user_group_type());
				if(!in_array($user_group_type, array('seller', 'buyer', 'shipper'))){
					messageInModal(translate('systmess_error_invalid_data'));
				}

				$message_key = empty($_GET['message_key']) ? null : $_GET['message_key'];
				$template = empty($_GET['template']) ? null : $_GET['template'];
				if(!isset($this->_get_messages($user_group_type, $id_user)[$template][$message_key])){
					messageInModal(translate('systmess_error_invalid_data'));
				}

                $data['invite_message'] = $this->_get_messages($user_group_type, $id_user)[$template][$message_key];
                $data['id_user'] = $id_user;
				$this->view->display('new/invite/popup_invite_by_email_view', $data);
			break;
		}
	}

	public function ajax_send_email(){
		if (!isAjaxRequest()) {
			headerRedirect();
		}

		$op = $this->uri->segment(3);
		switch($op){
			case 'email':
				checkPermisionAjax('invite_friend');

				$validator_rules = array(
					array(
						'field' => 'message',
						'label' => translate('email_user_form_message_label'),
						'rules' => array('required' => '', 'max_len[500]' => '')
					),
					array(
						'field' => 'email',
						'label' => 'Seller Email',
						'rules' => array('required' => '', 'no_whitespaces' => '', 'valid_email' => '')
					)

				);

				$this->validator->set_rules($validator_rules);
				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
				}

				$friendEmail = cleanInput(request()->request->get('email'), true);

                try {
                    /** @var MailerInterface $mailer */
                    $mailer = $this->getContainer()->get(MailerInterface::class);
                    $mailer->send(
                        (new FriendInvite(user_name_session(), cleanInput(request()->request->get('message'))))
                            ->to(new Address($friendEmail))
                            ->subjectContext([
                                '[userName]' => user_name_session(),
                            ])
                    );
                } catch (\Throwable $th) {
                    jsonResponse(translate('email_has_not_been_sent'));
                }

                jsonResponse(translate('systmess_successfully_sent_email'), 'success', ['email' => $friendEmail]);
			break;
		}
	}

	private function _get_messages($group_type = null, $id_user = ''){
		$invite_messages = array(
			'buyer' => array(
				'general' => array(
					'currentClient' 	=> translate('friend_invite_buyer_current_client', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
					'friend' 			=> translate('friend_invite_buyer_friend', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
					'potentialClient' 	=> translate('friend_invite_buyer_potential_client', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
				),

				'twitter' => array(
					'currentClient' 	=> translate('friend_invite_buyer_current_client_twitter', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
					'friend' 			=> translate('friend_invite_buyer_friend_twitter', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
					'potentialClient' 	=> translate('friend_invite_buyer_potential_client_twitter', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user))
				)
			),
			'seller' => array(
				'general' => array(
					'currentClient' 	=> translate('friend_invite_seller_current_client', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
					'friend' 			=> translate('friend_invite_seller_friend', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
					'potentialClient' 	=> translate('friend_invite_seller_potential_client', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
				),

				'twitter' => array(
					'currentClient' 	=> translate('friend_invite_seller_current_client_twitter', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
					'friend' 			=> translate('friend_invite_seller_friend_twitter', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
					'potentialClient' 	=> translate('friend_invite_seller_potential_client_twitter', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
				)
			),
			'shipper' => array(
				'general' => array(
					'currentClient' 	=> translate('friend_invite_shipper_curent_client', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
					'friend' 			=> translate('friend_invite_shipper_friend', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
					'potentialClient' 	=> translate('friend_invite_shipper_potential_client', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
				),

				'twitter' => array(
					'currentClient' 	=> translate('friend_invite_shipper_curent_client_twitter', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
					'friend' 			=> translate('friend_invite_shipper_friend_twitter', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user)),
					'potentialClient' 	=> translate('friend_invite_shipper_potential_client_twitter', array('{{LINK}}' => __SITE_URL . 'register/ref/' . $id_user))
				)
			)
		);

		if(isset($invite_messages[$group_type])){
			return $invite_messages[$group_type];
		}

		return $invite_messages;
	}
}
