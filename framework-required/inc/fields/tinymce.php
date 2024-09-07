<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

/**
 * 嵌入tinymce编辑器
 * 
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 */

class AYA_Option_Fired_tinymce extends AYA_Field_Action
{
    public function action($field)
    {
        //检查数据
        if (empty($field['default'])) {
            $field['default'] = '';
        }

        self::tinymce($field);
    }

    public function tinymce($field)
    {
        $settings = array('tinymce' => 1, 'editor_height' => 300);

        if (isset($field['style']) && $field['style'] != '') {
            $settings['tinymce'] = array('content_css' => $field['style']);
        }

        if (isset($field['media']) && !$field['media']) {
            $settings['media_buttons'] = 0;
        } else {
            $settings['media_buttons'] = 1;
        }

        if (!empty($field['textarea_name'])) {
            $settings['textarea_name'] = $field['textarea_name'];
        }

        echo parent::before_tags($field);

        wp_editor($field['default'], $field['id'], $settings);

        echo parent::after_tags($field);
    }
}
