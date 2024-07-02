<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2021/11/10
 * Time: 11:29 AM
 * Brief:
 */
define ( 'ROOT_PATH', dirname ( __FILE__ ) );
ini_set ( "memory_limit", "-1" );

require_once "./lib/Util.php";
require_once "./lib/initOmsInfo.php";
require_once "./mongo/mongoBaseDao.php";

class phyNodeTrans {

    CONST telemetryClbIp = "11.186.11.146";

    /**
     *
     */
    function run() {

        //华南1 滨海腾大--鹅埠
        $fileBHTd2EBS1 = "tunnel_list_bh2eb_s1.txt";
        if (!$this->phyNodeProcess($fileBHTd2EBS1,mongoBaseDao::mongoUser30,mongoBaseDao::mongoPasswd30,
            mongoBaseDao::mongoIp30,initOmsInfo::siteIdA30,
            initOmsInfo::siteIdB30,"",initOmsInfo::HUANAN1URL)) {
            exit(1);
        };
        echo "=============== 滨海腾大--鹅埠 S1 phyNode process done ===============".PHP_EOL;
        sleep(1);
        //华南1 腾大--鹅埠
        $fileTd2EbS1 = "tunnel_list_td2eb_s1.txt";
        if (!$this->phyNodeProcess($fileTd2EbS1,mongoBaseDao::mongoUser30,mongoBaseDao::mongoPasswd30,
            mongoBaseDao::mongoIp30,initOmsInfo::siteIdA30,
            "",initOmsInfo::siteIdC30,initOmsInfo::HUANAN1URL)) {
            exit(1);
        };
        echo "=============== 腾大--鹅埠 S1 phyNode process done ===============".PHP_EOL;
        sleep(1);

        //华南2 滨海腾大--鹅埠
        $fileBHTd2EBS2 = "tunnel_list_bh2eb_s2.txt";
        if (!$this->phyNodeProcess($fileBHTd2EBS2,mongoBaseDao::mongoUser35,mongoBaseDao::mongoPasswd35,
            mongoBaseDao::mongoIp35,initOmsInfo::siteIdA35,
            initOmsInfo::siteIdB35,"",initOmsInfo::HUANAN2URL)) {
            exit(1);
        };
        echo "=============== 滨海腾大--鹅埠 S2 phyNode process done ===============".PHP_EOL;
        sleep(1);

        //华南2 腾大--鹅埠
        $fileTd2EBS2 = "tunnel_list_td2eb_s2.txt";
        if (!$this->phyNodeProcess($fileTd2EBS2,mongoBaseDao::mongoUser35,mongoBaseDao::mongoPasswd35,
            mongoBaseDao::mongoIp35,initOmsInfo::siteIdA35,
            "",initOmsInfo::siteIdC35,initOmsInfo::HUANAN2URL)) {
            exit(1);
        };
        echo "=============== 腾大--鹅埠 S2 phyNode process done ===============".PHP_EOL;

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
    function phyNodeProcess($filename,$mongoUser,$mongoPasswd,$mongoIp,$siteIdEb,$siteIdBhTd,$siteIdTd,$controllerUrl) {
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
                        'neId' => $nodeId
                    );
                    $cursor = mongoBaseDao::mongoDbQuery('sotn.phy-node-col',$mongoUser,$mongoPasswd,
                        $mongoIp,$filter,array());
                    foreach ($cursor as $document) {
                        $oldLinkInfo = json_encode($document);
                        //ILA不在这里处理
                        if ($siteIdEb !=null && $siteIdBhTd != null) {
                            $newLinkInfo = str_replace($siteIdEb, initOmsInfo::siteIdGdEb, $oldLinkInfo);
                            $newLinkInfo = str_replace($siteIdBhTd,initOmsInfo::siteIdGdTd,$newLinkInfo);
                            $phyLinkInfoS1New = json_decode($newLinkInfo, true);
                        }else if ($siteIdEb !=null && $siteIdTd != null) {
                            $newLinkInfo = str_replace($siteIdEb, initOmsInfo::siteIdGdEb, $oldLinkInfo);
                            $newLinkInfo = str_replace($siteIdTd,initOmsInfo::siteIdGdDso,$newLinkInfo);
                            $phyLinkInfoS1New = json_decode($newLinkInfo, true);
                        }
                    }
                    if ($phyLinkInfoS1New == null && empty($phyLinkInfoS1New)) {
                        continue;
                    }

                    $phyLinkInfoS1New['data']['network-topology']['topology'][0]['node'][0]
                    ['otn-phy-topology:physical']['system']['telemetry'][0]['ip'] = self::telemetryClbIp;
                    unset($phyLinkInfoS1New['_id']);
                    usleep(500);
                    $retFromLinkIn = mongoBaseDao::mongoInsert('sotn.phy-node-col',mongoBaseDao::mongoUser,
                        mongoBaseDao::mongoPasswd,mongoBaseDao::mongoIp,$phyLinkInfoS1New);
                    if ($retFromLinkIn != 1) {
                        echo "=============== add failed phy node ============".PHP_EOL;
                        echo 'add failed phy node is :'.$phyLinkInfoS1New."\n";
                        echo "=============== add failed phy node ============".PHP_EOL;
                        file_put_contents(ROOT_PATH."/log/phy_node_process.$current_time.log",$phyLinkInfoS1New."\n",FILE_APPEND);
                        return false;
                    }
                    file_put_contents(ROOT_PATH."/log/phy_node_process.$current_time.log",$phyLinkInfoS1New."\n",FILE_APPEND);
                    usleep(100);
                    $retFromLinkDelS1 = mongoBaseDao::mongoDel('sotn.phy-node-col',$mongoUser,$mongoPasswd,
                        $mongoIp,$filter,array());
                    if ($retFromLinkDelS1) {
                        echo "=============== del success ============".json_encode($filter).PHP_EOL;
                    }else{//站点生产的网元 可能之前已经删除了 这里
                        echo "=============== del failed ============".json_encode($filter).PHP_EOL;
                    }
                }
            }catch (\MongoDB\Driver\Exception\BulkWriteException $bulkWriteException) {
                echo "exception get data from mongodb and errorMsg is -> " . $bulkWriteException->getMessage() . PHP_EOL;
                return false;
            }
        }
        return true;
    }
}

$phyNode = new phyNodeTrans();
$phyNode->run();