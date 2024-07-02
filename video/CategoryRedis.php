<?php

Bd_Init::init();
Bd_LayerProxy::init(Bd_Conf::getConf('/layerproxy/'));
Bd_Log::trace('begin');
ini_set("memory_limit","2058m");
$clusterName = 'video';
$total_select = 0;
try {
    $conn = Bd_Db_ConnMgr::getConn($clusterName);
    if (false === $conn) {
        throw new Wenku_Error(Wenku_ErrorCodes::DB_CONNECTION_ERROR);
    }

    $ret = array();
    $sub_class = array();
    $th_class = array();
    $conds1 = array(
        'level = 1',
        'status = 1',
    );

    $level1 = $conn->select('category',array('cid','pid','name','level'),$conds1);

    if (false === $level1 ) {
        throw new Wenku_Error(Wenku_ErrorCodes::DB_QUERY_ERROR);
    }
    foreach ($level1 as $first_index => $one_class) {
        $two_class = $conn->select('category', array('cid','pid','name','level'), array('pid = ' => $one_class['cid'], 'status = 1'));
        if (count($two_class) > 0 || !empty($two_class)) {
            foreach ($two_class as $index => &$two_info) {
                $three_class = $conn->select('category', array('cid', 'name', 'level'), array('pid = ' => $two_info['cid'],'status = 1'));
                $two_class[$index] = array(
                    'cid' => intval($two_info['cid']),
                    'name' => $two_info['name'],
                    'level' => $two_info['level'],
                    'sub_c' => $three_class,
                );
            }
            $class_num = count($two_class);

            $ret[$one_class['cid']] = array(
                'cid' => intval($one_class['cid']),
                'name' => $one_class['name'],
                'level' => $one_class['level'],
                'class_num' => $class_num,
                'sub_c' => $two_class,
            );
        }
    }
    $ret = array_values($ret);

    $type = 0;
    $timeout = 3000;
    $filename = '';
    $cache_expire = 0;
    $for_internal = false;
    $key = 'video_class_tree_99999';
    $str = serialize($ret);
    // 存储bos
    $bos_service = new Service_Data_BcsVideo();
    $bos_upload = new Wenku_Service_BcsUpload();
    $upload_video = $bos_service->uploadWenkuVideo($str, $key,false);

}catch (Wenku_Error $exception) {
    Bd_Log::warning($e->getErrStr(),$e->getErrNo(),array('total_select' => $total_select));
    exit(0);
}
