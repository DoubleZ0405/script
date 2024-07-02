<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2021/11/3
 * Time: 10:55 AM
 * Brief:
 */


class mongoBaseDao {

    //mongo
    //测试
//    const mongoIp = "10.0.0.6:27017"; // toop36
//    const mongoUser = "root";
//    const mongoPasswd = "oPenlab@2020";
//
//    const mongoIp35 = "10.0.0.31:27017"; // toop35
//    const mongoUser35 = "root";
//    const mongoPasswd35 = "oPenlab@2020";
//
//    const mongoIp30 = "10.0.0.83:27017"; // toop30
//    const mongoUser30 = "root";
//    const mongoPasswd30 = "openlab123";

    //现网
    const mongoIp = "11.135.246.11:27017"; // 广电
    const mongoUser = "toop";
    const mongoPasswd = "W96Mo9cdBa";

    const mongoIp35 = "9.138.108.141:27017"; // 华南2
    const mongoUser35 = "mongouser";
    const mongoPasswd35 = "ApCgdsGg!";

    const mongoIp30 = "9.146.169.244:27017"; // 华南1
    const mongoUser30 = "toop";
    const mongoPasswd30 = "YnA5mztn";


    /**
     * @param $mongoUser
     * @param $mongoPasswd
     * @param $mongoIp
     * @param $filter
     * @param $options
     * @return bool|\MongoDB\Driver\Cursor
     * @throws \MongoDB\Driver\Exception\Exception
     */
    static function mongoDbQuery($collection,$mongoUser, $mongoPasswd, $mongoIp,$filter, $options) {
        $mongo_conn = new MongoDB\Driver\Manager("mongodb://".$mongoUser.":".urlencode($mongoPasswd)."@".$mongoIp);
        $query = new MongoDB\Driver\Query($filter, $options);
        $cursor = $mongo_conn->executeQuery($collection, $query);
        if ($cursor == null || empty($cursor) ) {
            echo "unable get mongodb data from ".$collection."\n";
            return false;
        }
        return $cursor;
    }

    /**
     * @param $colletion
     * @param $mongoUser
     * @param $mongoPasswd
     * @param $mongoIp
     * @param $filter
     * @param $options
     * @return int|null
     */
    static function mongoDel($colletion, $mongoUser, $mongoPasswd, $mongoIp, $filter, $options) {
        $mongo_conn = new MongoDB\Driver\Manager("mongodb://".$mongoUser.":".urlencode($mongoPasswd)."@".$mongoIp);
        $bulk_del = new MongoDB\Driver\BulkWrite();
        $bulk_del->delete($filter);
        $del_ret = $mongo_conn->executeBulkWrite($colletion,$bulk_del);
        return $del_ret->getDeletedCount();
    }

    /**
     * @param $colletion
     * @param $mongoUser
     * @param $mongoPasswd
     * @param $mongoIp
     * @param $document
     * @return int|null
     */
    static function mongoInsert($colletion, $mongoUser, $mongoPasswd, $mongoIp, $document) {
        $mongo_conn = new MongoDB\Driver\Manager("mongodb://".$mongoUser.":".urlencode($mongoPasswd)."@".$mongoIp);
        $bulk_insert = new MongoDB\Driver\BulkWrite();
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        $bulk_insert->insert($document);
        $ret = $mongo_conn->executeBulkWrite($colletion,$bulk_insert,$writeConcern);
        return $ret->getInsertedCount();
    }

    /**
     * @param $collection
     * @param $mongoUser
     * @param $mongoPasswd
     * @param $mongoIp
     * @param $filter
     * @param $document
     * @return \MongoDB\Driver\WriteResult
     */
    static function mongoUpdate($collection,$mongoUser,$mongoPasswd,$mongoIp,$filter,$document) {
        $mongo_conn = new MongoDB\Driver\Manager("mongodb://".$mongoUser.":".urlencode($mongoPasswd)."@".$mongoIp);
        $bulk_update = new MongoDB\Driver\BulkWrite();
        $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
        $bulk_update->update($filter,$document);
        $ret = $mongo_conn->executeBulkWrite($collection,$bulk_update,$writeConcern);
        return $ret;
    }
}



