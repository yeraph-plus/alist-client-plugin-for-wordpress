<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

/**
 * 复选框
 * 
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 */

class AYA_Option_Fired_checkbox extends AYA_Field_Action
{
    public function action($field)
    {
        return parent::before_tags($field) . self::checkbox($field) . parent::after_tags($field);
    }

    public function checkbox($field)
    {
        //检查默认值
        if (!empty($field['default'])) {
            $default = (is_array($field['default'])) ? $field['default'] : explode(',', $field['default']);
        } else {
            $default = array();
        }
        //检查并提取数据
        $entries = parent::entry_select($field);

        //定义格式
        $format = '<label class="quick-checkbox autowidth" for="%s"><input type="checkbox" id="%s" name="%s" value="%s" %s data-text="%s" />%s</label>';

        $html = '';
        //循环
        foreach ($entries as $ent_id => $ent_title) {
            $field_checked = in_array($ent_id, $default);

            $html .= sprintf(
                $format,
                $field['id'] . '-' . $ent_id,
                $field['id'] . '-' . $ent_id,
                $field['id'] . '[]',
                $ent_id,
                checked($field_checked, true, false),
                $ent_title,
                $ent_title
            );
        }
        return $html;
    }
}
