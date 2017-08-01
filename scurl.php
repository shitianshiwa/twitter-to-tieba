<?php
function  scurl ($url,$wp,$data,$cookie,$referer){
	$ch=curl_init($url);
	curl_setopt($ch,CURLOPT_USERAGENT ,'Mozilla/5.0 (Linux; Android 5.1.1; Nexus 6 Build/LYZ28E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/55.0.2883.87 Mobile Safari/537.36');
	curl_setopt($ch,CURLOPT_RETURNTRANSFER ,1);
	if(strlen($cookie)>0){
	    curl_setopt($ch,CURLOPT_COOKIE ,$cookie);
	}
	if(strlen($referer)>0){
	    curl_setopt($ch, CURLOPT_REFERER,$referer);
	}
	if ($wp=1){
		curl_setopt($ch,CURLOPT_POST ,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS ,$data);
	}
	$content=curl_exec($ch);
	curl_close($ch);
	return $content;
}