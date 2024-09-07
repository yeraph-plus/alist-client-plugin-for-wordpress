<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

/**
 * 嵌入颜色选择器
 * 
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 */

class AYA_Option_Fired_color extends AYA_Field_Action
{
    public function action($field)
    {
        //调用WP颜色选择器
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        $field['class'] = 'framework-color-picker';

        //检查数据
        if (empty($field['default'])) {
            $field['default'] = '#FFFFFF';
        }

        return parent::before_tags($field) . self::color($field) . parent::after_tags($field);
    }

    public function color($field)
    {
        //定义格式
        $format = '<input class="quick-color" type="text" id="%s" name="%s" value="%s" />';

        $html = sprintf(
            $format,
            $field['id'],
            $field['id'],
            $field['default']
        );

        return $html;
    }
}
