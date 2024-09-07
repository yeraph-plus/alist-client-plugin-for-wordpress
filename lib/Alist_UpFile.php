<?php

if (!defined('ABSPATH')) exit;

/**
 * Alist API 文件操作类 
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

class Alist_UpFile
{
    private $token, $server, $http;

    //初始化
    public function __construct($server, $token)
    {
        $this->server = rtrim($server, '/');
        $this->token = $token;
        $this->http = new HTTP_Request();
    }

    //文件操作方法
    public function fs_request($address, $query_data, $en_code = true)
    {
        //接口位置
        switch ($address) {
            case 'mkdir':
                //新建文件夹
                $api = '/api/fs/mkdir';
                break;
            case 'rename':
                //重命名
                $api = '/api/fs/rename';
                break;
            case 'batch_rename':
                //批量重命名
                $api = '/api/fs/batch_rename';
                break;
            case 'regex_rename':
                //正则重命名
                $api = '/api/fs/regex_rename';
                break;
            case 'move':
                //移动文件
                $api = '/api/fs/move';
                break;
            case 'recursive_move':
                //递归移动文件
                $api = '/api/fs/recursive_move';
                break;
            case 'copy':
                //复制文件
                $api = '/api/fs/copy';
                break;
            case 'remove':
                //删除文件或文件夹
                $api = '/api/fs/remove';
                break;
            case 'remove_empty_directory':
                //删除文件或文件夹
                $api = '/api/fs/remove_empty_directory';
                break;
            default:
                return false;
        }
        //编码为JSON
        if ($en_code) {
            $query_data = json_encode($query_data);
        }
        //请求头
        $this->http->set_header(['User-Agent: AIYA-CMS-CLI/1.0', 'Content-Type: application/json', 'Authorization:' . $this->token]);
        //发送请求
        $response = $this->http->post($this->server . $api, $query_data);

        //返回
        if ($response->status != 200) {
            return false;
        }

        $data = json_decode($response->data, true);

        if ($data['code'] == 200) {
            return true;
        } else {
            return 'ERROR:' . $data['message'];
        }
    }
    //表单上传文件 Tips：需要指定已存在的目录
    public function fs_from_upload($path, $file)
    {
        //接口位置
        $api_url = $this->server . '/api/fs/form';
        //请求头
        $this->http->set_header([
            'User-Agent: AIYA-CMS-CLI/1.0',
            'Authorization:' . $this->token,
            'Content-Type: multipart/form-data', //表单上传
            'Content-Length: ' . filesize($file),
            'File-Path: ' . urlencode($path . $file['name']),
            'As-Task: true'
        ]);

        //发送请求
        $response = $this->http->put($api_url, $file);
        //返回
        $data = json_decode($response->data, true);

        if ($data['code'] == 200) {
            return $data['data']['task'];
        }

        return false;
    }
    //流式上传
    public function fs_upload($path, $file)
    {
        //接口位置
        $api_url = $this->server . '/api/fs/stream';
        //请求头
        $this->http->set_header([
            'User-Agent: AIYA-CMS-CLI/1.0',
            'Authorization:' . $this->token,
            'Content-Type: application/octet-stream', //流式上传
            'Content-Length: ' . filesize($file),
            'File-Path: ' . urlencode($path . $file['name']),
            'As-Task: true'
        ]);

        //发送请求
        $response = $this->http->put($api_url, $file);
        //返回
        $data = json_decode($response->data, true);

        if ($data['code'] == 200) {
            return $data['data']['task'];
        }

        return false;
    }

    //文件操作方法别名

    //新建文件夹
    public function fs_mkdir($path)
    {
        //请求参数
        $query_data = [
            'path' => $path, //路径
        ];

        return $this->fs_request('mkdir', $query_data);
    }
    //重命名文件
    public function fs_rename($name, $path)
    {
        //请求参数
        $query_data = [
            'name' => $name, //重命名
            'path' => $path, //完整路径
        ];

        return $this->fs_request('rename', $query_data);
    }
    //移动文件
    public function fs_move($src, $dst, $names = [])
    {
        //请求参数
        $query_data = [
            'src_dir' => $src, //源文件夹
            'dst_dir' => $dst, //完整路径
            'names' => $names, //文件名（数组）
        ];

        return $this->fs_request('move', $query_data);
    }
    //复制文件
    public function fs_copy($src, $dst, $names = [])
    {
        //请求参数
        $query_data = [
            'src_dir' => $src, //源文件夹
            'dst_dir' => $dst, //完整路径
            'names' => $names, //文件名（数组）
        ];

        return $this->fs_request('copy', $query_data);
    }
    //删除文件
    public function fs_delete($dir, $names = [])
    {
        //请求参数
        $query_data = [
            'dir' => $dir, //文件夹
            'names' => $names, //文件名（数组）
        ];

        return $this->fs_request('delete', $query_data);
    }
}
