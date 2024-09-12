<?php
if (!defined('ABSPATH')) exit;

//注册AJAX动作
add_action('wp_ajax_alist_list_data', 'aya_alist_server_ajax_list_callback');
add_action('wp_ajax_nopriv_alist_list_data', 'aya_alist_server_ajax_list_callback');
//短代码组件
add_shortcode('alist_cli', 'aya_shortcode_alist_cli_fs_list_methods');
add_shortcode('alist_raw_url', 'aya_shortcode_alist_cli_get_raw_url');

//请求列表
function aya_shortcode_alist_cli_fs_list_methods($atts = array(), $content = null)
{
    $atts = shortcode_atts(
        array(
            'method' => 'get', //by:list by:search
            'title' => '',
            'path' => '/',
            'password' => '',
            'page' => 1,
            'per_page' => 0,
            'refresh' => false,
            'force_root' => false,
            'parent' => '',
            'keyword' => '',
            'scope' => 2, //0:all 1:file 2:dir
        ),
        $atts,
    );

    //重新格式化传入的参数
    $method = trim($atts['method']);
    $path = trim($atts['path']);

    switch ($method) {
        case 'list':
        case 'get':
            $query_params = array(
                'path' => $path,
                'password' => trim($atts['password']),
                'page' => intval($atts['page']),
                'per_page' => intval($atts['per_page']),
                'refresh' => boolval($atts['refresh']),
            );
            break;
        case 'dirs':
            $query_params = array(
                'path' => $path,
                'password' => trim($atts['password']),
                'force_root' => boolval($atts['force_root']),
            );
            break;
        case 'search':
            $query_params = array(
                'parent' => trim($atts['parent']),
                'keyword' => trim($atts['keyword']),
                'scope' => intval($atts['scope']),
                'page' => intval($atts['page']),
                'per_page' => intval($atts['per_page']),
            );
            break;
        default:
            return;
    }
    $json_atts = json_encode($query_params, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    $title = htmlentities($atts['title']);

    //插件设置
    $custom_css = AYF::get_opt('site_alist_custom_css', 'alist');
    $list_desc = AYF::get_opt('site_alist_list_desc', 'alist');
    $ajax_mode = AYF::get_opt('site_alist_ajax_mode', 'alist');

    $html = '';
    $html .= '<style>' . $custom_css . '</style>';
    $html .= '<div class="container alist-container">';
    //卡片标题
    if (!empty($atts['title'])) $html .= '<h3 class="mt-5 mb-4">' . $title . '</h3>';
    //AJAX异步加载的方法
    if ($ajax_mode) {
        //使用uid方法生成DOM的ID
        $unique_id = 'alist-' . uniqid();
        //容器标签
        $html .= '<div id="' . $unique_id . '">';
        $html .= '<div class="spinner-border spinner-alist mt-3" role="status"><span class="visually-hidden">LOADING...</span></div>';
        $html .= '</div>';

        $ajax_url = admin_url('admin-ajax.php');
        $ajax_body = http_build_query(
            array(
                'action' => 'alist_list_data',
                'nonce' => wp_create_nonce('alist_list_ajax_data'),
                'method' => $method,
                'path' => $path,
                'data_atts' => $json_atts,
            )
        );
        $html .= "
        <script>(function () {
            let ajax_url = '$ajax_url';
            let container = document.getElementById('$unique_id');

            fetch(ajax_url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: '$ajax_body'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok.');
                }
                return response.text();
            })
            .then(content => {
                container.innerHTML = content;
            })
            .catch(error => {
                console.error('Fetch operation error:', error);
                container.innerHTML = 'Unable to load content. Please try again later.';
            });
        })();
        </script>";
    } else {
        $html .= aya_alist_server_basic_list($method, $path, $atts);
    }
    //卡片描述
    $html .= '<p class="content-alist text-muted">' . $list_desc . '</p>';

    $html .= '</div>';

    $html .= do_shortcode($content);

    return $html;
}
//请求文件
function aya_shortcode_alist_cli_get_raw_url($atts = array(), $content = null)
{
    $atts = shortcode_atts(
        array(
            'path' => '/',
        ),
        $atts
    );

    $path = trim($atts['path']);

    $server = aya_alist_get_server();
    $token = aya_alist_get_token();

    if (!$server || !$token) {
        return __('连接失败', 'AIYA-ALIST');
    }

    //创建API对象
    $alist_cli = new Alist_API($server, $token);

    //获取文件
    $fs = $alist_cli->fs_get($path);

    //检查报错
    if (!is_array($fs)) {
        return $fs;
    }
    //检查是否为文件夹
    else if (boolval($fs['is_dir'])) {
        return __('目标路径为文件夹，项目不可用', 'AIYA-ALIST');
    }

    return $fs['raw_url'];
}
//异步获取文件列表
function aya_alist_server_ajax_list_callback()
{
    //验证请求
    if (!wp_verify_nonce($_POST['nonce'], 'alist_list_ajax_data')) {
        wp_die();
    }

    $data_method = stripslashes($_POST['method']);
    $data_path = trim(($_POST['path']), '/');
    $data_atts = stripslashes($_POST['data_atts']);
    //$data_atts = parse_str($_POST['data_atts'], $output_atts);
    //print_r($data_atts);

    echo aya_alist_server_basic_list($data_method, $data_path, $data_atts);

    wp_die();
}
//文件列表结构
function aya_alist_server_basic_list($method, $path, $atts)
{
    //错误返回时HTML
    $alert_before = '<div class="alert alert-light" role="alert">';
    $alert_after = '</div>';

    //获取服务器
    $server = aya_alist_get_server();
    $token = aya_alist_get_token();

    //检查服务器是否已设置
    if (!$server || !$token) {
        return $alert_before . __('Alist 接口不可用，请检查后台配置', 'AIYA-ALIST') . $alert_after;
    }
    //创建API对象
    $alist_cli = new Alist_API($server, $token);
    //Ping可用性
    if (!$alist_cli->ping()) {
        return $alert_before . __('无法连接到文件服务器，请稍后重试。', 'AIYA-ALIST') . $alert_after;
    }
    //to be continued...
    if ($method == 'dirs' || $method == 'search') {
        return $alert_before . 'to be continued...' . $alert_after;
    }

    //获取文件列表
    $res_fs = $alist_cli->fs_request($method, $atts, false);
    //print_r($res_fs);
    //检查报错
    if (!is_array($res_fs) || empty($res_fs)) {
        //配置
        switch ($res_fs) {
            case null:
                return $alert_before . __('请求超时。', 'AIYA-ALIST') . $alert_after;
            case "ERROR:EOF":
                return $alert_before . __('无法读取文件列表，请检查后台配置。', 'AIYA-ALIST') . $alert_after;
                //case ...
            default:
                //直接抛出
                return $alert_before . $res_fs . $alert_after;
        }
    }
    //返回文件展示模板
    if ($method == 'list') {
        return aya_alist_file_tamplate_list($path, $res_fs);
    }
    //输出文件卡片
    else if ($method == 'get') {
        return aya_alist_file_tamplate_card($path, $res_fs);
    }
    //输出文件夹列表
    else if ($method == 'dirs') {
        return aya_alist_file_tamplate_dirs($path, $res_fs);
    }
    //输出搜索结果
    else if ($method == 'search') {
        return aya_alist_file_tamplate_search($path, $res_fs);
    }
    return;
}
