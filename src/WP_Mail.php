<?php

/**
 * WP_Mail
 *
 * A simple class for creating and
 * sending Emails using WordPress
 *
 * @author     AnthonyBudd <anthonybudd94@gmail.com>
 */
Class WP_Mail
{

	private $to = [];
	private $cc = [];
	private $bcc = [];
	private $subject = '';
	private $headers = [];
	private $attachments = [];

	private $variables = [];
	private $template = FALSE;

	private $sendAsHTML = TRUE;


	public function __construct(){

	}

	public static function HTMLFilter(){
		return 'text/html';
	}

	public function sendAsHTML($html){
		if(is_bool($html)){
			$this->sendAsHTML = $html;
		}
		return $this;
	}

	public function to($to){
		$this->to = $to;
		return $this;
	}

	public function getTo(){
		return $this->to;
	}

	public function cc($cc){
		if(is_array($cc)){
			$this->cc = $cc;
		}else{
			$this->cc = [$cc];
		}
		return $this;
	}

	public function getCc(){
		return $this->cc;
	}

	public function bcc($bcc){
		if(is_array($bcc)){
			$this->bcc = $bcc;
		}else{
			$this->bcc = [$bcc];
		}

		return $this;
	}

	public function getBcc(){
		return $this->bcc;
	}

	public function subject($subject){
		$this->subject = $subject;
		return $this;
	}

	public function getSubject(){
		return $this->subject;
	}

	public function headers(Array $headers){
		$this->headers = $headers;
		return $this;
	}

	public function addHeader($header){
		$this->headers[] = $header;
		return $this;
	}

	public function getHeaders(){
		return $this->headers;
	}

	public function attachFile($path){
		if(!file_exists($path)){
			throw new Exception('Attachment not found');
		}

		$this->attachments[] = $path;
		return $this;
	}

	public function setTemplate($template, $variables = NULL){
		if(is_array($variables)){
			$this->setVariables($variables);
		}

		$this->template = $template;
		return $this;
	}

	public function setTemplatePath($templatePath, $variables = NULL){
		if(!file_exists($templatePath)){
			throw new Exception('File not found');
		}

		if(is_array($variables)){
			$this->setVariables($variables);
		}

		$this->setTemplate(file_get_contents($templatePath));
		return $this;
	}

	public function setVariables(Array $variables){
		if(!is_array($variables)){ 
			throw new Exception('$variables must be an assoc array');
		}

		$this->variables = $variables;
		return $this;
	}

	private function render(){
		$template = $this->template;

		preg_match_all('/\{\{\s*.+?\s*\}\}/', $template, $matches);
		foreach($matches[0] as $match){
			$var = str_replace('{', '', str_replace('}', '', preg_replace('/\s+/', '', $match)));

			if(isset($this->variables[$var])){
				$template = str_replace($match, $this->variables[$var], $template);
			}
		}

		return $template;
	}

	private function buildHeaders(){
		$headers = implode("\r\n", $this->headers) ."\r\n";

		foreach($this->bcc as $bcc){
			$headers .= sprintf("Bcc: %s \r\n", $bcc);
		}

		foreach($this->cc as $cc){
			$headers .= sprintf("Cc: %s \r\n", $cc);
		}

		return $headers;
	}

	private function beforeSend(){
		if($this->sendAsHTML){
			add_filter('wp_mail_content_type', ['WP_Mail', 'HTMLFilter']);
		}
	}		

	public function send(){
		$this->beforeSend();
		return wp_mail($this->to, $this->subject, $this->render(), $this->buildHeaders(), $this->attachments);
	}
}


$email = (new WP_Mail)
	->to('anthonybudd94@gmail.com')
	->cc([
		'anthonybudd94@gmail.com',
		'anthonybudd94@gmail.com',
	])
	->bcc('anthonybudd94@gmail.com')
	->subject('test')
	->headers([
		'From: Me Myself <me@example.net>',
		'Cc: John Q Codex <jqc@wordpress.org>',
	])
	->setTemplatePath('/Users/anthonybudd/Development/WP_Email/src/email.html', [
		'name' => 'Anthony Budd',
		'age' => '22',
	])
	->send();


