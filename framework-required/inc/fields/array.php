<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

/**
 * 输入框生成数组
 * 
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 */

class AYA_Option_Fired_array extends AYA_Field_Action
{
    public function action($field)
    {
        return parent::before_tags($field) . self::array($field) . parent::after_tags($field);
    }

    public function array($field)
    {
        //检查数据
        if (!empty($field['default']) && is_array($field['default'])) {
            $this_array = implode(',', $field['default']);
        } else {
            $this_array = '';
        }

        $field['default'] = $this_array;

        //加载方法
        $field['type'] = 'text';

        return parent::field_mult($field);
    }
}
