<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2021/10/27
 * Time: 8:35 PM
 * Brief:
 */

class Util {

    /**
     * 对象转换为数组
     * @param  object $object 需要转换的对象
     * @return array          转换后的数组
     */
    public static function object2array($object) {
        $arr =  json_decode( json_encode( $object),true);
        return  $arr;
    }

    /**
     * @param $array
     * @return mixed
     */
    public static function array2Object($array) {
        $object = json_decode( json_encode($array));
        return $object;
    }

    /**
     * @param $url
     * @param null $data
     * @param null $arr_header
     * @return mixed
     */
    public static function http_request_xml($url,$data,$arr_header)  {
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
}