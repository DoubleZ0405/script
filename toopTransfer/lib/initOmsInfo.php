<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2021/11/8
 * Time: 11:47 AM
 * Brief:
 */

class initOmsInfo {

    //鹅埠站点
    //测试
    CONST siteIdA35 = "Site-1594693825410";
    CONST siteIdA30 = "Site-1594694748049";
    CONST siteIdGdEb = "Site-1636084500452";

    //滨海腾大
    CONST siteIdB35 = "Site-1591085719251";
    CONST siteIdB30 = "Site-1592897247511";
    CONST siteIdGdTd = "Site-1636082040070";

    //腾讯大厦
    CONST siteIdC35 = "Site-1591085705359";
    CONST siteIdC30 = "Site-1592841585021";
    CONST siteIdGdDso = "Site-1636082188393";

    //华南1 30
//    CONST HUANAN1URL = "http://1.14.216.232";
    CONST HUANAN1URL = "http://9.146.169.234";


    //腾大--鹅埠
    CONST OMSID_TD2EB = "SiteLink-Site-1592841585021#Ne-1594694847523#MUX-1-50#PORT-1-50-MUXDMUX-Site-1594694748049#Ne-1594694847544#MUX-1-50#PORT-1-50-MUXDMUX";
    //滨海--鹅埠
    CONST OMSID_BH2EB = "SiteLink-Site-1592897247511#Ne-1594695165857#MUX-1-50#PORT-1-50-MUXDMUX-Site-1594694748049#Ne-1594695165874#MUX-1-50#PORT-1-50-MUXDMUX";


    //华南2 35
//    CONST HUANAN2URL = "http://109.244.130.105";
    CONST HUANAN2URL = "http://9.138.108.101";

    //腾大--鹅埠
    CONST OMSID_TD2EB_S2 = "SiteLink-Site-1591085705359#Ne-1594694403145#MUX-1-50#PORT-1-50-MUXDMUX-Site-1594693825410#Ne-1594694403158#MUX-1-50#PORT-1-50-MUXDMUX";
    //滨海--鹅埠
    CONST OMSID_BH2EB_S2 = "SiteLink-Site-1591085719251#Ne-1594694682628#MUX-1-50#PORT-1-50-MUXDMUX-Site-1594693825410#Ne-1594694682641#MUX-1-50#PORT-1-50-MUXDMUX";

    /**
     * @param $url
     * @param $omsId
     * @return mixed
     */
    static function getSiteNodeInfo($url,$omsId) {
        $url = "$url/restconf/operations/nms:get-site-node-paged";
        $post_data = '{"input":{"topology-ref":"site-topology","link-ref":'.'"'."$omsId".'"'.',"start-pos":0,"how-many":30,"sort-infos":[]}}';
        $headers = array(
            'Authorization: Basic YWRtaW46eWlqaW5nZGFpemFpV0FOU0hJWElBT1hJTkAyMDE5',
            'Content-Type: application/json',
            'Cookie: JSESSIONID=907t88xhiz9zp3us1c4qsnuj; x_host_key=1773d57ddd0-33490a95d22164158d81f756b5864759543dddf9'
        );
        $result = Util::http_request_xml($url,$post_data,$headers);
        $result_arr = json_decode($result,true);
        return $result_arr;
    }

    /**
     * @param $url
     * @param $id
     * @return mixed
     */
    static function getPhyNodeInfo($url,$id) {
        $url = "$url/restconf/operations/nms:get-phy-node-paged";
        $post_data = '{"input":{"topology-ref":"site-topology","tunnel-ref":'.'"'."$id".'"'.',"start-pos":0,"how-many":1000,"sort-infos":[]}}';
        $headers = array(
            'Authorization: Basic YWRtaW46eWlqaW5nZGFpemFpV0FOU0hJWElBT1hJTkAyMDE5',
            'Content-Type: application/json',
            'Cookie: JSESSIONID=907t88xhiz9zp3us1c4qsnuj; x_host_key=1773d57ddd0-33490a95d22164158d81f756b5864759543dddf9'
        );
        $result = Util::http_request_xml($url,$post_data,$headers);
        $result_arr = json_decode($result,true);
        return $result_arr;
    }

    /**
     * @param $url
     * @param $id
     * @return mixed
     */
    static function getTunnelInfo($url, $id) {
        $url = "$url/restconf/operations/nms:get-tunnel-paged";
        $post_data = '{"input":{"topology-ref":"site-topology","link-ref":'.'"'."$id".'"'.',"start-pos":0,"how-many":1000,"sort-infos":[{"ascending":true,"sort-name":"customer"}]}}';
        $headers = array(
            'Authorization: Basic YWRtaW46eWlqaW5nZGFpemFpV0FOU0hJWElBT1hJTkAyMDE5',
            'Content-Type: application/json',
            'Cookie: JSESSIONID=907t88xhiz9zp3us1c4qsnuj; x_host_key=1773d57ddd0-33490a95d22164158d81f756b5864759543dddf9'
        );
        $result = Util::http_request_xml($url,$post_data,$headers);
        $result_arr = json_decode($result,true);
        return $result_arr;
    }

    /**
     * @param $url
     * @param $id
     * @return mixed
     */
    static function getPhyLinkInfo($url,$id) {
        $url = "$url/restconf/operations/nms:get-phy-link-paged";
        $post_data = '{"input":{"topology-ref":"site-topology","tunnel-ref":'.'"'.$id.'"'.',"start-pos":0,"how-many":100,"sort-infos":[{"ascending":true,"sort-name":"physical#link-type"}]}}';
        $headers = array(
            'Authorization: Basic YWRtaW46eWlqaW5nZGFpemFpV0FOU0hJWElBT1hJTkAyMDE5',
            'Content-Type: application/json',
            'Cookie: JSESSIONID=907t88xhiz9zp3us1c4qsnuj; x_host_key=1773d57ddd0-33490a95d22164158d81f756b5864759543dddf9'
        );
        $result = Util::http_request_xml($url,$post_data,$headers);
        $result_arr = json_decode($result,true);
        return $result_arr;
    }
}