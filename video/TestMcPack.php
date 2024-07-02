<?php

$test = new TestMcPack();
$test->useSocket();

class TestMcPack {
    protected $_fd = null;
    protected static $_intVersion = 1;
    protected static $_strProvider = 'video_huopai';
    protected static $_intMagicNum = 0xfb709394;
    protected static $_intId = 0;
    protected static $_intReserved = 0;

    public function dataTest() {
        $video_data = array(
            'video_url' => 'www.baidu.com',
            'video_md5' => 'wwewe',
        );
        $video_data = "{\"video_url\":\"www.baidu.com\",\"video_md5\":\"wwewe\"}";
//        $video_data = json_encode($video_data);
        $video_data = urldecode($video_data);
        var_dump($video_data);

    }
    public function test() {
        $data = array(
            'thread_id' => intval(21211),
            'audit_id' => intval(121222),
            'name' => 'test',
        );
//var_dump($data);
        $mcpack_info = mc_pack_array2pack($data);
//var_dump($mcpack_info);

        $data_trans = mc_pack_pack2array($mcpack_info);
        var_dump($data_trans);
    }

    public function useSocket() {
        $ip = '10.101.16.197';
        $port = 9700;
        $arrInput = array(
            'thread_id' => intval(21211),
            'partner_origin' => 0,
        );
        $strCmdGetInstance = "get_instance_by_service -p video-short-importor.www.m1";
        $strOutput = shell_exec($strCmdGetInstance);
        if (empty($strOutput)) {
            echo 'get bns failed'."\n";
            return false;
        }

        $arrInstance = explode("\n", $strOutput);
        $arrHost = array();
        foreach($arrInstance as $strInstance) {
            if (!empty($strInstance)) {
                $arrTemp = explode(' ', $strInstance);
                $arrHost[] = array(
                    'ip'  => $arrTemp[0],
                    'port' => $arrTemp[1],
                );
            }
        }
        if (empty($arrHost)) {
            echo 'host parse failed' . "\n";
            return false;
        }
        $intCount = count($arrHost);
        $arrUseHost = $arrHost[mt_rand(0, $intCount)];

        $strSend = $this->_serialize($arrInput);
        if ($strSend === false) {
            Bd_Passport_Log::warning('pack input failed', -1);
            return false;
        }
        $this->_fd = $this->_connect($arrUseHost['ip'], $arrUseHost['port'],1000);
        if (!is_resource($this->_fd)) {
            $strLog = sprintf("connect %s:%d in %dms failed.", $ip, $port, 1000);
            Bd_Passport_Log::warning($strLog, -1);
            return false;
        }
        $ret = $this->_send($this->_fd, $strSend, 1000);
        if ($ret == false) {
            $strLog = sprintf("send to %s:%d in %dms failed.");
            Bd_Passport_Log::warning($strLog, -1);
            return false;
        }
        echo $arrUseHost['ip'].$arrUseHost['port'].json_encode($arrInput);
        echo $ret.'   '.'success';
        $this->_close();
    }

    protected function _close() {
        if (is_resource($this->_fd)) {
            fclose($this->_fd);
        }
        $this->_fd = null;
    }

    public function socketTransport() {
        $ip = '10.101.16.197';
        $port = 9700;

        //创建socket
        if(($sock = socket_create(AF_INET,SOCK_STREAM,SOL_TCP)) < 0) {
            echo "socket_create() 失败的原因是:".socket_strerror($sock)."\n";
            exit();
        }

        if(($result = socket_connect($sock, $ip, $port)) < 0){
            echo "socket_connect() 失败的原因是:".socket_strerror($sock)."\n";
            exit();
        }
        echo "连接OK\n";
//        if(($ret = socket_listen($sock,4)) < 0) {
//            echo "socket_listen() 失败的原因是:".socket_strerror($ret)."\n";
//            exit();
//        }
        $arrInput = array(
            'thread_id' => 1,
            'video_url' => 'tieba.baidu.com',
            'test' => 'test',
        );
        $mcpack_package = mc_pack_array2pack($arrInput);
        $input = array(
            'provider' => 'video_huopai',
        );

        if (!$this->nshead_write($sock,$arrInput,$input)) {
            echo "socket_write() 失败的原因是:".socket_strerror($sock)."\n";
            exit();
        }

//        if (!socket_write($sock,$mcpack_package,strlen($mcpack_package))) {
//            echo "socket_write() 失败的原因是:".socket_strerror($sock)."\n";
//            exit();
//        }
        echo "发送数据到服务器成功";

        socket_close($sock);

    }

    public function run() {
        $arrInput = array(
            'thread_id' => 1,
            'video_url' => 'tieba.baidu.com',
            'test' => 'test',
        );

        $url = 'http://10.101.16.197:9900';
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch,CURLOPT_BINARYTRANSFER,true);

        $mcpack_package = mc_pack_array2pack($arrInput);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$mcpack_package);
        $answer = curl_exec($ch);
        curl_close($ch);
        var_dump($answer);
        if (false === $answer ) {
            echo('call pushVideoInfoToSearch failed'.serialize($arrInput).'output = '.serialize($answer));
//            Lib_Audit::arrRet(Tieba_Errcode::ERR_PARAM_ERROR,'push failed');
        }

    }

    protected function _serialize($arrInput) {
        $mcpack = mc_pack_array2pack($arrInput);
        $nshead = $this->pack(strlen($mcpack));
        return $nshead.$mcpack;
    }

    protected function _connect($strIp, $strPort, $intTimeout) {
        $intErrno = 0;
        $strErrmsg = '';
        $ret = @fsockopen($strIp, intval($strPort), $intErrno, $strErrmsg, $intTimeout);
        if (!is_resource($ret)) {
            echo $intErrno;
            return false;
        }
        return $ret;
    }
    protected function _send($fd, $strSend, $intTimeout) {
        $intLen = strlen($strSend);
        if ($intLen < 0) {
            Bd_Passport_Log::warning('null string found', -1);
            return false;
        }

        stream_set_blocking($fd, true);
        $intSecond      = intval($intTimeout / 1000);
        $intMicroSecond = intval($intTimeout % 1000);
        stream_set_timeout($fd, $intSecond, $intMicroSecond);

        $intSent        = fwrite($fd, $strSend, strlen($strSend));
        return ($intSent === $intLen) ? $intSent : false;
    }

    protected function _packNshead($intBodyLen)  {
        $intLogId = $this->getLogId();
        return pack('SSIa16III', 0, 1, $intLogId, 'pass-glib', 0xfb709394, 0, $intBodyLen);
    }

    public function getLogId() {
        $arr = gettimeofday();

        return ((($arr['sec'] * 100000 + $arr['usec'] / 10) & 0x7FFFFFFF) | 0x80000000);
    }

    public static function setOptions($arrConfig) {
        if (isset($arrConfig['provider'])) self::$_strProvider = $arrConfig['provider'];
    }

    public function pack ($intBodyLen, $intReserved = null)
    {
        $intLogID = $this->getLogId();
        if (is_null($intReserved)) {
            $intReserved = self::$_intReserved;
        }
        return pack('SSIa16III', self::$_intId, self::$_intVersion, $intLogID,
            self::$_strProvider, self::$_intMagicNum, $intReserved, $intBodyLen);
    }
}
