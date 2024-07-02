<?php

Bd_Init::init();
Bd_LayerProxy::init(Bd_Conf::getConf('/layerproxy/'));
Bd_Log::trace('begin');
ini_set("memory_limit","2058m");
$_dataVideo =  new Service_Data_Video();
$_dataVideoList = new Service_Data_VideoList();
$_dataCategory = new Service_Data_Category();
//获取数据库配置
$db_conf = Bd_Conf::getConf('/db/cluster');
$db_conf_r = $db_conf['video'];

$mysql_r['server_name'] = $db_conf_r['host'][0]['ip'];
$mysql_r['port'] = $db_conf_r['host'][0]['port'];
$mysql_r['username'] = $db_conf_r['username'];
$mysql_r['password'] = $db_conf_r['password'];
$mysql_r['database'] = $db_conf_r['default_db'];

$total_select = 0;
$key_first_second = array();
$sub_class = array();

try {
    $conn = mysqli_connect($mysql_r['server_name'], $mysql_r['username'], $mysql_r['password'], $mysql_r['database'], $mysql_r['port']);

    if (false === $conn) {
        throw new Wenku_Error(Wenku_ErrorCodes::DB_CONNECTION_ERROR);
    }
    $conds = array(
        'level = 1',
        'status = 1',
    );

    $class_list = $_dataCategory->getHomeVideoCategory($conds);
    $index = 0;
    //子类数量小于3个不取数据
    foreach ($class_list as $key1 => $value) {
        $conds = array(
            'pid = ' => $value['cid'],
        );
        //子类数量小于3个不取数据
        $level1_has_level2 = $_dataCategory->getCateCount($conds);
        foreach ($level1_has_level2 as $index => $num) {
            $call_num = $num['count(cid)'];
            if ($call_num < 3) {
                unset($class_list[$key1]);
            }
        }
    }
    $new_cate_list = array_values($class_list);

    foreach ($new_cate_list as $cid_list => $category_list) {
        $videos = array();
        $conds = array(
            'pid = ' => $category_list['cid'],
        );
        //子类数量小于3个不取数据

        $sub_class = $_dataCategory->getHomeVideoCategory($conds);
        $count = 0;

        foreach ($sub_class as $value_info) {
            $count++;
            if ($count < 4) {
                $conds = array(
                    'cid2 = ' => $value_info['cid'],
                    'cid1 = ' => $category_list['cid'],
                    'status = 1',
                );

                $array = $_dataVideoList->getRedisVideo($rn, $conds);
                if ($array == null || count($array) < 0 || empty($array)) {
                    $index++;
                    $conds_callback = array(
                        'cid1 = ' => $category_list['cid'],
                        'status = 1',
                    );

                    $array = $_dataVideoList->getCallBackRedisVideo($index, $conds_callback);
                    if ($array == null || empty($array) || count($array) < 0) {
                        continue;
                    }else {
                        if (is_array($array)) {
                            $obj_ret = new StdClass();
                            foreach ($array as $key => $val) {
                                $obj_ret = $val;
                            }
                        }
                        array_push($videos, $obj_ret);
                    }
                } else {
                    if (is_array($array)) {
                        $obj_ret = new StdClass();
                        foreach ($array as $key => $val) {
                            $obj_ret = $val;
                        }
                    }
                    array_push($videos, $obj_ret);
                }
            }
        }
        $str = serialize($videos);
        $result = Saf_Api_Server::call('Redis', 'SET', array(
            'app' => 'class2task',
            'key' => $category_list['cid'],
            'value' => $str,
        ));
        unset($videos);
    }
    mysqli_close($conn);
}catch (Wenku_Error $exception) {
    Bd_Log::warning($e->getErrStr(),$e->getErrNo(),array('total_select' => $total_select));
    exit(0);
}
