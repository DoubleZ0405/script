<?php
/***************************************************************************
 *
 * Copyright (c) 2020 Tencent.com, Inc. All Rights Reserved
 * Tencent open optical platform(Toop)
 **************************************************************************/

/**
 * Author: quanweizhou@tencent.com
 * Date: 2021/4/7
 * Time: 7:49 PM
 * Brief:
 */

require_once('thrift/src/Thrift.php');
//$GLOBALS['THRIFT_ROOT'] = './thrift/lib/Thrift';
$GLOBALS['THRIFT_ROOT'] = 'thrift/lib/Thrift';
require_once('thrift/lib/Thrift/Type/TMessageType.php');
require_once('thrift/lib/Thrift/Type/TType.php');
require_once('thrift/lib/Thrift/Exception/TException.php');
require_once('thrift/lib/Thrift/Factory/TStringFuncFactory.php');
require_once('thrift/lib/Thrift/StringFunc/TStringFunc.php');
require_once('thrift/lib/Thrift/StringFunc/Core.php');
require_once('thrift/lib/Thrift/Transport/TSocket.php');
require_once('thrift/lib/Thrift/Transport/TBufferedTransport.php');
require_once('thrift/lib/Thrift/Protocol/TBinaryProtocol.php');

require_once('thrift/lib/HBase/Hbase.php');
require_once('thrift/lib/HBase/Types.php');

use Thrift\Transport\TSocket;
use Thrift\Transport\TBufferedTransport;
use Thrift\Protocol\TBinaryProtocol;
use Hbase\HbaseClient;
use Hbase\Mutation;
use Hbase\TScan;

//$socket = new TSocket('jx-wangping-hbase-zk-1.tencent-distribute.com:100.107.9.250', 2181);
$socket = new TSocket('100.107.9.250', 2181);
$socket->setSendTimeout(10000); // Ten seconds (too long for production, but this is just a demo ;)
$socket->setRecvTimeout(20000); // Twenty seconds
$transport = new TBufferedTransport( $socket );
$protocol = new TBinaryProtocol( $transport );
$client = new HbaseClient( $protocol );
try {
    $transport->open();
    $table = 'student';
    $rowkey = 'row1';
    $column = 'course:chinese';
    print_r($client->getTableNames());die();
    print_r($client->getRow($table, $rowkey, null));
//    print_r($client->get($table, $rowkey, $column, null));
    $transport->close();

} catch (TException $e) {
    print 'TException: '.$e->__toString().' Error: '.$e->getMessage()."\n";
}

function dataTrans2TDbank() {
//    store:
//    url: http://tl-tdbank-tdmanager.tencent-distribute.com:8099/api/tdbus_ip?bid=b_teg_net_toop&net_tag=all
//    update.interval: 60000
//  bid: b_teg_net_toop
//  tid: toop_in01_telemetry
//  port: 8000
//  max.connections: 40
//  max.route: 40
}
