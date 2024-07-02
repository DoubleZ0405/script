<?php
/***************************************************************************
 *
 * Copyright (c) 2012 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @author: zhouquanwei@baidu.com
 * Date: 2018/7/30
 * Time: 下午2:10
 * nani短信接口
 */

class Nani_Util_YdSms{

	private static $businessCode = 'game';
	private static $username = 'hanxuena';
	private static $key = 'hanxuena';
	private static $seg_charter = "";
	private static $send_sms_url = 'http://emsg.baidu.com/service/sendSms.json';

	public static function sendSms($mobile, $content){
		$content = iconv('GBK','UTF`-8',$content);
		$para_arr = array(
			'businessCode'=>self::$businessCode,
			'msgDest'=>$mobile,
			'msgContent'=>$content,
			'username' => self::$username,
		);

		$para_arr['signature'] = self::signature($para_arr);
		$post_data = '';
		foreach($para_arr as $k=>&$v){
			//$v = urlencode($v);
		}
		$post_data = http_build_query($para_arr);
		$url = self::$send_sms_url."?".$post_data;
		$ret = self::postCurl($url);
		return $ret;
	}

	public static function signature($para_arr){
		return md5($para_arr['username'].self::$key.$para_arr['msgDest'].($para_arr['msgContent']).$para_arr['businessCode']);
	}

	public static function postCurl($url){
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS,5000);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS,5000);
		$result = curl_exec($ch);
		if(curl_errno($ch))
		{
			$result =  curl_error($ch);
		}
		return $result;
	}

}


$dir = dirname(__FILE__);
$str = '';
//        $file = file_get_contents($dir.'/zhouquanwei_pc_base_wenku_view_pv_uv');
try {
	$file = fopen($dir . '/num_nani.txt', 'r');

	$i = 1;
	while ($line = fgets($file)) {
		echo "$i\n";
		$i++;
		$item = trim($line);
		$sms_content = iconv("utf-8", "gbk", "你好！百度Nani小视频邀请您参加有奖问卷调研：https://iwenjuan.baidu.com/?code=bckqae 。耽误您2分钟的宝贵时间，即有机会领取百度精美周边礼品一份！");
		$sms_ret = Nani_Util_YdSms::sendSms($item, $sms_content);
		$sms_ret = json_decode($sms_ret, true);
		file_put_contents($dir.'/log_ret.txt',json_encode($sms_ret)."\n",FILE_APPEND);
	}

	fclose($file);
}catch (Exception $exception) {
	echo$exception->getMessage();
}
//
echo 'done';
/* vim: set expandtab ts=4 sw=4 sts=4 tw=100: */
?>
