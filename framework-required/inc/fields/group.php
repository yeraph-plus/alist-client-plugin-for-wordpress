<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

/**
 * 编组
 * 
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 */

class AYA_Option_Fired_group extends AYA_Field_Action
{
    public function action($field)
    {
        $field['class'] = 'framework-field-group';

        //检查数据
        if (empty($field['sub_type']) || !is_array($field['sub_type'])) {
            //报错
            parent::out_error(__('Could not be built "group" in one loop.'));

            $field['sub_type'] = array();
        }

        return parent::before_tags($field) . self::group($field) . parent::after_tags($field);
    }

    public function group($field)
    {
        $html = '';
        $html .= '<div class="field-group-warp">';
        //循环
        foreach ($field['sub_type'] as $sub_field) {
            //跳过循环
            if (!isset($sub_field['id'])) {
                continue;
            }
            //跳过重复创建
            if ($sub_field['type'] == 'group') {
                //报错
                parent::out_error(__('Could not be built "group" in one loop.'));

                continue;
            }
            //读取设置值
            if (!empty($field['default'][$sub_field['id']])) {
                $sub_field['default'] = $field['default'][$sub_field['id']];
            }
            //重建参数
            if ($sub_field['type'] == 'tinymce') {
                $sub_field['id'] = $field['id'] . '-' . $sub_field['id'];
                $sub_field['textarea_name'] = $field['id'] . '[' . $sub_field['id'] . ']';
            } else {
                $sub_field['id'] = $field['id'] . '[' . $sub_field['id'] . ']';
            }
            //加载方法
            $html .= parent::field_mult($sub_field, true);
        }
        $html .= '</div>';

        return $html;
    }
}
