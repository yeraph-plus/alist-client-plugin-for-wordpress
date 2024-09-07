<?php

if (!defined('ABSPATH')) exit;

/**
 * 标准 Curl 请求类
 * 
 * Author Yeraph.
 * Version 1.0
 * 
 * https://www.yeraph.com/
 * 
 */

if (!class_exists('HTTP_Request')) {
    class HTTP_Request
    {
        private static $options;
        private $curl;

        public function __construct()
        {
            $this->curl = curl_init();
            //默认参数
            self::$options = [
                //CURLOPT_URL => $url,
                //CURLOPT_USERAGENT => ['User-Agent: AIYA-CMS-CLI/1.0', 'Content-Type: application/json'],
                CURLOPT_RETURNTRANSFER => true,
                //CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                //CURLOPT_ENCODING => 'gzip',
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_TIMEOUT => 30,
            ];
        }

        public function __destruct()
        {
            curl_close($this->curl);
        }

        //设置请求头
        public function set_header($headers = [])
        {
            if (!is_array($headers)) {
                $headers = [$headers];
            }
            self::$options[CURLOPT_HTTPHEADER] = $headers;
        }
        public function set_referer($referer = '')
        {
            self::$options[CURLOPT_REFERER] = $referer;
            self::$options[CURLOPT_AUTOREFERER] = true;
        }
        public function set_cookie($cookie = '')
        {
            self::$options[CURLOPT_COOKIE] = $cookie;
        }
        public function set_proxy($proxy = '')
        {
            self::$options[CURLOPT_PROXY] = rtrim($proxy,'/');
        }
        public function set_useragent($useragent = '')
        {
            if (empty($useragent)) {
                $useragent = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)';
            }
            self::$options[CURLOPT_USERAGENT] = $useragent;
        }

        //HTTP方法
        public function get($url, $params = [])
        {
            $param = '';

            if (!empty($params)) {
                $param = '?' . http_build_query($params);
            }
            self::$options[CURLOPT_URL] = $url . $param;
            self::$options[CURLOPT_CUSTOMREQUEST] = 'GET';

            return $this->request();
        }
        public function post($url, $post_data = [])
        {
            self::$options[CURLOPT_URL] = $url;
            self::$options[CURLOPT_CUSTOMREQUEST] = 'POST';

            self::$options[CURLOPT_POST] = true;
            if (is_array($post_data)) {
                self::$options[CURLOPT_POSTFIELDS] = http_build_query($post_data);
            } else {
                self::$options[CURLOPT_POSTFIELDS] = $post_data;
            }

            return $this->request();
        }
        public function put($url, $from_data)
        {
            self::$options[CURLOPT_URL] = $url;
            self::$options[CURLOPT_CUSTOMREQUEST] = 'PUT';
            self::$options[CURLOPT_POSTFIELDS] = $from_data;

            return $this->request();
        }
        public function delete($url, $from_data)
        {
            self::$options[CURLOPT_URL] = $url;
            self::$options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            self::$options[CURLOPT_POSTFIELDS] = $from_data;

            return $this->request();
        }

        //发起请求
        private function request()
        {
            //检查响应头格式
            $ress_headers = [];

            self::$options[CURLOPT_HEADERFUNCTION] = function ($curl, $header) use (&$ress_headers) {
                $len = strlen($header);
                $header = explode(':', $header, 2);

                if (count($header) < 2) {
                    return $len;
                }
                $ress_headers[strtolower(trim($header[0]))][] = trim($header[1]);

                return $len;
            };

            //加载设置
            curl_setopt_array($this->curl, self::$options);
            //获取响应
            $response = curl_exec($this->curl);

            if (curl_errno($this->curl)) {
                $http_status = curl_error($this->curl);
            } else {
                $http_status = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            }

            //转换为一个对象打包返回
            return new HTTP_Response($http_status, $ress_headers, $response);
        }
    }

    class HTTP_Response
    {
        public $status, $headers, $data;

        public function __construct($status, $headers, $data)
        {
            $this->status = $status;
            $this->headers = $headers;
            $this->data = $data;
        }
    }
}
