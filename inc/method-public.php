<?php
if (!defined('ABSPATH')) exit;

/*
 * ------------------------------------------------------------------------------
 * 插件方法
 * ------------------------------------------------------------------------------
 */

//请求Token并使用WP的SQL缓存
function aya_alist_request_token($server, $username, $password)
{
    $cache_token = 'alist_' . $username . '_token';

    $token_content = get_transient($cache_token);

    if ($token_content) {

        return $token_content;
    } else {

        $alist = new Alist_API($server, false);

        $token = $alist->get_temp_token($username, $password);

        //检查返回
        if (strpos($token, 'ERROR:') === false) {
            //设置缓存
            set_transient($cache_token, $token, 48 * 3600);
        } else {
            //覆盖返回
            $token = false;
        }

        return $token;
    }
}
//请求Token使用文件缓存（备用）
function aya_alist_request_token_file($server, $username, $password)
{
    $cache_token = 'alist_' . $username . '_token.jwt';

    if (is_file($cache_token)) {
        return file_get_contents($cache_token);
    }

    $alist = new Alist_API($server, false);

    $token_content = $alist->get_token($username, $password);

    file_put_contents($cache_token, $token_content);

    return $token_content;
}
//清除JWTOKEN缓存
function aya_alist_clear_token()
{
    $username = AYF::get_opt('site_alist_api_username', 'alist');
    $cache_token = 'alist_' . $username . '_token';

    if (get_transient($cache_token)) {
        delete_transient($cache_token);
    }
}
//获取服务器
function aya_alist_get_server()
{
    $server_url = AYF::get_opt('site_alist_api_url', 'alist');

    if (!empty($server_url)) {
        return trim($server_url, '/');
    }
    return false;
}
//获取请求hash
function aya_alist_get_token()
{
    $server = aya_alist_get_server();

    $name = AYF::get_opt('site_alist_api_username', 'alist');
    $pswd = AYF::get_opt('site_alist_api_password', 'alist');

    return aya_alist_request_token_file($server, $name, $pswd); //
}
//Ping测试
function aya_alist_server_ping_request()
{
    $the_server = aya_alist_get_server();

    if ($the_server) {
        $alist = new Alist_API($the_server, false);

        if ($alist->ping()) {
            $message = '接口状态：已连接';
        } else {
            $message = '接口状态：无法访问';
        }
    } else {
        return '请先设置Alist服务器地址';
    }

    return $message;
}
//请求测试
function aya_alist_server_list_request()
{
    $server = aya_alist_get_server();
    $token = aya_alist_get_token();

    $alist = new Alist_API($server, $token);

    //先请求用户查看权限
    if ($alist->ping()) {
        $user_info = $alist->get_info_me();
    } else {
        $user_info = array('disabled' => true);
    }

    $list = array();

    if (is_array($user_info) && $user_info['disabled'] != true) {
        //开始请求目录
        $folder_array = $alist->fs_list($user_info['base_path']);
        //数组为空
        if (!is_array($folder_array)) {
            $list['false'] = '无数据';
        } else {
            //结构化目录到设置表单
            foreach ($folder_array as $i => $folder) {
                //跳过文件
                if ($folder['is_dir'] == false) {
                    continue;
                }
                $list[$folder['name']] = '驱动器：/' . $folder['name'];
            }
        }
    } else {
        $list['false'] = '未找到驱动器，请先创建';
    }

    return $list;
}

/*
 * ------------------------------------------------------------------------------
 * 数据处理
 * ------------------------------------------------------------------------------
 */

//图标方法
function aya_alist_file_icon_map($file_name, $basical = false)
{
    if ($basical) {
        $icon_type = 'typ';
    } else {
        //获取设置
        $icon_type = AYF::get_opt('site_alist_view_icon', 'alist');
    }
    //禁用
    if ($icon_type == 'off') return;

    //pathinfo方法
    $path_info = pathinfo($file_name);

    $icon = false;

    //定义文件类型与图标的映射
    if (isset($path_info['extension'])) {

        $ext = strtolower($path_info['extension']);

        //键值表匹配
        if ($icon_type == 'typ') {
            //文本类型
            $text_types = ['txt', 'html', 'md', 'json', 'conf', 'yml', 'xml', 'log', 'ini', 'css', 'vtt', 'ass', 'srt', 'lrc'];
            //图片类型
            $image_types = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'heic', 'tiff', 'svg', 'raw', 'ico', 'swf'];
            //视频类型
            $video_types = ['mp4', 'mkv', 'flv', 'ts', 'mov', 'mpg', 'mpeg', 'webm', 'm3u8'];
            //音频类型
            $audio_types = ['mp3', 'flac', 'opus', 'ogg', 'aac', 'wav', 'wma', 'm4a'];
            //压缩文件
            $archive_types = ['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz', 'iso'];
            //文档类型
            $word_types = ['doc', 'docx'];
            $ppt_types = ['ppt', 'pptx'];
            $excel_types = ['xls', 'xlsx'];
            $pdf_types = ['pdf'];
            //代码类型
            $code_types = ['php', 'js', 'tsx', 'py', 'java', 'c', 'cpp', 'h', 'hpp', 'go', 'sql', 'swift', 'vue', 'rs', 'lua', 'sh', 'bat', 'cmd'];
            //可执行文件
            $binary_types = ['exe', 'msi', 'apk', 'ipa', 'dmg', 'deb', 'iso', 'pkg', 'appimage', 'snap'];

            switch (true) {
                case in_array($ext, $archive_types):
                    $icon = 'zip';
                    break;
                case in_array($ext, $image_types):
                    $icon = 'image';
                    break;
                case in_array($ext, $text_types):
                    $icon = 'text';
                    break;
                case in_array($ext, $video_types):
                    $icon = 'play';
                    break;
                case in_array($ext, $audio_types):
                    $icon = 'music';
                    break;
                case in_array($ext, $word_types):
                    $icon = 'word';
                    break;
                case in_array($ext, $ppt_types):
                    $icon = 'ppt';
                    break;
                case in_array($ext, $excel_types):
                    $icon = 'excel';
                    break;
                case in_array($ext, $pdf_types):
                    $icon = 'pdf';
                    break;
                case in_array($ext, $code_types):
                    $icon = 'code';
                    break;
                case in_array($ext, $binary_types):
                    $icon = 'binary';
                    break;
            }
        }
        //直接匹配
        else if ($icon_type == 'ext') {
            $icon_map = ['aac', 'ai', 'bmp', 'cs', 'css', 'csv', 'doc', 'docx', 'exe', 'gif', 'heic', 'html', 'java', 'jpg', 'js', 'json', 'jsx', 'key', 'm4p', 'md', 'mdx', 'mov', 'mp3', 'mp4', 'otf', 'pdf', 'php', 'png', 'ppt', 'pptx', 'psd', 'py', 'raw', 'rb', 'sass', 'scss', 'sh', 'sql', 'svg', 'tiff', 'tsx', 'ttf', 'txt', 'wav', 'woff', 'xls', 'xlsx', 'xml', 'yml'];

            if (in_array($ext, $icon_map)) {
                $icon = $ext;
            }
        }
    }

    if ($basical) {
        return $icon;
    }

    //拼接为html标签（bootstrap-icons）
    if ($icon) {
        //bi-file-earmark-word
        if ($icon_type == 'typ') {
            return '<i class="bi bi-file-earmark-' . $icon . '"></i>&nbsp; ';
        }
        //bi-filetype-doc
        else if ($icon_type == 'ext') {
            return '<i class="bi bi-filetype-' . $icon . '"></i>&nbsp; ';
        }
    } else {
        return '<i class="bi bi-file-earmark"></i>&nbsp; ';
    }
}
//图标方法（if结构）
function aya_alist_file_icon_map_else($file_name)
{
    //pathinfo方法
    $path_info = pathinfo($file_name);

    $icon = false;

    //定义文件类型与图标的映射
    if (isset($path_info['extension'])) {
        $ext = strtolower($path_info['extension']);

        //匹配文档类型
        if (in_array($ext, ['txt', 'html', 'md', 'json', 'conf', 'yml', 'xml', 'log', 'ini', 'css', 'vtt', 'ass', 'srt', 'lrc'])) {
            $icon = 'text';
        }
        //匹配图片类型
        else if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'heic', 'tiff', 'svg', 'raw', 'ico', 'swf'])) {
            $icon = 'image';
        }
        //匹配视频类型
        else if (in_array($ext, ['mp4', 'mkv', 'flv', 'ts', 'mov', 'mpg', 'mpeg', 'webm', 'm3u8'])) {
            $icon = 'play';
        }
        //匹配音频类型
        else if (in_array($ext, ['mp3', 'flac', 'opus', 'ogg', 'aac', 'wav', 'wma', 'm4a'])) {
            $icon = 'music';
        }
        //匹配压缩包类型
        else if (in_array($ext, ['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz', 'iso'])) {
            $icon = 'zip';
        }
        //匹配doc文档类型
        else if (in_array($ext, ['doc', 'docx'])) {
            $icon = 'word';
        }
        //匹配ppt文档类型
        else if (in_array($ext, ['ppt', 'pptx'])) {
            $icon = 'ppt';
        }
        //匹配xls文档类型
        else if (in_array($ext, ['xls', 'xlsx'])) {
            $icon = 'excel';
        }
        //匹配pdf文档类型
        else if (in_array($ext, ['pdf'])) {
            $icon = 'pdf';
        }
        //匹配代码文件类型
        else if (in_array($ext, ['php', 'js', 'tsx', 'py', 'java', 'c', 'cpp', 'h', 'hpp', 'go', 'sql', 'swift', 'vue', 'rs', 'lua', 'sh', 'bat', 'cmd'])) {
            $icon = 'code';
        }
        //匹配可执行文件类型
        else if (in_array($ext, ['exe', 'msi', 'apk', 'ipa', 'dmg', 'deb', 'iso', 'pkg', 'appimage', 'snap'])) {
            $icon = 'binary';
        }
        //匹配其他类型
        else {
            $icon = false;
        }
    }
    //拼接为html标签（bootstrap-icons）
    if ($icon) {
        return '<i class="bi bi-file-earmark-' . $icon . '"></i>&nbsp; ';
    } else {
        return '<i class="bi bi-file-earmark"></i>&nbsp; ';
    }
}
//计算文件大小
function aya_alist_file_size_format($bytes, $precision = 2)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $bytes = max($bytes, 0);
    //幂等计算
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);

    $bytes /= pow(1024, $pow);
    // $bytes /= (1 << (10 *$pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow];
}
//计算文件创建日期
function aya_alist_file_date_format($date)
{
    //DateTime方法
    $this_date = new DateTime($date);

    return $this_date->format('Y-m-d');
}
//格式化文件url
function aya_alist_file_link($file_path, $file_name = '', $file_sign = '', $basic_type = '', $add_class = 'btn-sm')
{
    if (empty($basic_type)) {
        //获取设置
        $link_type = AYF::get_opt('site_alist_view_link', 'alist');
    } else {
        //放入数组
        $link_type = array($basic_type);
    }
    $server = aya_alist_get_server();


    $file_name = (empty($file_name)) ? '' : '/' . rawurlencode($file_name);
    $file_sign = (empty($file_sign)) ? '' : '?sign=' . $file_sign;
    $link = '';

    $i = 0;
    $length = count($link_type);
    foreach ($link_type as $type) {
        switch ($type) {
            case 'page':
                $link .= '<a class="btn btn-alist-down ' . $add_class . '" href="' . $server . '/' . $file_path . $file_name . '" target="_blank" >查看</a>';
                break;
            case 'down':
                $link .= '<a class="btn btn-alist-down ' . $add_class . '" href="' . $server . '/d/' . $file_path . $file_name . $file_sign . '" target="_blank" >下载</a>';
                break;
            case 'proxy':
                $link .= '<a class="btn btn-alist-down ' . $add_class . '" href="' . $server . '/p/' . $file_path . $file_name . $file_sign . '" target="_blank" >下载</a>';
                break;
        }

        if (++$i < $length) {
            $link .= '&nbsp; ';
        }
    }

    return $link;
}
//文件列表模板
function aya_alist_file_tamplate_list($data_path, $data_fs)
{
    //提取内容
    $fs_list = $data_fs['content'];

    $html = '';
    $html .= '<table class="table table-hover">';
    $html .= '<thead><tr>';
    $html .= '<th>' . __('文件', 'AIYA-ALIST') . '</th>';
    $html .= '<th>' . __('日期', 'AIYA-ALIST') . '</th>';
    $html .= '<th>' . __('大小', 'AIYA-ALIST') . '</th>';
    $html .= '<th>' . __('下载地址', 'AIYA-ALIST') . '</th>';
    $html .= '</tr></thead>';
    $html .= '<tbody>';

    foreach ($fs_list as $fs) {
        //跳过文件夹
        if (boolval($fs['is_dir'])) {
            continue;
        }
        $html .= '<tr>';
        $html .= '<td>' . aya_alist_file_icon_map($fs['name']) . $fs['name'] . '</td>';
        $html .= '<td>' . aya_alist_file_date_format($fs['created']) . '</td>';
        $html .= '<td>' . aya_alist_file_size_format($fs['size']) . '</td>';
        $html .= '<td>' . aya_alist_file_link($data_path, $fs['name'],  $fs['sign']) . '</td>';
        $html .= '';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    return $html;
}
//文件卡片模板
function aya_alist_file_tamplate_card($data_path, $data_fs)
{
    $fs = $data_fs;

    $html = '';
    $html .= '';
    $html .= '<div class="card mb-4"><div class="card-body">';

    //是文件夹
    if (boolval($fs['is_dir'])) {

        $html .= '<h5 class="card-title mb-3"><i class="bi bi-folder"></i>&nbsp; ' . $fs['name'] . '</h5>';

        $html .= '<p class="card-text">';
        $html .= '创建日期：' . aya_alist_file_date_format($fs['created']) . '<br />';
        $html .= '大小：' . aya_alist_file_size_format($fs['size']) . '<br />';
        $html .= '</p>';

        if (!empty($fs['readme'])) {
            $html .= '<p class="card-text">' . $fs['readme'] . '</p>';
        }

        $html .= aya_alist_file_link($data_path, '',  $fs['sign'], 'page', '');
    }
    //是文件
    else {
        //获取设置
        $get_raw_url = AYF::get_opt('site_alist_get_raw_url', 'alist');

        $html .= '<h5 class="card-title mb-3">' . aya_alist_file_icon_map($fs['name']) . $fs['name'] . '</h5>';
        $html .= '<p class="card-text">';
        $html .= '提供者：' . $fs['provider'] . '<br />';
        $html .= '创建日期：' . aya_alist_file_date_format($fs['created']) . '<br />';
        $html .= '大小：' . aya_alist_file_size_format($fs['size']) . '<br />';
        $html .= '</p>';

        if (!empty($fs['readme'])) {
            $html .= '<p class="card-text">' . $fs['readme'] . '</p>';
        }
        //直链模式
        if ($get_raw_url) {
            $html .= '<a class="btn btn-alist-down" href="' . $fs['raw_url'] . '" target="_blank">直链</a>&nbsp; ';
        }

        $html .= aya_alist_file_link($data_path, '',  $fs['sign'], '', '');
    }

    $html .= '</div></div>';

    return $html;
}
//文件夹列表模板
function aya_alist_file_tamplate_dirs($data_path, $data_fs)
{
    return; //TODO
}
//搜索结果模板
function aya_alist_file_tamplate_search($data_path, $data_fs)
{
    return; //TODO
}
