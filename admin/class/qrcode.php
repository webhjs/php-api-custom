<?php
$PATH = __DIR__.'/../lib/';
include_once($PATH."phpqrcode/phpqrcode.php");
include_once($PATH."wxpay/WxPay.Api.php");
include_once($PATH."wxpay/WxPay.Data.php");
include_once($PATH."wxpay/WxPay.Notify.php");
include_once($PATH."wxpay/WxPay.NativePay.php");
include_once($PATH."wxpay/WxPay.Config.php");

class Wx{
	public function rcode(){
		$input = new WxPayUnifiedOrder();
		$config = new WxPayConfig();
		$input->SetBody("test"); //订单name
		$input->SetAttach("test"); // 订单id
		$input->SetOut_trade_no($config->GetMerchantId().date("YmdHis")); //交易信息时间
		$input->SetTotal_fee("1"); //price
		$input->SetTime_start(date("YmdHis")); //开始时间
		$input->SetTime_expire(date("YmdHis", time() + 600)); //过期时间
		$input->SetGoods_tag("test"); //商品name
		$input->SetNotify_url("http://paysdk.weixin.qq.com/notify.php");
		$input->SetTrade_type("NATIVE");
		$input->SetProduct_id("123456789");

		$notify = new NativePay();
		$result = $notify->GetPayUrl($input);
		$url = $result["code_url"];
		
		if(substr($url, 0, 6) == "weixin"){
			QRcode::png($url);
		}else{
			header('HTTP/1.1 404 Not Found');
		}
	}
}
