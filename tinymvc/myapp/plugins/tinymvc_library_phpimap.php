<?php

use PhpImap\Mailbox;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Remove [03.12.2021]
 * Not used
 */
class TinyMVC_Library_phpimap
{
    public $error_message = array();

    public function get_email_message($params)
    {
        $results = array();
        $imapAccounts = explode('|', $params);
        foreach ($imapAccounts as $account) {
            // 0 - server
            // 1 - login (email)
            // 2 - password
            $accountParams = explode(',', $account);
            $handler = new Mailbox('{' . trim($accountParams[0]) . ':993/imap/ssl}INBOX', trim($accountParams[1]), trim($accountParams[2]));

            $newMailIds = $handler->searchMailbox('UNSEEN');
            if (empty($newMailIds)) {
                $handler->disconnect();
                $this->error_message['info'][] = 'Email box ' . $accountParams[1] . ' is empty';

                continue;
            }

            foreach ($newMailIds as $id) {
                $msg = $handler->getMail($id);
                $mess_html = $mess_text = '';
                $attachments = 0;

                if (!in_array($msg->fromAddress, $results['emails'])) {
                    $results['emails'][] = $msg->fromAddress;
                }

                if (!empty($msg->textHtml)) {
                    $mess_html = $msg->textHtml;
                }

                if (!empty($msg->textPlain)) {
                    $mess_text = $msg->textPlain;
                }

                if (!empty($msg->attachments)) {
                    $attachments = 1;
                }

                $results['messages'][$accountParams[1]][] = array(
                    'email_account'=> $accountParams[1],
                    'email_from'   => $msg->fromAddress,
                    'mess_subject' => $msg->subject,
                    'mess_text'    => $mess_text,
                    'mess_html'    => $mess_html,
                    'mess_time'    => $msg->date,
                    'attachments'  => $attachments,
                    'status_record'=> 'new',
                );
            }
            $handler->disconnect();
        }

        return $results;
    }

    public function get_error()
    {
        if (empty($this->error_message['info'])) {
            return false;
        }

        return array('type_mess' => 'info', 'message' => implode('<br/>', $this->error_message['info']));
    }
}
