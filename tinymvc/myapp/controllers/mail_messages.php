<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Mail_messages_Controller extends TinyMVC_Controller
{
    public function administration(){
        checkAdmin('manage_email_messages');

        views(
            [
                'admin/header_view',
                'admin/mail_messages/index_view',
                'admin/footer_view'
            ],
            [
                'title' => 'The message list from email',
            ]
        );
    }

    public function send_email()
    {
        checkIsAjax();
        checkPermisionAjax('manage_email_messages');

        if (empty($mailMessageId = request()->request->getInt('id'))) {
            jsonResponse('Mail message ID was expected.');
        }

        /** @var Mail_Messages_Model $mailMessagesModel */
        $mailMessagesModel = model(Mail_Messages_Model::class);

        if (empty($mailMessage = $mailMessagesModel->findOne($mailMessageId, ['with' => ['content']]))) {
            jsonResponse('Mail message ID is wrong.');
        }

        if ($mailMessage['is_sent']) {
            jsonResponse('This email has already been sent earlier.');
        }

        $phpMailer = new PHPMailer(true);

        try {
            //Server settings
            $phpMailer->SMTPDebug = SMTP::DEBUG_SERVER;                          // Enable verbose debug output
            $phpMailer->isSMTP();                                                // Send using SMTP
            $phpMailer->Host       = config('env.SMTP_GMAIL_HOST');              // Set the SMTP server to send through
            $phpMailer->SMTPAuth   = true;                                       // Enable SMTP authentication
            $phpMailer->Username   = config('env.SMTP_GMAIL_ACCOUNT_EMAIL');     // SMTP username
            $phpMailer->Password   = config('env.SMTP_GMAIL_ACCOUNT_PASSWORD');  // SMTP password
            $phpMailer->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;                // `PHPMailer::ENCRYPTION_STARTTLS` Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $phpMailer->Port       = config('env.SMTP_GMAIL_PORT');              // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            $phpMailer->setFrom(empty($mailMessage['from']) ? config('env.EMAIL_NO_REPLY') : $mailMessage['from']);
            $phpMailer->addReplyTo(empty($mailMessage['reply_to']) ? config('env.EMAIL_SUPPORT') : $mailMessage['reply_to']);

            $emailsList = explode(\App\Common\EMAIL_DELIMITER, $mailMessage['to']);

            foreach ($emailsList as $email) {
                $phpMailer->addAddress($email);
            }

            if (!empty($mailMessage['bcc'])) {
                $phpMailer->addBCC($mailMessage['bcc']);
            }

            if (!empty($mailMessage['cc'])) {
                $phpMailer->addCC($mailMessage['cc']);
            }

            $phpMailer->isHTML(true);
            $phpMailer->Subject = $mailMessage['subject'];
            $phpMailer->Body = $mailMessage['content']['message'];

            ob_start();
            $phpMailer->send();
            ob_end_clean();
        } catch (Exception $e) {
            ob_end_clean();
            $mailMessagesModel->updateOne($mailMessageId, ['failure_log' => json_encode($phpMailer->ErrorInfo)]);
            jsonResponse('Couldn\'t send email. Please contact the development department to get more information about this error.');
        }

        $mailMessagesModel->updateOne(
            $mailMessageId,
            [
                'sent_date' => (new \DateTime())->format('Y-m-d H:i:s'),
                'is_sent'   => 1,
            ]
        );

        jsonResponse('The email was sent successfully. If you can\'t find your email, also check the spam folder and mark it as not spam.', 'success');
    }

    public function ajax_dt_email_message(){
        checkIsAjax();
        checkPermisionAjaxDT('manage_email_messages');

        $request = request()->request;

        $skip = $request->getInt('iDisplayStart') ?: null;
        $limit = $request->getInt('iDisplayLength') ?: null;

        $conditions = dtConditions(
            $request->all(),
            [
                ['as' => 'from',           'key' => 'sender',          'type' => 'cleanInput'],
                ['as' => 'to',             'key' => 'recipient',       'type' => 'cleanInput'],
                ['as' => 'is_sent',        'key' => 'is_sent',         'type' => 'int'],
                ['as' => 'sent_date_from', 'key' => 'sent_date_from',  'type' => 'formatDate:Y-m-d'],
                ['as' => 'sent_date_to',   'key' => 'sent_date_to',    'type' => 'formatDate:Y-m-d'],
            ]
        );

        $order = array_column(
            dt_ordering($request->all(), ['dt_sent_date' => 'sent_date']),
            'direction',
            'column'
        );

        $mailMessages = model(Notify_Model::class)->get_mail_messages(compact('conditions', 'order', 'skip', 'limit'));
        $countMailMessages = model(Notify_Model::class)->get_count_mail_messages(compact('conditions'));

        $output = [
            "iTotalDisplayRecords"  => $countMailMessages,
            "iTotalRecords"         => $countMailMessages,
			'aaData'                => [],
            "sEcho"                 => $request->getInt('sEcho'),
        ];

        if (empty($mailMessages)) {
            jsonResponse('', 'success', $output);
        }

        $appEnv = config('env.APP_ENV');

        foreach ($mailMessages as $mailMessage) {
            $row = [
                'dt_email_from' => $mailMessage['from'],
				'dt_id_record'  => $mailMessage['id'],
				'dt_sent_date'  => getDateFormat($mailMessage['sent_date']),
				'dt_email_to'   => $mailMessage['to'],
                'dt_view_url'   => '<a href="' . __SITE_URL . 'email_message/user_view/' . $mailMessage['email_key_link'] . '" target="_blank">View online</a>',
				'dt_subject'    => $mailMessage['subject'],
                'dt_is_sent'    => '<span class="label label-' . ($mailMessage['is_sent'] ? 'success' : 'warning') . '">' . ($mailMessage['is_sent'] ? 'Yes' : 'No') . '</span>',
            ];

            if ('dev' === $appEnv) {
                if ($mailMessage['is_sent']) {
                    $row['dt_actions'] = <<<SEND_EMAIL_BUTTON
                        <i
                            class="ep-icon ep-icon_envelope-stroke txt-green"
                            title="This email was sent"
                        ></i>
                    SEND_EMAIL_BUTTON;
                } else {
                    $row['dt_actions'] = <<<SEND_EMAIL_BUTTON
                        <a
                            class="ep-icon ep-icon_envelope txt-blue2 confirm-dialog"
                            data-callback="sendEmailMessage"
                            data-message="Are you sure you want to send this email?"
                            data-id="{$mailMessage['id']}"
                            title="Send an email"
                        ></a>
                    SEND_EMAIL_BUTTON;
                }
            }

            $output['aaData'][] = $row;
        }

        jsonResponse('', 'success', $output);
    }
}

/* End of file mail_messages.php */
/* Location: /tinymvc/myapp/controllers/mail_messages.php */
