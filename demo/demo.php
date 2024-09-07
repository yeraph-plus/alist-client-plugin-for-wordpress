<?php

define('ALIST_SERVER', 'http://127.0.0.1:5244'); // Alist 服务器地址
define('ALIST_USERNAME', 'guest'); // Alist 用户名
define('ALIST_PASSWORD', '123456'); // Alist 密码

require_once (__DIR__) . 'lib/Http_Request.php';
require_once (__DIR__) . 'lib/Alist.php';
require_once (__DIR__) . 'lib/Alist_UpFile.php';

//使用文件缓存Token
function aya_alist_token_cache()
{
    $cache_token = 'alist_' . ALIST_USERNAME . '_token.jwt';

    if (is_file($cache_token)) {
        return file_get_contents($cache_token);
    }
    //获取
    $alist = new Alist_API(ALIST_SERVER, false);

    $token_content = $alist->get_token(ALIST_USERNAME, ALIST_PASSWORD);

    file_put_contents($cache_token, $token_content);
    return $token_content;
}
//获取用户信息
function aya_alist_user_info_me()
{
    $TOKEN = aya_alist_token_cache();

    $alist = new Alist_API(ALIST_SERVER, $TOKEN);

    return $alist->get_info_me();
}
//获取文件列表
function aya_alist_file_list($path = '/')
{
    $TOKEN = aya_alist_token_cache();

    $alist = new Alist_API(ALIST_SERVER, $TOKEN);

    return $alist->fs_get($path);
}
//获取文件信息
function aya_alist_file_get($path)
{
    $TOKEN = aya_alist_token_cache();

    $alist = new Alist_API(ALIST_SERVER, $TOKEN);

    return $alist->fs_get($path);
}
//新建文件夹
function aya_alist_file_mkdir($path)
{
    $TOKEN = aya_alist_token_cache();

    $alist = new Alist_UpFile(ALIST_SERVER, $TOKEN);

    return $alist->fs_mkdir($path);
}