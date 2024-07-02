<?php

/*************************************************************************** 
 * 
 * Copyright (c) 2015 Baidu.com, Inc. All Rights Reserved
 * 
 **************************************************************************/ 

/** 
 * @author wanghan(wanghan@baidu.com)
 * @date 2015/08/04 17:45:48
 * @desc
 * @add zhouquanwei@baidu.com
 * 视频入库&删除自动化入口
 **/

Bd_Init::init();
Bd_LayerProxy::init(Bd_Conf::getConf('/layerproxy/'));

//获取数据库配置
$db_conf = Bd_Conf::getConf('/db/cluster');
$db_conf_r = $db_conf['evc_media'];

$mysql_r['server_name'] = $db_conf_r['host'][0]['ip'];
$mysql_r['port'] = $db_conf_r['host'][0]['port'];
$mysql_r['username'] = $db_conf_r['username'];
$mysql_r['password'] = $db_conf_r['password'];
$mysql_r['database'] = $db_conf_r['default_db'];

$log = './import_new_video.log';
$org_id = 883861220;

$id = file_get_contents('./new_video_id');
$id = trim($id);

$select_sql = "select count(*) as total from video where org_id=" . $org_id .";";
$result = mysqlQuery($mysql_r, $select_sql,$log);
$row = mysqli_fetch_assoc($result);
if (0 != count($row)) {
    $total = $row['total'];
} else {
    return;
}
while($id <= $total) {

    $start = $id-1;
    $select_sql = "select * from video where org_id=$org_id limit $start,1;";

    $result = mysqlQuery($mysql_r, $select_sql,$log,'gbk');
    $row = mysqli_fetch_assoc($result);

    if (0 != count($row) && $row['not_pull'] != 1) {

        // 增加删除通路by zhouquanwei@baidu.com
        if ($row['status'] == 0) {

            $command = array(
                'command_no'  => 1900123,
                'from_vid' => $row['pk_vid'],
                'hash' => $row['hash'],
                'node_layer' => 0,
                'title' => $row['name'],
                'enroll_mode' => '',
                'mode' => $row['mode'],
                'video_url' => $row['video_url'],
                'page_url' => $row['page_url'],
                'description' => $row['description'],
                'category' => $row['category'],
                'tag' => $row['tag'],
                'duration' => $row['duration'],
                'thumb_img' => $row['thumb_img'],
                'support' => $row['support'],
                'parent_id' => 0,
                'org_id' => $row['org_id'],
                'auth' => '',
                'create_time' => $row['createtime'],
                'update_time' => strtotime($row['updatetime']),
                'push_time' => 0,
                'is_hot' => 0,
                'comefrom' => 2,
                'status' => $row['status'],
            );
            $ret = Saf_Api_Server::call('Nmq', 'call', $command, null, null);

            $id = $id + 1;
            file_put_contents("./new_video_id", "$id");
            $stor_data_str = 'from_vid:'.$row['pk_vid']." ".'video_hash:'.$row['hash']." "
                .'title: '.$row['title']." "
                .'org_id: '.$row['org_id'];
            file_put_contents("./DelVideoInfo.log",$stor_data_str."\n",FILE_APPEND);
        }else {
            $command = array(
                'command_no'  => 1900100,
                'from_vid' => $row['pk_vid'],
                'hash' => $row['hash'],
                'node_layer' => 0,
                'title' => $row['name'],
                'enroll_mode' => '',
                'mode' => $row['mode'],
                'video_url' => $row['video_url'],
                'page_url' => $row['page_url'],
                'description' => $row['description'],
                'category' => $row['category'],
                'tag' => $row['tag'],
                'duration' => $row['duration'],
                'thumb_img' => $row['thumb_img'],
                'support' => $row['support'],
                'parent_id' => 0,
                'org_id' => $row['org_id'],
                'auth' => '',
                'create_time' => $row['createtime'],
                'update_time' => strtotime($row['updatetime']),
                'third_id' => $row['third_id'],
                'push_time' => 0,
                'is_hot' => 0,
                'comefrom' => 2,
                'status' => $row['status'],
            );
            $ret = Saf_Api_Server::call('Nmq', 'call', $command, null, null);
            $id = $id + 1;
            file_put_contents("./new_video_id", "$id");
        }
    } else {
        $id = $id + 1;
        file_put_contents("./new_video_id", "$id");
        continue;
    }
}

//触发邮件系统
system('cd /home/iknow/odp/app/video/script/ && /home/iknow/odp/php/bin/php Monitor.php');

/**
 * @brief complete
 * @param request
 * @return array
 */
function mysqlQuery($db,$sql,$log,$name='gbk') {
    try {
        $conn = mysqli_connect($db['server_name'], $db['username'], $db['password'], $db['database'], $db['port']);
        mysqli_set_charset($conn,$name);
        $result = mysqli_query($conn, $sql);
        mysqli_close($conn);
    } catch (Exception $e) {
        file_put_contents($log, "sql[" . $sql . "] errmsg[db error]\n",FILE_APPEND);
    }
    return $result;
}

