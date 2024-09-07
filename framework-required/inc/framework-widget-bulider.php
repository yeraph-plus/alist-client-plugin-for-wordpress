<?php
if (!defined('ABSPATH')) exit;

/**
 * AIYA-CMS Theme Options Framework 小工具的简化构造器
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

/*
//Widget Demo

class AYA_Demo_Widget extends AYA_Widget
{
    function widget_args()
    {
        $widget_args = array(
            'id' => 'demo-widget',
            'title' => 'Demo Widget',
            'classname' => 'demo-widget',
            'desc' => '',
            'field_build' => array(
                array(
                    'type' => 'input',
                    'id' => 'input',
                    'name' => 'input field',
                    'default' => '',
                ),
                array(
                    'type' => 'textarea',
                    'id' => 'textarea',
                    'name' => 'textarea field',
                    'default' => '',
                ),
                array(
                    'type' => 'checkbox',
                    'id' => 'checkbox',
                    'name' => 'checkbox field',
                    'default' => true,
                ),
                array(
                    'type' => 'select',
                    'id' => 'select',
                    'name' => 'select field',
                    'sub' => array(
                        '0' => 'off',
                        '1' => 'on',
                    ),
                    'default' => '',
                ),
            ),
        );

        return $widget_args;
    }
    function widget_func()
    {
        echo parent::widget_opt('input');
        echo parent::widget_opt('textarea');
        echo parent::widget_opt('checkbox'); //this field will return string 'true' or '', is not bool
        echo parent::widget_opt('select');
    }
}

*/
if (!class_exists('AYA_Widget')) {
    abstract class AYA_Widget extends WP_Widget
    {
        public $widget_instance;
        public $widget_field;
        public $widget_title;
        public $widget_mobile;

        //定义此类的必须方法
        abstract public function widget_args();
        abstract public function widget_func();

        //注册小工具信息
        public function __construct()
        {
            $widget = $this->widget_args();

            if (is_array($widget)) {
                //验证参数
                if (isset(($widget['field_build']))) {
                    $this->widget_field = (is_array($widget['field_build'])) ? $widget['field_build'] : array();
                } else {
                    $this->widget_field = array();
                }
                //标题和移动端配置
                $this->widget_title = true;
                $this->widget_mobile = true;
                //执行注册方法
                parent::__construct($widget['id'], $widget['title'], array('classname' => $widget['classname'], 'description' => $widget['desc']));
            }
        }
        //读取设置
        public function widget_opt($field_id)
        {
            $instance = $this->widget_instance;

            return isset($instance[$field_id]) ? $instance[$field_id] : null;
        }
        //小工具函数
        public function widget($args, $instance)
        {
            $this->widget_instance = $instance;

            extract($args);

            $title = isset($instance['title']) ? $instance['title'] : '';
            //$mobile_hide = isset($instance['mobile_hide']) && $instance['mobile_hide'] == 1 ? 'mobile-hide' : '';

            //判断是否为移动端
            if (wp_is_mobile() && $instance['mobile_hide'] == 'true') return '';

            echo $before_widget;

            if (!empty($title)) {
                echo $before_title . $title . $after_title;
            }

            $this->widget_func();

            echo $after_widget;
        }
        //选项表单
        public function form($instance)
        {
            //添加标题设置
            if ($this->widget_title == true) {

                $title_type = isset($instance['title']) ? $instance['title'] : '';

                echo '<p>';
                echo '<label for="' . $this->get_field_id('title') . '">' . __('Title:', 'AIYA-CMS') . '</label>';
                echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr($title_type) . '" />';
                echo '</p>';
            }
            //输入
            foreach ($this->widget_field as $field) {
                $field_id = $field['id'];
                $field_opt = (isset($instance[$field_id])) ? $instance[$field_id] : $field['default'];

                echo '<p>';

                //输入框
                if ($field['type'] == 'input') {
                    echo '<label for="' . $this->get_field_id($field_id) . '"> ' . $field['name'] . '</label>';
                    echo '<input class="widefat" id="' . $this->get_field_id($field_id) . '" name="' . $this->get_field_name($field_id) . '" type="text" value="' . esc_attr($field_opt) . '" />';
                }
                //文本框
                elseif ($field['type'] == 'textarea') {
                    echo '<label for="' . $this->get_field_id($field_id) . '"> ' . $field['name'] . '</label>';
                    echo '<textarea class="widefat" id="' . $this->get_field_id($field_id) . '" name="' . $this->get_field_name($field_id) . '">' . esc_attr($field_opt) . '</textarea>';
                }
                //单选框
                elseif ($field['type'] == 'checkbox') {
                    echo '<label for="' . $this->get_field_id($field_id) . '"> ';
                    echo '<input class="widefat" id="' . $this->get_field_id($field_id) . '" name="' . $this->get_field_name($field_id) . '" type="checkbox" value="true" ' . checked($field_opt, 'true', false) . ' />';
                    echo $field['name'] . '</label>';
                }
                //下拉框
                elseif ($field['type'] == 'select') {
                    if (empty($field['sub'])) continue; //找不到sub则跳过

                    echo '<label for="' . $this->get_field_id($field_id) . '"> ' . $field['name'] . '</label>';
                    echo '<select class="widefat" id="' . $this->get_field_id($field_id) . '" name="' . $this->get_field_name($field_id) . '" >';
                    //循环子项
                    foreach ($field['sub'] as $sub => $sub_name) {
                        echo '<option value="' . $sub . '" ' . selected($field_opt, $sub, false) . '>' . $sub_name . '</option>';
                    }
                    echo '</select>';
                }

                echo '</p>';
            }
            //移动端隐藏
            if ($this->widget_mobile == true) {

                $mobile_checked = isset($instance['mobile_hide']) ? $instance['mobile_hide'] : '';

                echo '<p>';
                echo '<label>';
                echo '<input class="widefat" id="' . $this->get_field_id('mobile_hide') . '" name="' . $this->get_field_name('mobile_hide') . '" type="checkbox" value="true" ' . checked($mobile_checked, 'true', false) . ' />';
                echo "\n" . __('移动端不显示这个小工具');
                echo '</label>';
                echo '</p>';
            }
        }
        //保存设置
        public function update($new_instance, $old_instance)
        {
            $widget = $this->widget_args();

            $instance = $old_instance;

            //标题设置
            if ($this->widget_title == true) {
                $instance['title'] = strip_tags($new_instance['title']);
            }
            //自定义设置
            foreach ($this->widget_field as $field) {
                $field_id = $field['id'];
                $instance[$field_id] = strip_tags($new_instance[$field_id]);
            }
            //移动端隐藏
            if ($this->widget_mobile == true) {
                $instance['mobile_hide'] = strip_tags($new_instance['mobile_hide']);
            }
            return $instance;
        }
    }
}
