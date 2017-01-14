<?php
header("Content-type: text/html; charset=utf-8");
ignore_user_abort(true);
$url = 'https://tieba.baidu.com/mo/q/apubpost';
$t = file_get_contents("./twitter.php?name=LoveLive_staff&id=0");//别忘了改这里
$tbs='';//tbs这不用我说了吧
$fid='';//贴吧对应的fid
$tid='';//帖子对应的tid
$word=urlencode('');//贴吧名称
$data = "co=$t&_t=1483857485637&tag=11&upload_img_info=&fid=$fid&src=1&word=$word&tbs=$tbs&z=4863894912&lp=6026";
$cookie='';//你的cookie
$ch = curl_init($url);
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 6 Build/LYZ28E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Mobile Safari/537.36');
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_COOKIE,$cookie);
curl_setopt($ch, CURLOPT_REFERER,'https://tieba.baidu.com/p/'.$tid.'?pn=0&');
curl_setopt($ch, CURLOPT_POST,1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
$content = curl_exec($ch);
curl_close($ch);
echo $content;
