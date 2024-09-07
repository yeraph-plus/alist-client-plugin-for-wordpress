<?php

if (!defined('ABSPATH')) exit;

/**
 * Alist API 类
 * 
 * Author Yeraph.
 * Version 1.0
 * 
 * https://www.yeraph.com/
 * 
 * Alist文档：
 * https://alist.nn.ci/zh/guide/
 * Alist API 文档：
 * https://alist-v3.apifox.cn/
 */

class Alist_API
{
    private $token, $server, $http;

    //初始化
    public function __construct($server, $token)
    {
        $this->server = rtrim($server, '/');
        $this->token = $token;
        $this->http = new HTTP_Request();
    }
    //获取临时token（48小时过期）
    public function get_temp_token($username, $password)
    {
        //接口位置
        $api_url = $this->server . '/api/auth/login';
        //请求头
        $this->http->set_header(['User-Agent: AIYA-CMS-CLI/1.0', 'Content-Type: application/json']);
        //请求参数
        $query_data = json_encode([
            'username' => $username,
            'password' => $password,
        ]);

        //发送请求
        $response = $this->http->post($api_url, $query_data);
        //返回
        if ($response->status == 200) {

            $data = json_decode($response->data, true);

            if ($data['code'] == 200) {
                return $data['data'];
            } else {
                return 'ERROR:' . $data['message'];
            }
        }

        return false;
    }
    //获取token（hash）
    public function get_token($username, $password)
    {
        //接口位置
        $api_url = $this->server . '/api/auth/login/hash';
        //请求头
        $this->http->set_header(['User-Agent: AIYA-CMS-CLI/1.0', 'Content-Type: application/json']);
        //请求参数
        $query_data = json_encode([
            'username' => $username,
            'password' => hash('sha256', $password . '-https://github.com/alist-org/alist'),
        ]);

        //发送请求
        $response = $this->http->post($api_url, $query_data);
        //返回
        if ($response->status == 200) {

            $data = json_decode($response->data, true);

            if ($data['code'] == 200) {
                return $data['data'];
            } else {
                return 'ERROR:' . $data['message'];
            }
        }

        return false;
    }
    //获取当前用户信息
    public function get_info_me()
    {
        //接口位置
        $api_url = $this->server . '/api/me';
        //请求头
        $this->http->set_header(['User-Agent: AIYA-CMS-CLI/1.0', 'Authorization:' . $this->token]);

        //发送请求
        $response = $this->http->get($api_url, []);
        //返回
        if ($response->status == 200) {

            $data = json_decode($response->data, true);

            if ($data['code'] == 200) {
                return $data['data'];
            } else {
                return 'ERROR:' . $data['message'];
            }
        }

        return false;
    }
    //获取站点设置
    public function get_settings()
    {
        //接口位置
        $api_url = $this->server . '/api/public/settings';
        //请求头
        $this->http->set_header(['User-Agent: AIYA-CMS-CLI/1.0']);

        //发送请求
        $response = $this->http->get($api_url, []);
        //返回
        if ($response->status == 200) {

            $data = json_decode($response->data, true);

            if ($data['code'] == 200) {
                return $data['data'];
            } else {
                return 'ERROR:' . $data['message'];
            }
        }

        return false;
    }
    //Ping检测
    public function ping()
    {
        //接口位置
        $api_url = $this->server . '/ping';
        //请求头
        $this->http->set_header(['User-Agent: AIYA-CMS-CLI/1.0']);

        //发送请求
        $response = $this->http->get($api_url, []);

        //返回
        if ($response->status == 200 && $response->data == "pong") {

            return true;
        }

        return false;
    }
    //文件获取方法
    public function fs_request($address, $query_data, $en_code = true)
    {
        //接口位置
        switch ($address) {
            case 'list':
                //列出文件目录
                $api = '/api/fs/list';
                break;
            case 'get':
                //获取文件信息
                $api = '/api/fs/get';
                break;
            case 'dirs':
                //获取目录
                $api = '/api/fs/dirs';
                break;
            case 'search':
                //搜索文件或文件夹
                $api = '/api/fs/search';
                break;
            default:
                return false;
        }
        //编码为JSON
        if ($en_code) {
            $query_data = json_encode($query_data);
        }
        //请求头
        $this->http->set_header(['User-Agent: AIYA-CMS-CLI/1.0', 'Content-Type: application/json', 'Authorization: ' . $this->token]);
        //发送请求
        $response = $this->http->post($this->server . $api, $query_data);

        //返回
        if ($response->status == 200) {

            $data = json_decode($response->data, true);

            if ($data['code'] == 200) {
                return $data['data'];
            } else {
                return 'ERROR:' . $data['message'];
            }
        }

        return false;
    }

    //文件获取方法别名

    //列出文件目录
    public function fs_list($path, $password = '', $page = 1, $per_page = 0, $refresh = false)
    {
        //请求参数
        $query_data = [
            'path' => $path, //路径
            'password' => $password, //密码
            'page' => $page,
            'per_page' => $per_page,
            'refresh' => $refresh //是否强制刷新
        ];

        //发送请求
        $response = $this->fs_request('list', $query_data);

        if (!($response) || !is_array($response)) return $response;

        return $response['content'];
    }
    //获取某个文件/目录信息
    public function fs_get($path, $password = '', $page = 1, $per_page = 0, $refresh = false)
    {
        //请求参数
        $query_data = [
            'path' => $path, //路径
            'password' => $password, //密码
            'page' => $page,
            'per_page' => $per_page,
            'refresh' => $refresh //是否强制刷新
        ];

        //发送请求
        $response = $this->fs_request('get', $query_data);

        if (!($response) || !is_array($response)) return $response;

        return $response;
    }
    //获取目录
    public function fs_dir($path, $password = '', $force_root = false)
    {
        //请求参数
        $query_data = [
            'path' => $path,
            'password' => $password,
            'force_root' => $force_root
        ];

        //发送请求
        $response = $this->fs_request('dirs', $query_data);

        if (!($response) || !is_array($response)) return $response;

        return $response;
    }
    //搜索文件或文件夹
    public function fs_search($parent, $keywords, $scope = 0, $page = 1, $per_page = 0, $password = '')
    {
        //请求参数
        $query_data = [
            'parent' => $parent, //搜索目录
            'keywords' => $keywords, //关键词
            'scope' => $scope, //0-全部 1-文件夹 2-文件
            'page' => $page,
            'per_page' => $per_page,
            'password' => $password //密码
        ];

        //发送请求
        $response = $this->fs_request('search', $query_data);

        if (!($response) || !is_array($response)) return $response;

        return $response['content'];
    }
}
