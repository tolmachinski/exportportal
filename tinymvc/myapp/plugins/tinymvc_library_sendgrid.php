<?php

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [03.12.2021]
 * library refactoring: code style, optimize code
 */
class TinyMVC_Library_Sendgrid
{
	private $from;
	private $reply_to;
	private $subject;
	private $personalizations = array();
	private $content = array();
	private $attachments = array();
	private $headers = array();

	private $disallowed_headers = array('x-sg-id', 'x-sg-eid', 'received', 'dkim-signature', 'Content-Type', 'Content-Transfer-Encoding', 'To', 'From', 'Subject', 'Reply-To', 'CC', 'BCC');

	private $payload_data;
	private $response;
	private $response_code;
	private $response_headers;
	private $errors;

	public $api_endpoint = 'https://api.sendgrid.com/v3/mail/send';
	public $api_key = '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'; //old, dev key
	public $from_name = '';
	public $from_email = '';
	public $reply_to_name = '';
	public $reply_to_email = '';
	public $html_content = '';
	public $text_content = '';
	public $mail_content = '';
	public $mail_subject = '';
	public $personalization_data = '';
	public $attached_files = '';
	public $additional_headers = '';
	public $sanbox_mode = false;

    /**
     * @param ContainerInterface $container The container instance
     */
    public function __construct(ContainerInterface $container)
    {
        $tmvc = $container->get('kernel');
        if (!empty($tmvc->my_config['sendgrid_key'])) {
            $this->api_key = $tmvc->my_config['sendgrid_key'];
        }

		if(!empty($params)){
			$this->initialize($params);
		}
	}

	public function initialize($index, $value = ''){
		if(is_array($index)){
			foreach($index as $property_key => $poperty_value){
				$this->initialize($property_key, $poperty_value);
			}
		} else {
			try {
				$reflection = new ReflectionProperty($this, $index);
				if($reflection->isPublic()){
					if($reflection->isStatic()){
						$reflection->setValue($value);
					} else {
						$reflection->setValue($this, $value);
					}
				}
			} catch(ReflectionException $e){
				$this->{$index} = $value;
			}
		}

		return $this;
	}

	public function change_api_key($value = ''){
		if(!empty($value)){
			$this->api_key = $value;
		}

		return $this;
	}

	public function change_endpoint($value = ''){
		if(!empty($value)){
			$this->api_endopoint = $value;
		}

		return $this;
	}

	public function enable_sandbox(){
		$this->sanbox_mode = true;

		return $this;
	}

	public function disable_sandbox(){
		$this->sanbox_mode = true;

		return $this;
	}

	public function add_content($value, $type){
		if(is_array($value) && is_array(current($value))){
			foreach($value as $index => $content_data){
				$this->add_content($content_data['value'], $content_data['type']);
			}
		} else {
			$this->content[] = array('type' => $type, 'value' => $value);
		}
		return $this;
	}

	public function clean_content(){
		$this->content = null;
		return $this;
	}

	public function add_attachment($path, $type = '', $is_inline = false, $content_id = ''){
		if(is_array($path) && is_array(current($path))){
			foreach($path as $index => $filedata){
				$this->add_attachment($filedata);
			}
		} else {
			if(is_array($path)) {
				foreach (array('content_id', 'is_inline', 'type', 'path') as $item)
				{
					if (isset($path[$item]))
					{
						$$item = $path[$item];
					}
				}
			}

			if(empty($type)){
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$type = finfo_file($finfo, $path);
				finfo_close ($finfo);
			}

			$attachment = array(
				'content' => $this->encode_base64($path),
				'filename' => basename($path),
				'type' => $type,
			);

			if($is_inline){
				$attachment['disposition'] = 'inline';
			}

			if(!empty($content_id)){
				$attachment['content_id'] = $content_id;
			}

			$this->attachments[] = $attachment;
		}

		return $this;
	}

	public function add_header($type, $value){
		if(is_array($type) && is_array(current($type))){
			foreach($type as $index => $header_data){
				$this->add_header($header_data['type'], $header_data['value']);
			}
		} else {
			if(!in_array($type, $this->disallowed_headers) && !empty($value)){
				$this->headers[$type] = $value;
			}
		}

		return $this;
	}

	public function subject($subject){
		$this->subject = $subject;

		return $this;
	}

	public function from($from, $sender){
		$this->from = $this->get_email_array($from, $sender);

		return $this;
	}

	public function reply_to($reply, $name){
		$this->reply_to = $this->get_email_array($reply, $name);

		return $this;
	}

	public function add_personilization($to, $cc = null, $bcc = null, $substitutions = null, $headers = null,  $custom_args = null, $subject = null, $send_at = null){
		if(is_array($to)){
			$current = current($to);
			if(is_array($current) && isset($current['to'])){
				foreach($to as $index => $personal_data){
					$this->add_personilization($personal_data);
				}
			} else {
				foreach (array('send_at', 'subject', 'custom_args', 'headers', 'substitutions', 'bcc', 'cc', 'to') as $item)
				{
					if (isset($to[$item]))
					{
						$$item = $to[$item];
					}
				}

				$this->envelope($to, $cc, $bcc, $substitutions, $headers,  $custom_args, $subject, $send_at);
			}
		} else {
			$this->envelope($to, $cc, $bcc, $substitutions, $headers,  $custom_args, $subject, $send_at);
		}

		return $this;
	}

	public function clean_personilization(){
		$this->personalizations = null;

		return $this;
	}

	public function _reset(){
		$this->clean_personilization();
		$this->clean_content();

		return $this;
	}

	public function compile(){
		$this->from($this->from_email, $this->from_name);
		$this->reply_to($this->reply_to_email, $this->reply_to_name);
		$this->subject($this->mail_subject);

		if(!empty($this->mail_content)){
			$this->add_content($this->mail_content, null);
		}

		if(!empty($this->additional_headers)){
			$this->add_header($this->additional_headers, null);
		}

		if(!empty($this->attached_files)){
			$this->add_attachment($this->attached_files);
		}

		if(!empty($this->personalization_data)){
			$this->add_personilization($this->personalization_data);
		}

		$this->payload();

		return $this;
	}

	public function send(){
		$this->compile()->request();
		if(!empty($this->response)){

			$this->error_handler();

			return false;
		}

		return true;
	}

	public function get_response($raw = false){
		if($raw){
			return $this->response;
		}

		return json_decode($this->response);
	}

	public function get_headers($raw = false){
		if($raw){
			return $this->response_headers;
		}

		return array_values(array_filter(explode(PHP_EOL, $this->response_headers)));
	}

	public function get_errors($prefix = '', $postfix = ''){
		if(!empty($this->errors['error_list'])){
			return $prefix . implode($postfix.$prefix, $this->errors['error_list']) . $postfix;
		}

		return null;
	}

	public function get_raw_message(){
		if(empty($this->payload_data)){
			$this->compile()->payload();
		}

		return $this->payload_data;
	}

	public function get_raw_errors(){
		return $this->errors;
	}

	private function encode_base64($value){
		if(is_file($value)){
			return base64_encode(fread(fopen($value, "r"), filesize($value)));
		}

		return base64_encode($value);
	}

	private function std_to_array($object){
		$rc =  (array) $object;
		foreach($rc as $key => &$field){
			if(is_object($field)){
				$field = (array) $field;
			}
		}

		return $rc;
	}

	private function get_email_array($email, $name = ''){
		$email_array['email'] = $email;
		if(!empty($name)){
			$email_array['name'] = $name;
		}

		return $email_array;
	}

	private function payload(){
		$payload['from'] = $this->from;
		$payload['reply_to'] = $this->reply_to;
		$payload['subject'] = $this->subject;
		$payload['content'] = $this->content;
		$payload['attachments'] = $this->attachments;
		$payload['headers'] = $this->headers;
		$payload['personalizations'] = $this->personalizations;

		if($this->sanbox_mode){
			$payload['mail_settings']['sandbox_mode']['enable'] = true;
		}

		$this->payload_data = array_filter($payload);
	}

	public function send_personilization($conditions = array()){
		$to = null;
		$cc = null;
		$bcc = null;
		$substitutions = null;
		$headers = null;
		$custom_args = null;
		$subject = null;
		$send_at = null;

		extract($conditions);

		$this->envelope($to, $cc, $bcc, $substitutions, $headers,  $custom_args, $subject, $send_at);

		return $this;
	}

	private function envelope($to, $cc = null, $bcc = null, $substitutions = null, $headers = null,  $custom_args = null, $subject = null, $send_at = null){

		if(is_array($to)){
			$personalization['to'] = count($to) === count($to, 1) ? array($to) : $to;
		} else {
			$personalization['to'] = array(array('email' => $to));
		}

		if(!empty($cc)){
			if(is_array($to)){
				$personalization['cc'] = count($cc) === count($cc, 1) ? array($cc) : $cc;
			} else {
				$personalization['cc'] = array(array('email' => $cc));
			}
		}

		if(!empty($bcc)){
			if(is_array($bcc)){
				$personalization['bcc'] = count($bcc) === count($bcc, 1) ? array($bcc) : $bcc;
			} else {
				$personalization['bcc'] = array(array('email' => $bcc));
			}
		}

		if(!empty($subject)){
			$personalization['subject'] = $subject;
		}

		if(!empty($headers) && is_array($headers)){
			$personalization['headers'] = $headers;
		}

		if(!empty($substitutions) && is_array($substitutions)){
			$personalization['substitutions'] = $substitutions;
		}

		if(!empty($custom_args) && is_array($custom_args)){
			$personalization['custom_args'] = $custom_args;
		}

		if(!empty($send_at) && is_integer($send_at)){
			$personalization['send_at'] = $send_at;
		}

		$this->personalizations[] = $personalization;
	}

	private function request(){
		$curl = curl_init();
		$message_body = json_encode($this->payload_data);
		$headers = array(
			'Authorization: Bearer ' . $this->api_key,
			'Content-Type: application/json'
		);
		curl_setopt($curl, CURLOPT_URL, $this->api_endpoint);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($curl, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $message_body);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		curl_setopt($curl, CURLOPT_HEADER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($curl);
		$header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
		$this->response_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$this->response_headers = substr($response, 0, $header_size);
		$this->response = substr($response, $header_size);
	}

	private function error_handler(){
		$request_error = $this->get_response();
		$headers = $this->get_headers();

		$error_list = array(
			'error_code' => $this->response_code,
			'error_header' => $headers[1],
		);
		foreach($request_error->errors as $index => $error_info){
			$error_data['message'] = 'Error: "' . $error_info->message . '"';
			if(!empty($error_info->field)){
				$error_data['message'] .= '" at field "' . $error_info->field . '"';
			}
			if(!empty($error_info->help)){
				$error_data['message'] .= ".\r Get help:". $error_info->help . '"';
			}

			$error_data['raw'] = $this->std_to_array($error_info);
			$error_list['errors'][] = $error_data;
			$error_list['error_list'][] = $error_data['message'];
		}

		$this->errors = $error_list;
	}
}
