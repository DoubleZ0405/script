<?php

/**
 * @param $params
 * @return mixed
 * // �����ʼ�����
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
// ��ȡ�ɹ���ʧ�ܵ���־
$successLog = file_get_contents('./DelVideoInfo.log');
$errorLog = '';

if (empty($successLog) && empty($errorLog)) {
    exit;
}

//$successLog = iconv("utf-8", "gb2312",$successLog);
//$errorLog = iconv("utf-8", "gb2312",'edu_college ���ݱ�����ʧ�ܣ�');
//$errorLog = str_replace("\n","<br/>",$errorLog);
// ��װ����
$message = "<font color='red'>���гɹ�������־:</font><br>" . (empty($successLog) ? '<font color="red">��������!</font>' : $successLog) .
"<br>" . "<font color='red'>����ʧ��������־:</font><br>" . (empty($errorLog) ? '<font color="red">��������!</font>' : $errorLog);

$params = array (
    'to' => 'zhouquanwei@baidu.com,wanghan@baidu.com,donglu01@baidu.com.kouyanbin@baidu.com,zhangguangxi@baidu.com,yangfujia@baidu.com,weixinxin@baidu.com',
    'from' => 'zhouquanwei@baidu.com',
    'subject' => "gb2312", "utf-8",'���Ŀ���Ƶ���Զ�ɾ����Ƶͨ��:'.'--' . date("Y-m-d H:i:s"),
    'message' => $message,
);

sendEmail($params);
// ɾ����־
system('rm -rf ./DelVideoInfo.log');

?>

