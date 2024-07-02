<?php

define ( 'ROOT_PATH', dirname ( __FILE__ ) );
ini_set ( "memory_limit", "-1" );

require_once "../toopTransfer/lib/Util.php";
require_once "../toopTransfer/mongo/mongoBaseDao.php";

class amDeadLock {

    CONST mongoUser = "root";
    CONST mongoIp = "10.0.0.33:27017";
    CONST mongoPasswd = "oPenlab@2020";

    private static $positiveThresHold = "";
    private static $reverseThresHold = "";

    function run() {

        $current_time = date("Ymd_Hsm");
        fwrite(STDOUT,"请输入需要解锁的LinkId：");
        $linkId = trim(fgets(STDIN));
        if ($linkId == null || empty($linkId)) {
            echo "linkId 不为输入为空".PHP_EOL;
            exit(1);
        }

        fwrite(STDOUT, "请输入A-Z方向的阈值");
        self::$positiveThresHold = trim(fgets(STDIN));
        fwrite(STDOUT, "请输入Z-A方向的阈值");
        self::$reverseThresHold = trim(fgets(STDIN));

        if (self::$positiveThresHold != null) {
            $filer = array(
                'link-id' => $linkId
            );
            $amInfo = mongoBaseDao::mongoDbQuery('toop_am.threshold',self::mongoUser,self::mongoPasswd,
                self::mongoIp,$filer,array());
            foreach ($amInfo as $document) {
                $thresholdInfoNew = array();
                $thresholdInfo = Util::object2array($document);
                $arrThres = $thresholdInfo['thresholds'];
                for ($i = count($arrThres)-1; $i > 0; $i--) {
                    $thresholdInfoNew = $arrThres[$i];
                }
            }
            self::$reverseThresHold = $thresholdInfoNew['reverse-threshold'];
            $del = mongoBaseDao::mongoDel('toop_am.threshold',self::mongoUser,self::mongoPasswd,
                self::mongoIp,$filer,array());
            if (!$del) {
                echo "del failed link id is ".$linkId.PHP_EOL;
                exit(1);
            }

            for ($i=0; $i<10; $i++) {
                $thresholds[] = array(
                    'timestamp' => time(),
                    'positive-threshold' => intval(self::$positiveThresHold),
                    'reverse-threshold' => self::$reverseThresHold,
                );
            }
            $newThreshold = array(
                'link-id' => $linkId,
                'thresholds' => $thresholds
            );
            $retInsert = mongoBaseDao::mongoInsert('toop_am.threshold',self::mongoUser,self::mongoPasswd,
                self::mongoIp,$newThreshold);
            if ($retInsert != 1) {
                echo "=============== add failed ============".PHP_EOL;
                echo 'add failed phy node is :'.$newThreshold."\n";
                echo "=============== add failed ============".PHP_EOL;
                file_put_contents(ROOT_PATH."/am_process.$current_time.log",json_encode($newThreshold)."\n",FILE_APPEND);
                return false;
            }else {
                echo "=============== add success ============".PHP_EOL;
                echo "link Id is ",$linkId.PHP_EOL;
                file_put_contents(ROOT_PATH."/am_process.$current_time.log",json_encode($newThreshold)."\n",FILE_APPEND);
            }
        }elseif (self::$reverseThresHold != null) {
            $filer = array(
                'link-id' => $linkId
            );
            $amInfo = mongoBaseDao::mongoDbQuery('toop_am.threshold',self::mongoUser,self::mongoPasswd,
                self::mongoIp,$filer,array());
            foreach ($amInfo as $document) {
                $thresholdInfoNew = array();
                $thresholdInfo = Util::object2array($document);
                $arrThres = $thresholdInfo['thresholds'];
                for ($i = count($arrThres)-1; $i > 0; $i--) {
                    $thresholdInfoNew = $arrThres[$i];
                }
            }
            self::$positiveThresHold = $thresholdInfoNew['positive-threshold'];
            $del = mongoBaseDao::mongoDel('toop_am.threshold',self::mongoUser,self::mongoPasswd,
                self::mongoIp,$filer,array());
            if (!$del) {
                echo "del failed link id is ".$linkId.PHP_EOL;
                exit(1);
            }

            for ($i=0; $i<10; $i++) {
                $thresholds[] = array(
                    'timestamp' => time(),
                    'positive-threshold' => self::$positiveThresHold,
                    'reverse-threshold' => intval(self::$reverseThresHold),
                );
            }
            $newThreshold = array(
                'link-id' => $linkId,
                'thresholds' => $thresholds
            );

            $retInsert = mongoBaseDao::mongoInsert('toop_am.threshold',self::mongoUser,self::mongoPasswd,
                self::mongoIp,$newThreshold);
            if ($retInsert != 1) {
                echo "=============== add failed ============".PHP_EOL;
                echo 'add failed phy node is :'.$newThreshold."\n";
                echo "=============== add failed ============".PHP_EOL;
                file_put_contents(ROOT_PATH."/am_process.$current_time.log",json_encode($newThreshold)."\n",FILE_APPEND);
                return false;
            }else {
                echo "=============== add success ============".PHP_EOL;
                echo "link Id is ",$linkId.PHP_EOL;
                file_put_contents(ROOT_PATH."/am_process.$current_time.log",json_encode($newThreshold)."\n",FILE_APPEND);
            }
        }
    }
}
$amDeadLock = new amDeadLock();
$amDeadLock->run();