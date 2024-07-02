<?php


class mongoBaseDao {

    const mongoIp = "xx"; 
    const mongoUser = "";
    const mongoPasswd = "";

    const mongoIp35 = ""; 
    const mongoUser35 = "";
    const mongoPasswd35 = "!";

    const mongoIp30 = ""; 
    const mongoUser30 = "";
    const mongoPasswd30 = "";


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



