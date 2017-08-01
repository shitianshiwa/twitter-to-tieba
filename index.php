<?php
header("Content-type: text/html; charset=utf-8");
ignore_user_abort(true);
require dirname(__FILE__).'/settings.php';
require dirname(__FILE__).'/scurl.php';
$name=urlencode($argv[1]);
$tid=$argv[2];
$tbn=urlencode($argv[3]);
$fid=json_decode(file_get_contents('http://tieba.baidu.com/f/commit/share/fnameShareApi?ie=utf-8&fname='.$tbn),1)["data"]["fid"];
$tbs=json_decode(scurl ('http://tieba.baidu.com/dc/common/tbs','','',$cookie,''),1)["tbs"];
$api=file_get_contents('https://mobile.twitter.com/'.$name);
preg_match_all('/<div[^>]*?>(.[\s\S]*?)<\/div>/u',$api,$kd1);
preg_match_all('/<div class="tweet-text" data-id="(.+?)">/u',$api,$kd2);
$pic='';
$text=urlencode(str_replace("twitter"," * ",str_replace("goo.gl"," * ",str_replace("@","@ ",strip_tags($kd1[1][8])))));
$tweetid=$kd2[1][0];
$check=file($name.'.txt')[0];
if ($tweetid>$check){
	if (strlen($text)>0){
		$data=str_replace("10;","",'co='.$name.':'.htmlentities($text).$pic.'&_t=1484059982471&tag=11&upload_img_info=&fid='.$fid.'&src=1&word='.$tbn.'&tbs='.$tbs.'&z='.$tid);
		$a=json_decode(scurl ('https://tieba.baidu.com/mo/q/apubpost',1,$data,$cookie,'http://tieba.baidu.com/p/'.$tid.'?pn=0&'),1);
		$log=fopen('log.txt',"a");
		flock($log,LOCK_EX);
		fwrite($log,'['.time().','.json_encode($a).','.$name.','.urldecode($text).','.$translate.']'."\r\n");
		fclose($log,LOCK_UN);
		if ($a['no']>"0"){
			echo "失败！";
		}else {
			echo '发送成功';
		}
		$fp=fopen($name.'.txt',"w");
		flock($fp,LOCK_EX);
		fwrite($fp,$tweetid);
		fclose($fp,LOCK_UN);
	}else {
		echo '字数不能为0';
	}
}else {
	echo '该推文已被发送过';
}