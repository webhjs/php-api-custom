<?php
/*用户类接口*/
class test extends common {
	
	private $_data;
   
   public static $nm ;

   static function nmMethod(){
      self::$nm += 1;
      echo self::$nm;
   }
	
   
	/*注册用户*/
	public function index(){
      test::nmMethod();
	}
	
}
?>