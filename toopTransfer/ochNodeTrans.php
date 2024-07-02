<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2021/11/9
 * Time: 4:13 PM
 * Brief:
 */
define ( 'ROOT_PATH', dirname ( __FILE__ ) );
ini_set ( "memory_limit", "-1" );

require_once "./lib/Util.php";
require_once "./lib/initOmsInfo.php";
require_once "./mongo/mongoBaseDao.php";

class ochNodeTrans {


    function run() {

        //华南1 滨海腾大--鹅埠
        $fileBHTd2EBS1 = "tunnel_list_bh2eb_s1.txt";
        if (!$this->ochNodeProcess($fileBHTd2EBS1,mongoBaseDao::mongoUser30,mongoBaseDao::mongoPasswd30,
            mongoBaseDao::mongoIp30,initOmsInfo::siteIdA30,
            initOmsInfo::siteIdB30,"",initOmsInfo::HUANAN1URL)) {
            exit(1);
        };
        echo "=============== 滨海腾大--鹅埠 S1 phyLink process done ===============".PHP_EOL;
        sleep(1);

        //华南1 腾大--鹅埠
        $fileTd2EbS1 = "tunnel_list_td2eb_s1.txt";
        if (!$this->ochNodeProcess($fileTd2EbS1,mongoBaseDao::mongoUser30,mongoBaseDao::mongoPasswd30,
            mongoBaseDao::mongoIp30,initOmsInfo::siteIdA30,
            "",initOmsInfo::siteIdC30,initOmsInfo::HUANAN1URL)) {
            exit(1);
        };
        echo "=============== 腾大--鹅埠 S1 phyLink process done ===============".PHP_EOL;
        sleep(1);

        //华南2 滨海腾大--鹅埠
        $fileBHTd2EBS2 = "tunnel_list_bh2eb_s2.txt";
        if (!$this->ochNodeProcess($fileBHTd2EBS2,mongoBaseDao::mongoUser35,mongoBaseDao::mongoPasswd35,
            mongoBaseDao::mongoIp35,initOmsInfo::siteIdA35,
            initOmsInfo::siteIdB35,"",initOmsInfo::HUANAN2URL)) {
            exit(1);
        };
        echo "=============== 滨海腾大--鹅埠 S2 phyLink process done ===============".PHP_EOL;
        sleep(1);

        //华南2 腾大--鹅埠
        $fileTd2EBS2 = "tunnel_list_td2eb_s2.txt";
        if (!$this->ochNodeProcess($fileTd2EBS2,mongoBaseDao::mongoUser35,mongoBaseDao::mongoPasswd35,
            mongoBaseDao::mongoIp35,initOmsInfo::siteIdA35,
            "",initOmsInfo::siteIdC35,initOmsInfo::HUANAN2URL)) {
            exit(1);
        };
        echo "=============== 腾大--鹅埠 S2 phyLink process done ===============".PHP_EOL;
    }

    /**
     * @param $filename
     * @param $mongoUser
     * @param $mongoPasswd
     * @param $mongoIp
     * @param $siteIdEb
     * @param $siteIdBhTd
     * @param $siteIdTd
     * @param $controllerUrl
     * @return bool
     * @throws \MongoDB\Driver\Exception\Exception
     */
    function ochNodeProcess($filename,$mongoUser,$mongoPasswd,$mongoIp,$siteIdEb,$siteIdBhTd,$siteIdTd,$controllerUrl) {
        $current_time = date("Ymd_Hsm");
        $dir = dirname(__FILE__);
        $file = fopen($dir.'/tunnel/'.$filename,'r');
        while ($tunnelId = fgets($file)) {
            $tunnelId = str_replace(PHP_EOL, '', $tunnelId);
            $phyNodeInfo = initOmsInfo::getPhyNodeInfo($controllerUrl, $tunnelId);
            $phyNodeList = $phyNodeInfo['output']['node'];
            try {
                for ($i=0; $i<count($phyNodeList); $i++) {
                    $nodeId = $phyNodeList[$i]['node-id'];
                    $filter = array(
                        'ochNodeId' => $nodeId
                    );
                    $cursor = mongoBaseDao::mongoDbQuery('sotn.och-node-col',$mongoUser,$mongoPasswd,
                        $mongoIp,$filter,array());
                    foreach ($cursor as $document) {
                        $oldLinkInfo = json_encode($document);
                        if ($siteIdEb !=null && $siteIdBhTd != null) {
                            $newLinkInfo = str_replace($siteIdEb, initOmsInfo::siteIdGdEb, $oldLinkInfo);
                            $newLinkInfo = str_replace($siteIdBhTd,initOmsInfo::siteIdGdTd,$newLinkInfo);
                            $ochNodeInfoNew = json_decode($newLinkInfo, true);
                        }else if ($siteIdEb !=null && $siteIdTd != null) {
                            $newLinkInfo = str_replace($siteIdEb, initOmsInfo::siteIdGdEb, $oldLinkInfo);
                            $newLinkInfo = str_replace($siteIdTd,initOmsInfo::siteIdGdDso,$newLinkInfo);
                            $ochNodeInfoNew = json_decode($newLinkInfo, true);
                        }
                    }
                    if ($ochNodeInfoNew == null && empty($ochNodeInfoNew)) {
                        continue;
                    }
                    usleep(500);
                    unset($ochNodeInfoNew['_id']);
                    $retFromLinkIn = mongoBaseDao::mongoInsert('sotn.och-node-col',mongoBaseDao::mongoUser,
                        mongoBaseDao::mongoPasswd,mongoBaseDao::mongoIp,$ochNodeInfoNew);
                    if ($retFromLinkIn != 1) {
                        echo "=============== add failed phy node ============".PHP_EOL;
                        echo 'add failed phy node is :'.$ochNodeInfoNew."\n";
                        echo "=============== add failed phy node ============".PHP_EOL;
                        file_put_contents(ROOT_PATH."/log/phy_node_process.$current_time.log",$ochNodeInfoNew."\n",FILE_APPEND);
                        return false;
                    }

                    usleep(100);
                    $retFromLinkDelS1 = mongoBaseDao::mongoDel('sotn.och-node-col',$mongoUser,$mongoPasswd,
                        $mongoIp,$filter,array());
                    if ($retFromLinkDelS1) {
                        echo "=============== del success ============".json_encode($filter).PHP_EOL;
                    }else{
                        echo "=============== del failed ============".json_encode($filter).PHP_EOL;
                    }
                    file_put_contents(ROOT_PATH."/log/phy_node_process.$current_time.log",json_encode($ochNodeInfoNew)."\n",FILE_APPEND);
                }

            }catch (\MongoDB\Driver\Exception\BulkWriteException $bulkWriteException) {
                echo "exception get data from mongodb and errorMsg is -> " . $bulkWriteException->getMessage() . PHP_EOL;
                return false;
            }
        }
        return true;
    }

}
$ochRun = new ochNodeTrans();
$ochRun->run();