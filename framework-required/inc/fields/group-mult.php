<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

/**
 * 编组循环调用
 * 
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 */

class AYA_Option_Fired_group_mult extends AYA_Field_Action
{
    public function action($field)
    {
        $field['class'] = 'framework-field-mult clearfix';

        return parent::before_tags($field) . self::group_mult($field) . parent::after_tags($field);
    }

    public function group_mult($field)
    {
        $html = '';

        //检查设置值数组
        if (empty($field['default']) || !is_array($field['default'])) {
            $field['default'] = array();
            $n = 0;
        } else {
            $n = count($field['default'], 0);
        }
        $html .= '<div class="field-group-warp">';
        //创建自增表单格式模板
        $html .= '<template id="' . $field['id'] . '">';
        $html .= '<div class="group-item">';
        foreach ($field['sub_type'] as $sub_field) {
            //跳过循环
            if (!isset($sub_field['id'])) {
                continue;
            }
            //跳过重复创建
            if ($sub_field['type'] == 'group' || $sub_field['type'] == 'group_mult' || $sub_field['type'] == 'tinymce') {
                continue;
            }

            //重建参数
            $sub_field['id'] = $field['id']  . '[{{i}}][' . $sub_field['id'] . ']';

            //加载方法
            $html .= parent::field_mult($sub_field, true);
        }
        //$html .= '<a href="#" class="del-item">' . __('Delete') . '</a>';
        $html .= '</div>';
        $html .= '</template>';
        //循环
        for ($i = 1; $i <= $n; $i++) {
            $html .= '<div class="group-item">';
            //循环
            foreach ($field['sub_type'] as $sub_field) {
                //跳过循环
                if (!isset($sub_field['id'])) {
                    continue;
                }
                //跳过重复创建
                if ($sub_field['type'] == 'group' || $sub_field['type'] == 'group_mult' || $sub_field['type'] == 'tinymce') {
                    //报错
                    parent::out_error(__('This field can not built duplicate creation.'));

                    continue;
                }

                //读取设置值
                if (!empty($field['default'][$i][$sub_field['id']])) {
                    $sub_field['default'] = $field['default'][$i][$sub_field['id']];
                }
                //重建参数
                $sub_field['id'] = $field['id']  . '[' . $i . '][' . $sub_field['id'] . ']';

                //加载方法
                $html .= parent::field_mult($sub_field, true);
            }

            $html .= '<a href="#" class="del-item">' . __('Delete') . '</a>';
            $html .= '</div>';
        }

        $html .= '<a href="#" class="add-item button-secondary" data-group-name="' . $field['id'] . '">' . __('Add') . '</a>';

        $html .= '</div>';

        return $html;
    }
}
