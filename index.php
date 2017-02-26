<?php
header("Content-type: text/html; charset=utf-8");
ignore_user_abort(true);
require dirname(__FILE__).'/settings.php';
$name=urlencode($_GET['name']);
$tid=urlencode($_GET['tid']);
$tbn=urlencode($_GET['tbn']);
$fid=json_decode(file_get_contents('http://tieba.baidu.com/f/commit/share/fnameShareApi?ie=utf-8&fname='.$tbn),1)["data"]["fid"];
$tbscurl=curl_init('http://tieba.baidu.com/dc/common/tbs');
curl_setopt($tbscurl,CURLOPT_USERAGENT,'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 6 Build/LYZ28E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Mobile Safari/537.36');
curl_setopt($tbscurl,CURLOPT_COOKIE,$cookie);
curl_setopt($tbscurl,CURLOPT_RETURNTRANSFER,1);
$tbsjson = curl_exec($tbscurl);
curl_close($tbscurl);
$tbs=json_decode($tbsjson,1)["tbs"];
$twurl = 'https://mobile.twitter.com/'.$name;
$zh = curl_init($twurl);
curl_setopt($zh,CURLOPT_USERAGENT,'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 6 Build/LYZ28E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Mobile Safari/537.36');
curl_setopt($zh,CURLOPT_RETURNTRANSFER,1);
$api = curl_exec($zh);
curl_close($zh);
preg_match('/<script type(.+?)>(.+?)<\/script>/ies',$api,$json);
$b=json_decode($json[2],1);
$pic='图片地址:'.$b["state"]["timeline"]["items"][0]["data"]["tweet"]["inlineMedia"]["mediaDetails"]["imageUrl"];
$from=$b["state"]["pageData"]["title"];
$text=$b["state"]["timeline"]["items"][0]["data"]["tweet"]["text"]["textString"];
$textold=str_replace("t.co","*",str_replace("goo.gl","*",str_replace("@","@ ",str_replace("\n"," ",str_replace("&"," ",str_replace(">"," ",str_replace("<"," ",str_replace("'"," ",str_replace('"'," ",$b["state"]["timeline"]["items"][0]["data"]["tweet"]["text"]["textString"])))))))));
$time=$b["state"]["timeline"]["items"][0]["data"]["tweet"]["timestamp"];
$tweetid=$b["state"]["timeline"]["items"][0]["data"]["tweet"]["id"];
$check= file($name.'.txt')[0];
if($tweetid>$check){
$url = 'https://tieba.baidu.com/mo/q/apubpost';
$t = $from.':'.$textold.' '.$time;
if(strlen($t)>0){
$getlanguage='text_to_translate='.$text;
$gl = curl_init('https://www.translate.com/translator/ajax_lang_auto_detect');
curl_setopt($gl,CURLOPT_RETURNTRANSFER,1);
curl_setopt($gl, CURLOPT_REFERER,'https://www.translate.com/');
curl_setopt($gl, CURLOPT_POST,1);
curl_setopt($gl, CURLOPT_POSTFIELDS,"$getlanguage");
$languagejson = curl_exec($gl);
curl_close($gl);
$language=json_decode($languagejson,1)["language"];
$tr='text_to_translate='.$text.'&source_lang='.$language.'&translated_lang=zh&use_cache_only=false';
$bdtr = curl_init('https://www.translate.com/translator/ajax_translate');
curl_setopt($bdtr,CURLOPT_RETURNTRANSFER,1);
curl_setopt($bdtr, CURLOPT_REFERER,'https://www.translate.com/');
curl_setopt($bdtr, CURLOPT_POST,1);
curl_setopt($bdtr, CURLOPT_POSTFIELDS,"$tr");
$bdback = curl_exec($bdtr);
curl_close($bdtr);
$translate=json_decode($bdback,1)["translated_text"];
if(strlen($translate)>0){
$data = 'co='.$t.'
翻译:'.str_replace("t.co","*",str_replace("goo.gl","*",str_replace("\n"," ",str_replace("&"," ",str_replace(">"," ",str_replace("<"," ",str_replace("'"," ",str_replace('"',' ',$translate)))))))).'
'.$pic.&upload_img_info=&fid='.$fid.'&src=1&word='.$tbn.'&tbs='.$tbs.'&z='.$tid;
$ch = curl_init($url);
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 6 Build/LYZ28E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Mobile Safari/537.36');
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_COOKIE,$cookie);
curl_setopt($ch, CURLOPT_REFERER,'http://tieba.baidu.com/p/'.$tid.'?pn=0&');
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS,"$data");
$content = curl_exec($ch);
curl_close($ch);
$a = json_decode($content,1);
$log = fopen('log.txt',"a");
    flock($log,LOCK_EX);
    fwrite($log,'["'.time().'","'.$content.'","'.$name.'","'.$t.'","'.$translate.'"]'."\r\n");
    fclose($log,LOCK_UN);
if($a['no']>"0") {
  echo "失败！";
}else {echo '发送成功';}
$fp = fopen($name.'.txt',"w");
    flock($fp,LOCK_EX);
    fwrite($fp,$tweetid);
    fclose($fp,LOCK_UN);
}else{echo '翻译字数不能为0';}
}else{echo '字数不能为0';}
}else{echo '该推文已被发送过';}
