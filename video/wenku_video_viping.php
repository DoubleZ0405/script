<?php

Bd_Init::init();
Bd_LayerProxy::init(Bd_Conf::getConf('/layerproxy/'));
Bd_Log::trace('begin');
ini_set("memory_limit","2058m");
$_dataVideo =  new Service_Data_Video();
$_dataVideoList = new Service_Data_VideoList();
$_dataCategory = new Service_Data_Category();
//获取数据库配置
$db_conf = Bd_Conf::getConf('/db/cluster');
$db_conf_r = $db_conf['video'];

$mysql_r['server_name'] = $db_conf_r['host'][0]['ip'];
$mysql_r['port'] = $db_conf_r['host'][0]['port'];
$mysql_r['username'] = $db_conf_r['username'];
$mysql_r['password'] = $db_conf_r['password'];
$mysql_r['database'] = $db_conf_r['default_db'];


$conn = mysqli_connect($mysql_r['server_name'], $mysql_r['username'], $mysql_r['password'], $mysql_r['database'], $mysql_r['port']);
if (false === $conn) {
    throw new Wenku_Error(Wenku_ErrorCodes::DB_CONNECTION_ERROR);
}
//org_id  每次推的机构新入库视频
$org_id = 3287749707;

$select_sql = "select hash as video_hash from video where org_id = ".$org_id;
$result = mysqli_query($conn, $select_sql);
$row = mysqli_fetch_assoc($result);
$video_hash = $row['video_hash'];
$video_url = "http://wenku.baidu.com/video/course/v/".$video_hash;

// 保证1s一个url推送 不然会失败
usleep(1000000);

//推送post封装
$post_params = array(
    'url' => $video_url,
    'channel' => 'wenku-video',
    'type' => 'ADD',
    'level' => 1,
);
$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, 'http://cp01-rdqa-dev400-liuhai.epc.baidu.com:8015/sns/api/ps/vipping');
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $post_params);
curl_setopt($curl, CURLOPT_TIMEOUT, 30);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Expect:'));
$output = curl_exec($curl);
curl_close($curl);
var_dump($output);






