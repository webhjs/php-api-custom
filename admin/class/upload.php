<?php
class upload{
	public $message;
	public $code;
	private $_allow_type = [
		"image/jpeg",
		"image/png",
		"image/gif"
	];
	private $_file_name;
	private $_file_err;
	private $_file_tmp;
	private $_file_type;
	private $_file_size;

	const URL = __DIR__.'/../uploads';
	
	public function __construct($name=''){
		@$this->_file_name = $_FILES[$name]['name'];
		@$this->_file_err = $_FILES[$name]['error'];
		@$this->_file_tmp = $_FILES[$name]['tmp_name'];
		@$this->_file_type = $_FILES[$name]['type'];
		@$this->_file_size = $_FILES[$name]['size'];
	}
	
	/*检验文件规格*/
	public function run(){
		if(empty($this->_file_name)){
			$this->message = '上传文件不能为空';
			$this->code = 405;
			return;
		}

		$this->vali_error($this->_file_err);
		$this->rule_type($this->_file_type,$this->_allow_type);
		$this->rule_size($this->_file_size);
		$this->dir_power();
		return $this->upload($this->_file_tmp,$this->_file_name);
	}
	
	/*上传中途错误*/
	private function vali_error($err){
		if($err > 0){
			switch($_FILES['userfile']['error']>0){
				case 1: 
					$this->message = '超过php.ini规定值';
					$this->code = 405;
					break;
				case 2: 
					$this->message = '超过了HTML中"MAX_FILE_SIZE规定值';
					$this->code = 405;
					break;
				case 3: 
					$this->message = '文件只被上传了部分';
					$this->code = 405;
					break;
				case 4: 
					$this->message = '没有上传文件';
					$this->code = 405;
					break;
				default : 
					$this->message = '未知错误';
					$this->code = 405;
			}
			return;
		}
	}
   
	/*规定上传类型*/
	private function rule_type($filetype,$allowtype){
		if(is_array($allowtype)){
			if(!in_array($filetype,$allowtype)){
				$this->message = '不是允许上传的格式';
				$this->code = 405;
				return;
			}
		}
	}
	
	/*规定大小*/
	const MAX_SIZE = 2000000;
	private function rule_size($size){
		if($size > upload::MAX_SIZE){
			$this->message = '文件超过额定大小'.upload::MAX_SIZE;
			$this->code = 405;
			return;
		}
	}
	
	//判读目录是否存在以及写入权限
	private function dir_power(){
		if(!is_dir(upload::URL)){
			mkdir(upload::URL,666);
		}
		if(!is_writable(upload::URL)){
			$this->message = '上传目录不可写';
			$this->code = 405;
			return;
		}
	}
	
	/*上传文件*/
	public function upload($tmp,$name){
		if(is_uploaded_file($tmp)){
			$dir = date('Ymd',time());
			if(!is_dir(upload::URL.'/'.$dir)){
				mkdir(upload::URL.'/'.$dir,666);
			}
         $id=uniqid ( rand (), true );
			if(!move_uploaded_file($tmp,upload::URL.'/'.$dir.'/'.$id.'_'.$name)){
				$this->message = '临时文件移动失败';
				$this->code = 405;
				return;
			}
		}else{
			$this->message = '临时文件不存在';
			$this->code = 405;
			return;
		}
		return '/'.$dir.'/'.$id.'_'.$name;
	}
   
   /*base64上传文件*/
   const MAX_BASE = 30*1024;
	public function pubbaseimg($base64_image_content=''){
      if(empty($base64_image_content)){
			$this->message = '上传文件不能为空';
			$this->code = 405;
			return;
		}
      $file =  mt_rand(1,9);
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
         $type = $result[2];
         $dir = date('Ymd',time());
         $id=uniqid ( rand (), true );
         $new_file = upload::URL.'/'.$dir.'/'.$id.'_'.$file.'.'.$type;
         
         /*base64文件格式*/
         $tok = strtok($result[0],':');
         $tok = strtok(';'); /*$str不用在加了，指针已经下调*/
         if(is_array($this->_allow_type)){
            if(!in_array($tok,$this->_allow_type)){
               $this->message = '不是允许上传的格式';
               $this->code = 405;
               return;
            }
         }
         
         /*base64上传目录*/
         if(!is_dir(upload::URL)){
            mkdir(upload::URL,666);
         }
         if(!is_writable(upload::URL)){
            $this->message = '上传目录不可写';
            $this->code = 405;
            return;
         }
         if(!is_dir(upload::URL.'/'.$dir)){
            mkdir(upload::URL.'/'.$dir,666);
         }
         /*base64文件*/
         if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
            /*base64文件尺寸*/
            if(filesize($new_file) > upload::MAX_BASE){
               unlink($new_file); /*删除临时文件*/
               $this->message = '临时文件超过额定大小'.upload::MAX_BASE;
               $this->code = 405;
               return;
            }
            return '/'.$dir.'/'.$id.'_'.$file.'.'.$type;
         }
         $this->message = '临时文件移动失败';
         $this->code = 405;
         return;
      }else{
         $this->message = 'base64数据不存在';
         $this->code = 405;
         return;
      }
	}
   
	
	/*删除文件*/
	public static function delimg($img){
      if(is_array($img)){
         foreach($img as $key => $value){
            if(file_exists(upload::URL.$value)){
               unlink(upload::URL.$value);
            }
         }
      }else{
         if(file_exists(upload::URL.$img)){
            unlink(upload::URL.$img);
         }
      }
	}
}
?>