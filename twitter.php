<?php
header("Content-type: text/html; charset=utf-8");
ignore_user_abort(true);
$name=$_GET['name'];
$id=$_GET['id'];
$url = 'https://mobile.twitter.com/'.$name;
$zh = curl_init($url);
curl_setopt($zh,CURLOPT_HEADER,'Content-type:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8');
curl_setopt($zh,CURLOPT_USERAGENT,'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 6 Build/LYZ28E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Mobile Safari/537.36');
curl_setopt($zh,CURLOPT_RETURNTRANSFER,1);
$content = curl_exec($zh);
curl_close($zh);
preg_match('/<script type(.+?)>(.+?)<\/script>/ies',$content,$a);
$b=json_decode($a[2],1);
$from=$b["state"]["pageData"]["title"];
$text=$b["state"]["timeline"]["items"][$id]["data"]["tweet"]["text"]["textParts"][0]["text"];
$time=$b["state"]["timeline"]["items"][$id]["data"]["tweet"]["utcTimestamp"];
echo $from.''.$text.''.$time;
