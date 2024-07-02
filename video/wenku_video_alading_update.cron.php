<?php
/**
 * @name 文库视频阿拉丁增量
 * 每天的10点执行
 * @author zhouquanwei@baidu.com
 * @since 2017/9/18
 */


class ApplicationUpdate {

	private $db_wenku = null;
	private $org_names = array(); //机构名称列表
	private $alading_output_path = ''; //定义阿拉丁输出目录
	private $video_playcnt = array();//视频点击量缓存
	private $video_recommend = array();//视频推荐缓存
	private $video_exported = array();//已导出视频缓存
    private $mysql_r = array();
    private $mysql_w = array();

	
	const WENKU_ALADING_XML_PER_PAGE = 300;//每页xml数量
	const WENKU_VIDEO_PLAYCNT_INTERFACE_URL = 'https://wenku.baidu.com/video/interface/getrecdocs?type=playcnt&id=';
	const WENKU_VIDEO_PC_PAGE_URL = 'https://wenku.baidu.com/video/course/v/';//文库PC播放页URL前缀
	const WENKU_VIDEO_H5_PAGE_URL = 'https://wk.baidu.com/video/course/v/';//文库H5播放页URL前缀
	
	public function __construct() {

        Bd_Init::init();
        Bd_LayerProxy::init(Bd_Conf::getConf('/layerproxy/'));
        //获取数据库配置
        $db_conf = Bd_Conf::getConf('/db/cluster');
        $db_conf_r = $db_conf['video'];

        $this->mysql_r['server_name'] = $db_conf_r['host'][0]['ip'];
        $this->mysql_r['port'] = $db_conf_r['host'][0]['port'];
        $this->mysql_r['username'] = $db_conf_r['username'];
        $this->mysql_r['password'] = $db_conf_r['password'];
        $this->mysql_r['database'] = $db_conf_r['default_db'];

        $db_conf_w = $db_conf['video_write'];

        $this->mysql_w['server_name'] = $db_conf_w['host'][0]['ip'];
        $this->mysql_w['port'] = $db_conf_w['host'][0]['port'];
        $this->mysql_w['username'] = $db_conf_w['username'];
        $this->mysql_w['password'] = $db_conf_w['password'];
        $this->mysql_w['database'] = $db_conf_w['default_db'];
		//初始化阿拉丁输出目录
		$this->alading_output_path = '/home/iknow/odp/webroot/alading/';
	}
	
	function Run() {

        @ini_set('memory_limit','1024M');
	    $now = time();//获取xml生成时间
	    $process_log_file = $this->alading_output_path.'/wenku_update_process.html';
	    file_put_contents($process_log_file,'<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><title>刷新文库任务进度</title>'.date('Y-m-d H:i:s',time()).' 增量导出任务开始...<br/>');

	    //获取机构列表
        $org_sql = "select * from org";

        $result = $this->mysqlQueryDb($this->mysql_r, $org_sql,'gbk');

        while ($row = mysqli_fetch_assoc($result)) {
            $alading_org_list[] = $row;
        }

	    if(empty($alading_org_list)) {
	        file_put_contents($process_log_file,date('Y-m-d H:i:s',time()).'机构列表为空，任务结束',FILE_APPEND);
	        return;
	    }
	    //机构名称缓存
	    foreach ($alading_org_list as $org_info){

	        $this->org_names[$org_info['org_id']] = $org_info['org_name'];
	    }
	    file_put_contents($process_log_file,date('Y-m-d H:i:s',time()).' 加载注册机构:'.count($this->org_names).'<br/>',FILE_APPEND);

	    //读取视频列表
	    $alading_cnt_sql = "select count(1) as 'Cnt' from video where status = 1 AND `duration` > 10 AND `update_time` > `push_time` AND `pid` > 0 ";
        $result_cnt = $this->mysqlQueryDb($this->mysql_r, $alading_cnt_sql,'gbk');
	    $alading_cnt_query = mysqli_fetch_assoc($result_cnt);

	    $cnt_rs = $alading_cnt_query['Cnt'];

	    if($cnt_rs <= 0){
	        //没数据
	        file_put_contents($process_log_file,date('Y-m-d H:i:s',time()).'没可用数据，任务结束',FILE_APPEND);
	        return;
	    }
	    file_put_contents($process_log_file,date('Y-m-d H:i:s',time()).' 增量数据总量：'.$cnt_rs['Cnt'].'条<br/>',FILE_APPEND);
        $total_page_count = ceil($cnt_rs/self::WENKU_ALADING_XML_PER_PAGE);
        file_put_contents($process_log_file,date('Y-m-d H:i:s',time()).' 将生成'.$total_page_count.'个XML文件，一个sitemap<br/>',FILE_APPEND);

		$site_map_list_xmlfiles = array();

		for($page_num=1;$page_num <= $total_page_count;$page_num++) {
		    var_dump($page_num);
		    echo 'processing...';
			file_put_contents($process_log_file,date('Y-m-d H:i:s',time()).' 正在生成第'.$page_num.'个增量XML文件，时间较长，请耐心等待...<br/>',FILE_APPEND);
			$start = ($page_num - 1) * self::WENKU_ALADING_XML_PER_PAGE;
            $alading_video_sql = "select * from video where status = 1 AND `duration` > 10 AND `pid` > 0  limit $start,".self::WENKU_ALADING_XML_PER_PAGE;
            $result_cnt = $this->mysqlQueryDb($this->mysql_r, $alading_video_sql,'gbk');

            while ($row = mysqli_fetch_assoc($result_cnt)) {
                $alading_querys[] = $row;
            }

			if(empty($alading_querys)){
			    file_put_contents($process_log_file,date('Y-m-d H:i:s',time()).' 列表没可用数据，任务结束',FILE_APPEND);    
			    return;
			}
			
        	$xml_doc = new DOMDocument('1.0','UTF-8');
        	$xml_doc->formatOutput = true;
        	$xml_header = $xml_doc->createElement('urlset');
        	$xml_header = $xml_doc->appendChild($xml_header);
        	
    	    foreach ($alading_querys as $video_info){
                echo 'doing...';
    			$xml_item = $xml_doc->createElement('url');
    			$xml_item_loc = $xml_doc->createElement('loc');
    			$xml_item_loc_value = $xml_doc->createTextNode(str_ireplace('https://','http://',self::WENKU_VIDEO_PC_PAGE_URL).$video_info['hash']);

    			$xml_item_loc->appendChild($xml_item_loc_value);
    			$xml_item->appendChild($xml_item_loc);
    
    			$xml_item_lastmod = $xml_doc->createElement('lastmod',date('Y-m-d\TH:i:s',$now));
    			$xml_item->appendChild($xml_item_lastmod);
    			
                $xml_item_data = $xml_doc->createElement('data');
                $xml_item_data_display = $xml_doc->createElement('display');
                
                $xml_item_data_display_id = $xml_doc->createElement('id',$video_info['hash']);
    	        $xml_item_data_display->appendChild($xml_item_data_display_id);
    	        
    	        $xml_item_data_display_sourcetime = $xml_doc->createElement('sourceTime',$video_info['update_time']);
    	        $xml_item_data_display->appendChild($xml_item_data_display_sourcetime);
    	        
    	        $xml_item_data_display_name = $xml_doc->createElement('name');
    	        $xml_item_data_display_name_value = $xml_doc->createCDATASection($video_info['title']);
    	        $xml_item_data_display_name->appendChild($xml_item_data_display_name_value);
    	        $xml_item_data_display->appendChild($xml_item_data_display_name);
    	        
    	        if(!empty($video_info['tag'])){
    	            $xml_item_data_display_tags = $xml_doc->createElement('tags');
    	            $xml_item_data_display_tags_value = $xml_doc->createCDATASection($video_info['tag']);
    	            $xml_item_data_display_tags->appendChild($xml_item_data_display_tags_value);
    	            $xml_item_data_display->appendChild($xml_item_data_display_tags);
    	        }
    	        
    	        $xml_item_data_display_category = $xml_doc->createElement('category');
    	        $xml_item_data_display_category_value = $xml_doc->createCDATASection('教育');
    	        $xml_item_data_display_category->appendChild($xml_item_data_display_category_value);
    	        $xml_item_data_display->appendChild($xml_item_data_display_category);
    	        
    	        $xml_item_data_display_image = $xml_doc->createElement('image');
    	        $xml_item_data_display_image_value = $xml_doc->createCDATASection(str_ireplace('https://','http://',$video_info['thumb_img']));
    	        $xml_item_data_display_image->appendChild($xml_item_data_display_image_value);
    	        $xml_item_data_display->appendChild($xml_item_data_display_image);
    	        
    	        $xml_item_data_display_playUrlForWeb = $xml_doc->createElement('playUrlForWeb');
    	        $xml_item_data_display_playUrlForWeb_value = $xml_doc->createCDATASection(self::WENKU_VIDEO_PC_PAGE_URL.$video_info['hash']);
    	        $xml_item_data_display_playUrlForWeb->appendChild($xml_item_data_display_playUrlForWeb_value);
    	        $xml_item_data_display->appendChild($xml_item_data_display_playUrlForWeb);
    	        
    	        $xml_item_data_display_playUrlForH5 = $xml_doc->createElement('playUrlForH5');
    	        $xml_item_data_display_playUrlForH5_value = $xml_doc->createCDATASection(self::WENKU_VIDEO_H5_PAGE_URL.$video_info['hash']);
    	        $xml_item_data_display_playUrlForH5->appendChild($xml_item_data_display_playUrlForH5_value);
    	        $xml_item_data_display->appendChild($xml_item_data_display_playUrlForH5);
    	        
    	        $xml_item_data_display_videoFormat = $xml_doc->createElement('videoFormat',$this->getVideoFileExtension($video_info['video_url']));
    	        $xml_item_data_display->appendChild($xml_item_data_display_videoFormat);
    	        
    	        $xml_item_data_display_description = $xml_doc->createElement('description');
    	        $video_description = $video_info['description'] ? $video_info['description'] : $video_info['title'];
    	        $video_description = strip_tags($video_info['description']);
    	        $xml_item_data_display_description_value = $xml_doc->createCDATASection($video_description);
    	        $xml_item_data_display_description->appendChild($xml_item_data_display_description_value);
    	        $xml_item_data_display->appendChild($xml_item_data_display_description);
    	        
    	        $xml_item_data_display_number2 = $xml_doc->createElement('Number2',date('Ymd',$video_info['create_time']));
    	        $xml_item_data_display->appendChild($xml_item_data_display_number2);
    	        
    	        $xml_item_data_display_delete = $xml_doc->createElement('isDelete',($video_info['pid'] > 0 ? 0 : 1));
    	        $xml_item_data_display->appendChild($xml_item_data_display_delete);
    	        
    	        $xml_item_data_display_definition = $xml_doc->createElement('definition','标清');
    	        $xml_item_data_display->appendChild($xml_item_data_display_definition);
    	        
    	        $xml_item_data_display_duration = $xml_doc->createElement('duration',$video_info['duration']);
    	        $xml_item_data_display->appendChild($xml_item_data_display_duration);
    	        
    	        $xml_item_data_display_uploader = $xml_doc->createElement('uploader');
    	        $xml_item_data_display_uploader_value = $xml_doc->createCDATASection($this->org_names[$video_info['org_id']]);
    	        $xml_item_data_display_uploader->appendChild($xml_item_data_display_uploader_value);
    	        $xml_item_data_display->appendChild($xml_item_data_display_uploader);
    	        
    	        $xml_item_data_display_playcnt = $xml_doc->createElement('playcnt',$this->getVideoPlayCount($video_info['hash']));//从FE那边获取播放数
    	        $xml_item_data_display->appendChild($xml_item_data_display_playcnt);
    	        
    	        $xml_item_data_display_comment = $xml_doc->createElement('comment',0);//@todo 评论数暂无
    	        $xml_item_data_display->appendChild($xml_item_data_display_comment);
    	        
    	        //获取推荐视频列表
    	        if(isset($this->video_recommend[$video_info['hash']]) && !empty($this->video_recommend[$video_info['hash']])){
    	            $recommend_video_list = $this->video_recommend[$video_info['hash']];
    	        }else{
                    $alading_video_recommend_sql = "select * from video where status = 1 AND pid = '{$video_info['pid']}' AND hash != '{$video_info['hash']}' AND duration > 10  LIMIT 20";
                    $result_cnt = $this->mysqlQueryDb($this->mysql_r, $alading_video_recommend_sql,'gbk');

                    while ($row = mysqli_fetch_assoc($result_cnt)) {
                        $recommend_video_list[] = $row;
                    }

    	        }
    	        
    	        if(!empty($recommend_video_list)) {
    	            //从缓存中读取推荐视频
    	            if(!isset($this->video_recommend[$video_info['hash']])){
    	               $this->video_recommend[$video_info['hash']] = $recommend_video_list;    
    	            }
    	            
                    //推荐视频父节点
        	        $xml_item_data_display_relatedRecommend = $xml_doc->createElement('relatedRecommend');    	            
        	        foreach ($recommend_video_list as $rk => $recommend_video){
        	            $xml_item_data_display_relatedRecommend_item = $xml_doc->createElement('relatedRecommendItem');
        	            
        	            $xml_item_data_display_relatedRecommend_item_url = $xml_doc->createElement('recommendUrl');
        	            $xml_item_data_display_relatedRecommend_item_url_value = $xml_doc->createCDATASection(self::WENKU_VIDEO_PC_PAGE_URL.$recommend_video['hash']);
        	            $xml_item_data_display_relatedRecommend_item_url->appendChild($xml_item_data_display_relatedRecommend_item_url_value);
        	            $xml_item_data_display_relatedRecommend_item->appendChild($xml_item_data_display_relatedRecommend_item_url);
        	            
        	            $xml_item_data_display_relatedRecommend_item_name = $xml_doc->createElement('recommendName');
        	            $xml_item_data_display_relatedRecommend_item_name_value = $xml_doc->createCDATASection($recommend_video['title']);
        	            $xml_item_data_display_relatedRecommend_item_name->appendChild($xml_item_data_display_relatedRecommend_item_name_value);
        	            $xml_item_data_display_relatedRecommend_item->appendChild($xml_item_data_display_relatedRecommend_item_name);
        	            
        	            $xml_item_data_display_relatedRecommend_item_img = $xml_doc->createElement('recommendImg');
        	            $xml_item_data_display_relatedRecommend_item_img_value = $xml_doc->createCDATASection(str_ireplace('https://','http://',$recommend_video['thumb_img']));
        	            $xml_item_data_display_relatedRecommend_item_img->appendChild($xml_item_data_display_relatedRecommend_item_img_value);
        	            $xml_item_data_display_relatedRecommend_item->appendChild($xml_item_data_display_relatedRecommend_item_img);
        	            
        	            $xml_item_data_display_relatedRecommend_item_location = $xml_doc->createElement('recommendLocation',intval($rk+1));
        	            $xml_item_data_display_relatedRecommend_item->appendChild($xml_item_data_display_relatedRecommend_item_location);
        	            
        	            $xml_item_data_display_relatedRecommend_item_user = $xml_doc->createElement('recommentUserName');
        	            $xml_item_data_display_relatedRecommend_item_user_name = $xml_doc->createCDATASection($this->org_names[$recommend_video['org_id']]);
        	            $xml_item_data_display_relatedRecommend_item_user->appendChild($xml_item_data_display_relatedRecommend_item_user_name);
        	            $xml_item_data_display_relatedRecommend_item->appendChild($xml_item_data_display_relatedRecommend_item_user);
        	            
        	            $xml_item_data_display_relatedRecommend_item_duration = $xml_doc->createElement('recommentDuration',$recommend_video['duration']);
        	            $xml_item_data_display_relatedRecommend_item->appendChild($xml_item_data_display_relatedRecommend_item_duration);
        	            
        	            $xml_item_data_display_relatedRecommend_item_playcnt = $xml_doc->createElement('recommentPlayCnt',$this->getVideoPlayCount($recommend_video['hash']));//从FE那边获取播放数
        	            $xml_item_data_display_relatedRecommend_item->appendChild($xml_item_data_display_relatedRecommend_item_playcnt);
        	            
        	            $xml_item_data_display_relatedRecommend->appendChild($xml_item_data_display_relatedRecommend_item);
        	        }
        	        $xml_item_data_display->appendChild($xml_item_data_display_relatedRecommend);
        	        unset($recommend_video_list);
    	        }
    	        
    	        $xml_item_data->appendChild($xml_item_data_display);
    	        $xml_item->appendChild($xml_item_data);
    	        
    	        $xml_header->appendChild($xml_item);
    	        
    	        //将已完成视频加入待标记队列
    	        $this->video_exported[] = "'".$video_info['hash']."'";
                echo 'tag...';
    	    }   
    	    
			$xml_body = $xml_doc->saveXML();
			//输出文件
			$unit_xml_file_name = $this->alading_output_path.'/wenku_update_'.$page_num.'.xml';
			$site_map_list_xmlfiles[] = 'http://nj02-junheng2p-wenku101.nj02.baidu.com:8083/aladingxml/zhouquanwei/wenku_update_'.$page_num.'.xml';
			file_put_contents($unit_xml_file_name,$xml_body);
			unset($alading_querys,$xml_doc,$xml_root,$xml_body,$xml_header,$unit_xml_file_name);    	         	
			
		}
		
		//生成sitemap
		if(count($site_map_list_xmlfiles) > 0){
			file_put_contents($process_log_file,date('Y-m-d H:i:s',time()).' 正在生成增量sitemap文件，任务即将结束...<br/>',FILE_APPEND);
			$xml_doc = new DOMDocument('1.0','UTF-8');
			$xml_doc->formatOutput = true;
			$xml_root = $xml_doc->createElement('sitemapindex');
			$xml_root = $xml_doc->appendChild($xml_root);					
			foreach ($site_map_list_xmlfiles as $sitemap){
				$xml_item = $xml_doc->createElement('sitemap');
				$xml_item_loc = $xml_doc->createElement('loc');
				$xml_item_loc_value = $xml_doc->createTextNode($sitemap);
				$xml_item_loc->appendChild($xml_item_loc_value);
				$xml_item->appendChild($xml_item_loc);	
				
				$xml_lastmodified = $xml_doc->createElement('lastmod',date('Y-m-d H:i:s',$now));
				$xml_item->appendChild($xml_lastmodified);
				$xml_root->appendChild($xml_item);
			}
			$xml_body = $xml_doc->saveXML();
			file_put_contents($this->alading_output_path.'/wenku_update_sitemap.xml',$xml_body);			
			unset($xml_doc,$xml_root,$xml_body,$site_map_list_xmlfiles);
		}
		
		file_put_contents($process_log_file,date('Y-m-d H:i:s',time()).' 导出任务完成，正在标记已导出的视频...<br/>',FILE_APPEND);
		//标记本批导出的视频，	更新pushTime字段
		$total_exported = count($this->video_exported);
		if($total_exported > 0){
		    for($idx_todel=0;$idx_todel<$total_exported;$idx_todel=$idx_todel+self::WENKU_ALADING_XML_PER_PAGE){
		        $tmp_todel = array_slice($this->video_exported,$idx_todel,self::WENKU_ALADING_XML_PER_PAGE);
		        $hash_todel = join(',',$tmp_todel);
		        //更新视频的pushTime
                $update_time_sql = "update video SET `pushTime` = '$now' WHERE `hash` IN ({$hash_todel}) ";
                $this->mysqlQueryDb($this->mysql_r, $update_time_sql,'gbk');
    	        unset($tmp_todel,$hash_todel);
		    }
		}
        file_put_contents($process_log_file,date('Y-m-d H:i:s',time()).' 【恭喜】标记完成，增量任务全部完成！<br/>',FILE_APPEND);
	}

    /**
     * @param $hash
     * @return int|mixed
     * //从文库FE获取视频点击数
     */
    function getVideoPlayCount($hash) {
        if(isset($this->video_playcnt[$hash]) && $this->video_playcnt[$hash] > 0){
            return $this->video_playcnt[$hash];
        }
        $url = self::WENKU_VIDEO_PLAYCNT_INTERFACE_URL.$hash;
        $ret = $this->request($url);
        if($ret){
            $result = json_decode($ret,true);
            if(isset($result['playCnt'])){
                $this->video_playcnt[$hash] = $result['playCnt'];
                return $result['playCnt'];
            }
        }
        return 5000;
    }

    /**
     * @param $url
     * @param string $data
     * @return mixed
     */
    function request($url, $data = ''){
        $ssl = substr($url, 0, 8) == "https://" ? true : false;
        $ch  = curl_init();

        $POST_TYPE = $data ? 1 : false;
        curl_setopt($ch, CURLOPT_POST, $POST_TYPE);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        if ($ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }
        $revdata = curl_exec($ch);
        curl_close($ch);
        return $revdata;
    }


    /**
     * @param $url
     * @return string
     *  //获取视频文件扩展名
     */
    function getVideoFileExtension($url){
        $path_info = pathinfo($url);
        if(isset($path_info['extension'])){
            return $path_info['extension'];
        }
        return 'flv';
    }

    /**
     * @param $to
     * @param $title
     * @param $body
     * @param int $type
     * @return bool
     */
    function send_mail($to, $title, $body, $type=1){
		$subject = '=?UTF-8?B?'.base64_encode($title).'?=';
		$headers = "MIME-Version: 1.0"."\r\n";
		$headers .= "Content-type:text/html;charset=utf-8"."\r\n";
		$headers .= "From: chuanke-service@baidu.com";
	
		if($type == 0){
			//营销类
			ini_set('SMTP','edm.sys.baidu.com');
		} else {
			ini_set('SMTP','proxy-in.baidu.com');
		}
		$access_date = date('Ymd');
		$is_send = mail($to, $subject, $body, $headers);
		$txt = date('Y-m-d H:i:s')."\t".$to."\t".$title."\t".$is_send;
		file_put_contents("/tmp/email_".$access_date.".txt", $txt."\n", FILE_APPEND);
		return $is_send;
	}

    /**
     * @param $db
     * @param $sql
     * @param $log
     * @param string $name
     * @return bool|mysqli_result
     */
    function mysqlQueryDb($db,$sql,$log,$name='utf8') {
        try {
            $conn = mysqli_connect($db['server_name'], $db['username'], $db['password'], $db['database'], $db['port']);
            mysqli_set_charset($conn,$name);
            $result = mysqli_query($conn, $sql);
            mysqli_close($conn);
        } catch (Exception $e) {
            file_put_contents($log, "sql[" . $sql . "] errmsg[db error]\n",FILE_APPEND);
        }
        return $result;
    }


}

$cronApp = new ApplicationUpdate();
$cronApp->Run();