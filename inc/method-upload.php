<?php
if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', 'aya_alist_server_add_metabox');

//注册MetaBox
function aya_alist_server_add_metabox()
{
    if (AYF::get_opt('site_alist_create_folder', 'alist')) {
        //添加MetaBox
        add_action('add_meta_boxes', function () {
            add_meta_box('aya-alist-shortcode-tips-box', 'Alist Shortcode (folder)', 'aya_alist_shortcode_tips', 'post', 'normal', 'core');
        });
        //注册动作钩子
        //add_action('save_post', 'aya_alist_mkdir_save_post_request');
    }
}
//定义分类文件夹结构
function aya_alist_server_create_path($post_id)
{
    //获取设置
    $root_path = AYF::get_opt('site_alist_create_folder_drive', 'alist');
    $sub_path_type = AYF::get_opt('site_alist_create_folder_format', 'alist');

    if ($root_path == 'false' || $sub_path_type == 'false') {
        return;
    } else {
        //二级文件夹
        switch ($sub_path_type) {
            case 'by_title':
                $sub_path = get_the_title($post_id);
                break;
            case 'by_date':
                $sub_path = get_the_date('Y-m-d', $post_id);
                break;
            case 'by_id':
                $sub_path = 'POST_' . $post_id;
                break;
            case 'by_date_id':
                $sub_path = 'POST_' . $post_id . '_' . get_the_date('Ymd', $post_id);
                break;
            default:
                return;
        }
    }

    //拼接完整路径
    $path = '/' . $root_path . '/' . $sub_path;

    $server = aya_alist_get_server();
    $token = aya_alist_get_token();

    $alist = new Alist_UpFile($server, $token);

    //创建文件夹
    if ($alist->fs_mkdir($path)) {
        return;
    } else {
        //在WP中报错
        return;
    }
}
//创建文件夹
function aya_alist_mkdir_save_post_request($post_id)
{
    //检查是否是自动保存
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    //检查用户权限
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    aya_alist_server_create_path($post_id);

    return;
}
//Metabox组件
function aya_alist_shortcode_tips($post)
{
    $path = aya_alist_server_create_path($post->ID);
    $meta_box = '';
    //$meta_box .= '<img id="alist_request_spinner" src="' . esc_url(get_admin_url() . 'images/spinner.gif') . '" />';
    $meta_box .= '<hr />';
    $meta_box .= '<code id="alist_shortcode_tamplate">';
    $meta_box .= '[alist_cli path="' . $path . '" title="文件列表" /]';
    $meta_box .= '</code>';
    $meta_box .= '<a id="alist_shortcode_copy" href="javascript:void(0)">' . __('Copy') . '</a>';

    echo $meta_box;
?>
    <script>
        jQuery(document).ready(function($) {
            $('#alist_shortcode_copy').on('click', function(element) {
                element.preventDefault();
                var codeText = $('#alist_shortcode_tamplate').text();
                navigator.clipboard.writeText(codeText);
            });
        });
    </script>
<?php

    return;
}
//表单上传
function aya_alist_upload_form($post)
{
    if (!current_user_can('edit_post', $post->ID)) {
        return;
    }
    $server = aya_alist_get_server();
    $token = aya_alist_get_token();

    $alist = new Alist_UpFile($server, $token); // 如果上传账号只授权的一个目录，这里返回的链接还需要加上该目录

    $save_path = $_POST['path'];

    $rs = $alist->fs_mkdir($save_path);
    
    if ($rs['code'] == 200) {
        
        $rs = $alist->fs_upload($_FILES['file'], $save_path);
        
        $url_1 =  $server .  $save_path . $_FILES['file']['name'] . "\n";
        echo $url_1 . "\n";
        echo "<img src='" . $url_1 . "'>"; // 如果文件比较大，返回的链接打开可能是502，需要刷新缓存才能显示

        exit();
    }
    exit(json_encode($rs));

    $path = aya_alist_server_create_path($post->ID);

    $html = '';
    $html .= '<form id="alist_upload_form" method="post" enctype="multipart/form-data">';
    $html .= '<input type="hidden" name="path" value="' . $path . '" />';
    $html .= '<input type="file" name="file" multiple="multiple" />';
    $html .= '<input type="submit" value="' . __('Upload') . '" />';
    $html .= '</form>';

    echo $html;
}
