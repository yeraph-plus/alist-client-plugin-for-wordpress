## Alist 文件列表客户端插件 for WordPress

![截图1](https://github.com/yeraph-plus/alist-client-plugin-for-wordpress/blob/master/screenshot/2024-09-07%20230410.png)

![截图2](https://github.com/yeraph-plus/alist-client-plugin-for-wordpress/blob/master/screenshot/2024-09-07%20230531.png)

使用WordPress短代码在文章中插入Alist服务器中的文件链接，通过Alist托管站点的文件下载。

#### 插件设置

**PHP需要 `curl` 和 `json` 拓展**

Alist服务器地址需要公网可访问，游客访问、下载代理等请在Alist中设置。

前端使用 bootstrap 和 bootstrap-icons ，支持自动匹配文件图标。

默认使用AJAX请求文件列表，可选使用同步加载，但是会拖慢页面加载速度。

文件的列表样式在插件设置中自行修改。

支持在文章发布时自动向Alist请求新建文件夹。

通过RestAPI转发了Alist的接口，可用于实现其他功能。

#### 短代码功能

文件列表：

`[alist_cli method="list" title="文件列表标题" path="/" password="" page="1" per_page="0" refresh="false" ]列表描述[/alist_cli]`

文件/文件夹：

`[alist_cli method="get" path="/" password="" page="1" per_page="0" refresh="false" ][/alist_cli]`

最简调用：

`[alist_cli path="/" /]`（不指定接口方法时，默认使用get接口）

直接输出文件真实地址：

`[alist_raw_url path="/readme.md" /]`（用于嵌入网页播放器）

#### 增加功能

[Alist 文档](https://alist.nn.ci/zh/guide/api/auth.html)

Alist的API封装和使用的请求方法在 `/lib` ，请自行查看，是根据 Alist API (V3) 实现的 PHP 客户端实例。

`demo.php` 提供了一些简单的使用方法样例，类内部完整封装了Alist的 `auth` 、 `fs` 、 `public` 这三组接口，自行查看，其他的一些功能接口感觉一般也用不上，就没管。