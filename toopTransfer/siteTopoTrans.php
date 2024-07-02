<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2021/10/27
 * Time: 3:48 PM
 * Brief:
 */

define ( 'ROOT_PATH', dirname ( __FILE__ ) );
ini_set ( "memory_limit", "-1" );

require_once "./lib/Util.php";
require_once "./lib/initOmsInfo.php";
require_once "./mongo/mongoBaseDao.php";

class siteTopoTrans {

    //mongo
    private static $_mongoIp35 = mongoBaseDao::mongoIp35; // toop35 华南2
    private static $_mongoUser35 = mongoBaseDao::mongoUser35;
    private static $_mongoPasswd35 = mongoBaseDao::mongoPasswd35;

    private static $_mongoIp30 = mongoBaseDao::mongoIp30; // toop30 华南1
    private static $_mongoUser30 = mongoBaseDao::mongoUser30;
    private static $_mongoPasswd30 = mongoBaseDao::mongoPasswd30;

    private static $_mongoIp = mongoBaseDao::mongoIp; // toop36 广电
    private static $_mongoUser = mongoBaseDao::mongoUser;
    private static $_mongoPasswd = mongoBaseDao::mongoPasswd;

    //鹅埠站点
    private static $siteIdA35 = "";
    private static $siteIdA30 = "";
    private static $siteIdGdEb = "Site-1636084500452";

    //滨海腾大
    private static $siteIdB35 = "";
    private static $siteIdB30 = "";
    private static $siteIdGdTd = "Site-1636082040070";

    //腾讯大厦
    private static $siteIdC35 = "";
    private static $siteIdC30 = "";
    private static $siteIdGdDso = "Site-1636082188393";

    //ILA站点
    private static $siteNodeIlaListS1 = array();
    private static $siteNodeIlaListS2 = array();

    /**
     * @throws \MongoDB\Driver\Exception\Exception
     * main 入口
     */
    public function run() {
        /*** 运行环境&&参数初始化 ***/
//        $this->initMongoCon();
        $this->initSiteParams();

        /*** 处理site-view-topo ***/
        if (!$this->siteViewNodeProcess()) exit(1);
        sleep(2);
        if (!$this->siteViewLinkProcess()) exit(1);
        echo "=============== site-view-topo process done ===============".PHP_EOL;
        sleep(2);
         /** 处理site-topo */
        if (!$this->siteNodeProcess()) exit(1);
        sleep(3);
        if (!$this->siteLinkProcess()) exit(1);
        echo "=============== site-topo process done ===============".PHP_EOL;

    }

    /**
     * 初始化站点ID信息
     */
    function initSiteParams() {
        //华南1 滨海腾大--鹅埠
        $siteArr = initOmsInfo::getSiteNodeInfo(initOmsInfo::HUANAN1URL,initOmsInfo::OMSID_BH2EB);
        for ($i=0; $i<count($siteArr['output']['node']); $i++) {
            $siteType = $siteArr['output']['node'][$i]['site']['site-type'];
            $siteNodeId = $siteArr['output']['node'][$i]['node-id'];
            if ($siteType == "ILA") {
                self::$siteNodeIlaListS1[] = array(
                    'siteNodeId' => $siteNodeId
                );
            }
        }

        //华南1 腾大--鹅埠
        $siteArr = initOmsInfo::getSiteNodeInfo(initOmsInfo::HUANAN1URL,initOmsInfo::OMSID_TD2EB);
        for ($i=0; $i<count($siteArr['output']['node']); $i++) {
            $siteType = $siteArr['output']['node'][$i]['site']['site-type'];
            $siteNodeId = $siteArr['output']['node'][$i]['node-id'];
            if ($siteType == "ILA") {
                self::$siteNodeIlaListS1[] = array(
                    'siteNodeId' => $siteNodeId
                );
            }
        }

        //华南2 滨海腾大--鹅埠
        $siteArr = initOmsInfo::getSiteNodeInfo(initOmsInfo::HUANAN2URL,initOmsInfo::OMSID_BH2EB_S2);
        for ($i=0; $i<count($siteArr['output']['node']); $i++) {
            $siteType = $siteArr['output']['node'][$i]['site']['site-type'];
            $siteNodeId = $siteArr['output']['node'][$i]['node-id'];
            if ($siteType == "ILA") {
                self::$siteNodeIlaListS2[] = array(
                    'siteNodeId' => $siteNodeId
                );
            }
        }

        //华南2 腾大--鹅埠
        $siteArr = initOmsInfo::getSiteNodeInfo(initOmsInfo::HUANAN2URL,initOmsInfo::OMSID_TD2EB_S2);
        for ($i=0; $i<count($siteArr['output']['node']); $i++) {

            $siteType = $siteArr['output']['node'][$i]['site']['site-type'];
            $siteNodeId = $siteArr['output']['node'][$i]['node-id'];
            if ($siteType == "ILA") {
                self::$siteNodeIlaListS2[] = array(
                    'siteNodeId' => $siteNodeId
                );
            }
        }

        self::$siteIdA35 = initOmsInfo::siteIdA35;//鹅埠
        self::$siteIdB35 = initOmsInfo::siteIdB35;//深圳-滨海腾大(SZ-BHTD)
        self::$siteIdC35 = initOmsInfo::siteIdC35;//滨海


        self::$siteIdA30 = initOmsInfo::siteIdA30;
        self::$siteIdB30 = initOmsInfo::siteIdB30;
        self::$siteIdC30 = initOmsInfo::siteIdC30;
    }

    /**
     * @param $collection
     * @return array|bool
     * @throws \MongoDB\Driver\Exception\Exception
     */
    function getTopoStruct($collection) {
        $mongo_conn = new MongoDB\Driver\Manager("mongodb://".self::$_mongoUser.":".urlencode(self::$_mongoPasswd)."@".self::$_mongoIp);
        $filter = array();
        $options = array();

        $query = new MongoDB\Driver\Query($filter, $options);
        $cursor =$this->mongoDbQuery($collection, array(),array());
        if ($cursor == null || empty($cursor) ) {
            echo "unable get ne info from ".self::collection."\n";
            return false;
        }
        foreach ($cursor as $document) {
            $row = Util::object2array($document);
            $topology_base = $row['data']['network-topology']['topology'][0]['site-topology:site']['risk-group'][0]['plane'];
            if ($topology_base == null || count($topology_base) <=0) {
                echo "获取topo 基础信息 为空".PHP_EOL;
                return false;
            }
            foreach ($topology_base as $index => $plane_info) {
                $count = 0;
                if ($plane_info['plane-name'] == "B") {
                    foreach ($plane_info['link-ref'] as $oms_link_info) {
                        $count++;
                        $oms_id = str_replace("SiteLink-","", $oms_link_info);
                        $oms_arr_tmp = explode("#", $oms_id);
                        self::$source_site_id = $oms_arr_tmp[0];
                        $dest_info = explode("Site",$oms_arr_tmp[3]);
                        if ($count == 1) {
                            self::$dest_siteA_id = "Site".$dest_info[1];
                        }
                        if ($count == 2) {
                            self::$dest_siteB_id = "Site".$dest_info[1];
                        }
                    }
                }
            }
        }
        $siteInfo = array(
            "source_site_id" => self::$source_site_id,
            "dest_siteA_id" => self::$dest_siteA_id,
            "dest_siteB_id" => self::$dest_siteB_id,
        );
        return $siteInfo;
    }

    /**
     * @return bool
     */
    function siteViewNodeProcess() {
        $current_time = date("Ymd_Hsm");
        $prepareDelSiteId30 = array(
            initOmsInfo::siteIdA30,
            initOmsInfo::siteIdB30,
            initOmsInfo::siteIdC30,
        );

        $prepareDelSiteId35 = array(
            initOmsInfo::siteIdA35,
            initOmsInfo::siteIdB35,
            initOmsInfo::siteIdC35,
        );

        foreach ($prepareDelSiteId30 as $siteId) {
            $filter = array(
                'viewNodeId' => $siteId
            );
            $retFromDel = mongoBaseDao::mongoDel('sotn.view-node-col',self::$_mongoUser30,
                self::$_mongoPasswd30,self::$_mongoIp30,$filter,array());
            if ($retFromDel) {
                echo "=============== add success site-view-topo ============".PHP_EOL;
                echo 'add success viewNodeId is :'.$siteId."\n";
                echo "=============== add success site-view-topo ============".PHP_EOL;
                file_put_contents(ROOT_PATH."/log/site_view_topo_process.$current_time.log",$siteId."\n",FILE_APPEND);
            }else {
                echo "=============== add failed site-view-topo ============".PHP_EOL;
                echo 'add failed viewNodeId is :'.$siteId."\n";
                echo "=============== add failed site-view-topo ============".PHP_EOL;
                file_put_contents(ROOT_PATH."/log/site_view_topo_process.$current_time.log",$siteId."\n",FILE_APPEND);
            }
        }
        sleep(2);
        foreach ($prepareDelSiteId35 as $siteId) {
            $filter = array(
                'viewNodeId' => $siteId
            );
            $retFromDel = mongoBaseDao::mongoDel('sotn.view-node-col',self::$_mongoUser35,
                self::$_mongoPasswd35,self::$_mongoIp35,$filter,array());
            if ($retFromDel) {
                echo "=============== add success site-view-topo ============".PHP_EOL;
                echo 'add success viewNodeId is :'.$siteId."\n";
                echo "=============== add success site-view-topo ============".PHP_EOL;
                file_put_contents(ROOT_PATH."/log/site_view_topo_process.$current_time.log",$siteId."\n",FILE_APPEND);
            }else {
                echo "=============== add failed site-view-topo ============".PHP_EOL;
                echo 'add failed viewNodeId is :'.$siteId."\n";
                echo "=============== add failed site-view-topo ============".PHP_EOL;
                file_put_contents(ROOT_PATH."/log/site_view_topo_process.$current_time.log",$siteId."\n",FILE_APPEND);
            }
        }

        return true;
    }

    /**
     * @return bool
     * @throws \MongoDB\Driver\Exception\Exception
     */
    function siteViewLinkProcess() {
        $current_time = date("Ymd_Hsm");
        //step1 数据处理 EB -- 滨海腾大
        //华南1 30
        $viewLinkIdSouth1 = initOmsInfo::siteIdB30."---".initOmsInfo::siteIdA30;
        $newLinkId = initOmsInfo::siteIdGdTd."---".initOmsInfo::siteIdGdEb;

        $newViewLinkArr = array();
        $filterS1 = array(
            'viewLinkId' => $viewLinkIdSouth1
        );
        $siteViewLinkInfo = mongoBaseDao::mongoDbQuery('sotn.view-link-col',mongoBaseDao::mongoUser30,
            mongoBaseDao::mongoPasswd30,mongoBaseDao::mongoIp30,$filterS1,array());
        foreach ($siteViewLinkInfo as $document) {
            $newViewLinkArr = Util::object2array($document);
            $linkRef = $newViewLinkArr['data']['network-topology']['topology'][0]['link'][0]['supporting-link'][0]['link-ref'];
            $list_arr = explode("---",$viewLinkIdSouth1);
            $siteIdTd = $list_arr[0];
            $siteIdEb = $list_arr[1];
            $linkTrans = str_replace($siteIdTd,initOmsInfo::siteIdGdTd, $linkRef);
            $linkTrans2 = str_replace($siteIdEb, initOmsInfo::siteIdGdEb, $linkTrans);
            $newViewLinkArr['data']['network-topology']['topology'][0]['link'][0]['link-id'] = $newLinkId;
            $newViewLinkArr['data']['network-topology']['topology'][0]['link'][0]['destination']['dest-node'] = initOmsInfo::siteIdGdEb;
            $newViewLinkArr['data']['network-topology']['topology'][0]['link'][0]['source']['source-node'] = initOmsInfo::siteIdGdTd;
            $newViewLinkArr['data']['network-topology']['topology'][0]['link'][0]['supporting-link'][0]['link-ref'] = $linkTrans2;
            $newViewLinkArr['viewLinkId'] = $newLinkId;
        }

        //华南2 35
        $viewLinkIdSouth2 = initOmsInfo::siteIdB35."---".initOmsInfo::siteIdA35;
        $filterS2 = array(
            'viewLinkId' => $viewLinkIdSouth2,
        );
        $siteViewLinkInfoSouth2 = mongoBaseDao::mongoDbQuery('sotn.view-link-col',mongoBaseDao::mongoUser35,
            mongoBaseDao::mongoPasswd35,mongoBaseDao::mongoIp35,$filterS2,array());
        foreach ($siteViewLinkInfoSouth2 as $doument) {
            $row = Util::object2array($doument);
            $linkRef = $row['data']['network-topology']['topology'][0]['link'][0]['supporting-link'][0]['link-ref'];
            $linkTrans = str_replace(initOmsInfo::siteIdB35,self::$siteIdGdTd,$linkRef);
            $linkTrans2 = str_replace(initOmsInfo::siteIdA35,self::$siteIdGdEb,$linkTrans);

            $newViewLinkArr['data']['network-topology']['topology'][0]['link'][0]['supporting-link'][1]['link-ref'] = $linkTrans2;
            $newViewLinkArr['data']['network-topology']['topology'][0]['link'][0]['view-topology:view']['bundle-number'] = 2;
        }

        //merge后的新viewLink写进新控制器的mongo
        try {
            if ($newViewLinkArr != null || count($newViewLinkArr)>0 ) {
                unset($newViewLinkArr['_id']);
                $retFromViewLinkIn = mongoBaseDao::mongoInsert('sotn.view-link-col',mongoBaseDao::mongoUser,
                    mongoBaseDao::mongoPasswd,mongoBaseDao::mongoIp,$newViewLinkArr);
                if ($retFromViewLinkIn != 1) {
                    echo "=============== add failed site-view-topo ============".PHP_EOL;
                    echo 'add failed viewLinkId is :'.json_encode($newViewLinkArr)."\n";
                    echo "=============== add failed site-view-topo ============".PHP_EOL;
                    file_put_contents(ROOT_PATH."/log/site_view_topo_process.$current_time.log",json_encode($newViewLinkArr)."\n",FILE_APPEND);
                }
            }
            file_put_contents(ROOT_PATH."/log/site_view_topo_process.$current_time.log",json_encode($newViewLinkArr)."\n",FILE_APPEND);
        }catch (\MongoDB\Driver\Exception\BulkWriteException  $bulkWriteException) {
            echo "exception when insert data into mongodb and errorMsg is -> ".$bulkWriteException->getMessage().PHP_EOL;
            return false;
        }
        sleep(2);

        //处理完 清理旧数据
        $retFromViewLinkDelS1 = mongoBaseDao::mongoDel('sotn.view-link-col',mongoBaseDao::mongoUser35,
            mongoBaseDao::mongoPasswd35, mongoBaseDao::mongoIp35,$filterS2,array());
        sleep(1);
        $retFromViewLinkDelS2 = mongoBaseDao::mongoDel('sotn.view-link-col',mongoBaseDao::mongoUser30,
            mongoBaseDao::mongoPasswd30, mongoBaseDao::mongoIp30,$filterS1,array());
        if ($retFromViewLinkDelS1 && $retFromViewLinkDelS2 ) {
            echo "=============== del success ============".PHP_EOL;
        }else{
            echo "=============== del falied ============".json_encode($filterS1).json_encode($filterS2).PHP_EOL;
        }
        sleep(2);
        //step2 数据处理 EB -- 腾大
        //华南1 30
        $viewLinkIdSouth1 = initOmsInfo::siteIdC30."---".initOmsInfo::siteIdA30;
        $newLinkId = initOmsInfo::siteIdGdDso."---".initOmsInfo::siteIdGdEb;

        $newViewLinkArr = array();
        $filterS1 = array(
            'viewLinkId' => $viewLinkIdSouth1
        );
        $siteViewLinkInfo = mongoBaseDao::mongoDbQuery('sotn.view-link-col',
            mongoBaseDao::mongoUser30,mongoBaseDao::mongoPasswd30,mongoBaseDao::mongoIp30,$filterS1,array());
        foreach ($siteViewLinkInfo as $document) {
            $newViewLinkArr = Util::object2array($document);
            $linkRef = $newViewLinkArr['data']['network-topology']['topology'][0]['link'][0]['supporting-link'][0]['link-ref'];
            $list_arr = explode("---",$viewLinkIdSouth1);
            $siteIdTd = $list_arr[0];
            $siteIdEb = $list_arr[1];
            $linkTrans = str_replace($siteIdTd,initOmsInfo::siteIdGdDso, $linkRef);
            $linkTrans2 = str_replace($siteIdEb, initOmsInfo::siteIdGdEb, $linkTrans);
            $newViewLinkArr['data']['network-topology']['topology'][0]['link'][0]['link-id'] = $newLinkId;
            $newViewLinkArr['data']['network-topology']['topology'][0]['link'][0]['destination']['dest-node'] = initOmsInfo::siteIdGdEb;
            $newViewLinkArr['data']['network-topology']['topology'][0]['link'][0]['source']['source-node'] = initOmsInfo::siteIdGdDso;
            $newViewLinkArr['data']['network-topology']['topology'][0]['link'][0]['supporting-link'][0]['link-ref'] = $linkTrans2;
            $newViewLinkArr['viewLinkId'] = $newLinkId;
        }

        //华南2 35
        $viewLinkIdSouth2 = initOmsInfo::siteIdC35."---".initOmsInfo::siteIdA35;
        $filterS2 = array(
            'viewLinkId' => $viewLinkIdSouth2,
        );
        $siteViewLinkInfoSouth2 = mongoBaseDao::mongoDbQuery('sotn.view-link-col',
            mongoBaseDao::mongoUser35,mongoBaseDao::mongoPasswd35,mongoBaseDao::mongoIp35,$filterS2,array());
        foreach ($siteViewLinkInfoSouth2 as $doument) {
            $row = Util::object2array($doument);
            $linkRef = $row['data']['network-topology']['topology'][0]['link'][0]['supporting-link'][0]['link-ref'];
            $linkTrans = str_replace(initOmsInfo::siteIdC35,initOmsInfo::siteIdGdDso,$linkRef);
            $linkTrans2 = str_replace(initOmsInfo::siteIdA35,initOmsInfo::siteIdGdEb,$linkTrans);

            $newViewLinkArr['data']['network-topology']['topology'][0]['link'][0]['supporting-link'][1]['link-ref'] = $linkTrans2;
            $newViewLinkArr['data']['network-topology']['topology'][0]['link'][0]['view-topology:view']['bundle-number'] = 2;
        }
        //merge后的新viewLink写进新控制器的mongo
        if ($newViewLinkArr != null || count($newViewLinkArr) >0 ) {
            unset($newViewLinkArr['_id']);
            $retFromViewLinkIn = mongoBaseDao::mongoInsert('sotn.view-link-col',mongoBaseDao::mongoUser,
                mongoBaseDao::mongoPasswd,mongoBaseDao::mongoIp,$newViewLinkArr);
            if ($retFromViewLinkIn != 1) {
                echo "=============== add failed site-view-topo ============".PHP_EOL;
                echo 'add failed viewLinkId is :'.$newViewLinkArr."\n";
                echo "=============== add failed site-view-topo ============".PHP_EOL;
                file_put_contents(ROOT_PATH."/log/site_view_topo_process.$current_time.log",json_encode($newViewLinkArr)."\n",FILE_APPEND);
            }
        }
        sleep(2);
        file_put_contents(ROOT_PATH."/log/site_view_topo_process.$current_time.log",json_encode($newViewLinkArr)."\n",FILE_APPEND);
        //处理完 清理旧数据
        $retFromViewLinkDelS1 = mongoBaseDao::mongoDel('sotn.view-link-col',mongoBaseDao::mongoUser35,
            mongoBaseDao::mongoPasswd35, mongoBaseDao::mongoIp35,$filterS2,array());
        sleep(1);
        $retFromViewLinkDelS2 = mongoBaseDao::mongoDel('sotn.view-link-col',mongoBaseDao::mongoUser30,
            mongoBaseDao::mongoPasswd30, mongoBaseDao::mongoIp30,$filterS1,array());
        if ($retFromViewLinkDelS1 && $retFromViewLinkDelS2 ) {
            echo "=============== del success ============".json_encode($filterS1).json_encode($filterS2).PHP_EOL;
        }else{
            echo "=============== del failed ============".json_encode($filterS1).json_encode($filterS2).PHP_EOL;
        }

        return true;
    }


    /**
     * @throws \MongoDB\Driver\Exception\Exception
     */
    function siteNodeProcess() {
        $current_time = date("Ymd_Hsm");
        //鹅埠站点-- site-node-col 华南1 2 merge
        $filterS1 = array(
            'siteId' => self::$siteIdA30
        );
        $siteNodeInfoS1Info = mongoBaseDao::mongoDbQuery("sotn.site-node-col", self::$_mongoUser30,
            self::$_mongoPasswd30,self::$_mongoIp30,$filterS1,array());
        foreach ($siteNodeInfoS1Info as $value) {
            $oldSiteInfo = json_encode($value);
            $newSiteInfo = str_replace(self::$siteIdA30, self::$siteIdGdEb, $oldSiteInfo);
            echo PHP_EOL;
            $siteNodeInfoS1New = json_decode($newSiteInfo,true);
        }

        $filterS2 = array(
            'siteId' => self::$siteIdA35
        );
        $siteNodeInfoS2Info = mongoBaseDao::mongoDbQuery("sotn.site-node-col",self::$_mongoUser35,
            self::$_mongoPasswd35,self::$_mongoIp35,$filterS2,array());
        foreach ($siteNodeInfoS2Info as $value) {
            $oldSiteInfoS2 = json_encode($value);
            $newSiteInfoS2 = str_replace(self::$siteIdA35,self::$siteIdGdEb,$oldSiteInfoS2);
            $siteNodeInfoS2New = json_decode($newSiteInfoS2,true);
        }
        $supportingRackS1 = $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'];
        $supportingRackS2 = $siteNodeInfoS2New['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'];
        $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'] = array_merge($supportingRackS1,$supportingRackS2);

        $supportingNodeS1 = $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['supporting-node'];
        $supportingNodeS2 = $siteNodeInfoS2New['data']['network-topology']['topology'][0]['node'][0]['supporting-node'];
        $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['supporting-node'] = array_merge($supportingNodeS1,$supportingNodeS2);

        $terminationPointS1 = $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['termination-point'];
        $terminationPointS2 = $siteNodeInfoS2New['data']['network-topology']['topology'][0]['node'][0]['termination-point'];
        $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['termination-point'] = array_merge($terminationPointS1,$terminationPointS2);
        $siteNodeMergedNew = $siteNodeInfoS1New;

        $filterEb = array(
            'siteId' => self::$siteIdGdEb
        );
        $cursor = mongoBaseDao::mongoDbQuery('sotn.site-node-col',self::$_mongoUser,self::$_mongoPasswd,
            self::$_mongoIp,$filterEb,array());
        $arr = Util::object2array($cursor);
        foreach ($cursor as $siteInfo) {
            $arr = Util::object2array($siteInfo);
        }
        $siteNodeMergedNew = array_merge($arr,$siteNodeMergedNew);
        sleep(2);
        try {//合成三方数据后 merge回去
            if ($siteNodeMergedNew != null || count($siteNodeMergedNew)>0 ) {
                unset($siteNodeMergedNew['_id']);
                $retFromMongo = mongoBaseDao::mongoUpdate('sotn.site-node-col',self::$_mongoUser,
                    self::$_mongoPasswd,self::$_mongoIp,$filterEb,$siteNodeMergedNew);
                if ($retFromMongo->getModifiedCount() != 1) {
                    echo "=============== add failed site-view-topo ============".PHP_EOL;
                    echo 'add failed viewLinkId is :'.$siteNodeMergedNew."\n";
                    echo "=============== add failed site-view-topo ============".PHP_EOL;
                    file_put_contents(ROOT_PATH."/log/site_topo_process.$current_time.log",$siteNodeMergedNew."\n",FILE_APPEND);
                }
            }
        }catch (\MongoDB\Driver\Exception\BulkWriteException  $bulkWriteException) {
            echo "exception when insert data into mongodb and errorMsg is -> " . $bulkWriteException->getMessage() . PHP_EOL;
            return false;
        }
        sleep(1);
        //处理完 清理旧数据
        $retFromViewLinkDelS1 = mongoBaseDao::mongoDel('sotn.site-node-col',self::$_mongoUser35,
            self::$_mongoPasswd35, self::$_mongoIp35,$filterS2,array());
        $retFromViewLinkDelS2 = mongoBaseDao::mongoDel('sotn.site-node-col',self::$_mongoUser30,
            self::$_mongoPasswd30, self::$_mongoIp30,$filterS1,array());
        if ($retFromViewLinkDelS1 && $retFromViewLinkDelS2 ) {
            echo "=============== del success ============".PHP_EOL;
        }else{
            echo "=============== del failed ============".json_encode($filterS1).json_encode($filterS2).PHP_EOL;
        }


        //滨海腾大站点-- site-node-col 华南1 2 merge
        $filterS1 = array(
            'siteId' => self::$siteIdB30
        );
        $siteNodeInfoS1Info = mongoBaseDao::mongoDbQuery("sotn.site-node-col", self::$_mongoUser30,
            self::$_mongoPasswd30,self::$_mongoIp30,$filterS1,array());
        foreach ($siteNodeInfoS1Info as $value) {
            $oldSiteInfo = json_encode($value);
            $newSiteInfo = str_replace(self::$siteIdB30, self::$siteIdGdTd, $oldSiteInfo);
            $siteNodeInfoS1New = json_decode($newSiteInfo,true);
        }

        $filterS2 = array(
            'siteId' => self::$siteIdB35
        );
        $siteNodeInfoS2Info = mongoBaseDao::mongoDbQuery("sotn.site-node-col",self::$_mongoUser35,
            self::$_mongoPasswd35,self::$_mongoIp35,$filterS2,array());
        foreach ($siteNodeInfoS2Info as $value) {
            $oldSiteInfoS2 = json_encode($value);
            $newSiteInfoS2 = str_replace(self::$siteIdB35,self::$siteIdGdTd,$oldSiteInfoS2);
            $siteNodeInfoS2New = json_decode($newSiteInfoS2,true);
        }
        $supportingRackS1 = $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'];
        $supportingRackS2 = $siteNodeInfoS2New['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'];
        $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'] = array_merge($supportingRackS1,$supportingRackS2);

        $supportingNodeS1 = $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['supporting-node'];
        $supportingNodeS2 = $siteNodeInfoS2New['data']['network-topology']['topology'][0]['node'][0]['supporting-node'];
        $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['supporting-node'] = array_merge($supportingNodeS1,$supportingNodeS2);

        $terminationPointS1 = $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['termination-point'];
        $terminationPointS2 = $siteNodeInfoS2New['data']['network-topology']['topology'][0]['node'][0]['termination-point'];
        $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['termination-point'] = array_merge($terminationPointS1,$terminationPointS2);
        $siteNodeMergedNew = $siteNodeInfoS1New;

        $filterTD = array(
            'siteId' => self::$siteIdGdTd
        );
        $cursor = mongoBaseDao::mongoDbQuery('sotn.site-node-col',self::$_mongoUser,self::$_mongoPasswd,
            self::$_mongoIp,$filterTD,array());
        $arr = Util::object2array($cursor);
        foreach ($cursor as $siteInfo) {
            $arr = Util::object2array($siteInfo);
        }
        $supportingRackS1 = $siteNodeMergedNew['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'];
        $supportingRackS2 = $arr['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'];
        $siteNodeMergedNew['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'] = array_merge($supportingRackS1,$supportingRackS2);

        $supportingNodeS1 = $siteNodeMergedNew['data']['network-topology']['topology'][0]['node'][0]['supporting-node'];
        $supportingNodeS2 = $arr['data']['network-topology']['topology'][0]['node'][0]['supporting-node'];
        $siteNodeMergedNew['data']['network-topology']['topology'][0]['node'][0]['supporting-node'] = array_merge($supportingNodeS1,$supportingNodeS2);

        $terminationPointS1 = $siteNodeMergedNew['data']['network-topology']['topology'][0]['node'][0]['termination-point'];
        $terminationPointS2 = $arr['data']['network-topology']['topology'][0]['node'][0]['termination-point'];
        $siteNodeMergedNew['data']['network-topology']['topology'][0]['node'][0]['termination-point'] = array_merge($terminationPointS1,$terminationPointS2);
        sleep(1);
        try {//合成三方数据后 merge回去
            if ($siteNodeMergedNew != null || count($siteNodeMergedNew)>0 ) {
                unset($siteNodeMergedNew['_id']);
                $retFromMongo = mongoBaseDao::mongoUpdate('sotn.site-node-col',self::$_mongoUser,
                    self::$_mongoPasswd,self::$_mongoIp,$filterTD,$siteNodeMergedNew);
                if ($retFromMongo->getModifiedCount() != 1) {
                    echo "=============== add failed site-view-topo ============".PHP_EOL;
                    echo 'add failed viewLinkId is :'.$siteNodeMergedNew."\n";
                    echo "=============== add failed site-view-topo ============".PHP_EOL;
                    file_put_contents(ROOT_PATH."/log/site_topo_process.$current_time.log",$siteNodeMergedNew."\n",FILE_APPEND);
                }
            }
        }catch (\MongoDB\Driver\Exception\BulkWriteException  $bulkWriteException) {
            echo "exception when insert data into mongodb and errorMsg is -> " . $bulkWriteException->getMessage() . PHP_EOL;
            return false;
        }
        sleep(1);
        //处理完 清理旧数据
        $retFromViewLinkDelS1 = mongoBaseDao::mongoDel('sotn.site-node-col',self::$_mongoUser35,
            self::$_mongoPasswd35, self::$_mongoIp35,$filterS2,array());
        sleep(1);
        $retFromViewLinkDelS2 = mongoBaseDao::mongoDel('sotn.site-node-col',self::$_mongoUser30,
            self::$_mongoPasswd30, self::$_mongoIp30,$filterS1,array());
        if ($retFromViewLinkDelS1 && $retFromViewLinkDelS2 ) {
            echo "=============== del success ============".PHP_EOL;
        }else{
            echo "=============== del falied ============".json_encode($filterS1).json_encode($filterS2).PHP_EOL;
        }


        //腾大站点-- site-node-col 华南1 2 merge
        $filterS1 = array(
            'siteId' => self::$siteIdC30
        );
        $siteNodeInfoS1Info = mongoBaseDao::mongoDbQuery("sotn.site-node-col", self::$_mongoUser30,
            self::$_mongoPasswd30,self::$_mongoIp30,$filterS1,array());
        foreach ($siteNodeInfoS1Info as $value) {
            $oldSiteInfo = json_encode($value);
            $newSiteInfo = str_replace(self::$siteIdC30, self::$siteIdGdDso, $oldSiteInfo);
            $siteNodeInfoS1New = json_decode($newSiteInfo,true);
        }

        $filterS2 = array(
            'siteId' => self::$siteIdC35
        );
        $siteNodeInfoS2Info = mongoBaseDao::mongoDbQuery("sotn.site-node-col",self::$_mongoUser35,
            self::$_mongoPasswd35,self::$_mongoIp35,$filterS2,array());
        foreach ($siteNodeInfoS2Info as $value) {
            $oldSiteInfoS2 = json_encode($value);
            $newSiteInfoS2 = str_replace(self::$siteIdC35,self::$siteIdGdDso,$oldSiteInfoS2);
            $siteNodeInfoS2New = json_decode($newSiteInfoS2,true);
        }
        $supportingRackS1 = $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'];
        $supportingRackS2 = $siteNodeInfoS2New['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'];
        $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'] = array_merge($supportingRackS1,$supportingRackS2);

        $supportingNodeS1 = $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['supporting-node'];
        $supportingNodeS2 = $siteNodeInfoS2New['data']['network-topology']['topology'][0]['node'][0]['supporting-node'];
        $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['supporting-node'] = array_merge($supportingNodeS1,$supportingNodeS2);

        $terminationPointS1 = $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['termination-point'];
        $terminationPointS2 = $siteNodeInfoS2New['data']['network-topology']['topology'][0]['node'][0]['termination-point'];
        $siteNodeInfoS1New['data']['network-topology']['topology'][0]['node'][0]['termination-point'] = array_merge($terminationPointS1,$terminationPointS2);
        $siteNodeMergedNew = $siteNodeInfoS1New;

        $filterDso = array(
            'siteId' => self::$siteIdGdDso
        );
        $cursor = mongoBaseDao::mongoDbQuery('sotn.site-node-col',self::$_mongoUser,self::$_mongoPasswd,
            self::$_mongoIp,$filterDso,array());
        $arr = Util::object2array($cursor);
        foreach ($cursor as $siteInfo) {
            $arr = Util::object2array($siteInfo);
        }
        $supportingRackS1 = $siteNodeMergedNew['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'];
        $supportingRackS2 = $arr['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'];
        $siteNodeMergedNew['data']['network-topology']['topology'][0]['node'][0]['site-topology:site']['supporting-rack'] = array_merge($supportingRackS1,$supportingRackS2);

        $supportingNodeS1 = $siteNodeMergedNew['data']['network-topology']['topology'][0]['node'][0]['supporting-node'];
        $supportingNodeS2 = $arr['data']['network-topology']['topology'][0]['node'][0]['supporting-node'];
        $siteNodeMergedNew['data']['network-topology']['topology'][0]['node'][0]['supporting-node'] = array_merge($supportingNodeS1,$supportingNodeS2);

        $terminationPointS1 = $siteNodeMergedNew['data']['network-topology']['topology'][0]['node'][0]['termination-point'];
        $terminationPointS2 = $arr['data']['network-topology']['topology'][0]['node'][0]['termination-point'];
        $siteNodeMergedNew['data']['network-topology']['topology'][0]['node'][0]['termination-point'] = array_merge($terminationPointS1,$terminationPointS2);
        sleep(1);
        try {//合成三方数据后 merge回去
            if ($siteNodeMergedNew != null || count($siteNodeMergedNew)>0 ) {
                unset($siteNodeMergedNew['_id']);
                $retFromMongo = mongoBaseDao::mongoUpdate('sotn.site-node-col',self::$_mongoUser,
                    self::$_mongoPasswd,self::$_mongoIp,$filterDso,$siteNodeMergedNew);
                if ($retFromMongo->getModifiedCount() != 1) {
                    echo "=============== add failed site-view-topo ============".PHP_EOL;
                    echo 'add failed viewLinkId is :'.$siteNodeMergedNew."\n";
                    echo "=============== add failed site-view-topo ============".PHP_EOL;
                    file_put_contents(ROOT_PATH."/log/site_topo_process.$current_time.log",$siteNodeMergedNew."\n",FILE_APPEND);
                }
            }
        }catch (\MongoDB\Driver\Exception\BulkWriteException  $bulkWriteException) {
            echo "exception when insert data into mongodb and errorMsg is -> " . $bulkWriteException->getMessage() . PHP_EOL;
            return false;
        }
        sleep(1);
        //处理完 清理旧数据
        $retFromViewLinkDelS1 = mongoBaseDao::mongoDel('sotn.site-node-col',self::$_mongoUser35,
            self::$_mongoPasswd35, self::$_mongoIp35,$filterS2,array());
        sleep(1);
        $retFromViewLinkDelS2 = mongoBaseDao::mongoDel('sotn.site-node-col',self::$_mongoUser30,
            self::$_mongoPasswd30, self::$_mongoIp30,$filterS1,array());
        if ($retFromViewLinkDelS1 && $retFromViewLinkDelS2 ) {
            echo "=============== del success ============".PHP_EOL;
        }else{
            echo "=============== del failed ============".json_encode($filterS1).json_encode($filterS2).PHP_EOL;
        }

        //处理ILA站点
        foreach (self::$siteNodeIlaListS1 as $siteNodeInfo) {//华南1
            try {
                $filter = array(
                    'siteId' => $siteNodeInfo['siteNodeId'],
                );
                $cursor = mongoBaseDao::mongoDbQuery('sotn.site-node-col',self::$_mongoUser30,
                self::$_mongoPasswd30,self::$_mongoIp30,$filter,array());
                foreach ($cursor as $document) {
                    $retFromViewLinkIn = mongoBaseDao::mongoInsert('sotn.site-node-col',self::$_mongoUser,
                        self::$_mongoPasswd,self::$_mongoIp,$document);
                    echo $siteNodeInfo['siteNodeId'].PHP_EOL;
                    if ($retFromViewLinkIn != 1) {
                        echo "=============== add failed site-view-topo ============".PHP_EOL;
                        echo 'add failed viewLinkId is :'.$cursor."\n";
                        echo "=============== add failed site-view-topo ============".PHP_EOL;
                        file_put_contents(ROOT_PATH."/log/site_view_topo_process.$current_time.log",$cursor."\n",FILE_APPEND);
                    }
                }

                $retFromViewLinkDelS1 = mongoBaseDao::mongoDel('sotn.site-node-col',self::$_mongoUser30,
                    self::$_mongoPasswd30, self::$_mongoIp30,$filter,array());
                if ($retFromViewLinkDelS1) {
                    echo "=============== del success ============".json_encode($filter).PHP_EOL;
                }else{
                    echo "=============== del failed ============".json_encode($filter).PHP_EOL;
                }
            }catch (\MongoDB\Driver\Exception\BulkWriteException $exception) {
                echo "exception when insert data into mongodb and errorMsg is -> ".$exception->getMessage().PHP_EOL;
                return false;
            }
        }

        foreach (self::$siteNodeIlaListS2 as $siteNodeInfo) {//华南2
            try {
                $filter = array(
                    'siteId' => $siteNodeInfo['siteNodeId'],
                );
                $cursor = mongoBaseDao::mongoDbQuery('sotn.site-node-col',self::$_mongoUser35,
                    self::$_mongoPasswd35,self::$_mongoIp35,$filter,array());
                foreach ($cursor as $document) {
                    $retFromViewLinkIn = mongoBaseDao::mongoInsert('sotn.site-node-col',self::$_mongoUser,
                        self::$_mongoPasswd,self::$_mongoIp,$document);
                    echo $siteNodeInfo['siteNodeId'].PHP_EOL;
                    if ($retFromViewLinkIn != 1) {
                        echo "=============== add failed site-view-topo ============".PHP_EOL;
                        echo 'add failed viewLinkId is :'.$cursor."\n";
                        echo "=============== add failed site-view-topo ============".PHP_EOL;
                        file_put_contents(ROOT_PATH."/log/site_topo_process.$current_time.log",$cursor."\n",FILE_APPEND);
                    }
                }
                $retFromViewLinkDelS2 = mongoBaseDao::mongoDel('sotn.site-node-col',self::$_mongoUser35,
                    self::$_mongoPasswd35, self::$_mongoIp35,$filter,array());
                if ($retFromViewLinkDelS2) {
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


    /**
     * @return bool
     * @throws \MongoDB\Driver\Exception\Exception
     */
    function siteLinkProcess() {
        $current_time = date("Ymd_Hsm");
        //华南1 滨海腾大--鹅埠OMS
        $filterOms = array(
            'siteLinkId' => initOmsInfo::OMSID_BH2EB
        );
        $cursor = mongoBaseDao::mongoDbQuery('sotn.site-link-col',self::$_mongoUser30,self::$_mongoPasswd30,
            self::$_mongoIp30,$filterOms,array());
        foreach ($cursor as $document) {
            $oldSiteInfoS1 = json_encode($document);
            $newSiteInfoS1 = str_replace(self::$siteIdA30, self::$siteIdGdEb, $oldSiteInfoS1);
            $newSiteInfoS1 = str_replace(self::$siteIdB30, self::$siteIdGdTd, $newSiteInfoS1);
            $siteLinkInfoS2New = json_decode($newSiteInfoS1, true);
        }
        if ($siteLinkInfoS2New != null || count($siteLinkInfoS2New) >0 ) {
            unset($siteLinkInfoS2New['_id']);
            $retFromViewLinkIn = mongoBaseDao::mongoInsert('sotn.site-link-col',self::$_mongoUser,
                self::$_mongoPasswd,self::$_mongoIp,$siteLinkInfoS2New);
            if ($retFromViewLinkIn != 1) {
                echo "=============== add failed site-topo ============".PHP_EOL;
                echo 'add failed viewLinkId is :'.$siteLinkInfoS2New."\n";
                echo "=============== add failed site-topo ============".PHP_EOL;
                file_put_contents(ROOT_PATH."/log/site_topo_process.$current_time.log",$siteLinkInfoS2New."\n",FILE_APPEND);
            }
        }
        sleep(1);
        $retFromLinkDelS1 = mongoBaseDao::mongoDel('sotn.site-link-col',self::$_mongoUser30,
            self::$_mongoPasswd30, self::$_mongoIp30,$filterOms,array());
        if ($retFromLinkDelS1) {
            echo "=============== del success ============".json_encode($filterOms).PHP_EOL;
        }else{
            echo "=============== del falied ============".json_encode($filterOms).PHP_EOL;
        }

        //华南1 腾大--鹅埠OMS
        $filterOms = array(
            'siteLinkId' => initOmsInfo::OMSID_TD2EB
        );
        $cursor = mongoBaseDao::mongoDbQuery('sotn.site-link-col',self::$_mongoUser30,self::$_mongoPasswd30,
            self::$_mongoIp30,$filterOms,array());
        foreach ($cursor as $document) {
            $oldSiteInfoS1 = json_encode($document);
            $newSiteInfoS1 = str_replace(self::$siteIdA30, self::$siteIdGdEb, $oldSiteInfoS1);
            $newSiteInfoS1 = str_replace(self::$siteIdC30, self::$siteIdGdDso, $newSiteInfoS1);
            $siteLinkInfoS2New = json_decode($newSiteInfoS1, true);
        }
        if ($siteLinkInfoS2New != null || count($siteLinkInfoS2New) >0 ) {
            unset($siteLinkInfoS2New['_id']);
            $retFromViewLinkIn = mongoBaseDao::mongoInsert('sotn.site-link-col',self::$_mongoUser,
                self::$_mongoPasswd,self::$_mongoIp,$siteLinkInfoS2New);
            if ($retFromViewLinkIn != 1) {
                echo "=============== add failed site-topo ============".PHP_EOL;
                echo 'add failed viewLinkId is :'.$siteLinkInfoS2New."\n";
                echo "=============== add failed site-topo ============".PHP_EOL;
                file_put_contents(ROOT_PATH."/log/site_topo_process.$current_time.log",$siteLinkInfoS2New."\n",FILE_APPEND);
            }
        }
        sleep(1);
        $retFromLinkDelS1 = mongoBaseDao::mongoDel('sotn.site-link-col',self::$_mongoUser30,
            self::$_mongoPasswd30, self::$_mongoIp30,$filterOms,array());
        if ($retFromLinkDelS1) {
            echo "=============== del success ============".json_encode($filterOms).PHP_EOL;
        }else{
            echo "=============== del falied ============".json_encode($filterOms).PHP_EOL;
        }

        //华南2 滨海腾大--鹅埠 OMS
        $filterOms = array(
            'siteLinkId' => initOmsInfo::OMSID_BH2EB_S2
        );
        $cursor = mongoBaseDao::mongoDbQuery('sotn.site-link-col',self::$_mongoUser35,self::$_mongoPasswd35,
            self::$_mongoIp35,$filterOms,array());
        foreach ($cursor as $document) {
            $oldSiteInfoS1 = json_encode($document);
            $newSiteInfoS1 = str_replace(self::$siteIdA35, self::$siteIdGdEb, $oldSiteInfoS1);
            $newSiteInfoS1 = str_replace(self::$siteIdB35, self::$siteIdGdTd, $newSiteInfoS1);
            $siteLinkInfoS2New = json_decode($newSiteInfoS1, true);
        }
        if ($siteLinkInfoS2New != null || count($siteLinkInfoS2New) >0 ) {
            unset($siteLinkInfoS2New['_id']);
            $retFromViewLinkIn = mongoBaseDao::mongoInsert('sotn.site-link-col',self::$_mongoUser,
                self::$_mongoPasswd,self::$_mongoIp,$siteLinkInfoS2New);
            if ($retFromViewLinkIn != 1) {
                echo "=============== add failed site-topo ============".PHP_EOL;
                echo 'add failed viewLinkId is :'.$siteLinkInfoS2New."\n";
                echo "=============== add failed site-topo ============".PHP_EOL;
                file_put_contents(ROOT_PATH."/log/site_topo_process.$current_time.log",$siteLinkInfoS2New."\n",FILE_APPEND);
            }
        }
        sleep(1);
        $retFromLinkDelS1 = mongoBaseDao::mongoDel('sotn.site-link-col',self::$_mongoUser35,
            self::$_mongoPasswd35, self::$_mongoIp35,$filterOms,array());
        if ($retFromLinkDelS1) {
            echo "=============== del success ============".json_encode($filterOms).PHP_EOL;
        }else{
            echo "=============== del falied ============".json_encode($filterOms).PHP_EOL;
        }

        //华南2 腾大--鹅埠 OMS
        $filterOms = array(
            'siteLinkId' => initOmsInfo::OMSID_TD2EB_S2
        );
        $cursor = mongoBaseDao::mongoDbQuery('sotn.site-link-col',self::$_mongoUser35,self::$_mongoPasswd35,
            self::$_mongoIp35,$filterOms,array());
        foreach ($cursor as $document) {
            $oldSiteInfoS1 = json_encode($document);
            $newSiteInfoS1 = str_replace(self::$siteIdA35, self::$siteIdGdEb, $oldSiteInfoS1);
            $newSiteInfoS1 = str_replace(self::$siteIdC35, self::$siteIdGdDso, $newSiteInfoS1);
            $siteLinkInfoS2New = json_decode($newSiteInfoS1, true);
        }
        if ($siteLinkInfoS2New != null || count($siteLinkInfoS2New) >0 ) {
            unset($siteLinkInfoS2New['_id']);
            $retFromViewLinkIn = mongoBaseDao::mongoInsert('sotn.site-link-col',self::$_mongoUser,
                self::$_mongoPasswd,self::$_mongoIp,$siteLinkInfoS2New);
            if ($retFromViewLinkIn != 1) {
                echo "=============== add failed site-topo ============".PHP_EOL;
                echo 'add failed viewLinkId is :'.$siteLinkInfoS2New."\n";
                echo "=============== add failed site-topo ============".PHP_EOL;
                file_put_contents(ROOT_PATH."/log/site_topo_process.$current_time.log",$siteLinkInfoS2New."\n",FILE_APPEND);
            }
        }
        sleep(1);
        $retFromLinkDelS1 = mongoBaseDao::mongoDel('sotn.site-link-col',self::$_mongoUser35,
            self::$_mongoPasswd35, self::$_mongoIp35,$filterOms,array());
        if ($retFromLinkDelS1) {
            echo "=============== del success ============".json_encode($filterOms).PHP_EOL;
        }else{
            echo "=============== del failed ============".json_encode($filterOms).PHP_EOL;
        }

        return true;
    }


    /**
     * @return array
     */
    function buildSiteViewLink($siteAId, $siteBId) {
        $site_view_link_list = array(
            self::$source_site_id."---".self::$dest_siteA_id,
            self::$source_site_id."---".self::$dest_siteB_id,
        );
        return $site_view_link_list;
    }

    /**
     *
     */
    function initMongoCon() {
        self::$_mongoIp = getenv('mongoIpNorthChina2');
        self::$_mongoUser = getenv('mongoUserNorthChina2');
        self::$_mongoPasswd = getenv('mongoPasswdNorthChina2');
        self::$_controllerPasswd = getenv('adminPasswd');
    }

}

$siteTopoTrans = new siteTopoTrans();
$siteTopoTrans->run();



















