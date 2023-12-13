<?php

use App\Email\Systmessages;
use App\Logger\CommunicationLogger;
use ExportPortal\Bridge\Mailer\Mime\RefAddress;
use Monolog\Logger;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

/**
 * This model is deprecated for working with table mail_messages
 * For table mail_messages was created mail_messages_model
 *
 * @see Mail_Messages_Model
 */
class Notify_Model extends TinyMVC_Model
{
	public $user_systmessages_table = 'user_systmessages';

	private $mail_messages_table = 'mail_messages';

    /**
     * @deprecated `v2.32.0` `2022/01/18` in favor of `symfony/notification`
     *
     * Instead of using this method, you need to use `\Symfony\Component\Notifier\NotifierInterface` instance like this:
     *
     * ```
     * <?php
     *
     * declare(strict_types=1);
     *
     * use ExportPortal\Bridge\Notifier\Notification\SystemNotification;
     * use ExportPortal\Bridge\Notifier\Recipient\AggregatedRecipient;
     * use ExportPortal\Bridge\Notifier\Recipient\Recipient;
     * use Symfony\Component\Notifier\NotifierInterface;
     *
     * $notifier = $this->getContainer()->get(NotifierInterface::class);
     * $notifier->send(new SystemNotification($notificationCode, $recplacementContext), new AggregatedRecipient([
     *      new Recipient($userId),
     * ]));
     * ```
     */
    function send_notify($data)
    {
		if (empty(array_filter($data['id_users'])) || empty($data['mess_code'])) {
			return false;
		}

        /** @var SystMess_Model $systemMessagesModel */
        $systemMessagesModel = model(SystMess_Model::class);

        $message_info = $systemMessagesModel->get_message(array('mess_code' => $data['mess_code']));
        if(empty($message_info)) {
            return false;
        }

        $message = $message_info['message'];
        $title = $message_info['title'];
        $mess_type = $message_info['mess_type'];
        $type = $message_info['type'];
        $module = $message_info['module'];
        $date_send = date('Y-m-d H:i:s');
        $calendar_only = empty($data['systmess']) || $data['systmess'] == false ? 1 : 0;
        $users = arrayByKey(model('user')->get_simple_users(array('users_list' => implode(',', $data['id_users']))), 'idu');

        $user_systmess_settings = array_map(
            function ($setting) {
                return arrayByKey($setting, 'module');
            },
            arrayByKey(model('users_systmess_settings')->get_settings($data['id_users']), 'id_user', true)
        );

        if(!empty($data['replace'])) {
            $message = str_replace(array_keys($data['replace']), array_values($data['replace']), $message);
            $title = str_replace(array_keys($data['replace']), array_values($data['replace']), $title);
        }

        $values_system_mess = array();
        foreach($users as $id_user => $user) {
            $values_system_mess[] = $insertMessage = [
                'init_date' => $date_send,
                'idu' => $id_user,
                'idmess' => $message_info['idmess'],
                'title' => $title,
                'message' => $message,
                'mess_type' => $mess_type,
                'module' => $module,
                'calendar_only' => $calendar_only
            ];

            if (
                !$calendar_only
                && !empty(arrayGet($user_systmess_settings, "{$id_user}.{$module}.email_notification"))
                && 'new' === $type
            ) {
                $additionalContent = '';

                if (isset($insertMessage['additional'])) {
                    foreach($insertMessage['additional'] as $additional) {
                        $additionalContent .= '<p>' . $additional . '</p>';
                    }
                }

                /** @var MailerInterface $mailer */
                $mailer = container()->get(MailerInterface::class);
                $mailer->send(
                    (new Systmessages($user['fname'], $insertMessage['init_date'], $insertMessage['title'], !empty($additionalContent) ? $additionalContent : ''))
                        ->to(new RefAddress((string) $id_user, new Address($user['email'])))
                );

            }

            //region monolog
			$logger = new Logger('CommunicationLogger');
			$logger->pushHandler(new CommunicationLogger('monolog_logs', $this->db));
			$logger->error("Notification sent with the message: \"{$message}\"", [
				'id_user' => $id_user,
				'type'    => 'notification',
				'details' => [
					'title'   => $title,
					'message' => $message
				]
			]);
            //endregion monolog
        }

        return $this->db->insert_batch($this->user_systmessages_table, $values_system_mess);
    }

	function send_systmess_multiple($data){

		if (empty($data)) {
			return false;
        }

        $values_system_mess = array();

		foreach($data as $notify){
            if(empty($notify['mess_code'])) {
                return false;
            }

            /** @var SystMess_Model $systemMessagesModel */
            $systemMessagesModel = model(SystMess_Model::class);

            $message_info = $systemMessagesModel->get_message(array('mess_code' => $notify['mess_code']));
            if(empty($message_info)) {
                return  false;
            }

            $message = $message_info['message'];
            $title = $message_info['title'];
            $mess_type = $message_info['mess_type'];
            $module = $message_info['module'];
            $date_send = date('Y-m-d H:i:s');
            $calendar_only = empty($notify['systmess']) || $notify['systmess'] == false ? 1 : 0;

            if(!empty($notify['replace'])){
                $message = str_replace(array_keys($notify['replace']), array_values($notify['replace']), $message);
                $title = str_replace(array_keys($notify['replace']), array_values($notify['replace']), $title);
            }

            foreach($notify['id_users'] as $user) {
                $values_system_mess[] = "('$date_send', $user, '$title', '$message', '$mess_type', '$module', $calendar_only)";
            }
		}

        $values_system_mess = implode(',', $values_system_mess);

		if(!empty($values_system_mess)) {
			$sql = "
                INSERT INTO user_systmessages
                    (init_date, idu, title, message, mess_type, `module`, calendar_only)
                VALUES $values_system_mess
            ";

			$this->db->query($sql);
		}
	}

	public function get_emails($verified = false, $messages_count = 50, $id = null){
        $this->db->from('mail_messages');
        $this->db->where('is_sent', 0);

        if($verified){
            $this->db->where('is_verified', 1);
        }

        if(!empty($id)){
            $this->db->where('id', (int) $id);
        }

        $this->db->limit((int) $messages_count);
        return $this->db->query_all();
	}

	public function get_sent_messages_in_last_day(){
		return $this->db->query_all("SELECT * FROM mail_messages WHERE `is_sent` = 1 AND `sent_date` >= DATE_ADD(NOW(), INTERVAL -1 DAY)");
	}

	public function delete_emails($days = 3){
		$sql = "DELETE
				FROM mail_messages
				WHERE is_sent = 1 AND TIMESTAMPDIFF(DAY, sent_date, NOW()) > ?";
		return $this->db->query($sql, [$days]);
	}

	public function exist_email($key){
		$sql = "SELECT COUNT(*) as counter
				FROM mail_messages
				WHERE email_key_link = ?";

		$rez = $this->db->query_one($sql, array($key));

		return $rez['counter'];
	}

	public function get_email_by_key($key){
		$sql = "SELECT *
				FROM mail_messages
				WHERE email_key_link = ?";
		return $this->db->query_one($sql, array($key));
	}

	public function update_sent_emails($emails_list = array(), $data = array()){
		if(empty($emails_list)){
			return false;
		}

		$this->db->in("id", $emails_list);
		return $this->db->update("mail_messages", $data);
    }

    public function get_not_verified_mails()
    {
        $this->db->select('`id`,`to`');
        $this->db->from('mail_messages');
        $this->db->where('is_verified', 0);
        $this->db->where('is_sent', 0);
        $this->db->limit(30);

        return $this->db->query_all();
    }

    public function get_mail_messages(array $params = array())
    {
        $skip = null;
        $limit = null;
        $order = array();
        $conditions = array();

        extract($params);

        $this->db->from($this->mail_messages_table);
        if (isset($conditions['from'])) {
            $this->db->where_raw("`from` LIKE ?", '%' . $conditions['from'] . '%');
        }

        if (isset($conditions['to'])) {
            $this->db->where_raw("`to` LIKE ?", '%' . $conditions['to'] . '%');
        }

        if (isset($conditions['is_sent'])) {
            $this->db->where('is_sent', $conditions['is_sent']);
        }

        if (isset($conditions['sent_date_from'])) {
            $this->db->where('DATE(sent_date) >= ?', $conditions['sent_date_from']);
        }

        if (isset($conditions['sent_date_to'])) {
            $this->db->where('DATE(sent_date) <= ?', $conditions['sent_date_to']);
        }

        //region OrderBy
        $ordering = array();
        foreach ($order as $column => $direction) {
            if (!empty($direction) && is_string($direction)) {
                $direction = mb_strtoupper($direction);
                $ordering[] = "{$column} {$direction}";
            } else {
                $ordering[] = $column;
            }
        }

        if (!empty($ordering)) {
            $this->db->orderby(implode(', ', $ordering));
        }
        //endregion OrderBy

        //region Limits
        if (null !== $limit) {
            if (null !== $skip) {
                $this->db->limit($limit, $skip);
            } else {
                $this->db->limit($limit);
            }
        }
        //endregion Limits

        return $this->db->get();
    }

    public function get_count_mail_messages(array $params = array()){
        $conditions = array();

        extract($params);

        if (!empty($conditions['from'])) {
            $this->db->where_raw("`from` LIKE ?", '%' . $conditions['from'] . '%');
        }

        if (!empty($conditions['to'])) {
            $this->db->where_raw("`to` LIKE ?", '%' . $conditions['to'] . '%');
        }

        if (isset($conditions['is_sent'])) {
            $this->db->where('is_sent', $conditions['is_sent']);
        }

        if (isset($conditions['sent_date_from'])) {
            $this->db->where('DATE(sent_date) >= ?', $conditions['sent_date_from']);
        }

        if (isset($conditions['sent_date_to'])) {
            $this->db->where('DATE(sent_date) <= ?', $conditions['sent_date_to']);
        }

        $this->db->select('COUNT(*) as count_messages');
        $this->db->from($this->mail_messages_table);
        $result = $this->db->get_one();

        return (int) ($result['count_messages'] ?? 0);
    }

    /**
     * @param int $userId
    */
    public function deleteEmailsByUserId(int $userId)
    {
        $this->db->where('id_user', $userId);
        return $this->db->delete($this->mail_messages_table);
    }
}
