<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2021/11/9
 * Time: 11:18 AM
 * Brief:
 */

define ( 'ROOT_PATH', dirname ( __FILE__ ) );
ini_set ( "memory_limit", "-1" );

require_once "./lib/Util.php";
require_once "./lib/initOmsInfo.php";
require_once "./mongo/mongoBaseDao.php";

class phyLinkTrans {


    /**
     * @throws \MongoDB\Driver\Exception\Exception
     * main 入口
     */
    function run() {
        //华南1 滨海腾大--鹅埠
        $fileBHTd2EBS1 = "tunnel_list_bh2eb_s1.txt";
        $fileOchNameBhTd2EbS1 = str_replace("tunnel","ochLink",$fileBHTd2EBS1);
        if (!$this->phyLinkProcess($fileBHTd2EBS1,mongoBaseDao::mongoUser30,mongoBaseDao::mongoPasswd30,
            mongoBaseDao::mongoIp30,initOmsInfo::siteIdA30,
            initOmsInfo::siteIdB30,"",initOmsInfo::HUANAN1URL,$fileOchNameBhTd2EbS1)) {
            exit(1);
        };
        echo "=============== 滨海腾大--鹅埠 S1 phyLink process done ===============".PHP_EOL;
        sleep(1);

        //华南1 腾大--鹅埠
        $fileTd2EbS1 = "tunnel_list_td2eb_s1.txt";
        $fileOchNameTd2EbS1 = str_replace("tunnel","ochLink",$fileTd2EbS1);
        if (!$this->phyLinkProcess($fileTd2EbS1,mongoBaseDao::mongoUser30,mongoBaseDao::mongoPasswd30,
            mongoBaseDao::mongoIp30,initOmsInfo::siteIdA30,
            "",initOmsInfo::siteIdC30,initOmsInfo::HUANAN1URL,$fileOchNameTd2EbS1)) {
            exit(1);
        };
        echo "=============== 腾大--鹅埠 S1 phyLink process done ===============".PHP_EOL;
        sleep(1);

        //华南2 滨海腾大--鹅埠
        $fileBHTd2EBS2 = "tunnel_list_bh2eb_s2.txt";
        $fileOchNameBhTd2EbS2 = str_replace("tunnel","ochLink",$fileBHTd2EBS2);
        if (!$this->phyLinkProcess($fileBHTd2EBS2,mongoBaseDao::mongoUser35,mongoBaseDao::mongoPasswd35,
            mongoBaseDao::mongoIp35,initOmsInfo::siteIdA35,
            initOmsInfo::siteIdB35,"",initOmsInfo::HUANAN2URL,$fileOchNameBhTd2EbS2)) {
            exit(1);
        };
        echo "=============== 滨海腾大--鹅埠 S2 phyLink process done ===============".PHP_EOL;
        sleep(1);

        //华南2 腾大--鹅埠
        $fileTd2EBS2 = "tunnel_list_td2eb_s2.txt";
        $fileOchNameTd2EbS2 = str_replace("tunnel","ochLink",$fileTd2EBS2);
        if (!$this->phyLinkProcess($fileTd2EBS2,mongoBaseDao::mongoUser35,mongoBaseDao::mongoPasswd35,
            mongoBaseDao::mongoIp35,initOmsInfo::siteIdA35,
            "",initOmsInfo::siteIdC35,initOmsInfo::HUANAN2URL,$fileOchNameTd2EbS2)) {
            exit(1);
        };
        echo "=============== 腾大--鹅埠 S2 phyLink process done ===============".PHP_EOL;
    }


    /**
     * @param $filename
     * @param $mongoUser
     * @param $mongoPasswd
     * @param $mongoIp
     * @param $siteIdNewEb
     * @param $siteIdNewBhTd
     * @param $siteIdNewTd
     * @return bool
     * @throws \MongoDB\Driver\Exception\Exception
     */
    function phyLinkProcess($filename,$mongoUser,$mongoPasswd,$mongoIp,$siteIdEb,$siteIdBhTd,$siteIdTd,
                            $controllerUrl,$fileId) {
        $current_time = date("Ymd_Hsm");
        $dir = dirname(__FILE__);
        $file = fopen($dir.'/tunnel/'.$filename,'r');
        while ($tunnelId = fgets($file)) {
            //光纤连接
            $tunnelId = str_replace(PHP_EOL, '', $tunnelId);
            $phyLinkInfo = initOmsInfo::getPhyLinkInfo($controllerUrl,$tunnelId);
            $linkList = $phyLinkInfo['output']['link'];
            try {
                for ($i=0; $i<count($linkList); $i++) {
                    $linkId = $linkList[$i]['link-id'];
                    echo $linkId.PHP_EOL;
                    if ($linkList[$i]['physical']['link-type'] == "os-link") {
                        echo 'add success ochLinkId is :'.$tunnelId."\n";
                        $ochLinkId = $linkList[$i]['physical']['supported-link'][0]['link-ref'];
                        echo "och link id is ".$ochLinkId.PHP_EOL;
                        file_put_contents(ROOT_PATH."/ochLink/".$fileId,$ochLinkId."\n",FILE_APPEND);
                    }
                    $filter = array(
                        'linkId' => $linkId
                    );
                    $cursor = mongoBaseDao::mongoDbQuery('sotn.phy-link-col',$mongoUser,$mongoPasswd,
                        $mongoIp,$filter,array());
                    foreach ($cursor as $document) {
                        echo json_encode($filter).PHP_EOL;
                        $oldLinkInfo = json_encode($document);
                        if ($siteIdEb !=null && $siteIdBhTd != null) {
                            $newLinkInfo = str_replace($siteIdEb, initOmsInfo::siteIdGdEb, $oldLinkInfo);
                            $newLinkInfo = str_replace($siteIdBhTd,initOmsInfo::siteIdGdTd,$newLinkInfo);
                            $phyLinkInfoS1New = json_decode($newLinkInfo, true);
                        }else if ($siteIdEb !=null && $siteIdTd != null) {
                            $newLinkInfo = str_replace($siteIdEb, initOmsInfo::siteIdGdEb, $oldLinkInfo);
                            $newLinkInfo = str_replace($siteIdTd,initOmsInfo::siteIdGdDso,$newLinkInfo);
                            $phyLinkInfoS1New = json_decode($newLinkInfo, true);
                        }
                        usleep(500);
                        unset($phyLinkInfoS1New['_id']);
                        $retFromLinkIn = mongoBaseDao::mongoInsert('sotn.phy-link-col',mongoBaseDao::mongoUser,
                            mongoBaseDao::mongoPasswd,mongoBaseDao::mongoIp,$phyLinkInfoS1New);
                        if ($retFromLinkIn != 1) {
                            echo "=============== add failed phy link ============".PHP_EOL;
                            echo 'add failed phy link is :'.$phyLinkInfoS1New."\n";
                            echo "=============== add failed phy link ============".PHP_EOL;
                            file_put_contents(ROOT_PATH."/log/phy_link_process.$current_time.log",json_encode($phyLinkInfoS1New)."\n",FILE_APPEND);
                            return false;
                        }
                    }
                    file_put_contents(ROOT_PATH."/log/phy_link_process.$current_time.log",json_encode($phyLinkInfoS1New)."\n",FILE_APPEND);
                    usleep(100);
                    $retFromLinkDelS1 = mongoBaseDao::mongoDel('sotn.phy-link-col',$mongoUser,$mongoPasswd,
                        $mongoIp,$filter,array());
                    if ($retFromLinkDelS1) {
                        echo "=============== del success ============".json_encode($filter).PHP_EOL;
                    }else{
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

$phyTopoTrans = new phyLinkTrans();
$phyTopoTrans->run();