<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2021/11/16
 * Time: 11:34 AM
 * Brief:
 */

define ( 'ROOT_PATH', dirname ( __FILE__ ) );
ini_set ( "memory_limit", "-1" );

require_once "./lib/Util.php";
require_once "./lib/initOmsInfo.php";
require_once "./mongo/mongoBaseDao.php";

class mongoTunnelRestore {

    function run() {
        $current_time = date("Ymd_Hsm");
        $tunnelDocumnet = file_get_contents(ROOT_PATH."/tunnel-col.json",true);
        $tunnelDocumnet = json_decode($tunnelDocumnet,true);
        foreach ($tunnelDocumnet as $tunnelInfo) {
            unset($tunnelInfo['_id']);
//            $ret = mongoBaseDao::mongoInsert('sotn.tunnel-col',mongoBaseDao::mongoUser30,mongoBaseDao::mongoPasswd30,
//                mongoBaseDao::mongoIp30,$tunnelInfo,array());
            $ret = mongoBaseDao::mongoInsert('sotn.tunnel-col',mongoBaseDao::mongoUser,mongoBaseDao::mongoPasswd,
                mongoBaseDao::mongoIp,$tunnelInfo,array());
            if ($ret != 1) {
                echo "=============== add failed tunnel ============".PHP_EOL;
                echo 'add failed phy link is :'.$tunnelDocumnet."\n";
                echo "=============== add failed tunnel ============".PHP_EOL;
                file_put_contents(ROOT_PATH."/log/phy_link_process.$current_time.log",json_encode($tunnelDocumnet)."\n",FILE_APPEND);
                echo "\n";
            }else {
                echo "=============== add success tunnel ============".$tunnelInfo['tunnelId'].PHP_EOL;
            }
        }
    }
}

$mongoRestore = new mongoTunnelRestore();
$mongoRestore->run();

