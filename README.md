![](https://kdwnil.ml/favicon.ico)
# twitter-to-tieba
## shell专版
## 使用说明
由于本版本是shell版本，使用默认配置请按照下面这样设置crontab
<pre><code>* * * * * php /home/index.php b 1 a</code></pre>
这个例子是指每分钟执行在home目录的脚本去检查一位 twitter id为b的用户是否发送了新推，若有则发送到tid为1的在a吧的贴子上<br>
下面是对应原脚本第9-16行的代码
<pre><code>$name=urlencode($argv[1]);//urlencode($_GET['name']);</code>
if(strlen($name)<=0){
echo 'id不得为空';
die;
}
$tid=$argv[2];//$_GET['tid'];
$tbn=urlencode($argv[3]);//urlencode(($_GET['tbn']));
$la='zh';//urlencode(($_GET['la']));//urlencode($argv[4]);//默认语言为中文，如需其他语言请自行修改</code></pre>
如果需要改造成通过get传参数来使用本脚本，请把第9，14，15行的注释及注释前的内容删除，并在使用时传送对应参数
## 用处
把指定用户的推特转发到贴吧<br>
本版本使用translate.com源（基于必应翻译），本版本使用wap发贴接口，可解决贴吧对客户端验证的问题，如需Google翻译请选择[master分支](https://github.com/yaoyichi2011/twitter-to-tieba/tree/master/)，本版本支持检查cookie完整性<br />
## 相关人员
![NULLMIX](https://pan.nullmix.ml/favicon.ico)
![n0099 四叶重工](https://n0099.cf/favicon.ico)<br>
## 效果
![](https://github.com/yaoyichi2011/kdwnilpic/blob/master/twtotb1.png)
