<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2022/1/24
 * Time: 6:07 PM
 * Brief:
 */
define ( 'ROOT_PATH', dirname ( __FILE__ ) );
ini_set ( "memory_limit", "-1" );

require_once "./lib/Util.php";
require_once "./lib/initOmsInfo.php";
require_once "./mongo/mongoBaseDao.php";

class neUnregits {

    function run()
    {

        $url = "http://guangdian.toop.woa.com/restconf/operations/nms:get-phy-node-paged";
        $url_ne = "http://11.186.11.146/restconf/operations/eml-manager:unregiste-ne";
        $post_data = '{"input":{"topology-ref":"otn-phy-topology","start-pos":0,"how-many":100,"sort-infos":[]}}';
        $headers = array(
            'Authorization: Basic YWRtaW46eWlqaW5nZGFpemFpV0FOU0hJWElBT1hJTkAyMDE5',
            'Content-Type: application/json',
            'Cookie: JSESSIONID=907t88xhiz9zp3us1c4qsnuj; x_host_key=1773d57ddd0-33490a95d22164158d81f756b5864759543dddf9'
        );
        $result = Util::http_request_xml($url, $post_data, $headers);
        $arr_ret = json_decode($result, true);

        for ($i = 0; $i < count($arr_ret['output']['node']); $i++) {
            $ne_detail = $arr_ret['output']['node'][$i]['physical']['properties']['property'];
            foreach ($ne_detail as $index => $ne_info) {
                 if ($ne_info['value'] == "v1.33") {
                     $nodeId = $arr_ret['output']['node'][$i]['node-id'];
                    $input = array(
                        'input' => array(
                            'node-id' => $nodeId,
                            'force' => true
                        ),
                    );
                    $result = Util::http_request_xml($url_ne, json_encode($input), $headers);
                    echo $result . "\n";
                }
            }
        }
    }

}
$ne = new neUnregits();
$ne->run();