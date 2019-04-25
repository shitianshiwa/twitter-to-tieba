<?php
ini_set('display_errors', 'On');
require 'src/GoogleTranslate.php';
use \Statickidz\GoogleTranslate;
$_GET = [];

// 百度账号BDUSS Cookie
$tieba_bduss = '';
//其他设置
$_GET['tieba'] = "";//贴吧名称
$_GET['twitter'] = "";//urlencode($_GET['twitter']);//*用户id或话题 **话题前必须带'#'
$_GET['tid'] = 0;//(int)($_GET['tid']);//帖子tid

// 是否使用谷歌翻译
$use_Google_Translate = true;
$reply_devices = 0;//客户端待处理
//// 回贴客户端 0:WAP|1:iPhone|2:Android|3:WindowsPhone|4:Windows8UWP 默认随机
//$reply_devices = rand(0, 4);
//// iPhone回贴秒吞
//$reply_devices = $reply_devices == 1 ? rand(2, 4) : $reply_devices;
function post_reply($bduss, $tbs, $devices, $tid, $fid, $tieba, $content) {
    if ($devices == 0) {
        // WAP回贴
        $post_data = [
            'co' => $content,
            'src' => 1,
            'word' => $tieba,
            'tbs' => $tbs,
            'fid' => $fid,
            'z' => $tid
        ];
        $curl = curl_init('http://tieba.baidu.com/mo/q/apubpost');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($curl, CURLOPT_COOKIE, "BDUSS={$bduss};");
        return json_decode(curl_exec($curl), true);
    } else {
        // 客户端回贴
        $post_data = [
            'BDUSS' => $bduss,
            '_client_id' => 'wappc_136'.mt_rand(1000000000, 9999999999).'_'.mt_rand(100, 999),
            '_client_type' => $devices,
            '_client_version' => '6.5.2',
            '_phone_imei' => md5($bduss),
            'anonymous' => 0,
            'content' => $content,
            'fid' => $fid,
            'kw' => $tieba,
            'net_type' => 3,
            'tbs' => $tbs,
            'tid' => $tid,
            'title' => ''
        ];
        foreach ($post_data as $key => $value) {
            $sign .= "{$key}={$value}";
        }
        $post_data['sign'] = strtoupper(md5($sign.'tiebaclient!!!'));
        $curl = curl_init('http://c.tieba.baidu.com/c/c/post/add');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($curl, CURLOPT_COOKIE, "BDUSS={$bduss};");
        return json_decode(curl_exec($curl), true);
    }
}

function get_tieba_fid($forum) {
    return json_decode(file_get_contents("http://tieba.baidu.com/f/commit/share/fnameShareApi?ie=utf-8&fname={$forum}"), true)['data']['fid'];
}

function get_tbs($bduss) {
    $curl = curl_init('http://tieba.baidu.com/dc/common/tbs');
    curl_setopt($curl, CURLOPT_COOKIE, "BDUSS={$bduss};");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    return json_decode(curl_exec($curl), true)['tbs'];
}
function get_from_mobile_twitter($url, $type = 'get', $header = [], $post_data = null){
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Linux; Android 8.0.0; Pixel 2 XL Build/OPD1.170816.004) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Mobile Safari/537.36');
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    if($type == 'post'){
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    }
    return curl_exec($curl);
}
//内容锁，判断是否有更新
if(is_file("{$_GET['twitter']}_posted.csv"))
    $tweets_posted = str_getcsv(file_get_contents("{$_GET['twitter']}_posted.csv"));
else
    $tweets_posted = [0];
//判断是否为话题
$is_topic = substr($_GET["twitter"], 0, 1) == '#' ? true : false;
//追踪是否有更新
if ($is_topic) {
    $check_update = json_decode(file_get_contents("https://twitter.com/i/search/timeline?q=" . urlencode($_GET["twitter"]) . "&latent_count=1&min_position={$tweets_posted[0]}"), true);
} else {
    $check_update = json_decode(file_get_contents('https://twitter.com/i/profiles/show/' . $_GET["twitter"] . '/timeline/tweets?composed_count=0&include_available_features=0&include_entities=0&include_new_items_bar=true&interval=30000&latent_count=0&min_position=' . $tweets_posted[0]), true);
}
if($check_update["new_latent_count"] > 0){
    $tbs = get_tbs($tieba_bduss);
    $fid = get_tieba_fid($_GET['tieba']);
    preg_match('/gt=([0-9]*);/', get_from_mobile_twitter('https://mobile.twitter.com/'), $get_gt);
    if($is_topic){
        $user_info = json_decode(get_from_mobile_twitter('https://api.twitter.com/graphql/GpQevDzQ0VLTV4-68vrePA', 'post', ["authorization: Bearer AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA", "content-type: application/json", "x-guest-token: " . $get_gt[1]], json_encode(["variables" => json_encode(["screen_name" => $_GET["twitter"], "withHighlightedLabel" => true]), "queryId" => "GpQevDzQ0VLTV4-68vrePA"])), true);
        $tweets = json_decode(get_from_mobile_twitter('https://api.twitter.com/2/timeline/profile/' . $user_info["data"]["user"]["rest_id"] . '.json?' . $user_info["data"]["user"]["rest_id"] . '&count=' . $check_update, 'get', ["authorization: Bearer AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA", "x-guest-token: " . $get_gt[1]]), true);
    }else{
        $tweets = json_decode(get_from_mobile_twitter("https://api.twitter.com/2/search/adaptive.json?q=" . urlencode($_GET["twitter"]) . "&count=20&query_source=hash&pc=1", 'get', ["authorization: Bearer AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA", "x-guest-token: " . $get_gt[1]]), true);
    }
    foreach ($tweets["globalObjects"]["tweets"] as $tweet_id => $tweet) {
        //$tweet_id =  $tweet[1];//tweets id
        $tweet_content = $tweet["text"];//文字内容
        //好了好了，现在支持了// 不支持推特话题链接
        $tweet_content = str_replace(["https://t.co/[0-9a-zA-Z]{10}"], '', $tweet_content); // 去除链接
        if (in_array($tweet_id, $tweets_posted) == false) {
            $translate_result = $use_Google_Translate == true ? (new GoogleTranslate) -> translate('auto', 'zh-CN', $tweet_content) : null;
        echo "Should post reply about Tweet({$tweet_id}) with content {$tweet_content} on tieba forum {$_GET['tieba']} with thread id {$_GET['tid']} via wap post port" . (isset($translate_result) ? ', using Google Translate Service' : null) ."\r\n";
            $reply_content = "用户名：@{$_GET['twitter']}\r\n推文：{$tweet_content}\r\n" . (isset($translate_result) ? "翻译：{$translate_result}\r\n" : null) . "推文链接：https://twitter.com/{$_GET['twitter']}/status/{$tweet_id}";//将要发送到贴吧的文字
            $reply_result = post_reply($tieba_bduss, $tbs, $reply_devices, $_GET['tid'], $fid, $_GET['tieba'], $reply_content);
            if ($reply_result[$reply_devices == 0 ? 'no' : 'error_code'] == 0) {
                echo 'successful posted';
                $tweet_log = fopen("{$_GET['twitter']}_posted.csv", 'a');//这个是锁
                flock($tweet_log, LOCK_EX);
                fwrite($tweet_log, "\"{$tweet_id}\",\r\n");
                fclose($tweet_log);
            } else {
                echo "reply failed, error code:{$reply_result[$reply_devices == 0 ? 'no' : 'error_code']}, reason: {$reply_result[$reply_devices == 0 ? 'error' : 'msg']}";
            }
            //这个是log
            $reply_log = [
                'reply_time' => date(DATE_ISO8601),
                'twitter_name' => $_GET['twitter'],
                'tweet_id' => $tweet_id,
                'tweet_content' => str_replace(["\r\n", "\n"], '', $tweet_content),
                'reply_content' => $reply_content,
                'reply_devices' => $reply_devices,
                'reply_result' => implode(' ', $reply_result)
            ];
            $reply_log_file = fopen('reply_log.json', 'a');
            flock($reply_log_file, LOCK_EX);
            fwrite($reply_log_file, json_encode($reply_log, JSON_UNESCAPED_UNICODE) . "\r\n");
            fclose($reply_log_file);
            sleep(1);
        }
    }
}