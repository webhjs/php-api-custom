<?php
	class common{
		/*��ȡ���������*/
		public function getbody(){
			//����ƥ����ύģʽ
			if(preg_match('/multipart\/form-data/i',$_SERVER['CONTENT_TYPE'])){
				return $_POST;
			}

         //����ƥ��json�ύģʽ
         if(preg_match('/application\/json/i',$_SERVER['CONTENT_TYPE'])){
            $jsondata = file_get_contents('php://input');
            return json_decode($jsondata,true);
         }
         
         //����ƥ��GET�ύģʽ
			if(strstr($_SERVER['REQUEST_URI'], '?')){
				$step = strpos($_SERVER['REQUEST_URI'],"?");
				$data = urldecode(substr($_SERVER['REQUEST_URI'],$step+1));
				return $this->_str($data);
			}
         //$data = urldecode(iconv("utf-8", "gb2312",file_get_contents('php://input')));
			$data = urldecode(file_get_contents('php://input'));		 //��ȡԭʼ������
         //����POST���ύ��ʽ
			if(strstr($data, '=')){
				return $this->_str($data);
			}
		}
		
		/*json������ʾ*/
		public function json($message,$code){
			/*if($code !== 200 && $code > 200){
				header('http/1.1 '.$code.' '.$this->_statusCode[$code]);				
			}*/
			header('Content-Type:application/json;charset:utf-8');
			if(!empty($message)){				
				echo json_encode(['message'=>$message,'code'=>$code]);
			}
			die;
		}
		
		/*�и�get������*/
		private function _str($data){
			$str = explode('&',$data);
			while(list($key,$value) = each($str)){
				$item = explode('=',$value);
				$array[$item[0]]= $item[1];
			}
			return $array;
		}
		
		/*��ȡ���󷽷�*/
		public function getmethod(){
			return $_SERVER['REQUEST_METHOD'];
		}
		
		/*�������ݿ�*/
		public function db(){
			$db = new PDO('mysql:host='.DBHOST.';dbname='.DBNAME,DBUSER,DBPASS);
			$db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);//��������Ϊ�쳣�׳�
			$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);//����Ԥ��������ģ�� ��ֹת��intΪstring
			return $db;
		}
		
		/*�ж�token*/
		public function tokenvalid($token){
			if(empty($token)){
				self::json('token not null',Errors::TOKEN_NOT_NULL);
				return false;
			}
			try{
				$sql="select `update_time`,`id` from `user` where `token` = ?";
				$sm = self::db()->prepare($sql);
				$sm->execute([$token]);
				$data = $sm->fetch(PDO::FETCH_ASSOC);
			}catch(Exception $e){
				self::json($e->getMessage(),$e->getCode());
			}	
			if(time()+8*3600 - strtotime($data['update_time']) > EXPIRE_TIME){
				self::json('token expired',Errors::TOKEN_EXPIRED);
				return false;
			}
			return $data;
		}
      
		/*����ʱ�����*/
		public function updatatime($token){
			try{
				$sql="UPDATE `user` SET `update_time`=? WHERE `token` = ?";
				$updatetime = date('Y-m-d H:i:s',time()+8*3600);
				$sm = self::db()->prepare($sql);
				return	$sm->execute([$updatetime,$token]);
			}catch(Exception $e){
				self::json($e->getMessage(),$e->getCode());
			}
		}
	}
?>