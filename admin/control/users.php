<?php
/*用户类接口*/
class users extends common {
	
	private $_data;
	private $_name;
	private $_password;
	private $_phone;
	private $_mail;
	private $_code;
	
	public function __construct(){
		@$this->_data = $this->getbody();
		@$this->_name = strip_tags(trim($this->_data['name']));
		@$this->_password = strip_tags(trim($this->_data['password']));
		@$this->_phone = strip_tags(trim($this->_data['phone']));
		@$this->_mail = strip_tags(trim($this->_data['mail']));
		@$this->_code = strip_tags(trim($this->_data['code']));
		@$this->_token = strip_tags(trim($this->_data['token']));
	}
	
	/*注册用户*/
	public function register(){
		if($this->getmethod() != 'POST'){
			echo $this->json('method error',405);
			return;
		}
		if(empty($this->_name)){
			echo $this->json('account not null',Errors::ACCOUNT_NOT_NULL);
			return;
		}
		if(empty($this->_password)){
			echo $this->json('pass not null',Errors::PASS_NOT_NULL);
			return;
		}
		if(empty($this->_phone) || empty($this->_mail)){
			echo $this->json('phone or mail not null',Errors::PHONE_MAIL_NOT_NULL);
			return;
		}
		if(!preg_match("/^1[34578]{1}\d{9}$/",$this->_phone)){
			echo $this->json('phone format error',Errors::PHONE_FORMAT_ERROR);
			return;
		}
		if(!filter_var($this->_mail, FILTER_VALIDATE_EMAIL)){
			echo $this->json('mail format error',Errors::MAIL_FORMAT_ERROR);
			return;
		}
		if(empty($this->_code)){
			echo $this->json('vali code not null',Errors::CODE_NOT_NULL);
			return;
		}
		
		
		session_start();
		if(!isset($_SESSION['validate']) || $_SESSION['validate'] != $this->_code){
			echo $this->json('validate code error',Errors::CODE_ERROR);
			return;
		}
		unset($_SESSION['validate']);
		
		$model = new user($this->db());
		if($lastid = $model->adduser($this->_name,$this->_password,$this->_mail,$this->_phone)){
			echo json_encode([
				'code' => 0,
				'message' => 'register success',
				'lastid' => $lastid 
			]);
		}else{
			$this->json($model->message,$model->code);
		}
	}
	
	/*用户登陆*/
	public function login(){
		if($this->getmethod() != 'POST'){
			echo $this->json('method error',405);
			return;
		}
		if(empty($this->_name)){
			echo $this->json('account not null',Errors::ACCOUNT_NOT_NULL);
			return;
		}
		if(empty($this->_password)){
			echo $this->json('password not null',Errors::PASS_NOT_NULL);
			return;
		}
		$model = new user($this->db());
		if($token = $model->loginuser($this->_name,$this->_password)){
			echo json_encode([
				'code' => 0,
				'message' => 'login success',
				'token' => $token 
			]);
		}else{
			$this->json($model->message,$model->code);
		}
	}
   
   /*用户退出*/
	public function loginout(){
		if($this->getmethod() != 'GET'){
			echo $this->json('method error',405);
			return;
		}
		if(empty($this->_token)){
			echo $this->json('token not null',Errors::TOKEN_NOT_NULL);
			return;
		}
      if(!$return = $this->tokenvalid($this->_token)){
			echo $this->json('token validata fail',Errors::TOKEN_FAIL);
			return;
		}
		$model = new user($this->db());
		if($model->loginout($this->_token)){
			echo json_encode([
				'code' => 0,
				'message' => 'login out success'
			]);
		}else{
			$this->json($model->message,$model->code);
		}
	}
   
   /*获取用户信息*/
	public function getUserInfo(){
		if($this->getmethod() != 'GET'){
			echo $this->json('method error',405);
			return;
		}
		if(empty($this->_token)){
			echo $this->json('token not null',Errors::TOKEN_NOT_NULL);
			return;
		}
      if(!$return = $this->tokenvalid($this->_token)){
			echo $this->json('token validata fail',Errors::TOKEN_FAIL);
			return;
		}
		$model = new user($this->db());
		if($userinfo = $model->userinfo($this->_token)){
			echo json_encode([
				'code' => 0,
				'message' => 'get userinfo success',
				'userinfo' => $userinfo
			]);
		}else{
			$this->json($model->message,$model->code);
		}
	}
	
	/*发送sms*/
	public function sendsms(){
		if($this->getmethod() != 'POST'){
			echo $this->json('method error',Errors::METHOD_NOT_ALLOW);
			return;
		}
		if(empty($this->_phone)){
			echo $this->json('phone not null',Errors::PHONE_NOT_NULL);
			return;
		}
		if(!preg_match("/^1[34578]{1}\d{9}$/i",$this->_phone)){
			echo $this->json('phone format error',Errors::PHONE_FORMAT_ERROR);
			return;
		}
		try{
			include_once(__DIR__.'/../class/smsapi.class.php');
			$sms= new SmsApi();
			$code = $sms->randNumber();
			session_start();
			$_SESSION['validate'] = $code;
			$sms->send($this->_phone,['code' => $code,'product' => SMS_PRODUCT],SMS_MODEL);
			$this->json('send success',200);
			return true;
		}catch(Exception $e){
			$this->json($e->getMessage(),$e->getCode());
		}
	}
}
?>