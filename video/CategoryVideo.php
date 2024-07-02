<?php
/**
 * @author: zhouquanwei@baidu.com
 * Date: 2017/8/4
 * Time: 上午1:44
 * @brief 计算wap端的分类首页视频
 */
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
$third_class = array();
$ret = array();
$videos = array();

try {
    $conn = mysqli_connect($mysql_r['server_name'], $mysql_r['username'], $mysql_r['password'], $mysql_r['database'], $mysql_r['port']);
    if (false === $conn) {
        throw new Wenku_Error(Wenku_ErrorCodes::DB_CONNECTION_ERROR);
    }
    $conds = array(
        'level = 2',
        'status = 1',
    );
    $class_list = $_dataCategory->getHomeVideoCategory($conds);
    $index = 0;
    //子类数量小于3个不取数据
    foreach ($class_list as $key1 => $value) {
        $conds = array(
            'pid = ' => $value['cid'],
            'status = 1',
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

    foreach ($new_cate_list as $v_key => $v_list) {
        $flag = 1;
        $videos = array();
        $conds = array(
            'pid = ' => $v_list['cid'],
            'status = 1',
        );
        $third_class = $_dataCategory->getHomeVideoCategory($conds);
        $count = 0;
        foreach ($third_class as $value_info) {
            $conds = array(
                'cid3 = ' => $value_info['cid'],
                'cid2 = ' => $v_list['cid'],
                'status = 1',
            );
            $rn = 1;

            $array = $_dataVideoList->getRedisVideo($rn, $conds);
            if (count($array) > 0) {
                $flag++;
                if ($flag <= 3) {
                    if (is_array($array)) {
                        $obj_ret = new StdClass();
                        foreach ($array as $key => $val) {
                            $obj_ret = $val;
                        }
                    }
                    array_push($videos, $obj_ret);
                    unset($array);
                }
            }
        }

        if (count($videos) <3) {

            $rn = 3-count($videos);
            $conds_callback = array(
                'cid2 = ' => $v_list['cid'],
                'status = 1',
            );
            $array = $_dataVideoList->getCallBackRedisVideo($rn, $conds_callback);

            if (is_array($array)) {
                $obj_ret = new StdClass();
                foreach ($array as $key => $val) {
                    $obj_ret = $val;
                }
            }
            array_push($videos, $obj_ret);
            unset($array);
        }

        $str = serialize($videos);
        $result = Saf_Api_Server::call('Redis', 'SET', array(
            'app' => 'class2task',
            'key' => $v_list['cid'],
            'value' => $str,
        ));
        var_dump($result);
        unset($videos);
    }
    mysqli_close($conn);
}catch (Wenku_Error $exception) {
    Bd_Log::warning($e->getErrStr(),$e->getErrNo(),array('total_select' => $total_select));
    exit(0);
}
