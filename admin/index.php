<?php
   header('Access-Control-Allow-Origin: *');
   header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
   header('Access-Control-Allow-Methods: POST, GET, OPTIONS, DELETE, PUT, HEAD');
   header("content-type:text/html;charset=utf-8");
   
	require_once __DIR__.'/class/Errors.php';
	$url=explode('/',$_SERVER['QUERY_STRING']);

	$version = $url[1];
	$control = $url[2];
	$method = $url[3];
	
	$allow_vesion = ['api','1.0'];
	
	global $statusCode;
	$statusCode	= [
		200 => 'ok',
		204 => 'no content',
		100 => 'bad request',
		403 => 'forbidden',
		404 => 'no founds',
		405 => 'method not allowed',
		500 => 'server internal error',
	];
   
   if(preg_match("/.*\.\w+$/",$_SERVER['QUERY_STRING'])){
      if(!file_exists ( $_SERVER['QUERY_STRING'] )){
         sererr('访问文件不存在!',404);
      }
   }
   
	if (empty($version) || empty($method) || empty($method)){
		sererr('填写完整的接口!',405);
	}
	
	if(!in_array($version,$allow_vesion)){
		sererr('版本不支持!',405);
	}
	
	$confpath = __DIR__.'/lib/conf.php';
	include_once($confpath);
	
	$commonpath = __DIR__.'/common/common.php';
	include_once($commonpath);
	
	$filepath = __DIR__.'/control/'.$control.'.php';
	if(!file_exists ( $filepath)){
		sererr('未定义的类!',405);
	}
	
	include_once($filepath);
	if(!method_exists($control,$method)){
		sererr('未定义的方法!',405);
	}
	
	$modelpath = __DIR__.'/model/'.$control.'.model.php';
	if(file_exists ( $modelpath )){
		include_once($modelpath);
	}
	
	$con_pra = new $control;
	$con_pra -> $method();
	
	function sererr($message,$code){
		header('http/1.1 '.$code.' '.$GLOBALS['statusCode'][$code]);
		throw new Exception($message,$code);
	}
	
?>