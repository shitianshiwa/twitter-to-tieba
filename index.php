<?php
header("Content-type: text/html; charset=utf-8");
ignore_user_abort(true);
require dirname(__FILE__).'/settings.php';
require dirname(__FILE__).'/scurl.php';
if(file_exists('db')!=1){
    mkdir('db');
}
$name=urlencode($argv[1]);//urlencode($_GET['name']);
if(strlen($name)<=0){
    echo 'id不得为空';
    die;
}
$tid=$argv[2];//$_GET['tid'];
$tbn=urlencode($argv[3]);//urlencode(($_GET['tbn']));
$la='zh';//urlencode(($_GET['la']));//urlencode($argv[4]);
$fid=json_decode(file_get_contents('http://tieba.baidu.com/f/commit/share/fnameShareApi?ie=utf-8&fname='.$tbn),1)["data"]["fid"];
$tbs=json_decode(scurl('http://tieba.baidu.com/dc/common/tbs','','',$cookie,''),1)["tbs"];
$api=file_get_contents('https://mobile.twitter.com/'.$name);
preg_match_all('/<div[^>]*?>(.[\s\S]*?)<\/div>/u',$api,$kd1);
preg_match_all('/<div class="tweet-text" data-id="(.+?)">/u',$api,$kd2);
$text=chop(str_replace(array("goo.gl","twitter","google","YouTube","youtu.be","「","」")," * ",str_replace("@","@ ",strip_tags($kd1[1][8]))));
$tweetid=$kd2[1][0];
$check=file(dirname(__FILE__).'/db/'.$name.'.txt')[0];
if ($tweetid > $check){
	if (strlen($text)>0){
		$language=json_decode(scurl('https://www.translate.com/translator/ajax_lang_auto_detect',1,'text_to_translate='.urlencode($text),'','https://www.translate.com/',1),1)["language"];
		    $translate=json_decode(scurl('https://www.translate.com/translator/ajax_translate',1,'text_to_translate='.urlencode($text).'&source_lang='.$language.'&translated_lang='.$la.'&use_cache_only=false','','https://www.translate.com/',1),1)["translated_text"];
		    $tr='翻译:'.chop(urlencode(htmlentities(str_replace(array("goo.gl","twitter","google","人们致以","「","」")," * ",$translate),ENT_DISALLOWED,'UTF-8',0)))."\n";
		if (strlen($translate)>0){
			$data='co='.$kd3[1].'(@'.$name.')：'."\n".urlencode(htmlentities($text,ENT_DISALLOWED,'UTF-8',0))."\n".$tr."\n".'&_t=1484059982471&tag=11&upload_img_info='.${$name}.'&fid='.$fid.'&src=1&word='.$tbn.'&tbs='.$tbs.'&z='.$tid;
			$a=json_decode(scurl('https://tieba.baidu.com/mo/q/apubpost',1,$data,$cookie,'http://tieba.baidu.com/p/'.$tid.'?pn=0&',1),1);
			$log=fopen(dirname(__FILE__).'/db/log.txt',"a");
			flock($log,LOCK_EX);
			fwrite($log,'['.time().','.json_encode($a).',"'.$name.'","'.$text.'","'.$translate.'"]'."\r\n");
			fclose($log,LOCK_UN);
			if ($a['no']!="0"){
				echo "错误代码#".$a['no'].',错误原因：'.$a['error'];
			}else {
				echo '发送成功';
			}
			$fp=fopen(dirname(__FILE__).'/db/'.$name.'.txt',"w");
			flock($fp,LOCK_EX);
			fwrite($fp,$tweetid);
			fclose($fp,LOCK_UN);
		}else {
			echo '翻译字数不能为0';
		}
	}else {
		echo '字数不能为0';
	}
}else {
	echo '该推文已被发送过';
}
