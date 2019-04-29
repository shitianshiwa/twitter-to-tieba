# twitter-to-tieba v5.1
将指定用户的推特自动转发到贴吧

## 功能
* 将任意推特用户的转发到贴吧的主题贴下
* 可设置多个贴吧用户与多套推特转发参数
* [推文Google翻译（机翻不完美），使用此库](https://github.com/statickidz/php-google-translate-free)
* 选择/随机回贴客户端（WAP、iPhone、安卓、WP、Win8UWP）
* 回贴失败自动重发（无法检测被吞/删），回贴与推文log
* 无需数据库仅本地存储（使用json+csv存储log）

**发起：**[BANKA2017](https://ailand.date)
**主程：**[n0099 四叶重工](https://n0099.net)

## 警告
使用本脚本可能会导致贴吧账号被永久封禁
## 效果
![](https://kdnetwork.github.io/api/images/twtotb1.png)
## 使用
1. 填写`t2t.php`8~13行贴吧用户信息配置项
```php
$tieba_users = [
    // 第一个百度账号配置，扩充请复制
    '用户名' => [ /*用户名，仅供标示用不需要是百度ID*/
        'bduss' => '', // 百度账号BDUSS Cookie
        // 回贴客户端 0:WAP|1:iPhone|2:Android|3:WindowsPhone|4:Windows8UWP
        // 随机或固定客户端二选一
        'reply_device' => rand(0, 4) // 随机
        //'reply_device' => 0 // 固定
    ],
];
```
2. 执行命令或设置crontab
```bash
php-cgi t2t.php
username=
tieba=
tid=
twitter=
using_translate=
```
或web访问[http://example.com/path/t2t.php
?username=
&tieba=
&tid=
&twitter=
&using_translate=]()

|参数名|用途|
|---|---|
|username|发贴用户名，用户信息此前已在t2t.php中配置过|
|tieba|转发主题贴所在贴吧名称|
|tid|转发主题贴tid|
|twitter|推特账号id或话题名称（话题带前置#）|
|using_translate|是否使用谷歌机翻推文，取值true或false|
## 环境需求
- php7
- php-curl
