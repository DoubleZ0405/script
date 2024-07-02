<?php

require __DIR__ . '/../vendor/autoload.php';

use HelloBase\Connection;
use HelloBase\Supports\Integer;
use Hbase\IOError;


$config = array(
    'host' => '10.0.0.55',
    'port' => '6004',
    'auto_connect' => true,
    'persist' => false,
    'debug_handler' => null,
    'send_timeout' => 100000000,
    'recv_timeout' => 100000000,
    'transport' => Connection::TRANSPORT_FRAMED,
    'protocol' => Connection::PROTOCOL_COMPACT,
);
$hbase_connect = new Connection($config);
$hbase_connect->connect();
var_dump($hbase_connect->tables());
die();
//$table = $hbase_connect->table('toop:AMPLIFIER');
//foreach ($table->scan() as $row => $columes) {
//    var_dump($row,$columes);die();
//}
//print_r($hbase_connect->tables());