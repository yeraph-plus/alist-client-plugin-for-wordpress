<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

/**
 * 输入框
 * 
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 */

class AYA_Option_Fired_text extends AYA_Field_Action
{
    public function action($field)
    {
        //检查数据
        if (empty($field['default'])) {
            $field['default'] = '';
        }

        return parent::before_tags($field) . self::text($field) . parent::after_tags($field);
    }

    function text($field)
    {
        //定义格式
        $format = '<input class="quick-input autowidth" type="text" id="%s" name="%s" value="%s" />';

        $html = sprintf(
            $format,
            $field['id'],
            $field['id'],
            $field['default']
        );

        return $html;
    }
}
