<?php

/**
 * AIYA-CMS Theme Options Framework 短代码弹窗编辑器插件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

//Shortcode Editor Demo
/*
//This method only can be used in editor create a panel
//, will not achieve the shortcode's function

//If first load , must be initialized
AYA_Shortcode::instance();

AYA_Shortcode::shortcode_register('button', array(
    'id'       => 'new-button-shortcode',
    'title'    => 'Button',
    'note'    => 'Some base Button',
    'template' => '[button {{attributes}}] {{content}} [/button]',
    'field_build'   => array(
        array(
            'id' => 'url',
            'type'  => 'text',
            'label' => 'Button URL',
            'desc'  => 'Add the button\'s url eg http://example.com',
            'default'   => 'http://example.com',
        ),
        array(
            'id' => 'name',
            'type'  => 'text',
            'label' => 'Content Text',
            'desc'  => 'Add the button\'s text',
            'default'   => 'Your Content!',
        ),
        array(
            'id' => 'content',
            'type'  => 'textarea',
            'label' => 'Content Text',
            'desc'  => 'Add the button\'s text',
            'default'   => 'Your Content!',
        ),
        array(
            'id' => 'color',
            'type'  => 'select',
            'label' => 'Button Color',
            'desc'  => 'Add the button\'s color',
            'sub' => array(
                'red' => 'Red',
                'blue' => 'Blue',
                'green' => 'Green',
            ),
            'default' => 'red',
        ),
        array(
            'id' => 'full_width',
            'type'  => 'checkbox',
            'label' => 'Occupy a row',
            'desc'  => 'Occupy a row this button',
            'default'   => false,
        )
    )
));
*/

if (!class_exists('AYA_Shortcode')) {
    class AYA_Shortcode_Manager
    {
        private static $shortcode_array = array();

        public function __construct()
        {
            add_action('admin_enqueue_scripts', array($this, 'enqueue_script'));
            add_action('media_buttons', array($this, 'media_button'), 20);
            add_action('admin_footer', array($this, 'shortcode_popup_html'));
        }
        //加载样式
        public function enqueue_script()
        {
            //add_thickbox();
            wp_enqueue_script('aiya-shortcode-manager', AYF_URI . '/framework-required/assects/js/framework-shortcode-editor.js');
        }
        //注册短代码方法
        public static function shortcode_register($shortcode_name, $args)
        {
            $add_array = &self::$shortcode_array;
            //增加一组
            $add_array[$shortcode_name] = $args;
        }
        //按钮
        public function media_button($editor_id = 'content')
        {
            //验证当前窗口
            $screen_base = get_current_screen()->base;

            if ($screen_base !== 'post') {
                return;
            }
            $screen_inline = '#TB_inline?width=1000&height=1000&inlineId=insert-shortcode-button';

            echo '<a id="scodedit-button" class="thickbox button" href="' . $screen_inline . '"><span class="dashicons dashicons-shortcode"></span> ' . __('Add Shortcode') . '</a>';
        }
        //弹窗
        public static function shortcode_popup_html()
        {
            //验证当前窗口
            $screen_base = get_current_screen()->base;

            if ($screen_base !== 'post') {
                return;
            }

            echo self::shortcode_build_html();
        }
        //弹窗HTML结构
        public static function shortcode_build_html()
        {
            $shortcode = self::$shortcode_array;

            $html = '';
            $html .= '<div id="insert-shortcode-button" style="display: none;"><div class="scodedit-wrap">';
            //外层select
            $html .= '<div class="scodedit-parent-select">';
            $html .= '<h3>' . __('Select the ShortCode') . '</h3>';
            //$html .= '<label for="scodedit-parent">' . __('Select the ShortCode') . '</label>';
            $html .= '<select name="scodedit-parent" id="select-scodedit-shortcode"><option>' . __('Cancel Type') . '</option>';
            //从监听数组中遍历
            foreach ($shortcode as $short) {
                $html .= '<option data-title="' . $short['title'] . '" value="' . $short['id'] . '">' . $short['title'] . '</option>';
            }
            $html .= '</select></div>';
            $html .= '<h3 id="scodedit-sub-title"></h3>';


            //组件结构
            foreach ($shortcode as $short) {
                //用于JS快捷构建的模板参数
                $short_template = ' data-shortcode-template="' . $short['template'] . '"';
                //HTML结构
                $html .= '<div id="' . $short['id'] . '" class="scodedit-sub-type" ' . $short_template . '>';
                $html .= '<table><tbody>';
                //循环
                foreach ($short['field_build'] as $param) {
                    //生成组件
                    $html .= self::shortcode_build_fields($param);
                }

                $html .= '</tbody></table>';

                //提示框
                if (array_key_exists('note', $short)) {
                    $html .= '<p class="scodedit-note">' . $short['note'] . '</p>';
                }

                $html .= '</div>';
            }

            //保存按钮
            $html .= '<div class="submit_button">';
            $html .= '<input type="button" id="scodedit-insert-shortcode" class="button-primary" value="' .  __('Add') . '" onclick="scodeditInsertShortcode();" />';
            $html .= '<a href="#" id="scodedit-cancel-shortcode" class="button-secondary scodedit-cancel-shortcode" onclick="tb_remove()">' . __('Cancel') . '</a>';
            $html .= '</div>';

            $html .= '</div></div>';

            return $html;
        }
        //组件生成
        public static function shortcode_build_fields($param)
        {
            $key = $param['id'];
            $label = isset($param['label']) ? $param['label'] : '';
            $desc = isset($param['desc']) ? $param['desc'] : '';
            $default = isset($param['default']) ? $param['default'] : '';

            $html = '';
            $html .= '<tr>';

            $html .= '<td class="scodedit-form-label">' . esc_html($label) . '</td>';
            $html .= '<td><label class="screen-reader-text" for="' . esc_attr($key) . '">' . esc_html($label) . '</label>';

            switch ($param['type']) {
                case 'text':
                    $field = '<input type="text" class="scodedit-form-text scodedit-input" name="' . esc_attr($key) . '" id="' . esc_attr($key) . '" value="' . $default . '" />' . "\n";
                    break;
                case 'textarea':
                    $field = '<textarea rows="10" cols="30" name="' . esc_attr($key) . '" id="' . esc_attr($key) . '" class="scodedit-form-textarea scodedit-input">' . $default . '</textarea>' . "\n";
                    break;
                case 'select':
                    if (empty($param['sub'])) break; //找不到sub则跳过

                    $field = '<select name="' . esc_attr($key) . '" id="' . esc_attr($key) . '" class="scodedit-form-select scodedit-input">' . "\n";

                    foreach ($param['sub'] as $sub => $name) {
                        $field .= '<option value="' . esc_attr($sub) . '">' . esc_attr($name) . '</option>' . "\n";
                    }

                    $field .= '</select>' . "\n";

                    break;

                case 'checkbox':
                    $field = '<input type="checkbox" name="' . esc_attr($key) . '" id="' . esc_attr($key) . '" class="scodedit-form-checkbox scodedit-input"' . ($default ? 'checked' : '') . '>' . "\n";
                    break;

                default:

                    break;
            }
            $html .= stripslashes($field);
            $html .= '<span class="scodedit-form-desc">' . esc_html($desc) . '</span></td>' . "\n";

            $html .= '</tr>';

            return $html;
        }
    }

    class AYA_Shortcode extends AYA_Shortcode_Manager
    {
        private static $instance;
        //实例化
        public static function instance()
        {
            if (is_null(self::$instance)) new self();
        }
        //初始父类方法
        public function __construct()
        {
            parent::__construct();
        }
    }
}
