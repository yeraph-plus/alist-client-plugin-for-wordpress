<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

/**
 * 嵌入文件管理器
 * 
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 */

class AYA_Option_Fired_upload extends AYA_Field_Action
{
    public function action($field)
    {
        //调用WP上传组件
        wp_enqueue_media();

        $field['class'] = 'framework-upload';

        //检查数据
        if (empty($field['default'])) {
            $field['default'] = '';
        }

        return parent::before_tags($field) . self::upload($field) . parent::after_tags($field);
    }

    public function upload($field)
    {
        //检查按钮文本
        $button_text = (empty($field['button_text'])) ? __('Upload') : $field['button_text'];

        //定义格式
        $format = '<input type="text" id="%s" name="%s" value="%s" class="quick-upload-input autowidth" />';

        $format .= '<a id="%s" class="quick-upload-button button autowidth" href="javascript:void(0);">%s</a>';

        $html = sprintf(
            $format,
            $field['id'] . '-upload',
            $field['id'],
            $field['default'],
            $field['id'],
            $button_text
        );

        return $html;
    }
}
