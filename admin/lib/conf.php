<?php
	define('DBHOST','localhost');
	define('DBNAME','rest');
	define('DBUSER','root');
	define('DBPASS','root');
	
	define('SALT','api');	/*密码盐*/
	
	define('EMAIL','mail1395516797@163.com');	/*管理员邮箱*/
	define('EMAIL_HOST','smtp.163.com');	/*邮箱服务器*/
	define('EMAIL_PASS','hjs139551');	/*密码*/
	
	define('SMS','sms1395516797');	/*sms 账号*/
	define('SMS_PASS','6924a0eff79dc5fbdee221603df64e89');	/*密码*/
	define('SMS_MODEL','100003');	/*sms 模板*/
	define('SMS_PRODUCT','云端账号');	/*sms 产品*/
	
	define('EXPIRE_TIME',60*60);	/*token 过期时间*/
?>