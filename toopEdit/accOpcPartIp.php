<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/



/**
 * Author: quanweizhou@tencent.com
 * Date: 2020/7/10
 * Time: 10:13 AM
 * Brief: opc /26段Ip池计算入库
 */
require_once 'vendor/autoload.php';
define ( 'ROOT_PATH', dirname ( __FILE__ ) );

class accOpcPartIp {

    const DATABASE_NAME = "toop";
    private static $tableName = 'opc_part_pool';
    private static $regionA = "华南";
    private static $setA = "平面一";
    private static $regionB = "华北";
    private static $setB = "平面二";
    private static $campus = "广州-华新园";

    /**
     * main入口
     */
    public static function run() {
        self::getExcelInfo();
    }

    /**
     *
     */
    public static function getExcelInfo() {
        $templateName = dirname(__FILE__).'/ft_local/info.csv';
        try {
            $read = new PHPExcel_Reader_CSV();
            $objPHPExcel = $read->load($templateName);
            $objWorksheet = $objPHPExcel->getActiveSheet();
            $highestRow = $objWorksheet->getHighestRow();//取得总行数
            $highestColumn = $objWorksheet->getHighestColumn(); //取得总列数
            for($row=1; $row<=$highestRow; $row++) {//从第一行开始读取数据
                $rowData = $objWorksheet->rangeToArray('A' . $row . ':' . $highestColumn . $row, NULL, TRUE, FALSE);
                $dbParam = array(
                    'region' => $rowData[0][0],
                    'set_name' => $rowData[0][1],
                    'oms_id' => $rowData[0][2],
                    'network_ip' => $rowData[0][4],
                    'start_ip' => $rowData[0][5],
                    'end_ip' => $rowData[0][6],
                    'broadcast_ip' => $rowData[0][7],
                );
                self::save2Ip($dbParam);
            }
        }catch (Exception $e) {
            $e->getMessage();
        }
    }

    /**
     * @param $arrParams
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
        $oms_id = $arrParams['oms_id'];
        $network_ip = $arrParams['network_ip'];
        $start_ip = $arrParams['start_ip'];
        $end_ip = $arrParams['end_ip'];
        $broadcast_ip = $arrParams['broadcast_ip'];
        $create_time = time();
        $table_name = self::$tableName;
        $insert_sql = "insert into $table_name (region,set_name,oms_id,network_ip,start_ip,end_ip,broadcast_ip,create_time) values ('$region','$set','$oms_id','$network_ip','$start_ip','$end_ip','$broadcast_ip',$create_time);";
        mysqli_query($conn,"SET NAMES 'UTF8'");
        $result = mysqli_query($conn,$insert_sql);
        //写入日志并判断是否插入数据库成功
        if ($result) {
            echo 'add success :'.$insert_sql."\n";
            file_put_contents(ROOT_PATH."/opc_info_success.log",$insert_sql."\n",FILE_APPEND);
        }else {
            echo 'add failed :'.$insert_sql."\n";
            file_put_contents(ROOT_PATH."/opc_info_failed.log",$insert_sql."\n",FILE_APPEND);
        }
        mysqli_close($conn);
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

accOpcPartIp::run();