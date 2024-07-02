<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitc60232928ee91daa4a80ea543e2aacf9
{
    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Thrift\\' => 7,
        ),
        'H' => 
        array (
            'HelloBase\\' => 10,
            'Hbase\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Thrift\\' => 
        array (
            0 => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Thrift',
            1 => __DIR__ . '/..' . '/packaged/thrift/src',
        ),
        'HelloBase\\' => 
        array (
            0 => __DIR__ . '/..' . '/fatrbaby/hellobase/src/HelloBase',
        ),
        'Hbase\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'CalculatorHandler' => __DIR__ . '/../..' . '/thrift/tutorial/php/PhpServer.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'FacebookBase' => __DIR__ . '/../..' . '/thrift/contrib/fb303/php/FacebookBase.php',
        'Hbase\\AlreadyExists' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Types.php',
        'Hbase\\BatchMutation' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Types.php',
        'Hbase\\ColumnDescriptor' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Types.php',
        'Hbase\\HbaseClient' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\HbaseIf' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_append_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_append_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_atomicIncrement_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_atomicIncrement_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_checkAndPut_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_checkAndPut_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_compact_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_compact_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_createTable_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_createTable_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_deleteAllRowTs_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_deleteAllRowTs_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_deleteAllRow_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_deleteAllRow_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_deleteAllTs_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_deleteAllTs_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_deleteAll_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_deleteAll_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_deleteTable_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_deleteTable_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_disableTable_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_disableTable_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_enableTable_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_enableTable_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getColumnDescriptors_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getColumnDescriptors_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRegionInfo_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRegionInfo_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRowTs_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRowTs_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRowWithColumnsTs_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRowWithColumnsTs_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRowWithColumns_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRowWithColumns_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRow_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRow_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRowsTs_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRowsTs_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRowsWithColumnsTs_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRowsWithColumnsTs_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRowsWithColumns_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRowsWithColumns_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRows_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getRows_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getTableNames_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getTableNames_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getTableRegions_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getTableRegions_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getVerTs_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getVerTs_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getVer_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_getVer_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_get_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_get_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_incrementRows_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_incrementRows_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_increment_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_increment_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_isTableEnabled_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_isTableEnabled_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_majorCompact_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_majorCompact_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_mutateRowTs_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_mutateRowTs_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_mutateRow_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_mutateRow_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_mutateRowsTs_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_mutateRowsTs_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_mutateRows_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_mutateRows_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerClose_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerClose_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerGetList_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerGetList_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerGet_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerGet_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerOpenTs_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerOpenTs_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerOpenWithPrefix_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerOpenWithPrefix_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerOpenWithScan_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerOpenWithScan_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerOpenWithStopTs_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerOpenWithStopTs_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerOpenWithStop_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerOpenWithStop_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerOpen_args' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\Hbase_scannerOpen_result' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Hbase.php',
        'Hbase\\IOError' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Types.php',
        'Hbase\\IllegalArgument' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Types.php',
        'Hbase\\Mutation' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Types.php',
        'Hbase\\TAppend' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Types.php',
        'Hbase\\TCell' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Types.php',
        'Hbase\\TColumn' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Types.php',
        'Hbase\\TIncrement' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Types.php',
        'Hbase\\TRegionInfo' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Types.php',
        'Hbase\\TRowResult' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Types.php',
        'Hbase\\TScan' => __DIR__ . '/..' . '/fatrbaby/hellobase/bridge/Hbase/Types.php',
        'TApplicationException' => __DIR__ . '/../..' . '/thrift/lib/php/src/Thrift.php',
        'TBase' => __DIR__ . '/../..' . '/thrift/lib/php/src/Thrift.php',
        'TBinaryProtocol' => __DIR__ . '/../..' . '/thrift/lib/php/src/protocol/TBinaryProtocol.php',
        'TBinaryProtocolAccelerated' => __DIR__ . '/../..' . '/thrift/lib/php/src/protocol/TBinaryProtocol.php',
        'TBinaryProtocolFactory' => __DIR__ . '/../..' . '/thrift/lib/php/src/protocol/TBinaryProtocol.php',
        'TBinarySerializer' => __DIR__ . '/../..' . '/thrift/lib/php/src/protocol/TBinarySerializer.php',
        'TBufferedTransport' => __DIR__ . '/../..' . '/thrift/lib/php/src/transport/TBufferedTransport.php',
        'TCompactProtocol' => __DIR__ . '/../..' . '/thrift/lib/php/src/protocol/TCompactProtocol.php',
        'TCompcatProtocolFactory' => __DIR__ . '/../..' . '/thrift/lib/php/src/protocol/TCompactProtocol.php',
        'TException' => __DIR__ . '/../..' . '/thrift/lib/php/src/Thrift.php',
        'TForkingServer' => __DIR__ . '/../..' . '/thrift/lib/php/src/server/TForkingServer.php',
        'TFramedTransport' => __DIR__ . '/../..' . '/thrift/lib/php/src/transport/TFramedTransport.php',
        'THttpClient' => __DIR__ . '/../..' . '/thrift/lib/php/src/transport/THttpClient.php',
        'TMemoryBuffer' => __DIR__ . '/../..' . '/thrift/lib/php/src/transport/TMemoryBuffer.php',
        'TMessageType' => __DIR__ . '/../..' . '/thrift/lib/php/src/Thrift.php',
        'TNullTransport' => __DIR__ . '/../..' . '/thrift/lib/php/src/transport/TNullTransport.php',
        'TPhpStream' => __DIR__ . '/../..' . '/thrift/lib/php/src/transport/TPhpStream.php',
        'TProtocol' => __DIR__ . '/../..' . '/thrift/lib/php/src/protocol/TProtocol.php',
        'TProtocolException' => __DIR__ . '/../..' . '/thrift/lib/php/src/protocol/TProtocol.php',
        'TProtocolFactory' => __DIR__ . '/../..' . '/thrift/lib/php/src/protocol/TProtocol.php',
        'TServer' => __DIR__ . '/../..' . '/thrift/lib/php/src/server/TServer.php',
        'TServerSocket' => __DIR__ . '/../..' . '/thrift/lib/php/src/transport/TServerSocket.php',
        'TServerTransport' => __DIR__ . '/../..' . '/thrift/lib/php/src/transport/TServerTransport.php',
        'TSimpleServer' => __DIR__ . '/../..' . '/thrift/lib/php/src/server/TSimpleServer.php',
        'TSocket' => __DIR__ . '/../..' . '/thrift/lib/php/src/transport/TSocket.php',
        'TSocketPool' => __DIR__ . '/../..' . '/thrift/lib/php/src/transport/TSocketPool.php',
        'TTransport' => __DIR__ . '/../..' . '/thrift/lib/php/src/transport/TTransport.php',
        'TTransportException' => __DIR__ . '/../..' . '/thrift/lib/php/src/transport/TTransport.php',
        'TTransportFactory' => __DIR__ . '/../..' . '/thrift/lib/php/src/transport/TTransportFactory.php',
        'TType' => __DIR__ . '/../..' . '/thrift/lib/php/src/Thrift.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitc60232928ee91daa4a80ea543e2aacf9::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitc60232928ee91daa4a80ea543e2aacf9::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitc60232928ee91daa4a80ea543e2aacf9::$classMap;

        }, null, ClassLoader::class);
    }
}