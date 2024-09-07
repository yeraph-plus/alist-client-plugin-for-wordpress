<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

/**
 * 单选框
 * 
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 */

class AYA_Option_Fired_radio extends AYA_Field_Action
{
    public function action($field)
    {
        //检查数据
        if (empty($field['default'])) {
            $field['default'] = '';
        }

        return parent::before_tags($field) . self::radio($field) . parent::after_tags($field);
    }

    public function radio($field)
    {
        //检查并返回数据
        $entries = parent::entry_select($field);

        //定义格式
        $format = '<label class="quick-radio autowidth" for="%s"><input type="radio" id="%s" name="%s" value="%s" %s data-text="%s" />%s</label>';

        $html = '';
        //循环
        foreach ($entries as $ent_id => $ent_title) {
            /*
            $checked = '';
            if ($field['default'] == $ent_id) {
                $checked = 'checked="checked"';
            }
            */

            $html .= sprintf(
                $format,
                $field['id'] . '-' . $ent_id,
                $field['id'] . '-' . $ent_id,
                $field['id'],
                $ent_id,
                checked($ent_id, $field['default'], false),
                $ent_title,
                $ent_title
            );
        }

        return $html;
    }
}
