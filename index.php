<?php
ini_set('display_errors', 'On');
ini_set('max_execution_time', 0);
//ignore_user_abort(true);
require_once ('src/GoogleTranslate.php');
use \Statickidz\GoogleTranslate;

// 百度账号BDUSS Cookie
$tieba_bduss = '';
// 是否使用谷歌翻译
$use_Google_Translate = true;
// 回贴客户端 0:WAP|1:iPhone|2:Android|3:WindowsPhone|4:Windows8UWP 默认随机
$reply_devices = rand(0, 4);
// iPhone回贴秒吞
$reply_devices = $reply_devices == 1 ? 0 : $reply_devices;
$_GET['twitter'] = urlencode($_GET['twitter']);
$_GET['tid'] = (int)($_GET['tid']);

function post_reply($bduss, $tbs, $devices, $tid, $fid, $tieba, $content) {
    if ($devices == 'WAP') {
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

$twitter = file_get_contents("https://mobile.twitter.com/{$_GET['twitter']}");
$tbs = get_tbs($tieba_bduss);
$fid = get_tieba_fid($_GET['tieba']);
preg_match_all('/<div class="tweet-text" data-id="(\d*)">\n(.*?)\n<\/div>/', $twitter, $tweets, PREG_SET_ORDER);

foreach ($tweets as $tweet) {
    $tweet_id =  $tweet[1];
    $tweet_content = $tweet[2];
    // 不支持推特话题链接
    preg_match_all('/<a .*?data-url="(.*?)".*?>(.*?)<\/a>/', $tweet_content, $links, PREG_SET_ORDER);
    $tweet_content = trim(strip_tags($tweet_content));
    foreach ($links as $link) {
        $tweet_content = str_replace($link[2], $link[1], $tweet_content);
    }
    //$tweet_content = str_replace(['http://', 'https://'], '', $tweet_content); // 去除链接
    $tweets_posted = str_getcsv(file_get_contents("{$_GET['twitter']}_posted.csv"));
    if (in_array($tweet_id, $tweets_posted) == false) {
        $translate_result = $use_Google_Translate == true ? (new GoogleTranslate) -> translate('auto', 'zh-CN', $tweet_content) : null;
        echo "Should post reply about Tweet({$tweet_id}) with content {$tweet_content} on tieba forum {$_GET['tieba']} with thread id {$_GET['tid']} via wap post port" . (isset($translate_result) ? ', using Google Translate Service' : null) ."\r\n";
        $reply_content = "用户名：@{$_GET['twitter']}\r\n推文：{$tweet_content}\r\n" . (isset($translate_result) ? "翻译：{$translate_result}\r\n" : null) . "推文链接：https://twitter.com/{$_GET['twitter']}/status/{$tweet_id}";
        $reply_result = post_reply($tieba_bduss, $tbs, $reply_devices, $_GET['tid'], $fid, $_GET['tieba'], $reply_content);
        if ($reply_result['no'] == 0) {
            echo 'successful posted';
            $tweet_log = fopen("{$_GET['twitter']}_posted.csv", 'a');
            flock($tweet_log, LOCK_EX);
            fputcsv($tweet_log, "{$tweet_id}\r\n");
            fclose($tweet_log);
        } else {
            echo "reply failed, error code:{$reply_result['no']}, reason: {$reply_result['error']}";
        }
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
        sleep(1000);
    }
}
