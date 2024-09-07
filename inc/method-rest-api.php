<?php
if (!defined('ABSPATH')) exit;

//注册钩子
add_action('rest_api_init', 'aya_alist_register_route');

//插件路由
function aya_alist_rest_register_route()
{
    register_rest_route('aya-alist-client', '/api/fs/get', array(
        'methods' => 'POST',
        'callback' => 'aya_alist_server_api_fs_get',
        'permission_callback' => 'aya_alist_server_api_rest_nonce',
    ));
    register_rest_route('aya-alist-client', '/api/fs/list', array(
        'methods' => 'POST',
        'callback' => 'aya_alist_server_api_fs_list',
        'permission_callback' => 'aya_alist_server_api_rest_nonce',
    ));
    register_rest_route('aya-alist-client', '/api/fs/search', array(
        'methods' => 'POST',
        'callback' => 'aya_alist_server_api_fs_search',
        'permission_callback' => 'aya_alist_server_api_rest_nonce',
    ));
}
//请求验证
function aya_alist_server_api_rest_nonce($request)
{
    //获取请求头
    $nonce = $request->get_header('X-WP-Nonce');
    //验证nonce参数
    if (! wp_verify_nonce($nonce, 'aya_alist_rest_api')) {
        //失败
        return new WP_Error('invalid_nonce', 'Invalid nonce', array('status' => 403));
    }
    return true;
}
//请求方法
function aya_alist_server_api_fs($address, $query_data)
{
    //获取配置
    $server = aya_alist_get_server();
    $token = aya_alist_get_token();
    //创建API对象
    $alist_cli = new Alist_API($server, $token);

    //Ping可用性
    if (!$alist_cli->ping()) {
        return new WP_Error('alist_error', 'Server is not available', array('status' => 500));
    }

    $result = $alist_cli->fs_request($address, $query_data);

    //返回结果
    return wp_send_json($result);
}
//REST 获取文件信息
function aya_alist_server_api_fs_get($request)
{
    //获取请求参数
    $params = $request->get_params();
    //返回结果
    return aya_alist_server_api_fs('get', $params);
}
//REST 获取文件列表
function aya_alist_server_api_fs_list($request)
{
    //获取请求参数
    $params = $request->get_params();
    //返回结果
    return aya_alist_server_api_fs('list', $params);
}
//REST 获取搜索结果
function aya_alist_server_api_fs_search($request)
{
    //获取请求参数
    $params = $request->get_params();
    //返回结果
    return aya_alist_server_api_fs('search', $params);
}
