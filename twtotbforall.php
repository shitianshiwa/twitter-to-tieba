<?php
header("Content-type: text/html; charset=utf-8");
ignore_user_abort(true);
require dirname(__FILE__).'/settings.php';
require dirname(__FILE__).'/scurl.php';
function  head ($bduss){
	return scurl('http://top.baidu.com/user/pass','get','','BDUSS='.$bduss,'www.baidu.com',1);
}
if(file_exists(dirname(__FILE__).'/db')!=1){
    mkdir(dirname(__FILE__).'/db',0777);
}
$name=urlencode($argv[1]);//urlencode($_GET['name']);
if(strlen($name)<=0){
    echo 'id不得为空';
    die;
}
$tid=$argv[2];//$_GET['tid'];
$tbn=urlencode($argv[3]);//urlencode(($_GET['tbn']));
$ti=urlencode($argv[4]);//urlencode(($_GET['ti']));
$la='zh';//urlencode(($_GET['la']));//urlencode($argv[5]);
$bdname=json_decode(head($bduss),1)["un"];
$del=array("goo.gl","twitter","google","YouTube","youtu.be","「","」","『","』","｢","｣","ow.ly");
$fid=json_decode(file_get_contents('http://tieba.baidu.com/f/commit/share/fnameShareApi?ie=utf-8&fname='.$tbn),1)["data"]["fid"];
$tbs=json_decode(scurl('http://tieba.baidu.com/dc/common/tbs','','',$cookie,'',1),1)["tbs"];
$api=file_get_contents('https://mobile.twitter.com/'.$name);
preg_match_all('/<div[^>]*?>(.[\s\S]*?)<\/div>/u',$api,$kd1);
preg_match_all('/<div class="tweet-text" data-id="(.+?)">/u',$api,$kd2);
preg_match('/<strong class=\"fullname\">(.+?)<\/strong>/',$api,$kd3);
$text=chop(str_replace($del," * ",str_replace("@","@ ",strip_tags($kd1[1][8]))));
$tweetid=$kd2[1][0];
$check=file(dirname(__FILE__).'/db/'.$name.'.txt')[0];
if ($tweetid > $check){
	if (strlen($text)>0){
		$language=json_decode(scurl('https://www.translate.com/translator/ajax_lang_auto_detect',1,'text_to_translate='.urlencode($text),'','https://www.translate.com/',1),1)["language"];
		    $translate=json_decode(scurl('https://www.translate.com/translator/ajax_translate',1,'text_to_translate='.urlencode($text).'&source_lang='.$language.'&translated_lang='.$la.'&use_cache_only=false','','https://www.translate.com/',1),1)["translated_text"];
		    $tr='翻译:'.chop(urlencode(htmlentities(str_replace($del," * ",$translate),ENT_DISALLOWED,'UTF-8',0)))."\n";
		if (strlen($translate)>0){
			$data='co='.str_replace($del," * ",$kd3[1]).'(@'.str_replace($del,' * ',$name).')：'."\n".urlencode(htmlentities($text,ENT_DISALLOWED,'UTF-8',0))."\n".$tr."\n".'&ti='.$ti.'&src=1&word='.$bdname.'&tbs='.$tbs.'&ifpost=1&ifposta=0&post_info=0&tn=baiduWiseSubmit&fid='.$fid.'&verify=&verify_2=&pinf=1_2_0&pic_info=&z='.$tid.'&last=0&pn=0&r=0&see_lz=0&no_post_pic=0&floor=23&sub1=%E5%9B%9E%E8%B4%B4';
			
			$a=scurl('http://tieba.baidu.com/mo/q---116ED871A6E72C4C43D64EF10253A2CA%3AFG%3D1--1-3-0--2--wapp_1507042257801_296/submit',1,$data,$cookie,'http://tieba.baidu.com/mo/q---116ED871A6E72C4C43D64EF10253A2CA%3AFG%3D1--1-3-0--2--wapp_1507042257801_296/m?kz='.$tid.'&is_bakan=0&lp=5010&pinf=1_2_0',1);
			if (!preg_match('/<span class=\"light\">回贴成功<\/span>/',$a)){
			    //preg_match('/<div class=\"d\">未知错误,请返回重试<\/div>',$a,$whyerror);
			    $result='发送失败';
			}else {
				$result='发送成功';
			}
			echo $result;
			$log=fopen(dirname(__FILE__).'/db/log.txt',"a");
			flock($log,LOCK_EX);
			fwrite($log,'['.time().','.$result.',"'.$name.'","'.$text.'","'.$translate.'"]'."\r\n");
			fclose($log,LOCK_UN);
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
