<?php


define ( 'ROOT_PATH', dirname ( __FILE__ ) );

class opcSplitIp {

    const DATABASE_NAME = "toop";
    private static $tableName = 'opc_splitting_pool';
    private static $regionA = "华南";
    private static $setA = "平面一";
    private static $regionB = "华北";
    private static $setB = "平面二";
    private static $campus = "深汕-光明";

    /**
     * main入口
     */
    public static function run() {
        self::getInfo();
    }

    public static function getInfo() {
        $conn = mysqli_connect("9.134.126.129:3306","root","123");
        mysqli_select_db($conn,self::DATABASE_NAME);
        $select_sql = "select count(*) as total from opc_part_pool where region = '华南' and set_name = '平面一';";
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
            $ip_prepare_compute = "select * from opc_part_pool where region = '华南' and set_name = '平面一' limit $start,1;";
            $ret = mysqli_query($conn,$ip_prepare_compute);
            while ($row = mysqli_fetch_assoc($ret)) {
                $network_ip_long = ip2long($row['network_ip']);
                $broadcast_ip_long = ip2long($row['broadcast_ip']);
                $ip_total = ($broadcast_ip_long-$network_ip_long)+1;
                $group = intval($ip_total/4);
                
                for ($i=$network_ip_long; $i<=$broadcast_ip_long; $i++) {
                    $dbParam = array(
                        'nms_ip' => long2ip($i),
                        'create_time' => time(),
                        'region' => '华南',
                        'set_name' => '平面二',
                    );
//                    self::save2Ip($dbParam);
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
        var_dump($insert_sql);die();
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
}
opcSplitIp::run();
