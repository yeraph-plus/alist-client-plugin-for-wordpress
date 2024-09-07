<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

/**
 * 文本框
 * 
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 */

class AYA_Option_Fired_textarea extends AYA_Field_Action
{
    public function action($field)
    {
        //检查数据
        if (empty($field['default'])) {
            $field['default'] = '';
        }

        return parent::before_tags($field) . self::textarea($field) . parent::after_tags($field);
    }

    function textarea($field)
    {
        //定义格式
        $format = '<textarea class="quick-textarea autowidth" id="%s" name="%s" >%s</textarea>';

        $html = sprintf(
            $format,
            $field['id'],
            $field['id'],
            $field['default']
        );

        return $html;
    }
}
