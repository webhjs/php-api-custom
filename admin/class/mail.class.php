<?php
	require_once __DIR__.'/../vendor/autoload.php';
	require_once __DIR__.'/../lib/conf.php';
	use Nette\Mail\Message;
	use Nette\Mail\SmtpMailer;
	class Email{
		private $_mail;
		private $_title;
		private $_content;
		public function __construct($email,$title,$content){
			$this->_mail = $email;
			$this->_title = $title;
			$this->_content = $content;
		}
		public function sendmail(){
			$mail = new Message;
			try{
				$mail->setFrom(EMAIL)
					->addTo($this->_mail)
					->setSubject($this->_title)
					->setBody($this->_content);
				$mailer = new SmtpMailer([
					'host' => EMAIL_HOST,
					'username' => EMAIL,
					'password' => EMAIL_PASS,
					'secure' => 'ssl',
				]);
				$mailer->send($mail);
			} catch (Exception $e){
				header('http/1.1 '.$code.' '.$this->_statusCode[$code]);		
				header('Content-Type:Application/json;charset:utf-8');
				json_encode(['message'=>$e->getMessage(),'code'=>$e->getCode()]);
			}
		}
	}
?>