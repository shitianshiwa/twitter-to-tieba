# twitter-to-tieba v5.0
将指定用户的推特自动转发到贴吧

## 功能
* 主要功能转推：将任意推特用户的转发到贴吧的主题贴下
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
1. 填写`t2t.php` 8~13行用户配置项
2. 执行命令或设置crontab
```
php-cgi t2t.php
username=发贴用户名，用户参数此前必须已在t2t.php中配置过
tieba=转发主题贴所在贴吧名称
tid=转发主题贴tid
twitter=推特账号id或话题名称（带前置#）
using_translate=是否使用谷歌机翻推文，取值true或false
```
3. 可设置多条命令以配置多套转发参数
## 环境需求
- php7
- php-curl