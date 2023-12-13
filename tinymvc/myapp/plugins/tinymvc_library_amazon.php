<?php

use Aws\Credentials\Credentials;
use Aws\Exception\AwsException;
use Aws\Ses\SesClient;

/**
 * Library Amazon
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [01.12.2021]
 * library refactoring: code style
 *
 * @link https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/d.-Libraries/b.-Amazon
 */
class TinyMVC_Library_Amazon
{
    private $config_set_name;
    private $api_secret;
    private $api_key;
    private $char_set = 'UTF-8';
    private $text_content;
    public $message_id;
    public $errors;

    public $mail_subject;
    public $reply_to_email;
    public $from_email;
    public $from_name = 'ExportPortal.com';
    public $to;
    public $bcc;
    public $cc;
    public $html_content;
    public $unsubscribe_email;
    public $unsubscribe_link;
    public $attachments;
    public $template_tag;
    public $sender_tag;

    public function __construct()
    {
        $this->config_set_name = $_ENV['AMAZON_SES_SET_NAME'];
        $this->api_secret = $_ENV['AMAZON_SES_SECRET'];
        $this->api_key = $_ENV['AMAZON_SES_KEY'];
    }

    public function send_email()
    {
        if (is_null($this->mail_subject)) {
            $this->errors = 'Subject is required';
            return false;
        }

        if (is_null($this->from_email)) {
            $this->errors = 'Sender email address is required';
            return false;
        }

        if (is_null($this->html_content)) {
            $this->errors = 'Email message is required';
            return false;
        }

        if (is_null($this->to)) {
            $this->errors = 'Please provide at least one mail recipient';
            return false;
        }

        if ( ! is_string($this->to)) {
            $this->errors = 'Recipient must be a string! Found: ' . gettype($this->to);
            return false;
        }

        $this->html_content = preg_replace('/[[:^print:]]/', '', $this->html_content);
        $this->text_content = library('html2text')->convert($this->html_content)->get_text();

        $SesClient = new SESClient(array(
            'version'     => 'latest',
            'region'      => 'us-west-2',
            'credentials' => new Credentials($this->api_key, $this->api_secret)
        ));

        $destination = array(
            'ToAddresses' => array($this->to)
        );

        if ( ! is_null($this->bcc)) {
            $destination['BccAddresses'] = explode(',', $this->bcc);
        }

        if ( ! is_null($this->cc)) {
            $destination['CcAddresses'] = explode(',', $this->cc);
        }

        try {
            $result = $SesClient->sendEmail([
                'ConfigurationSetName' => $this->config_set_name,
                'Destination' => $destination,
                'ReplyToAddresses' => is_null($this->reply_to_email) ? array($this->from_email) : array($this->reply_to_email),
                'Source' => $this->from_name . ' <' . $this->from_email . '>',
                'Message' => [
                    'Body' => [
                        'Html' => array(
                            'Charset' => $this->char_set,
                            'Data' => $this->html_content,
                        ),
                        'Text' => array(
                            'Charset' => $this->char_set,
                            'Data' => $this->text_content,
                        ),
                    ],
                    'Subject' => array(
                        'Charset' => $this->char_set,
                        'Data' => $this->mail_subject,
                    ),
                ]
            ]);

            $this->message_id = $result['MessageId'];

            return true;
        } catch (AwsException $exception) {
            $this->errors = $exception->getAwsErrorCode();

            return false;
        }
    }

    public function reset()
    {
        $this->errors = NULL;
        $this->message_id = NULL;
        $this->subject = NULL;
        $this->from_email = NULL;
        $this->reply_to_email = NULL;
        $this->to = NULL;
        $this->bcc = NULL;
        $this->cc = NULL;
        $this->html_content = NULL;
        $this->text_content = NULL;
        $this->unsubscribe_email = NULL;
        $this->unsubscribe_link = NULL;
        $this->attachments = NULL;
        $this->template_tag = NULL;
        $this->sender_tag = NULL;
    }
}

/* End of file tinymvc_library_amazon.php */
/* Location: /tinymvc/myapp/plugins/tinymvc_library_amazon.php */
