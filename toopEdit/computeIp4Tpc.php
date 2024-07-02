<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 *
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2020/7/9
 * Time: 2:55 PM
 * Brief: 分配IP /25段地址 for TPC4
 */
define ( 'ROOT_PATH', dirname ( __FILE__ ) );

class computeIp4Tpc {

    const DATABASE_NAME = "toop";
    private static $tableName = 'tpc_splitting_pool';
    private static $regionA = "华南";
    private static $setA = "平面一";
    private static $regionB = "华北";
    private static $setB = "平面二";
    private static $campus = "深汕-光明";

    /**
     * main入口
     */
    public static function run() {
        $arrParam = array(
            'start_ip' => "11.190.60.2",
            'end_ip' => "11.190.60.126",
            'campus' => self::$campus,
            'region' => self::$regionA,
            'set_name' => self::$setB,
        );
        self::getInfo();
//        self::splittingIp($arrParam);
    }

    public static function getInfo() {
        $conn = mysqli_connect("9.134.126.129:3306","root","123");
        mysqli_select_db($conn,self::DATABASE_NAME);
        $select_sql = "select count(*) as total from tpc_part_pool where campus = '' and region = '华南' and set_name = '平面二';";
        mysqli_query($conn,"SET NAMES 'UTF8'");
        $result = mysqli_query($conn,$select_sql);
        $row = mysqli_fetch_assoc($result);
        if (0 != count($row)) {
            $total = $row['total'];
        } else {
            return;
        }
        $start = 0;
        while ($total--) {
            $ip_prepare_compute = "select * from tpc_part_pool where campus = '' and region = '华南' and set_name = '平面二' limit $start,1;";
            $ret = mysqli_query($conn,$ip_prepare_compute);
            while ($row = mysqli_fetch_assoc($ret)) {
                $start_ip_long = ip2long($row['start_ip'])+1;
                $end_ip_long = ip2long($row['end_ip']);
                for ($i=$start_ip_long; $i<=$end_ip_long; $i++) {
                    $dbParam = array(
                        'nms_ip' => long2ip($i),
                        'create_time' => time(),
                        'region' => '华南',
                        'set_name' => '平面二',
                    );
                    self::save2Ip($dbParam);
                }
            }
            $start++;

        }
    }


    /**
     * @param $arrParam
     */
    public static function splittingIp($arrParam) {
        $start = explode('.',$arrParam['start_ip'])[3];
        $end = explode('.',$arrParam['end_ip'])[3];
        $count = ($end-$start)+1;
        $start_ip = $arrParam['start_ip'];
        for ($i=0; $i<$count; $i++) {
            $dbParams[] = array(
                'nms_ip' => $start_ip,
                'region' => $arrParam['region'],
                'set_name' => $arrParam['set_name'],
                'campus' => $arrParam['campus'],
            );
            $str_ip = ip2long($start_ip)+1;
            $start_ip = long2ip($str_ip);
        }
        self::save2Ip($dbParams);
    }

    /**
     * @param $arrParam
     * @return array|string
     */
    public static function computeSubStartIp($arrParam) {
        $start_ip = $arrParam['start_ip'];
        $mask= $arrParam['mask'];
        $sub_net = array();
        $arr_ip = explode('.',$start_ip);
        $mask_arr = explode('.',$mask);
        foreach ($arr_ip as $index => $ip_num) {
            //10进制转2进制
            $a = decbin($ip_num);
            $b = decbin($mask_arr[$index]);
            //补0补够8位
            $a = sprintf("%08d", $a);
            $b = sprintf("%04d", $b);//前补0
            $b = sprintf("%0.4f", $b);//后取多少位
            $c = $a & $b;
            $d = bindec($c);
            $sub_net[] = $d;
        }
        $sub_net = array_values($sub_net);
        $sub_net = implode('.', $sub_net);
        return $sub_net;
    }

    /**
     * @param $arrParams
     * @return array
     */
    static function save2Ip($arrParams) {
        $conn = mysqli_connect("9.134.126.129:3306","root","123");
//    $conn = mysqli_connect($sqlConfig['host'],$sqlConfig['user'],$sqlConfig['password']);     //线下db 需连vpn 绕过堡垒机
        while(!$conn) {
            $conn = mysqli_connect("9.134.126.129:3306","root","123");
//        $conn = mysqli_connect($sqlConfig['host'],$sqlConfig['user'],$sqlConfig['password']);
        }
        mysqli_select_db($conn,self::DATABASE_NAME);

        $region = $arrParams['region'];
        $set = $arrParams['set_name'];
//        $campus = $arrParams['campus'];
        $nms_ip = $arrParams['nms_ip'];
        $create_time = time();
        $table_name = self::$tableName;
        $insert_sql = "insert into $table_name (region,set_name,nms_ip,create_time) values ('$region','$set','$nms_ip',$create_time);";
        mysqli_query($conn,"SET NAMES 'UTF8'");
        $result = mysqli_query($conn,$insert_sql);
        $data = array();
        //写入日志并判断是否插入数据库成功
        if ($result) {
            echo 'add success :'.$insert_sql."\n";
            file_put_contents(ROOT_PATH."/tpc_info_success.log",$insert_sql."\n",FILE_APPEND);
        }else {
            echo 'add failed :'.$insert_sql."\n";
            file_put_contents(ROOT_PATH."/tpc_info_success.log",$insert_sql."\n",FILE_APPEND);
        }


        mysqli_close($conn);
        return $data;
    }

    /**
     * @param $db
     * @param $sql
     * @param $log
     * @param string $name
     * @return bool|mysqli_result
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
}

computeIp4Tpc::run();