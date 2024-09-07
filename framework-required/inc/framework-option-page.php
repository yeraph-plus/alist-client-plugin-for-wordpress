<?php
if (!defined('ABSPATH')) exit;

/**
 * AIYA-CMS Theme Options Framework 创建设置页面
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

if (!class_exists('AYA_Framework_Options_Page')) {
    class AYA_Framework_Options_Page
    {
        private $option_menu;
        private $option_conf;
        private $option_saved_key;

        private $cap_ability;
        private $in_multisite;

        private $menu_slug;
        private $menu_parent_slug;
        private $menu_icon;
        private $menu_title;
        private $menu_page_title;


        private $unfined_saved;

        private $saved_value;
        private $saved_message;

        public function __construct($option_conf, $option_menu)
        {

            $this->option_menu = $option_menu;
            $this->option_conf = $option_conf;
            //操作权限
            $this->cap_ability = 'manage_options';
            //检查管理员
            if (!current_user_can($this->cap_ability)) {
                return;
            }

            //检查输入参数组
            $default = [
                'slug' => 'settings',
                'icon' => 'dashicons-admin-generic',
                'title' => __('Settings'),
                'page_title' => __('Settings'),
                'parent' => '',
            ];

            $this->menu_slug = (isset($option_menu['slug'])) ? $option_menu['slug'] : $default['slug'];
            $this->menu_icon = (isset($option_menu['icon'])) ? $option_menu['icon'] : $default['icon'];
            $this->menu_parent_slug = (isset($option_menu['parent'])) ? $option_menu['parent'] : $default['parent'];
            $this->menu_title = (isset($option_menu['title'])) ? $option_menu['title'] : $default['title'];
            $this->menu_page_title = (empty($option_menu['page_title'])) ? $option_menu['title'] : $option_menu['page_title'];

            //定义保存键名
            $this->option_saved_key = 'aya_opt_' . $this->menu_slug;

            //检查多站点
            $this->in_multisite = self::in_multisite($option_menu);

            //多站点兼容
            add_action($this->in_multisite ? 'network_admin_menu' : 'admin_menu', array(&$this, 'add_admin_dashboard_menu'));

            //定义保存按钮排除
            $this->unfined_saved = array('callback', 'content', 'message', 'success', 'error', 'title_h1', 'title_h2');

            //定位页面加载JS
            if (isset($_GET['page']) && ($_GET['page'] == $this->menu_slug)) {
                //加载
                add_action('admin_enqueue_scripts', array(&$this, 'enqueue_ajax_script'));
            }
        }
        //创建页面
        public function add_admin_dashboard_menu()
        {
            /**
             * WP默认的菜单位置顺序如下：
             * 2 Dashboard
             * 4 Separator
             * 5 Posts
             * 10 Media
             * 15 Links
             * 20 Pages
             * 25 Comments
             * 59 Separator
             * 60 Appearance
             * 65 Plugins
             * 70 Users
             * 75 Tools
             * 80 Settings
             * 99 Separator
             */

            //别名前缀
            $slug_prefix = 'aya-options-';

            if ($this->menu_parent_slug == '') {
                add_menu_page($this->menu_page_title, $this->menu_title,  $this->cap_ability, $slug_prefix . $this->menu_slug, array(&$this, 'init_page'), $this->menu_icon, 99);
            } else {
                add_submenu_page($slug_prefix . $this->menu_parent_slug, $this->menu_page_title, $this->menu_title,  $this->cap_ability, $slug_prefix . $this->menu_slug, array(&$this, 'init_page'), 99);
            }
        }
        //检查多站点设置
        private function in_multisite($menu)
        {
            if (is_multisite()) {
                //检查设置表单
                if (isset($menu['multisite_mode']) && $menu['multisite_mode'] == true) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        //加载样式
        public function enqueue_ajax_script()
        {
            //Ajax
            wp_localize_script('aya-framework-ajax', 'aya_framework', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'base_url' => includes_url(),
                'cdn_url' => 'https://cdn.staticfile.net/',
            ));
        }
        //定义执行顺序
        public function init_page()
        {
            self::save_options_data();

            self::data_options_available();

            self::display_html();
        }
        //循环 htmlspecialchars 方法处理多层数组
        public function deep_htmlspecialchars($mixed, $quote_style = ENT_QUOTES, $charset = 'UTF-8')
        {
            if (is_array($mixed)) {
                foreach ($mixed as $key => $value) {
                    $mixed[$key] = self::deep_htmlspecialchars($value, $quote_style, $charset);
                }
            } elseif (is_string($mixed)) {
                $mixed = htmlspecialchars_decode($mixed, $quote_style);
            }
            return $mixed;
        }
        //提取数据
        public function data_options_available()
        {
            //设置表键名
            $saved_key = $this->option_saved_key;
            //多站点兼容
            if ($this->in_multisite) {
                $this->saved_value[$this->menu_slug] = get_site_option($saved_key);
            } else {
                $this->saved_value[$this->menu_slug] = get_option($saved_key);
            }
            //Fix
            $this->saved_value = self::deep_htmlspecialchars($this->saved_value, ENT_QUOTES, 'UTF-8');

            foreach ($this->option_conf as $key => $option) {
                //选项不存在ID则跳过
                if (isset($option['id']) && isset($this->saved_value[$this->menu_slug][$option['id']])) {
                    //格式化数据
                    $this->option_conf[$key]['default'] = $this->saved_value[$this->menu_slug][$option['id']];
                }
            }
        }
        //保存数据
        public function save_options_data()
        {
            //设置表键名
            $saved_key = $this->option_saved_key;
            //验证用户是否为管理员
            if (!current_user_can('manage_options')) return;

            //获取设置数据
            $new_value  = $this->saved_value;

            //检查From表单
            if (isset($_REQUEST['aya_option_field']) && check_admin_referer('aya_option_action', 'aya_option_field')) {
                //清除旧数据
                if (!empty($_POST['aya_option_reset'])) {
                    //多站点兼容
                    if ($this->in_multisite) {
                        delete_site_option($saved_key);
                    } else {
                        delete_option($saved_key);
                    }
                    //提示
                    $this->saved_message = __('Options reseted.');
                }
                //存入新数据
                if (!empty($_POST['aya_option_submit'])) {
                    //array('option_name' => 'option_value');
                    $new_value = array();
                    //处理数据
                    foreach ($this->option_conf as $option) {
                        //没有ID则跳过循环
                        if (in_array($option['type'], $this->unfined_saved) || !isset($option['id'])) {
                            continue;
                        }

                        $value = (empty($_POST[$option['id']])) ? '' : $_POST[$option['id']];

                        //如果是输入框
                        if ($option['type'] == 'text' || $option['type'] == 'textarea') {
                            $value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
                            $value = stripslashes($value);
                        }
                        //如果是复选框
                        elseif ($option['type'] == 'checkbox') {
                            $value = ($value == '') ? [] : $value;
                        }
                        //如果是数组
                        elseif ($option['type'] == 'array') {
                            $value = explode(',', $value);
                            $value = array_filter($value);
                        }
                        //如果是编辑器
                        elseif ($option['type'] == 'tinymce') {
                            $value = wp_unslash($value);
                        }
                        //如果是代码框
                        elseif ($option['type'] == 'code_editor') {
                            //$value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
                            $value = stripslashes($value);
                        }
                        //如果是设置组
                        elseif ($option['type'] == 'group' || $option['type'] == 'group_mult') {
                            $value = ($value == '') ? [] : $value;
                            $value = array_filter($value);
                        }
                        //其他
                        else {
                            $value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
                        }

                        $new_value[$option['id']] = $value;
                    }
                    //防止重复提交
                    if ($this->saved_value != $new_value) {
                        //多站点兼容
                        if ($this->in_multisite) {
                            update_site_option($saved_key, $new_value);
                        } else {
                            update_option($saved_key, $new_value);
                        }
                        //提示
                        $this->saved_message = __('Options saved.');
                    } else {
                        //提示
                        $this->saved_message = __('Options has already been saved.');
                    }
                }
            }
        }
        //HTML结构
        public function display_html()
        {
            $before_html = '';
            $after_html = '';

            $before_html .= '<div class="wrap" id="framework-page">';

            $before_html .= '<div class="container framework-title">';
            $before_html .= '<h1>' . esc_html($this->option_menu['title']) . '</h1>';
            if (!empty($this->option_menu['desc'])) {
                $before_html .= '<p>' . esc_html($this->option_menu['desc']) . '</p>';
            }
            $before_html .= '</div>';

            //保存提示
            if (!empty($this->saved_message)) {
                $before_html .= '<div class="container framework-content framework-saved-success">';
                $before_html .= '<p>' . esc_html($this->saved_message) . '</p>';
                $before_html .= '</div>';
            }

            $before_html .= '<div class="container framework-content framework-wrap">';

            //表单结构
            $before_html .= '<form method="post" id="from-wrap" action="#saved">';

            echo $before_html;

            //保存按钮
            $saved_button = false;
            //循环
            foreach ($this->option_conf as $option) {
                //如果是文本框则转换一次数据
                if (in_array($option['type'], array('text', 'textarea'))) {
                    $option['default'] = htmlspecialchars($option['default'], ENT_COMPAT, 'UTF-8');
                }
                //组件方法
                AYA_Field_Action::field($option);

                //放置保存按钮
                if (!in_array($option['type'], $this->unfined_saved)) {
                    $saved_button = true;
                }
            }
            //保存按钮
            if ($saved_button) {
                $button_html = '<div class="field-saved-button">';
                //Fix：检索表单的nonce隐藏字段
                wp_nonce_field('aya_option_action', 'aya_option_field');

                $button_html .= '<input type="submit" name="aya_option_submit" class="button-primary autowidth" value="' . esc_html__('Save Changes') . '" />';
                $button_html .= '<input type="submit" name="aya_option_reset" class="button-secondary autowidth" value="' . esc_html__('Clear') . '" />';

                $button_html .= '</div>';

                echo $button_html;
            }

            $after_html = '</form>';

            $after_html .= '</div>';

            echo $after_html;
        }
    }

    global $magic_file, $author_url;
    $magic_file = base64_decode(AYA_NAME_FILE);
    $author_url = base64_decode(AYA_NAME_SIGN);
}
