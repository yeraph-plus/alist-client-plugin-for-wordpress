### Alist 文件列表客户端插件 for WordPress


根据 Alist API (V3) 实现的 PHP 客户端实例。

---

**依赖 `Curl` 拓展**，使用的请求方法在 `lib/Http_Request.php` 。

`demo.php` 提供了一些简单的使用方法样例，类内部完整封装了Alist的 `auth` 、 `fs` 、 `public` 这三组接口，自行查看，其他的一些功能接口感觉一般也用不上，就没管。

[Alist 文档](https://alist.nn.ci/zh/guide/api/auth.html)