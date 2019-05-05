<?php
/*文章类接口*/
class products extends common {
	private $_data;
	private $_token;
	
	public function __construct(){
		@$this->_data = $this->getbody();
		@$this->_token = strip_tags(trim($this->_data['token']));
		@$this->_title = strip_tags(trim($this->_data['title']));
		@$this->_content = strip_tags(trim($this->_data['content']));
		@$this->_remark = strip_tags(trim($this->_data['remark']));
		@$this->_id = $this->_data["productid"];
		@$this->_base = strip_tags(trim($this->_data['base']));
		@$this->_image = $this->_data['image'];
		@$this->_edit = $this->_data['edit'];
		@$this->_page = strip_tags(trim($this->_data['page']));
		@$this->_limit = strip_tags(trim($this->_data['limit']));
		@$this->_like = strip_tags(trim($this->_data['like']));
	}

	public function pubproduct(){
		if($this->getmethod() != 'POST' && $this->getmethod() != 'OPTIONS'){
			echo $this->json('method error',405);
			return;
		}
		if(!$return = $this->tokenvalid($this->_token)){
			echo $this->json('token validata fail',Errors::TOKEN_FAIL);
			return;
		}
		if(empty($this->_title)){
			echo $this->json('title not null',Errors::TITLE_NOT_NULL);
			return;
		}
		if(empty($this->_content)){
			echo $this->json('content not null',Errors::CONTENT_NOT_NULL);
			return;
		}
		
		include_once(__DIR__.'/../class/upload.php');
		$upload = new upload('file');
      if(!empty($_FILES)){
         if(!$imageurl = $upload -> run()){
            echo $this->json($upload->message,$upload->code);
            return;
         };		//上传类返回存储路径
		}else{
         if(!$imageurl = $upload -> pubbaseimg($this->_base)){
            echo $this->json($upload->message,$upload->code);
            return;
         };
      }
		//$this->db()-> beginTransaction();//开启事务
		$moder = new product($this->db());
		if( !$return = $moder->addproduct($imageurl,$this->_title,$this->_content,$return['id'],$this->_remark) ){
			echo $this->json($moder->message,$moder->code);
			$upload->delimg($imageurl);
			//$this->db()->rollBack();
			return;
		}
		if(!$this->updatatime($this->_token)){
			echo $this->json('token update time fail',Errors::TOKEN_UPTIME_FAIL);
			//$this->db()->rollBack();//回滚
			return;
		}
		//$this->db()->commit();//提交
		
		echo json_encode([
			"message" => "添加产品成功",
			"code" => 0,
			"productId" => $return,
		]);
	}
	
	public function delproduct(){
		if($this->getmethod() != 'POST'){
			echo $this->json('method error',405);
			return;
		}
		if(!$return = $this->tokenvalid($this->_token)){
			echo $this->json('token validata fail',Errors::TOKEN_FAIL);
			return;
		}
		if(empty($this->_id)){
			echo $this->json('productid not found',Errors::PRODUCTID_NOT_NULL);
			return;
		}
		$moder = new product($this->db());
		if(!$moder->delproduct($this->_id)){
			echo $this->json($moder->message,$moder->code);
			return;
		}
		include_once(__DIR__.'/../class/upload.php');
		upload::delimg($this->_image);
		if(!$this->updatatime($this->_token)){
			echo $this->json('token update time fail',Errors::TOKEN_UPTIME_FAIL);
			return;
		}
		echo json_encode([
			"message" => "删除产品成功",
			"code" => 0,
		]);
	}
	
	public function updproduct(){
		if($this->getmethod() != 'POST'){
			echo $this->json('method error',405);
			return;
		}
		if(empty($this->_edit)){
			echo $this->json('query not null',405);
			return;
		}
		if(!$return = $this->tokenvalid($this->_token)){
			echo $this->json('token validata fail',Errors::TOKEN_FAIL);
			return;
		}
      include_once(__DIR__.'/../class/upload.php');
      $upload = new upload('file');
      $editArr = [];
      $baseIndex = null;
      while(!!list($key,$value) =each($this->_edit)){
         if(!empty($value['base'])){
            if(!$imageurl = $upload -> pubbaseimg($value['base'])){
               echo $this->json($upload->message,$upload->code);
               return;
            }
            upload::delimg($value['image']);
            unset($value['base']);
            $value['image']=$imageurl;
         }
         $editArr[$key] = $value;
      }
		$moder=new product($this->db());
		if(!$moder->updproduct($editArr)){
			echo $this->json($moder->message,$moder->code);
			return;
		}
		if(!$this->updatatime($this->_token)){
			echo $this->json('token update time fail',Errors::TOKEN_UPTIME_FAIL);
			return;
		}
		echo json_encode([
			"message" => "更新产品成功",
			"code" => 0,
		]);
	}
	
	public function productlist(){
		if($this->getmethod() != 'GET'){
			echo $this->json('method error',405);
			return;
		}
		if(empty($this->_page)){
			echo $this->json('page not null',Errors::PAGE_NOT_NULL);
			return;
		}
      if(empty($this->_limit)){
			echo $this->json('page limit not null',Errors::PAGE_LIMIT_NOT_NULL);
			return;
		}
		$moder=new product($this->db());
		if(!$data = $moder->productlist($this->_like,$this->_page,$this->_limit)){
			echo $this->json($moder->message,$moder->code);
			return;
		}
      if($data['totalpage'] == 0){
         echo $this->json('serach resource not exist',Errors::SERACH_SOURCE_NOT_EXIST);
			return;
      }
		echo json_encode([
			"message" => "产品列表获取成功",
			"code" => 0,
			"data"=>$data
		]);
	}
}
?>