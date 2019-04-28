<?php
ini_set('display_errors', 'On');
require_once './GoogleTranslate.php';
use \Statickidz\GoogleTranslate;

$tieba_users = [
    // 第一个百度账号配置，扩充请复制
    '用户名' => [ /*用户名，仅供标示用不需要是百度ID*/
        'bduss' => '', // 百度账号BDUSS Cookie
        // 回贴客户端 0:WAP|1:iPhone|2:Android|3:WindowsPhone|4:Windows8UWP
        // 随机或固定客户端二选一
        'reply_device' = rand(0, 4) // 随机
        //'reply_device' = 0 // 固定
    ],
];

$_GET['username'] = (string)$_GET['username'];
$_GET['tieba'] = (string)$_GET['tieba']; // 贴吧名称
$_GET['tid'] = (int)$_GET['tid']; //帖子tid
$_GET['twitter'] = urlencode($_GET['twitter']); //*用户id或话题 **话题前必须带'#'
$_GET['using_translate'] = (bool)$_GET['using_translate'];
$tieba_user = $tieba_users[$_GET['username']];

$curl_mobile_device_ua = 'Mozilla/5.0 (Linux; Android 8.0.0; Pixel 2 XL Build/OPD1.170816.004) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/71.0.3578.98 Mobile Safari/537.36';

function post_reply(string $bduss, string $tbs, int $devices, int $tid, int $fid, string $tieba, string $content): string {
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
        curl_setopt($curl, CURLOPT_USERAGENT, $curl_mobile_device_ua);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($curl, CURLOPT_COOKIE, "BDUSS={$bduss};");
        return json_decode(curl_exec($curl), true);
    } else {
        // 客户端回贴
        $post_data = [
            'BDUSS' => $bduss,
            '_client_id' => 'wappc_136'.mt_rand(1000000000, 9999999999).'_'.mt_rand(100, 999),
            '_client_type' => $devices,
            '_client_version' => '8.8.8',
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
        // tieba client sign
        foreach ($post_data as $key => $value) {
            $sign .= "{$key}={$value}";
        }
        $post_data['sign'] = strtoupper(md5($sign.'tiebaclient!!!'));
        $curl = curl_init('http://c.tieba.baidu.com/c/c/post/add');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_USERAGENT, $curl_mobile_device_ua);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post_data));
        curl_setopt($curl, CURLOPT_COOKIE, "BDUSS={$bduss};");
        return json_decode(curl_exec($curl), true);
    }
}

function get_tieba_fid(string $forum): int {
    return json_decode(file_get_contents("http://tieba.baidu.com/f/commit/share/fnameShareApi?ie=utf-8&fname={$forum}"), true)['data']['fid'];
}

function get_tbs(string $bduss): string {
    $curl = curl_init('http://tieba.baidu.com/dc/common/tbs');
    curl_setopt($curl, CURLOPT_COOKIE, "BDUSS={$bduss};");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    return json_decode(curl_exec($curl), true)['tbs'];
}

function get_from_mobile_twitter(string $url, string $type = 'get', array $header = [], $post_data = null) {
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_USERAGENT, $curl_mobile_device_ua);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    if ($type == 'post') {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    }
    return curl_exec($curl);
}

// 推文锁，判断推文是否已被发送过
if (is_file("{$_GET['twitter']}_posted.csv")) {
    $tweets_posted = str_getcsv(file_get_contents("{$_GET['twitter']}_posted.csv")); // possible OOM, should only read tail lines
} else {
    $tweets_posted = [0];
}

// 推特id是否为话题
$tw_is_topic = substr($_GET["twitter"], 0, 1) == '#' ? true : false;
// 推文是否有更新
if ($tw_is_topic) {
    $tw_update_check_json = json_decode(file_get_contents('https://twitter.com/i/search/timeline?q=' . urlencode($_GET["twitter"]) . "&latent_count=1&min_position={$tweets_posted[0]}"), true);
} else {
    $tw_update_check_json = json_decode(file_get_contents("https://twitter.com/i/profiles/show/{$_GET["twitter"]}timeline/tweets?composed_count=0&include_available_features=0&include_entities=0&include_new_items_bar=true&interval=30000&latent_count=0&min_position={$tweets_posted[0]}"), true);
}
if ($tw_update_check_json["new_latent_count"] > 0) {
    preg_match('/gt=([0-9]*);/', get_from_mobile_twitter('https://mobile.twitter.com/'), $tw_guest_token);
    $tw_curl_auth_header = 'authorization: Bearer AAAAAAAAAAAAAAAAAAAAANRILgAAAAAAnNwIzUejRCOuH5E6I8xnZz4puTs%3D1Zv7ttfk8LF81IUq16cHjhLTvJu4FA33AGWWjCpTnA';
    if ($tw_is_topic) {
        $tw_user_info = json_decode(get_from_mobile_twitter(
            'https://api.twitter.com/graphql/GpQevDzQ0VLTV4-68vrePA',
            'post',
            [
                $tw_curl_auth_header,
                'content-type: application/json',
                "x-guest-token: {$tw_guest_token[1]}"
            ],
            json_encode([
                'variables' => json_encode(["screen_name" => $_GET["twitter"], "withHighlightedLabel" => true]),
                'queryId' => 'GpQevDzQ0VLTV4-68vrePA'
            ])
        ), true);
        $tweets = json_decode(get_from_mobile_twitter(
            "https://api.twitter.com/2/timeline/profile/{$tw_user_info['data']['user']['rest_id']}.json?{$tw_user_info['data']['user']['rest_id']}&count={$tw_update_check_json["new_latent_count"]}",
            'get',
            [
                $tw_curl_auth_header,
                "x-guest-token: {$tw_guest_token[1]}"
            ]
        ), true);
    } else {
        $tweets = json_decode(get_from_mobile_twitter(
            'https://api.twitter.com/2/search/adaptive.json?q=' . urlencode($_GET["twitter"]) . '&count=20&query_source=hash&pc=1',
            'get',
            [
                $tw_curl_auth_header,
                "x-guest-token: {$tw_guest_token[1]}"
            ]
        ), true);
    }
    
    $tieba_tbs = get_tbs($tieba_user['bduss']);
    $tieba_fid = get_tieba_fid($_GET['tieba']);
    foreach ($tweets["globalObjects"]["tweets"] as $tweet_id => $tweet) {
        if (in_array($tweet_id, $tweets_posted) == false) {
            $tweet_content = $tweet["text"]; // 文字内容
            // 好了好了，现在支持了 2019-04-25 // 不支持推特话题链接 2017
            $tweet_content = str_replace(["https://t.co/[0-9a-zA-Z]{10}"], '', $tweet_content); // 去除链接
            $translated_result = $_GET['using_translate'] == true ? (new GoogleTranslate)->translate('auto', 'zh-CN', $tweet_content) : null;

            $reply_content = "ID：{$_GET['twitter']}\n推文：{$tweet_content}\n" . (isset($translated_result) ? "翻译：{$translated_result}\n" : null) . "链接：https://twitter.com/{$_GET['twitter']}/status/{$tweet_id}"; // 回帖内容
            $reply_result = post_reply($tieba_user['bduss'], $tieba_tbs, $tieba_user['reply_device'], $_GET['tid'], $tieba_fid, $_GET['tieba'], $reply_content);

            if ($reply_result[$tieba_user['reply_device'] == 0 ? 'no' : 'error_code'] == 0) {
                // 成功回复后打推文log
                $tweet_log = fopen("{$_GET['twitter']}_posted.csv", 'a');
                flock($tweet_log, LOCK_EX); // 
                fwrite($tweet_log, "\"{$tweet_id}\",\n"); // csv format
                fclose($tweet_log);
            }

            // 打回复log
            $reply_log = [
                'reply_time' => date(DATE_ISO8601),
                'twitter_name' => $_GET['twitter'],
                'tweet_id' => $tweet_id,
                'tweet_content' => str_replace(["\r\n", "\n"], '', $tweet_content),
                'reply_content' => $reply_content,
                'reply_device' => $tieba_user['reply_device'],
                'reply_result' => implode(' ', $reply_result)
            ];
            $reply_log_file = fopen('reply_log.json', 'a');
            flock($reply_log_file, LOCK_EX);
            fwrite($reply_log_file, json_encode($reply_log, JSON_UNESCAPED_UNICODE) . "\n");
            fclose($reply_log_file);

            sleep(1); // reply interval 1s
        }
    }
}