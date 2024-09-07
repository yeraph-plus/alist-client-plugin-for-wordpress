<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('AYA_Field_Action')) exit;

/**
 * 抽屉
 * 
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 */

class AYA_Option_Fired_select extends AYA_Field_Action
{
    public function action($field)
    {
        //检查数据
        if (empty($field['default'])) {
            $field['default'] = '';
        }

        return parent::before_tags($field) . self::select($field) . parent::after_tags($field);
    }

    public function select($field)
    {
        //增加查询
        $entries = parent::entry_select($field);

        $html = '<select class="quick-select autowidth" id="' . $field['id'] . '" name="' . $field['id'] . '"> ';

        $html .= '<option value="">Select...</option>';

        foreach ($entries as $id => $title) {

            //$selected = ($field['default'] == $id) ? 'selected="selected"' : '';

            $html .= '<option ' . selected($id, $field['default'], false) . ' value="' . $id . '">' . $title . '</option>';
        }
        $html .= '</select>';

        return $html;
    }
}
