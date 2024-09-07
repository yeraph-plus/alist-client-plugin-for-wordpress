<?php
if (!defined('ABSPATH')) exit;

/**
 * AIYA-CMS Theme Options Framework 创建Metabox组件
 * 
 * Author: Yeraph Studio
 * Author URI: http://www.yeraph.com/
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package AIYA-CMS Theme Options Framework
 * @version 1.0
 **/

if (!class_exists('AYA_Framework_Post_Meta')) {
    class AYA_Framework_Post_Meta
    {
        private $options;
        private $meta_inst;

        private $unfined_field;

        function __construct($options, $meta_inst)
        {
            $this->options = $options;
            $this->meta_inst = $meta_inst;

            //定义禁用项
            $this->unfined_field = array('group', 'group_mult', 'code_editor', 'tinymce');

            add_action('admin_menu', array(&$this, 'init_metaboxes'));

            add_action('post_updated', array(&$this, 'save_podefaultata'), 9);
            add_action('save_post', array(&$this, 'save_podefaultata'));
        }
        public function init_metaboxes()
        {
            $meta_box_areas = $this->meta_inst['add_box_in'];

            if (function_exists('add_meta_box') && is_array($meta_box_areas)) {
                foreach ($meta_box_areas as $meta_area) {
                    //检查模板参数，兼容页面、指定页面
                    if (isset($this->meta_inst['template']) && $meta_area == 'page') {
                        if (isset($_GET['post'])) {
                            $post_id = $_GET['post'];
                        } else {
                            $post_id = 0;
                        }

                        $page_template = get_post_meta($post_id, '_wp_page_template', true);

                        if ($this->meta_inst['template'] == $page_template) {
                            add_meta_box(
                                $this->meta_inst['id'],
                                $this->meta_inst['title'],
                                array(&$this, 'create_meta_box'),
                                $meta_area,
                                $this->meta_inst['context'],
                                $this->meta_inst['priority']
                            );
                        }
                    } else {
                        add_meta_box(
                            $this->meta_inst['id'],
                            $this->meta_inst['title'],
                            array(&$this, 'create_meta_box'),
                            $meta_area,
                            $this->meta_inst['context'],
                            $this->meta_inst['priority']
                        );
                    }
                }
            }
        }

        public function create_meta_box()
        {
            if (isset($_GET['post']))
                $post_id = $_GET['post'];
            else
                $post_id = 0;

            echo '<div class="tab-content framework-wrap">';

            foreach ($this->options as $option) {
                //排除不支持的组件
                if (in_array($option['type'], $this->unfined_field)) {
                    continue;
                }
                //获取字段数据
                $meta_value = get_post_meta($post_id, $option['id'], true);

                if ($meta_value != '') {
                    $option['default'] = $meta_value;
                }
                AYA_Field_Action::field($option);
            }

            echo '</div>';
        }

        public function save_podefaultata($post_id)
        {
            //跳过自动保存
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (isset($_POST['post_type']) && in_array($_POST['post_type'], $this->meta_inst['add_box_in'])) {

                //用户权限检查
                if ('page' == $_POST['post_type']) {
                    if (!current_user_can('edit_page', $post_id))
                        return false;
                } else {
                    if (!current_user_can('edit_post', $post_id))
                        return false;
                }

                foreach ($this->options as $option) {

                    //$old_data = get_post_meta($post_id, $option['id']);

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
                        delete_post_meta($post_id, $option['id'], $data);
                    } else {
                        update_post_meta($post_id, $option['id'], $data);
                    }
                }
            }
        }
    }
}
