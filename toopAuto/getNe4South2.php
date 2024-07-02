<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2021/1/27
 * Time: 4:58 PM
 * Brief: 计算华南2 设备版本信息 入库
 */

define ( 'ROOT_PATH', dirname ( __FILE__ ) );
ini_set ( "memory_limit", "-1" );

class getNe4South2 {

    const DATABASE_NAME = "toop";
    const collection = "phy-node-col";
    const REGION = "华南2";
//    private static $_mongoIp = "9.135.127.20:27017"; // toop-pilot
//    private static $_mongoUser = "mongouser";
//    private static $_mongoPasswd = "fCTuc*2856AH";
    //mongo
    private static $_mongoIp = ""; //toop-southchina-set2
    private static $_mongoUser = "";
    private static $_mongoPasswd = "";

    //mysql
    private static $_mysqlIp = "";
    private static $_mysqlUser = "";
    private static $_mysqlPort = "";
    private static $_mysqlPasswd = "";

    //controller
    private static $_controllerName = "admin";
    private static $_controllerPasswd = "";
    private static $_tableName = "ne_detail";



    /**
     * main入口
     */
    public function run() {

        //todo 更改地址 华南2
        self::$_mongoIp = getenv('mongoIpNorthChina1');
        self::$_mongoUser = getenv('mongoUserNorthChina1');
        self::$_mongoPasswd = getenv('mongoPasswdNorthChina1');
        self::$_controllerPasswd = getenv('adminPasswd');
        self::$_mysqlIp = getenv('mysqlIp');
        self::$_mysqlUser = getenv('mysqlUser');
        self::$_mysqlPasswd = getenv('mysqlPasswd');
        self::$_mysqlPort = getenv('mysqlPort');
//        $this->testCurl();
        $this->compatibleGetNeData2Db();
//        $this->getNeDataFromPhyNodeCol();
    }



    public function compatibleGetNeData2Db() {

        $url = "http://southchina2.toop.woa.com/restconf/operations/nms:get-phy-node-paged";
        $post_data = '{"input":{"topology-ref":"otn-phy-topology","start-pos":0,"how-many":10000,"sort-infos":[]}}';
        $headers = array(
            'Authorization: Basic dG9vcF9vcGVyYXRvcjpuaUo2VWRTMXNNclJBOHVnWFEyTzVWZVBMZkJ6RUtsYw==',
            'Content-Type: application/json',
            'Cookie: JSESSIONID=gnu93dxk9xbd1uc4exx17wk62; x-client-ssid=17f43913c77-81a18103ef4bd144fae348b9f20d982cda7d4cf1; x-host-key-front=17f43913c7f-e12dbe6a622dc02776774f21ea403eabc2d7467c; x-host-key-ngn=17f43913c77-0e331f29e2f24136fa11734300458f4afb798aed'
        );
        $result = $this->http_request_xml($url,$post_data,$headers);
        $result_arr = json_decode($result,true);
        for ($i=0; $i<count($result_arr['output']['node']); $i++) {
            if ($result_arr['output']['node'][$i]['physical']['implement-state'] == "implement") {
                $ne_yang_version = "";
                $software_version = "";
                $ne_detail = $result_arr['output']['node'][$i]['physical']['properties']['property'];
                foreach ($ne_detail as $index => $ne_info) {
                    switch ($ne_info['name']) {
                        case "ne.yang-version":
                            $ne_yang_version = $ne_info['value'];
                            break;
                        case "current-software":
                            $software_version = $ne_info['value'];
                            break;
                        case "collector-id":
                            $telemetry = $ne_info['value'];
                            break;
                        case "adapter-id":
                            $adapter = $ne_info['value'];
                            break;
                        default :
                            break;
                    }
                }

                $arrParams = array(
                    'ne_id' => $result_arr['output']['node'][$i]['node-id'],
                    'friendly_name' => $result_arr['output']['node'][$i]['physical']['friendly-name'],
                    'vendor_name' => $result_arr['output']['node'][$i]['physical']['vendor-name'],
                    'ne_ip' => $result_arr['output']['node'][$i]['physical']['ip'],
                    'vendor_type' => $result_arr['output']['node'][$i]['physical']['vendor-type'],
                    'node_type' => $result_arr['output']['node'][$i]['physical']['node-type'],
                    'risk_group_name' => $result_arr['output']['node'][$i]['physical']['risk-group-name'],
                    'zone' => $result_arr['output']['node'][$i]['physical']['zone'],
                    'campus' => $result_arr['output']['node'][$i]['physical']['campus'],
                    'ne_yang_version' => $ne_yang_version,
                    'software_version' => $software_version,
                    'implement_state' => $result_arr['output']['node'][$i]['physical']['implement-state'],
                    'telemetry' => $telemetry,
                    'adapter' => $adapter
                );

                $this->insertData2Db($arrParams);
            }
        }
    }

    /**
     * @throws \MongoDB\Driver\Exception\Exception
     */
    public function getNeDataFromPhyNodeCol() {

        $mongo_conn = new MongoDB\Driver\Manager("mongodb://".self::$_mongoUser.":".self::$_mongoPasswd."@".self::$_mongoIp);
        $filter = array();
        $options = array(
            'projection' => array(
                '_id' => 0,
            ),
        );

        $query = new MongoDB\Driver\Query($filter, $options);
        $cursor = $mongo_conn->executeQuery('sotn.phy-node-col', $query);
        if ($cursor == null || empty($cursor) ) {
            echo "unable get ne info from php-node-col"."\n";
            exit(1);
        }
        $count = 0;
        foreach ($cursor as $document) {
            $count++;
            $row = $this->object2array($document);
            $ne_id = $row['neId'];
            $slt_ne_id = $this->str_replace_multiple_consecutive('#', '%23', $ne_id);
            echo "当前处理第 $count 个ne，neID:=".$slt_ne_id."\n";
            echo PHP_EOL;
            //并行curl获取op树yang-version、software-version
            $curl_url = "curl -X GET --header 'Content-Type:application/json' --header 'Accept:application/json' -u ".self::$_controllerName.":".self::$_controllerPasswd." 'http://9.138.108.101/restconf/operational/network-topology:network-topology/topology/otn-phy-topology/node/$slt_ne_id'"; //toop-southchina1
            $result = shell_exec($curl_url);
            $arr_ret = json_decode($result,true);
            $ne_yang_version = "";
            $software_version = "";
            if (!empty($arr_ret) && $arr_ret != null) {

                $ne_detail = $arr_ret['node'][0]['otn-phy-topology:physical']['properties']['property'];
                foreach ($ne_detail as $index => $ne_info) {
                    if ("ne.yang-version" == $ne_info['name']) {
                        $ne_yang_version = $ne_info['value'];
                    }elseif ("software-version" == $ne_info['name']) {
                        $software_version = $ne_info['value'];
                    }
                }

                $arrParams = array(
                    'ne_id' => $ne_id,
                    'friendly_name' => $row['data']['network-topology']['topology'][0]['node'][0]['otn-phy-topology:physical']['friendly-name'],
                    'vendor_name' => $row['data']['network-topology']['topology'][0]['node'][0]['otn-phy-topology:physical']['vendor-name'],
                    'vendor_type' => $arr_ret['node'][0]['otn-phy-topology:physical']['vendor-type'],
                    'risk_group_name' => $row['data']['network-topology']['topology'][0]['node'][0]['otn-phy-topology:physical']['risk-group-name'],
                    'zone' => $row['data']['network-topology']['topology'][0]['node'][0]['otn-phy-topology:physical']['zone'],
                    'campus' => $row['data']['network-topology']['topology'][0]['node'][0]['otn-phy-topology:physical']['campus'],
                    'ne_yang_version' => $ne_yang_version,
                    'software_version' => $software_version,
                );
            }
            $this->insertData2Db($arrParams);
        }
    }

    function insertData2Db($arrParams) {
//        $conn = mysqli_connect("9.134.126.129:3306","root","123"); //线下db 需连vpn 绕过堡垒机
        $conn = mysqli_connect(self::$_mysqlIp,self::$_mysqlUser,self::$_mysqlPasswd,"toop",3306); //华南2mysql
        while(!$conn) {
//          $conn = mysqli_connect("9.134.126.129:3306","root","123");
            $conn = mysqli_connect(self::$_mysqlIp,self::$_mysqlUser,self::$_mysqlPasswd,"toop",3306); //华南2mysql
        }
//        mysqli_connect(self::DATABASE_NAME,$conn);
        $region = self::REGION;
        $campus = $arrParams['campus'];
        $zone = $arrParams['zone'];
        $risk_group_name = $arrParams['risk_group_name'];
        $ne_id = $arrParams['ne_id'];
        $friendly_name = $arrParams['friendly_name'];
        $vendor_name = $arrParams['vendor_name'];
        $ne_ip = $arrParams['ne_ip'];
        $vendor_type = $arrParams['vendor_type'];
        $node_type = $arrParams['node_type'];
        $ne_yang_version = $arrParams['ne_yang_version'];
        $software_version = $arrParams['software_version'];
        $create_time = time();
        $update_time = time();
        $implement_state = $arrParams['implement_state'];
        $telemetry = $arrParams['telemetry'];
        $adapter = $arrParams['adapter'];
        $table_name = self::$_tableName;
        $insert_sql = "insert into $table_name (region,zone,campus,risk_group_name,ne_id,friendly_name,vendor_name,ne_ip,vendor_type,node_type,ne_yang_version,software_version,create_time,update_time,implement_state,telemetry,adapter) values ('$region','$zone','$campus','$risk_group_name','$ne_id','$friendly_name','$vendor_name','$ne_ip','$vendor_type','$node_type','$ne_yang_version','$software_version',$create_time,$update_time,'$implement_state','$telemetry','$adapter');";
        mysqli_query($conn,"SET NAMES 'UTF8'");
        $result = mysqli_query($conn,$insert_sql);
        //写入日志并判断是否插入数据库成功
        $current_time = date("Ymd:h");
        if ($result) {
            echo "=============== add success ============".PHP_EOL;
            echo 'add success :'.$insert_sql."\n";
            echo "=============== done ============".PHP_EOL;
            file_put_contents(ROOT_PATH."/ne_info_success.$current_time.log",$insert_sql."\n",FILE_APPEND);
        }else {
            echo "=============== add failed ============".PHP_EOL;
            echo 'add failed :'.$insert_sql."\n";
            echo "=============== add failed ============".PHP_EOL;
            file_put_contents(ROOT_PATH."/ne_info_failed.$current_time.log",$insert_sql."\n",FILE_APPEND);
        }
        mysqli_close($conn);
    }

    /**
     * 对象转换为数组
     * @param  object $object 需要转换的对象
     * @return array          转换后的数组
     */

    function object2array($object) {
        $object =  json_decode( json_encode( $object),true);
        return  $object;
    }

    /**
     * @param $db
     * @param $sql
     * @param $log
     * @param string $name
     * @return bool|mysqli_result
     */
    function mysqlQuery($db, $sql, $log, $name = 'gbk') {
        try {
            $conn = mysqli_connect($db['server_name'], $db['username'], $db['password'], $db['database'], $db['port']);
            mysqli_set_charset($conn, $name);
            $result = mysqli_query($conn, $sql);
            mysqli_close($conn);
        } catch (Exception $e) {
            file_put_contents($log, "sql[" . $sql . "] errmsg[db error]\n", FILE_APPEND);
        }
        return $result;
    }

    public function testCurl() {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://9.138.108.101/restconf/operations/nms:get-phy-node-paged',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{"input":{"topology-ref":"otn-phy-topology","start-pos":0,"how-many":10000,"sort-infos":[]}}',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic YWRtaW46eWlqaW5nZGFpemFpV0FOU0hJWElBT1hJTkAyMDE5',
                'Content-Type: application/json',
                'Cookie: JSESSIONID=907t88xhiz9zp3us1c4qsnuj; x_host_key=1773d57ddd0-33490a95d22164158d81f756b5864759543dddf9'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        echo $response;
    }

    /**
     * @param $url
     * @param null $data
     * @param null $arr_header
     * @return mixed
     */
    private function http_request_xml($url,$data,$arr_header)  {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_CUSTOMREQUEST,'POST');
        // curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        // curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        if(!empty($arr_header)){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $arr_header);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        curl_close($curl);

        return $output;
    }

    /**
     * php将字符串中连续的某个字符替换为一个
     * @param string $search
     * @param string $replace
     * @param string $subject
     * @return string
     */
    function str_replace_multiple_consecutive($search, $replace, $subject) {
        return (string)preg_replace("/[" . $search . "]+/i", $replace, $subject);
    }
}
$run = new getNe4South2();
$run->run();