<?php

/**
 * WP_Mail
 *
 * A simple class for creating and
 * sending Emails using WordPress
 *
 * @author     AnthonyBudd <anthonybudd94@gmail.com>
 */
Class WP_Mail{

	private $to 			 = array();
	private $cc 			 = array();
	private $bcc 			 = array();
	private $headers 		 = array();
	private $attachments 	 = array();
	private $sendAsHTML 	 = TRUE;
	private $subject 		 = '';
	private $from 			 = '';

	private $beforeTemplate  = FALSE;
	private $beforeVariables = array();
	private $template 		 = FALSE;
	private $variables 		 = array();
	private $afterTemplate   = FALSE;
	private $afterVariables  = array();


	public function __construct(){}

	public static function init(){
		return new Self;
	}


	/**
	 * Set recipients
	 * @param  Array|String $to
	 * @return Object $this
	 */
	public function to($to){
		if(is_array($to)){
			$this->to = $to;
		}else{
			$this->to = array($to);
		}
		return $this;
	}


	/**
	 * Get recipients
	 * @return Array $to
	 */
	public function getTo(){
		return $this->to;
	}


	/**
	 * Set Cc recipients
	 * @param  String|Array $cc
	 * @return Object $this
	 */
	public function cc($cc){
		if(is_array($cc)){
			$this->cc = $cc;
		}else{
			$this->cc = array($cc);
		}
		return $this;
	}


	/**
	 * Get Cc recipients
	 * @return Array $cc
	 */
	public function getCc(){
		return $this->cc;
	}


	/**
	 * Set Email Bcc recipients
	 * @param  String|Array $bcc
	 * @return Object $this
	 */
	public function bcc($bcc){
		if(is_array($bcc)){
			$this->bcc = $bcc;
		}else{
			$this->bcc = array($bcc);
		}

		return $this;
	}


	/**
	 * Set email Bcc recipients
	 * @return Array $bcc
	 */
	public function getBcc(){
		return $this->bcc;
	}


	/**	
	 * Set email Subject
	 * @param  Srting $subject
	 * @return Object $this
	 */
	public function subject($subject){
		$this->subject = $subject;
		return $this;
	}


	/**
	 * Retruns email subject
	 * @return Array
	 */
	public function getSubject(){
		return $this->subject;
	}


	/**
	 * Set From header
	 * @param  String
	 * @return Object $this
	 */
	public function from($from){
		$this->from = $from;
		return $this;
	}

	/**	
	 * Set the email's headers
	 * @param  String|Array  $headers [description]
	 * @return Object $this
	 */
	public function headers($headers){
		if(is_array($headers)){
			$this->headers = $headers;
		}else{
			$this->headers = array($headers);
		}
		
		return $this;
	}


	/**
	 * Retruns headers
	 * @return Array
	 */
	public function getHeaders(){
		return $this->headers;
	}


	/**
	 * Returns email content type
	 * @return String
	 */
	public function HTMLFilter(){
		return 'text/html';
	}


	/**	
	 * Set email content type
	 * @param  Bool $html
	 * @return Object $this
	 */
	public function sendAsHTML($html){
		$this->sendAsHTML = $html;
		return $this;
	}


	/**	
	 * Attach a file or array of files.
	 * Filepaths must be absolute.
	 * @param  String|Array $path 
	 * @throws Exception
	 * @return Object $this
	 */
	public function attach($path){
		if(is_array($path)){
			$this->attachments = array();
			foreach($path as $path_) {
				if(!file_exists($path_)){
					throw new Exception("Attachment not found at $path");
				}else{
					$this->attachments[] = $path_;
				}
			}
		}else{
			if(!file_exists($path)){
				throw new Exception("Attachment not found at $path");
			}
			$this->attachments = array($path);
		}

		return $this;
	}


	/**
	 * Set the before-template file
	 * @param  string $template  Path to HTML template
	 * @param  array  $variables
	 * @throws Exception
	 * @return Object $this
	 */
	public function beforeTemplate($template, $variables = NULL){
		if(!file_exists($template)){
			throw new Exception('Template file not found');
		}

		if(is_array($variables)){ 
			$this->beforeVariables = $variables;
		}

		$this->beforeTemplate = $template;
		return $this;
	}


	/**
	 * Set the template file
	 * @param  string $template  Path to HTML template
	 * @param  array  $variables
	 * @throws Exception
	 * @return Object $this
	 */
	public function template($template, $variables = NULL){
		if(!file_exists($template)){
			throw new Exception('File not found');
		}

		if(is_array($variables)){ 
			$this->variables = $variables;
		}

		$this->template = $template;
		return $this;
	}


	/**
	 * Set the after-template file
	 * @param  string $template  Path to HTML template
	 * @param  array  $variables
	 * @throws Exception
	 * @return Object $this
	 */
	public function afterTemplate($template, $variables = NULL){
		if(!file_exists($template)){
			throw new Exception('Template file not found');
		}

		if(is_array($variables)){ 
			$this->afterVariables = $variables;
		}

		$this->afterTemplate = $template;
		return $this;
	}


	/**
	 * Renders the template
	 * @return string
	 */
	public function render(){
		return $this->renderPart('before') . 
			$this->renderPart('main') .
			$this->renderPart('after');
	}
	

	/**
	 * Render a specific part of the email
	 * @author Anthony Budd
	 * @param  string $part before, after, main
	 * @return string 
	 */
	public function renderPart($part = 'main'){
		switch($part){
			case 'before':
				$templateFile = $this->beforeTemplate;
				$variables    = $this->beforeVariables;
				break;

			case 'after':
				$templateFile = $this->afterTemplate;
				$variables    = $this->afterVariables;
				break;
			
			case 'main':
			default:
				$templateFile = $this->template;
				$variables    = $this->variables;
				break;
		}

		if($templateFile === FALSE){
			return '';
		}

		$template = file_get_contents($templateFile);

		if(!is_array($variables) || empty($variables)){
			return $template;
		}

		preg_match_all('/\{\{\s*.+?\s*\}\}/', $template, $matches);
		foreach($matches[0] as $match){
			$var = str_replace('{', '', str_replace('}', '', preg_replace('/\s+/', '', $match)));

			if(isset($variables[$var])){
				$template = str_replace($match, $variables[$var], $template);
			}
		}

		return $template;
	}


	/**
	 * Builds Email Headers
	 * @return string email headers
	 */
	private function buildHeaders(){
		$headers = '';

		$headers .= implode("\r\n", $this->headers) ."\r\n";

		foreach($this->bcc as $bcc){
			$headers .= sprintf("Bcc: %s \r\n", $bcc);
		}

		foreach($this->cc as $cc){
			$headers .= sprintf("Cc: %s \r\n", $cc);
		}

		if(!empty($this->from)){
			$headers .= sprintf("From: %s \r\n", $this->from);
		}

		return $headers;
	}


	/**
	 * Set the wp_mail_content_type filter, if necessary 
	 */
	private function beforeSend(){
		if(count($this->to) === 0){
			throw new Exception('You must set at least 1 recipient');
		}

		if(empty($this->template)){
			throw new Exception('You must set a template');
		}

		if($this->sendAsHTML){
			add_filter('wp_mail_content_type', array($this, 'HTMLFilter'));
		}
	}		


	/**
	 * Sends a rendered email using
	 * WordPress's wp_mail() function
	 * @return bool
	 */
	public function send(){
		$this->beforeSend();
		return wp_mail($this->to, $this->subject, $this->render(), $this->buildHeaders(), $this->attachments);
	}
}