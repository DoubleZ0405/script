<?php

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

$socket = new TSocket('localhost', 9090);
$socket->setSendTimeout(10000); // Ten seconds (too long for production, but this is just a demo ;)
$socket->setRecvTimeout(20000); // Twenty seconds
$transport = new TBufferedTransport( $socket );
$protocol = new TBinaryProtocol( $transport );
$client = new HbaseClient( $protocol );

try {
    $transport->open();

    $table = 'tablename';
    $rowkey = 'rowkey';
    $column = 'columnfamily:column';

    print_r($client->getRow($table, $rowkey, null));
    print_r($client->get($table, $rowkey, $column, null));
 
    $transport->close();

} catch (TException $e) {
    print 'TException: '.$e->__toString().' Error: '.$e->getMessage()."\n";
}
