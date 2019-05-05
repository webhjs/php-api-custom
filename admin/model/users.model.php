<?php
/*用户model*/
class user{
	public $message;
	public $code;
	private $_db;
	
	public function __construct($db){
		$this->_db = $db;
	}
	
	/*注册用户*/
	public function adduser($username,$password,$mail,$phone){
		$return = $this->_existuser($username);
		if(!empty($return)){
			$this->message = 'user exist';
			$this->code = Errors::USER_EXIST;
			return false;
		}
		try{
			$sql="insert into `user`(`name`,`password`,`mobile`,`email`,`creat_time`) values(:name,:pass,:phone,:mail,:addtime)";
			$addtime = date('Y-m-d H:i:s',time()+8*3600);
			$sm = $this->_db->prepare($sql);
			$password = $this->_md5($password);
			$sm->bindParam(':name',$username);
			$sm->bindParam(':pass',$password);
			$sm->bindParam(':mail',$mail);
			$sm->bindParam(':phone',$phone);
			$sm->bindParam(':addtime',$addtime);
			if(!$sm->execute()){
				$this->message = 'register fail';
				$this->code = Errors::REGISTER_FAIL;
				return false;
			}
			return $this->_db->lastInsertId();
		}catch(Exception $e){
			$this->message = $e->getMessage();
			$this->code = $e->getCode();
		}
	}
	
	/*用户登陆*/
	public function loginuser($username,$password){
		$return = $this->_existuser($username);
		if(empty($return)){
			$this->message = 'user no exist';
			$this->code = Errors::USER_NOT_EXIST;
			return false;
		}
		try{
			$sql="select * from `user` where `name`=? and `password`=?";
			$sm = $this->_db->prepare($sql);
			$password = $this->_md5($password);
			$sm->execute([$username,$password]);
			$data = $sm->fetch(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			$this->message = $e->getMessage();
			$this->code = $e->getCode();
		}
		if(empty($data)){
			$this->message = 'account password not match';
			$this->code = Errors::USER_OR_PASS_ERROR;
			return false;
		}
		
		try{
			$upsql="UPDATE `user` SET `update_time` =?,`token`=? WHERE `name` =?";
			$updatetime = date('Y-m-d H:i:s',time()+8*3600);
			$token = $this->settoken($username,$data['id']);
			$upsm = $this->_db->prepare($upsql);
			$upsm->execute([$updatetime,$token,$username]);
			return $token;
		}catch(Exception $e){
			$this->message = $e->getMessage();
			$this->code = $e->getCode();
		}
	}
	
   /*用户登出*/
	public function loginout($token){
		try{
			$upsql="UPDATE `user` SET `token` ='' WHERE `token` =?";
			$stn = $this->_db->prepare($upsql);
			$return = $stn->execute([$token]);
         if(!$return){
            $this->message = 'user login out fail';
            $this->code = 403;
            return false;
         }
         return $return;
		}catch(Exception $e){
			$this->message=$e->getMessage();
			$this->code=$e->getCode();
		}	
	}
   
   /*获取用户信息*/
	public function userinfo($token){
		try{
			$sql="select * from `user` where `token`=?";
			$stn = $this->_db->prepare($sql);
			$stn->execute([$token]);
			$return = $stn->fetch(PDO::FETCH_ASSOC);
         if(empty($return)){
            $this->message = 'user not login';
            $this->code = 403;
            return false;
         }
         return $return;
		}catch(Exception $e){
			$this->message=$e->getMessage();
			$this->code=$e->getCode();
		}	
	}
   
	/*盐加密密码*/
	private function _md5($pass){
		return md5($pass.SALT);
	}
   
	/*检查用户是否存在*/
	private function _existuser($username){
		try{
			$sql="select * from `user` where `name`=?";
			$stn = $this->_db->prepare($sql);
			$stn->execute([$username]);
			return $stn->fetch(PDO::FETCH_ASSOC);
		}catch(Exception $e){
			$this->message=$e->getMessage();
			$this->code=$e->getCode();
		}	
	}
   
	/*生成token*/
	private function settoken($username,$uid){
        $str = sha1(md5(uniqid(md5($username),true)));  //生成一个不会重复的字符串
        return $str;
    }
	
}
?>