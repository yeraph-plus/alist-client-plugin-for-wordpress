<?php
if (!defined('ABSPATH')) exit;

//防止错位加载
if (!class_exists('AYA_Framework_Setup')) exit;

/**
 * AIYA-CMS Theme Options Framework 组件方法构造
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

if (!class_exists('AYA_Field_Action')) {
    abstract class AYA_Field_Action extends AYA_Framework_Setup
    {
        //必须方法
        abstract public function action($field);
        //组件构造
        public static function field($field)
        {
            //过滤组件
            if (in_array($field['type'], array('title_1', 'title_2'))) {
                return self::title_tags($field);
            }
            if (in_array($field['type'], array('content', 'message', 'success', 'dismiss', 'warning'))) {
                return self::content_tags($field);
            }
            //回调组件
            if (in_array($field['type'], array('callback'))) {
                $callback_class = parent::$class_name . $field['type'];

                if (class_exists($callback_class)) {
                    //New
                    $callback = new $callback_class($field);

                    return $callback->callback($field);
                }
            }
            //检查默认值
            if (empty($field['default'])) {
                $field['default'] = '';
            }
            //调用组件
            if (!empty($field['id']) && isset($field['id'])) {

                $new_class = parent::$class_name . $field['type'];

                //开始调用
                if (class_exists($new_class)) {
                    //New
                    $class = new $new_class();

                    echo $class->action($field);
                } else {
                    //报错
                    self::out_error(__('Field not found "type" : ') . print($field));
                }
            } else {
                //报错
                self::out_error(__('Field not found "id" : ') . $field['type']);
            }
        }
        //内部调用
        public static function field_mult($field, $field_action = false)
        {
            //直接提取方法
            if (!empty($field['id']) && isset($field['id'])) {

                $new_class = parent::$class_name . $field['type'];

                //开始调用
                if (class_exists($new_class)) {
                    //New
                    $class = new $new_class();
                    $function = ($field_action) ? 'action' : $field['type'];

                    return $class->$function($field);
                }
            }
        }
        //输出标题
        public static function title_tags($field)
        {
            if (empty($field['desc'])) {
                return;
            }
            //输出
            $html = '<div class="section-title-field"><h3 class="' . $field['type'] . '">' . self::preg_desc($field['desc']) . '</h3></div>';
            echo $html;
        }
        //输出提示内容
        public static function content_tags($field)
        {
            if (empty($field['desc'])) {
                return;
            }
            //切换标记
            switch ($field['type']) {
                case 'content':
                    $icon = '';
                    break;
                case 'message':
                    $icon = '<span class="dashicons dashicons-info"></span>';
                    break;
                case 'success':
                    $icon = '<span class="dashicons dashicons-yes-alt"></span>';
                    break;
                case 'dismiss':
                    $icon = '<span class="dashicons dashicons-dismiss"></span>';
                    break;
                case 'warning':
                    $icon = '<span class="dashicons dashicons-warning"></span>';
                    break;
            }
            //输出
            $html = '<div class="form-field section-content-field"><p class="' . $field['type'] . '">' . $icon . "  " . self::preg_desc($field['desc']) . '</p></div>';
            echo $html;
        }
        //Before结构
        public static function before_tags($field)
        {
            //CSS选择器
            $class = array();
            $class[] = 'form-field';
            $class[] = 'section-' . $field['type'];
            if (!empty($field['class'])) {
                $class[] = $field['class'];
            }
            $html = '<div class="' . implode(' ', $class) . '">';

            //选项名称
            if (!empty($field['title'])) {
                $html .= '<label class="field-label" for="' . $field['id'] . '">' . self::preg_desc($field['title']) . '</label>';
            }
            $html .= '<div class="field-area">';

            return $html;
        }
        //After结构
        public static function after_tags($field)
        {
            $html = '';
            //添加描述
            if (!empty($field['desc'])) {
                $html = '<p class="desc">' . self::preg_desc($field['desc']) . '</p>';
            }

            $html .= '</div></div>';

            return $html;
        }
        //报错
        public static function out_error($message)
        {
            echo '<div class="field-error"><p>' . esc_html($message) . '</p></div>';
        }
        //一些转换html语法
        public static function preg_desc($desc)
        {
            $desc = htmlspecialchars($desc);
            $desc = preg_replace('/\[br\/]/', '<br />', $desc);
            $desc = preg_replace('/\[b\](.*?)\[\/b\]/', '<strong>$1</strong>', $desc);
            $desc = preg_replace('/\[i\](.*?)\[\/i\]/', '<em>$1</em>', $desc);
            $desc = preg_replace('/\[u\](.*?)\[\/u\]/', '<ins>$1</ins>', $desc);
            $desc = preg_replace('/\[s\](.*?)\[\/s\]/', '<del>$1</del>', $desc);
            $desc = preg_replace('/\[code\](.*?)\[\/code\]/', '<code>$1</code>', $desc);
            $desc = preg_replace('/\[pre\](.*?)\[\/pre\]/', '<pre>$1</pre>', $desc);
            $desc = preg_replace('/\[url=(.*?)\](.*?)\[\/url\]/', '<a href="$1" target="_blank">$2</a>',$desc);;

            return $desc;
        }
        //数据检查方法
        public static function test_input($value, $type = '')
        {
            switch ($type) {
                case 'name':
                    //只包含字母跟空格
                    $err = (!preg_match("/^[a-zA-Z ]*$/", $value)) ? __('Only letters and white space allowed') : '';
                    break;
                case 'email':
                    //检查邮箱合法性
                    $err = (!preg_match("/([\w\-]+\@[\w\-]+\.[\w\-]+)/", $value)) ? __('Invalid email format') : '';
                    break;
                case 'url':
                    //检查URL合法性
                    $err = (!preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $value)) ? __('Invalid url format') : '';
                    break;
                case 'number':
                    //检查是否为数字
                    $err = (is_numeric($value)) ? '' : __('Invalid number format');
                    break;
                case 'required':
                    //检查是否为空
                    $err = empty($value == '') ? __('This is required') : '';
                default:
                    $err = '';
                    break;
            }
            return $err;
        }
        //检查选择器
        public static function entry_select($field)
        {
            //内置查询
            if (!empty($field['sub_mode']) && $field['sub_mode'] != '') {
                $new_array = self::select_entries($field['sub_mode']);
            }
            //直接输出
            elseif (!empty($field['sub']) && is_array($field['sub']) && $field['sub'] != '') {
                $new_array = $field['sub'];
            } else {
                $new_array = array();
            }
            return $new_array;
        }
        //选择器的查询方法
        private static function select_entries($value)
        {
            if ($value == '') return;

            //获取所有可显示的Taxonomy
            $taxonomies_names = get_taxonomies(array('show_ui' => true, '_builtin' => false), 'names');
            //将category、post_tag、nav_menu作为标记添加到数组，方便查询
            $taxonomies_names[] = 'category';
            $taxonomies_names[] = 'post_tag';
            $taxonomies_names[] = 'nav_menu';

            //获取所有可显示的Post
            $post_types = get_post_types(array('public' => true, '_builtin' => false), 'names');
            //将post、page作为标记添加到数组，方便查询
            $post_types[] = 'post';
            $post_types[] = 'page';

            //开始查询
            $entries = array();

            //如果是Post
            if (in_array($value, $post_types)) {
                //查询参数
                $t_args = array(
                    'post_type' => $value,
                    'post_parent' => 0
                );
                //返回
                $entries = self::get_posts_by_level($t_args);
            }
            //如果是Taxonomy
            elseif (in_array($value, $taxonomies_names)) {
                //查询参数
                $t_args = array(
                    'taxonomy' => $value,
                    'hide_empty' => false,
                    'parent' => 0
                );
                //返回
                $entries = self::get_terms_by_level($t_args);
            }
            //如果是Sidebar
            elseif ($value == 'sidebar') {
                //获取wp_registered_sidebars
                global $wp_registered_sidebars;

                $sidebars = $wp_registered_sidebars;
                //遍历，返回id和name组成关联数组
                foreach ($sidebars as $sidebar) {
                    $entries[$sidebar['id']] = $sidebar['name'];
                }
            }
            //如果是User
            elseif ($value == 'user') {
                //直接获取所有用户
                $all_users = get_users();
                //遍历，返回user_ID和user_login组成关联数组
                foreach ($all_users as $user) {
                    $entries[$user->ID] = $user->user_login;
                }
            } else {
                $entries = $value;
            }

            return $entries;
        }
        //递归方法获取文章
        private static function get_posts_by_level($args, $space = '')
        {
            $posts = array();
            //设置每页显示文章数999避免循环
            $args['posts_per_page'] = 999;
            //获取文章
            $top_posts = get_posts($args);

            if (!empty($top_posts)) {
                //遍历
                foreach ($top_posts as $post) {
                    //将文章ID和文章标题存入$posts数组中
                    $posts[$post->ID] = $post->post_title;

                    //查询父级ID
                    $args['post_parent'] = $post->ID;
                    //递归此方法
                    $child_posts = self::get_posts_by_level($args);
                    //遍历
                    foreach ($child_posts as $key => $title) {
                        //存入$posts数组中
                        $posts[$key] = $space . $title;
                    }
                }
            }
            //返回$posts
            return $posts;
        }
        //递归方法获取全部分类
        private static function get_terms_by_level($args, $space = '')
        {
            $terms = array();
            //获取分类
            $top_terms = get_terms($args);

            if ($top_terms && !is_wp_error($top_terms)) {
                //遍历
                foreach ($top_terms as $term) {
                    //将标签ID和标签名称存储在$terms数组中
                    $terms[$term->term_id] = $term->name;
                    //查询下一级联标签ID
                    $args['parent'] = $term->term_id;
                    //递归此方法
                    $child_terms = self::get_terms_by_level($args, $space);
                    //遍历
                    foreach ($child_terms as $key => $title) {
                        $terms[$key] = $space . $title;
                    }
                }
            }
            //返回$terms
            return $terms;
        }
    }
}
