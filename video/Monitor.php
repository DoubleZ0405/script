<?php
/***************************************************************************
 *
 * Copyright (c) 2014 Baidu.com, Inc. All Rights Reserved
 *
 **************************************************************************/

/**
 * @author: zhouquanwei@baidu.com
 * Date: 2017/9/12
 * Time: 下午3:21
 * 视频下架监控系统
 */

/**
 * @param $params
 * @return mixed
 * // 发送邮件函数
 */
function sendEmail($params) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, 'http://cp01-breezedust.epc.baidu.com:8099/email.php');
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
    curl_setopt($curl, CURLOPT_TIMEOUT, 30);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:'));
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}
// 获取成功或失败的日志
$successLog = file_get_contents('./DelVideoInfo.log');
$errorLog = '';

if (empty($successLog) && empty($errorLog)) {
    exit;
}

//$successLog = iconv("utf-8", "gb2312",$successLog);
//$errorLog = iconv("utf-8", "gb2312",'edu_college 数据表备份失败！');
//$errorLog = str_replace("\n","<br/>",$errorLog);
// 组装数据
$message = "<font color='red'>运行成功数据日志:</font><br>" . (empty($successLog) ? '<font color="red">暂无数据!</font>' : $successLog) .
"<br>" . "<font color='red'>运行失败数据日志:</font><br>" . (empty($errorLog) ? '<font color="red">暂无数据!</font>' : $errorLog);

$params = array (
    'to' => 'zhouquanwei@baidu.com,wanghan@baidu.com,donglu01@baidu.com.kouyanbin@baidu.com,zhangguangxi@baidu.com,yangfujia@baidu.com,weixinxin@baidu.com',
    'from' => 'zhouquanwei@baidu.com',
    'subject' => "gb2312", "utf-8",'【文库视频】自动删除视频通报:'.'--' . date("Y-m-d H:i:s"),
    'message' => $message,
);

sendEmail($params);
// 删除日志
system('rm -rf ./DelVideoInfo.log');

?>

