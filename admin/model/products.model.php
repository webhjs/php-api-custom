<?php
/*用户model*/
class product{
	public $message;
	public $code;
	private $_db;
	
	public function __construct($db){
		$this->_db = $db;
	}
	
	public function addproduct($image,$title,$content,$uid,$remark){
		$sql = "insert into `product`(`image`,`title`,`content`,`user_id`,`creat_time`,`remark`) values(?,?,?,?,?,?);";
		$time = date('Y-m-d H:i:s',time()+8*3600);
		try{
			$sm = $this->_db->prepare($sql);
			$sm->execute([$image,$title,$content,$uid,$time,$remark]);
		}catch(Exception $e){
			$this->message = $e->getMessage();
			$this->code = $e->getCode();
			return;
		}
		return $this->_db->lastInsertId();
	}
	
   public function delproduct($idList){
      $id_Dele= implode(",",$idList);
		$delsql="delete from `product` where id in (${id_Dele});";
		try{
			$sm = $this->_db->prepare($delsql);
			$sm -> execute();
		}catch(Exception $e){
			$this->message = $e->getMessage();
			$this->code = $e->getCode();
			return;
		}
		return true;
	}
   
	public function updproduct($data){
      try{
         $this->_db->beginTransaction();
         $productid='';
         while(!!list($key,$value) =each($data)){
            $productid = $value['id'];
            $str = '';
            while(!!list($key2,$value2) =each($value)){
               $str .= "`".$key2."`"."='".$value2."',";
            }
            $str = substr($str,0,-1);
            $sql = "update `product` set ".$str." where `id`=?";
            $sm = $this->_db->prepare($sql);
            $sm -> execute([$productid]);
         }
         $this->_db->commit();
      }catch(Exception $e){
         $this->message = $e->getMessage();
         $this->code = $e->getCode();
         $this->_db->rollBack();
         return false;
      }
		return true;
	}
	
	public function productlist($like='',$page=1,$size=5){
		$sql= "select * from `product` where `title` like '%".$like."%';";
		$total = $this->_db->query($sql)->rowCount();
      if($total == 0){
         return [
            "totalpage" => $total
         ];
      }
		$pagenum=ceil($total/$size);
		if($page > $pagenum){
			$page = $pagenum;
		}else if($page < 1){
			$page = 1;
		}
		$listsql="select * from `product` where `title` like '%".$like."%' order by `creat_time` desc limit ?,?";
      $start = ($page-1)*$size;
		try{
			$sm = $this->_db->prepare($listsql);
			$sm -> execute([$start,$size]);
		}catch(Exception $e){
			$this->message = $e->getMessage();
			$this->code = $e->getCode();
			return;
		}
		return [
			"productlist" => $sm->fetchAll(PDO::FETCH_ASSOC),
			"totalpage" => $total,
			"pagenum" => $pagenum,
			"pageindex" => $page
		];
	}
}
?>