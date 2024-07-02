<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2021/11/10
 * Time: 5:37 PM
 * Brief: 还是手动改 不用代码实现 只是拿到id
 */
define ( 'ROOT_PATH', dirname ( __FILE__ ) );
ini_set ( "memory_limit", "-1" );

require_once "./lib/Util.php";
require_once "./lib/initOmsInfo.php";
require_once "./mongo/mongoBaseDao.php";

class baseTopoTrans {

    function run() {
        $this->filePathInit();
        $this->omsProcess();
        echo "生成新的复用段ID done".PHP_EOL;
        $str = file_get_contents(ROOT_PATH."/omsId.txt",true);
        var_dump($str);
        echo "\n";
    }

    function omsProcess () {
        $newBhTd2EbS1 = str_replace(initOmsInfo::siteIdA30,initOmsInfo::siteIdGdEb,
            initOmsInfo::OMSID_BH2EB);
        $newBhTd2EbS1 = str_replace(initOmsInfo::siteIdB30,initOmsInfo::siteIdGdTd,
            $newBhTd2EbS1);
        file_put_contents(ROOT_PATH."/omsId.txt",$newBhTd2EbS1."\n",FILE_APPEND);

        $newTd2EbS1 = str_replace(initOmsInfo::siteIdA30,initOmsInfo::siteIdGdEb,
            initOmsInfo::OMSID_TD2EB);
        $newTd2EbS1 = str_replace(initOmsInfo::siteIdC30,initOmsInfo::siteIdGdDso,
            $newTd2EbS1);
        file_put_contents(ROOT_PATH."/omsId.txt",$newTd2EbS1."\n",FILE_APPEND);

        $newBhTd2EbS2 = str_replace(initOmsInfo::siteIdA35,initOmsInfo::siteIdGdEb,
            initOmsInfo::OMSID_BH2EB_S2);
        $newBhTd2EbS2 = str_replace(initOmsInfo::siteIdB35,initOmsInfo::siteIdGdTd,
            $newBhTd2EbS2);
        file_put_contents(ROOT_PATH."/omsId.txt",$newBhTd2EbS2."\n",FILE_APPEND);

        $newTd2EbS2 = str_replace(initOmsInfo::siteIdA35,initOmsInfo::siteIdGdEb,
            initOmsInfo::OMSID_TD2EB_S2);
        $newTd2EbS2 = str_replace(initOmsInfo::siteIdC35,initOmsInfo::siteIdGdDso,
            $newTd2EbS2);
        file_put_contents(ROOT_PATH."/omsId.txt",$newTd2EbS2."\n",FILE_APPEND);

//        $cursor = mongoBaseDao::mongoDbQuery('sotn.topo-col',mongoBaseDao::mongoUser,mongoBaseDao::mongoPasswd,
//            mongoBaseDao::mongoIp,array(),array());
//        foreach ($cursor as $document) {
//            $row = Util::object2array($document);
//
//        }

    }

    /**
     *
     */
    function filePathInit() {
        $ret1 = mkdir(iconv("UTF-8","GBK",ROOT_PATH."/tunnel"),0777,true);
        $ret2 = mkdir(iconv("UTF-8","GBK",ROOT_PATH."/ochLink"),0777,true);
        $ret3 = mkdir(iconv("UTF-8","GBK",ROOT_PATH."/log"),0777,true);
        if ($ret1 && $ret2 && $ret3) {
            echo "tunnel && ochLink && log 目录创建成功".PHP_EOL;
        }else {
            echo "目录 已存在".PHP_EOL;
        }
    }
}
$baseTopo = new baseTopoTrans();
$baseTopo->run();