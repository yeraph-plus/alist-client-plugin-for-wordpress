<?php
if (!defined('ABSPATH')) exit;

/**
 * AIYA-CMS Theme Options Framework 创建Taxonomy额外字段
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

if (!class_exists('AYA_Framework_Term_Meta')) {
    class AYA_Framework_Term_Meta
    {
        private $options;
        private $option_tax_meta;

        private $unfined_field;

        function __construct($option_conf, $option_tax_meta)
        {
            $this->options = $option_conf;
            $this->option_tax_meta = $option_tax_meta;

            //定义禁用项
            $this->unfined_field = array('group', 'group_mult', 'code_editor', 'tinymce');

            //如果传入是数组
            if (is_array($option_tax_meta)) {
                //循环执行
                foreach ($this->option_tax_meta as $taxonomy) {
                    add_action($taxonomy . '_add_form_fields', array(&$this, 'add_taxonomy_field'), 10, 2);
                    add_action($taxonomy . '_edit_form_fields', array(&$this, 'edit_taxonomy_field'), 10, 2);

                    add_action('created_' . $taxonomy, array(&$this, 'save_taxonomy_field'), 10, 1);
                    add_action('edited_' . $taxonomy, array(&$this, 'save_taxonomy_field'), 10, 1);
                    add_action('delete_' . $taxonomy, array(&$this, 'delete_taxonomy_field_data'), 10, 1);
                }
            } else {
                //检查是否为空
                if (!empty($option_tax_meta)) {
                    $taxonomy = $option_tax_meta;

                    add_action($taxonomy . '_add_form_fields', array(&$this, 'add_taxonomy_field'), 10, 2);
                    add_action($taxonomy . '_edit_form_fields', array(&$this, 'edit_taxonomy_field'), 10, 2);

                    add_action('created_' . $taxonomy, array(&$this, 'save_taxonomy_field'), 10, 1);
                    add_action('edited_' . $taxonomy, array(&$this, 'save_taxonomy_field'), 10, 1);
                    add_action('delete_' . $taxonomy, array(&$this, 'delete_taxonomy_field_data'), 10, 1);
                }
            }
        }
        //创建字段
        function add_taxonomy_field()
        {
            echo '<div class="form-field framework-wrap">';

            foreach ($this->options as $option) {
                //排除不支持的组件
                if (in_array($option['type'], $this->unfined_field)) {
                    continue;
                }

                AYA_Field_Action::field($option);
            }

            echo '</div>';
        }
        //编辑字段
        function edit_taxonomy_field($tag)
        {
            foreach ($this->options as $option) {
                //排除不支持的组件
                if (in_array($option['type'], $this->unfined_field)) {
                    continue;
                }
                //获取字段数据
                $feild_value = get_term_meta($tag->term_id, $option['id'], true);

                if ($feild_value != '') {
                    $option['default'] = $feild_value;
                }
                //重排组件结构
                $add_class = '';

                if ($option['type'] == 'color') $add_class = ' framework-color-picker';
                if ($option['type'] == 'switch') $add_class = ' framework-switcher';
                if ($option['type'] == 'upload') $add_class = ' framework-upload';

                $html = '<div class="form-field framework-wrap">';
                $html .= '<tr class="section-' . $option['type'] . $add_class . '"><th scope="row">';
                $html .= '<label for="' . $option['id'] . '">' . $option['title'] . '</label>';
                $html .= '</th><td>';
                $html .= AYA_Field_Action::field_mult($option);
                $html .= '<p class="description">' . $option['desc'] . '</p>';
                $html .= '</td></tr></div>';

                echo $html;
            }
        }
        //保存数据
        function save_taxonomy_field($term_id)
        {
            //用户权限检查
            if (!current_user_can('manage_categories')) return;

            foreach ($this->options as $option) {

                //$old_data = get_term_meta($term_id, $option['id'], true);

                //没有ID则直接跳过
                if (!isset($option['id'])) {
                    continue;
                }
                $data = empty($_POST[$option['id']]) ? '' : $_POST[$option['id']];
                //如果是数组
                if ($option['type'] == 'array') {
                    $data = explode(',', $data);
                    $data = array_filter($data);
                }
                //其他
                else {
                    //$data = wp_unslash($data);
                    $data = htmlspecialchars($data, ENT_QUOTES, "UTF-8");
                }

                if ($data == '') {
                    delete_term_meta($term_id, $option['id'], $data);
                } else {
                    update_term_meta($term_id, $option['id'], $data);
                }
            }
        }
        //删除数据
        function delete_taxonomy_field($term_id)
        {
            foreach ($this->options as $options) {
                if (isset($options['id'])) {
                    delete_term_meta($term_id, $options['id']);
                }
            }
        }
    }
}
