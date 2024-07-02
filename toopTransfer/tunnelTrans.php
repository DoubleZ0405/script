<?php

define ( 'ROOT_PATH', dirname ( __FILE__ ) );
ini_set ( "memory_limit", "-1" );

require_once "./lib/Util.php";
require_once "./lib/initOmsInfo.php";
require_once "./mongo/mongoBaseDao.php";

class tunnelTrans {

    //tunnel 业务Id
    private static $tunnelIdArrBHTD2EBS1 = array();
    private static $tunnelIdArrTD2EBS1 = array();
    private static $tunnelIdArrBHTD2EBS2 = array();
    private static $tunnelIdArrTD2EBS2 = array();

    //phpLink 光纤连接信息
    private static $phyLinkBHTD2EBS1 = array();
    private static $phyLinkTd2EBS1 = array();
    private static $phyLinkBHTD2EBS2 = array();
    private static $phyLinkTD2EBS2 = array();

    //phyNode ochNode(TPC4) 物理设备
    private static $phyNodeBHTD2EBS1 = array();
    private static $phyNodeTD2EBS1 = array();
    private static $phyNodeBHTD2EBS2 = array();
    private static $phyNodeTD2EBS2 = array();

    public function run() {
        //先初始化迁移的基础数据
        $this->initProcessInfo();

        //获取tunnel 处理业务迁移
        if (!$this->tunnelTransProcess()) {exit(1);}
        echo "=============== tunnel info  process done ===============".PHP_EOL;
    }

    /**
     *
     */
    function initProcessInfo() {
        //华南1 滨海腾大--鹅埠复用段包含的业务
        $tunnelInfo = initOmsInfo::getTunnelInfo(initOmsInfo::HUANAN1URL,initOmsInfo::OMSID_BH2EB);
        $tunnelList = $tunnelInfo['output']['tunnel'];
        for($i=0; $i<count($tunnelList); $i++) {
            //业务Id
            $tunnelId = $tunnelList[$i]['tunnel-id'];
            self::$tunnelIdArrBHTD2EBS1[$i] =$tunnelId;
            echo 'add success tunnelId is :'.$tunnelId."\n";
            file_put_contents(ROOT_PATH."/tunnel/tunnel_list_bh2eb_s1.txt",$tunnelId."\n",FILE_APPEND);
        }

        //华南1 腾大--鹅埠复用段包含的业务
        $tunnelInfo = initOmsInfo::getTunnelInfo(initOmsInfo::HUANAN1URL,initOmsInfo::OMSID_TD2EB);
        $tunnelList = $tunnelInfo['output']['tunnel'];
        for($i=0; $i<count($tunnelList); $i++) {
            //业务Id
            $tunnelId = $tunnelList[$i]['tunnel-id'];
            self::$tunnelIdArrTD2EBS1[$i] =$tunnelId;
            echo 'add success tunnelId is :'.$tunnelId."\n";
            file_put_contents(ROOT_PATH."/tunnel/tunnel_list_td2eb_s1.txt",$tunnelId."\n",FILE_APPEND);
        }

        //华南2 滨海腾大--鹅埠复用段包含的业务
        $tunnelInfo = initOmsInfo::getTunnelInfo(initOmsInfo::HUANAN2URL,initOmsInfo::OMSID_BH2EB_S2);
        $tunnelList = $tunnelInfo['output']['tunnel'];
        for($i=0; $i<count($tunnelList); $i++) {
            //业务Id
            $tunnelId = $tunnelList[$i]['tunnel-id'];
            self::$tunnelIdArrBHTD2EBS2[$i] =$tunnelId;
            echo 'add success tunnelId is :'.$tunnelId."\n";
            file_put_contents(ROOT_PATH."/tunnel/tunnel_list_bh2eb_s2.txt",$tunnelId."\n",FILE_APPEND);
        }

        //华南2 腾大--鹅埠复用段包含的业务
        $tunnelInfo = initOmsInfo::getTunnelInfo(initOmsInfo::HUANAN2URL,initOmsInfo::OMSID_TD2EB_S2);
        $tunnelList = $tunnelInfo['output']['tunnel'];
        for($i=0; $i<count($tunnelList); $i++) {
            //业务Id
            $tunnelId = $tunnelList[$i]['tunnel-id'];
            self::$tunnelIdArrTD2EBS2[$i] =$tunnelId;
            echo 'add success tunnelId is :'.$tunnelId."\n";
            file_put_contents(ROOT_PATH."/tunnel/tunnel_list_td2eb_s2.txt",$tunnelId."\n",FILE_APPEND);
        }
    }

    /**
     * @return bool
     * @throws \MongoDB\Driver\Exception\Exception
     */
    function tunnelTransProcess() {
        $current_time = date("Ymd_Hsm");
        //华南1 滨海腾达--鹅埠
        foreach (self::$tunnelIdArrBHTD2EBS1 as $tunnelId) {
            $filter = array(
                'tunnelId' => $tunnelId
            );
            try {
                $cursor = mongoBaseDao::mongoDbQuery('sotn.tunnel-col',mongoBaseDao::mongoUser30,
                    mongoBaseDao::mongoPasswd30,mongoBaseDao::mongoIp30,$filter,array());
                foreach ($cursor as $document) {
                    $oldTunnelInfo = json_encode($document);
                    $newTunnelInfo = str_replace(initOmsInfo::siteIdA30, initOmsInfo::siteIdGdEb, $oldTunnelInfo);
                    $newTunnelInfo = str_replace(initOmsInfo::siteIdB30,initOmsInfo::siteIdGdTd,$newTunnelInfo);
                    $tunelInfoS1New = json_decode($newTunnelInfo, true);
                }

                if ($tunelInfoS1New != null || count($tunelInfoS1New) >0 ) {
                    unset($tunelInfoS1New['_id']);
                    $retFromViewLinkIn = mongoBaseDao::mongoInsert('sotn.tunnel-col',mongoBaseDao::mongoUser,
                        mongoBaseDao::mongoPasswd,mongoBaseDao::mongoIp,$tunelInfoS1New);
                    if ($retFromViewLinkIn != 1) {
                        echo "=============== add failed tunnel ============".PHP_EOL;
                        echo 'add failed tunnel is :'.json_encode($tunelInfoS1New)."\n";
                        echo "=============== add failed tunnel ============".PHP_EOL;
                        file_put_contents(ROOT_PATH."/log/tunnel_process.$current_time.log",json_encode($tunelInfoS1New)."\n",FILE_APPEND);
                        return false;
                    }
                    file_put_contents(ROOT_PATH."/log/tunnel_process.$current_time.log",json_encode($tunelInfoS1New)."\n",FILE_APPEND);
                }
                sleep(1);
                $retFromLinkDelS1 = mongoBaseDao::mongoDel('sotn.tunnel-col',mongoBaseDao::mongoUser30,
                    mongoBaseDao::mongoPasswd30, mongoBaseDao::mongoIp30,$filter,array());
                if ($retFromLinkDelS1) {
                    echo "=============== del success ============".json_encode($filter).PHP_EOL;
                }else{
                    echo "=============== del failed ============".json_encode($filter).PHP_EOL;
                }
            }catch (\MongoDB\Driver\Exception\BulkWriteException $exception) {
                echo "exception when insert data into mongodb and errorMsg is -> ".$exception->getMessage().PHP_EOL;
                return false;
            }
        }
        sleep(1);
        //华南1 腾大--鹅埠
        foreach (self::$tunnelIdArrTD2EBS1 as $tunnelId) {
            $filter = array(
                'tunnelId' => $tunnelId
            );
            try {
                $cursor = mongoBaseDao::mongoDbQuery('sotn.tunnel-col',mongoBaseDao::mongoUser30,
                    mongoBaseDao::mongoPasswd30,mongoBaseDao::mongoIp30,$filter,array());
                foreach ($cursor as $document) {
                    $oldTunnelInfo = json_encode($document);
                    $newTunnelInfo = str_replace(initOmsInfo::siteIdA30, initOmsInfo::siteIdGdEb, $oldTunnelInfo);
                    $newTunnelInfo = str_replace(initOmsInfo::siteIdC30,initOmsInfo::siteIdGdDso,$newTunnelInfo);
                    $tunelInfoS1New = json_decode($newTunnelInfo, true);
                }

                if ($tunelInfoS1New != null || count($tunelInfoS1New) >0 ) {
                    unset($tunelInfoS1New['_id']);
                    $retFromViewLinkIn = mongoBaseDao::mongoInsert('sotn.tunnel-col',mongoBaseDao::mongoUser,
                        mongoBaseDao::mongoPasswd,mongoBaseDao::mongoIp,$tunelInfoS1New);
                    if ($retFromViewLinkIn != 1) {
                        echo "=============== add failed tunnel ============".PHP_EOL;
                        echo 'add failed tunnel is :'.$tunelInfoS1New."\n";
                        echo "=============== add failed tunnel ============".PHP_EOL;
                        file_put_contents(ROOT_PATH."/log/tunnel_process.$current_time.log",$tunelInfoS1New."\n",FILE_APPEND);
                        return false;
                    }
                    file_put_contents(ROOT_PATH."/log/tunnel_process.$current_time.log",$tunelInfoS1New."\n",FILE_APPEND);
                }
                sleep(1);
                $retFromLinkDelS1 = mongoBaseDao::mongoDel('sotn.tunnel-col',mongoBaseDao::mongoUser30,
                    mongoBaseDao::mongoPasswd30, mongoBaseDao::mongoIp30,$filter,array());
                if ($retFromLinkDelS1) {
                    echo "=============== del success ============".json_encode($filter).PHP_EOL;
                }else{
                    echo "=============== del failed ============".json_encode($filter).PHP_EOL;
                }
            }catch (\MongoDB\Driver\Exception\BulkWriteException $exception) {
                echo "exception when insert data into mongodb and errorMsg is -> ".$exception->getMessage().PHP_EOL;
                return false;
            }
        }
        sleep(2);

        //华南2 滨海腾大--鹅埠
        foreach (self::$tunnelIdArrBHTD2EBS2 as $tunnelId) {
            $filter = array(
                'tunnelId' => $tunnelId
            );
            try {
                $cursor = mongoBaseDao::mongoDbQuery('sotn.tunnel-col',mongoBaseDao::mongoUser35,
                    mongoBaseDao::mongoPasswd35,mongoBaseDao::mongoIp35,$filter,array());
                foreach ($cursor as $document) {
                    $oldTunnelInfo = json_encode($document);
                    $newTunnelInfo = str_replace(initOmsInfo::siteIdA35, initOmsInfo::siteIdGdEb, $oldTunnelInfo);
                    $newTunnelInfo = str_replace(initOmsInfo::siteIdB35,initOmsInfo::siteIdGdTd,$newTunnelInfo);
                    $tunelInfoS1New = json_decode($newTunnelInfo, true);
                }

                if ($tunelInfoS1New != null || count($tunelInfoS1New) >0 ) {
                    unset($tunelInfoS1New['_id']);
                    $retFromViewLinkIn = mongoBaseDao::mongoInsert('sotn.tunnel-col',mongoBaseDao::mongoUser,
                        mongoBaseDao::mongoPasswd,mongoBaseDao::mongoIp,$tunelInfoS1New);
                    if ($retFromViewLinkIn != 1) {
                        echo "=============== add failed tunnel ============".PHP_EOL;
                        echo 'add failed tunnel is :'.$tunelInfoS1New."\n";
                        echo "=============== add failed tunnel ============".PHP_EOL;
                        file_put_contents(ROOT_PATH."/log/tunnel_process.$current_time.log",$tunelInfoS1New."\n",FILE_APPEND);
                    }
                    file_put_contents(ROOT_PATH."/log/tunnel_process.$current_time.log",$tunelInfoS1New."\n",FILE_APPEND);
                }
                sleep(1);
                $retFromLinkDelS2 = mongoBaseDao::mongoDel('sotn.tunnel-col',mongoBaseDao::mongoUser35,
                    mongoBaseDao::mongoPasswd35, mongoBaseDao::mongoIp35,$filter,array());
                if ($retFromLinkDelS2) {
                    echo "=============== del success ============".json_encode($filter).PHP_EOL;
                }else{
                    echo "=============== del failed ============".json_encode($filter).PHP_EOL;
                }
            }catch (\MongoDB\Driver\Exception\BulkWriteException $exception) {
                echo "exception when insert data into mongodb and errorMsg is -> ".$exception->getMessage().PHP_EOL;
                return false;
            }
        }
        sleep(2);

        //华南2 腾大--鹅埠
        foreach (self::$tunnelIdArrTD2EBS2 as $tunnelId) {
            $filter = array(
                'tunnelId' => $tunnelId
            );
            try {
                $cursor = mongoBaseDao::mongoDbQuery('sotn.tunnel-col',mongoBaseDao::mongoUser35,
                    mongoBaseDao::mongoPasswd35,mongoBaseDao::mongoIp35,$filter,array());
                foreach ($cursor as $document) {
                    $oldTunnelInfo = json_encode($document);
                    $newTunnelInfo = str_replace(initOmsInfo::siteIdA35, initOmsInfo::siteIdGdEb, $oldTunnelInfo);
                    $newTunnelInfo = str_replace(initOmsInfo::siteIdC35,initOmsInfo::siteIdGdDso,$newTunnelInfo);
                    $tunelInfoS1New = json_decode($newTunnelInfo, true);
                }

                if ($tunelInfoS1New != null || count($tunelInfoS1New) >0 ) {
                    unset($tunelInfoS1New['_id']);
                    $retFromViewLinkIn = mongoBaseDao::mongoInsert('sotn.tunnel-col',mongoBaseDao::mongoUser,
                        mongoBaseDao::mongoPasswd,mongoBaseDao::mongoIp,$tunelInfoS1New);
                    if ($retFromViewLinkIn != 1) {
                        echo "=============== add failed tunnel ============".PHP_EOL;
                        echo 'add failed tunnel is :'.$tunelInfoS1New."\n";
                        echo "=============== add failed tunnel ============".PHP_EOL;
                        file_put_contents(ROOT_PATH."/log/tunnel_process.$current_time.log",$tunelInfoS1New."\n",FILE_APPEND);
                    }
                    file_put_contents(ROOT_PATH."/log/tunnel_process.$current_time.log",$tunelInfoS1New."\n",FILE_APPEND);
                }
                sleep(1);
                $retFromLinkDelS2 = mongoBaseDao::mongoDel('sotn.tunnel-col',mongoBaseDao::mongoUser35,
                    mongoBaseDao::mongoPasswd35, mongoBaseDao::mongoIp35,$filter,array());
                if ($retFromLinkDelS2) {
                    echo "=============== del success ============".json_encode($filter).PHP_EOL;
                }else{
                    echo "=============== del failed ============".json_encode($filter).PHP_EOL;
                }
            }catch (\MongoDB\Driver\Exception\BulkWriteException $exception) {
                echo "exception when insert data into mongodb and errorMsg is -> ".$exception->getMessage().PHP_EOL;
                return false;
            }
        }

        return true;
    }
}

$tunnelTrans = new tunnelTrans();
$tunnelTrans->run();
