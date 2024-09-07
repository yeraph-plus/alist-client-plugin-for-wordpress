<?php

//document.html
function framework_doc_about_page()
{
    $document_file = plugin_dir_path(__FILE__) . '/assects/document.html';

    $open_file = fopen($document_file, 'r') or die('Unable to open file!');

    echo fread($open_file, filesize($document_file));

    fclose($open_file);
}

new AYA_Framework_Setup();

//----- about page -----

$about_info = array(
    'title' => 'AIYA-Framework',
    'slug' => 'sample',
    'icon' => 'dashicons-admin-generic',
    'desc' => 'AIYA-CMS Theme Options Framework.',
);
$about_options = array(
    array(
        'function' => 'framework_doc_about_page',
        'type' => 'callback',
    ),
);

new AYA_Framework_Options_Page($about_options, $about_info);

//----- options page -----

$page_info = array(
    'page_title' => 'Sample Options',
    'title' => 'Sample Options',
    'slug' => 'sample-field',
    'parent' => 'sample',
    'desc' => 'AIYA-CMS Theme Options Framework.',
    //'in_multisite' => false,
);
$page_options = array(
    array(
        'desc' => 'H2 Title desc',
        'type' => 'title_1',
    ),
    array(
        'desc' => 'H2 Title desc',
        'type' => 'title_2',
    ),
    array(
        'desc' => 'Text desc',
        'type' => 'content',
    ),
    array(
        'desc' => 'Text desc',
        'type' => 'message',
    ),
    array(
        'title' => 'Input Example',
        'desc' => 'This field is input box',
        'id' => 'sample_input',
        'type' => 'text',
        'default' => '',
    ),
    array(
        'title' => 'Textarea Example',
        'desc' => 'Description or Notice',
        'id' => 'sample_textarea',
        'type' => 'textarea',
        'default'  => 'Default content',
    ),
    array(
        'title' => 'Color Example',
        'desc' => 'Set type as color picker worked.',
        'id' => 'sample_color_picker',
        'type' => 'color',
        'default'  => '',
    ),
    array(
        'title' => 'Radio Example',
        'desc' => 'Set type as select.',
        'id' => 'sample_radio',
        'type' => 'radio',
        'sub'  => array(
            'custom_radio_1' => 'Radio 1',
            'custom_radio_2' => 'Radio 2',
            'custom_radio_3' => 'Radio 3',
        ),
        'default' => 'custom_radio_2',
    ),
    array(
        'title' => 'Switch Example',
        'desc' => 'Set type as select.',
        'id' => 'sample_switch',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => 'Checkbox Example',
        'desc' => 'Set type as select.',
        'id' => 'sample_checkbox',
        'type' => 'checkbox',
        'sub'  => array(
            'custom_check_1' => 'Checkbox 1',
            'custom_check_2' => 'Checkbox 2',
            'custom_check_3' => 'Checkbox 3',
        ),
        'default' => 'custom_check_1,custom_check_3', // or array('custom_check_1', 'custom_check_3'),
    ),
    array(
        'title' => 'Checkbox Example 2',
        'desc' => 'Set type as menus.',
        'id' => 'sample_checkbox_2',
        'type' => 'checkbox',
        'sub_mode' => 'nav_menu',
        'default' => '',
    ),
    array(
        'title' => 'Checkbox Example 3',
        'desc' => 'Set type as categories.',
        'id' => 'sample_checkbox_3',
        'type' => 'checkbox',
        'sub_mode' => 'category',
        'default' => '',
    ),
    array(
        'title' => 'Select Example',
        'desc' => 'Set type as select.',
        'id' => 'sample_select',
        'type' => 'select',
        'sub'  => array(
            'select_1' => 'Select 1',
            'select_2' => 'Select 2',
            'select_3' => 'Select 3',
        ),
        'default' => 'select_3',
    ),
    array(
        'title' => 'Select Example 2',
        'desc' => 'Set type as pages.',
        'id' => 'sample_select_2',
        'type'    => 'select',
        'sub_mode' => 'page',
        'default' => '',
    ),
    array(
        'title' => 'Select Example 3',
        'desc' => 'Set type as users.',
        'id' => 'sample_select_3',
        'type' => 'select',
        'sub_mode' => 'user',
        'default' => '',
    ),
    array(
        'title' => 'Select Example 4',
        'id' => 'sample_select_4',
        'desc' => 'Set type as sidebars.',
        'type'    => 'select',
        'sub_mode' => 'sidebar',
        'default' => '',
    ),
    array(
        'title' => 'File Upload Example',
        'desc' => 'Upload a file or fill the blank with file uri.',
        'id' => 'sample_upload',
        'type' => 'upload',
        'button_text' => 'New Upload',
        'default'  => '',
    ),
);

new AYA_Framework_Options_Page($page_options, $page_info);

//----- options page -----

$child_info = array(
    'title' => 'Sample Options Group',
    'slug' => 'sample-group',
    'parent' => 'sample',
    'desc' => 'AIYA-CMS Theme Options Framework.',
);
$child_options = array(
    array(
        'title' => 'Group Options Example',
        'desc' => 'Set the type as group.',
        'id' => 'sample_group',
        'type' => 'group',
        'sub_type' => array(
            array(
                'title' => 'Title',
                'id' => 'group_title',
                'type' => 'text',
                'default'  => 'Title for image',
            ),
            array(
                'title' => 'Link',
                'id' => 'group_link',
                'type' => 'text',
                'default'  => 'https://',
            ),
            array(
                'title' => 'Color',
                'id' => 'group_color',
                'type' => 'color',
                'default'  => '',
            ),
            array(
                'title' => 'Image',
                'id' => 'group_image',
                'type' => 'upload',
                'button_text' => 'Upload',
                'default'  => '',
            ),
        ),
    ),
    array(
        'title' => 'Group Options Mult Mode',
        'desc' => 'Set the type as group mult mode.',
        'id' => 'sample_group_multiple',
        'type' => 'group_mult',
        'sub_type' => array(
            array(
                'title' => 'Title',
                'id' => 'group_title',
                'type' => 'text',
                'default'  => 'Title for image',
            ),
            array(
                'title' => 'Link',
                'id' => 'group_link',
                'type' => 'text',
                'default'  => 'https://',
            ),
        ),
    ),
    array(
        'title' => 'Group Options Mult Mode',
        'desc' => 'Set the type as group mult mode.',
        'id' => 'sample_group_multiple_2',
        'type' => 'group_mult',
        'sub_type' => array(
            array(
                'title' => 'Color',
                'id' => 'group_color',
                'type' => 'color',
                'default'  => '',
            ),
            array(
                'title' => 'Image',
                'id' => 'group_image',
                'type' => 'upload',
                'button_text' => 'Upload',
                'default'  => '',
            ),
        ),
    ),
    array(
        'title'  => 'Tinymce Editor Example',
        'id'    => 'tinymce_editor',
        'desc'  => 'Pleas add some content',
        'type'  => 'tinymce',
        'media' => true,
        'default'   => 'Hello, world.',
    ),
    array(
        'title'  => 'Code Editor Example',
        'id'    => 'code_editor',
        'desc'  => 'Pleas add some content',
        'type'  => 'code_editor',
        'settings' => array(
            'lineNumbers'   => true,
            'tabSize'       => 2,
            'theme'         => 'monokai',
            'mode'          => 'htmlmixed',
        ),
        'default'   => 'Hello, world.',
    ),
    array(
        'title'  => 'Code Editor Example',
        'id'    => 'code_editor_2',
        'desc'  => 'Pleas add some content',
        'type'  => 'code_editor',
        'settings' => array(),
        'default'   => 'Hello, world.',
    ),
);

new AYA_Framework_Options_Page($child_options, $child_info);

//GET Options:
//$get_options = get_option('aya_opt_sample');
//print_r($get_options);

//----- taxonomy feild -----

$in_term = array('category', 'post_tag');
$term_feild = array(
    array(
        'title' => 'Input Example',
        'desc' => 'This field is input box',
        'id' => 'sample_input',
        'type' => 'text',
        'default' => '',
    ),
    array(
        'title' => 'Textarea Example',
        'desc' => 'Description or Notice',
        'id' => 'sample_textarea',
        'type' => 'textarea',
        'default'  => '',
    ),
    array(
        'title' => 'File Upload Example',
        'desc' => 'Upload a file or fill the blank with file uri.',
        'id' => 'sample_upload_2',
        'type' => 'upload',
        'button_text' => 'Upload',
        'default'  => '',
    ),
);

new AYA_Framework_Term_Meta($term_feild, $in_term);
//GET Options:
//$get_value = get_term_meta($tag->term_id, 'sample_input', true);
//$get_value = get_term_meta($tag->term_id, 'sample_textarea', true);

//----- meta box -----

$meta_info = array(
    'title' => 'Meta box example',
    'id' => 'example_box',
    'context' => 'normal',
    'priority' => 'low',
    'add_box_in' => array('page', 'post'),
);
$info_meta = array(
    array(
        'title' => 'Input Example',
        'desc' => 'A text input example, Default content:"Say Hello."',
        'id' => 'text_example',
        'type' => 'text',
        'default'  => 'Say Hello.',
    ),
    array(
        'title' => 'Textarea Example',
        'desc' => 'A textarea example, Default content:"Default content."',
        'id' => 'textarea_example',
        'type' => 'textarea',
        'default'  => 'Default content.',
    ),
    array(
        'title' => 'Switch Example',
        'desc' => 'Set type as select.',
        'id' => 'sample_switch',
        'type' => 'switch',
        'default' => true,
    ),
    array(
        'title' => 'File Upload Example',
        'desc' => 'Upload a file or fill the blank with file uri.',
        'id' => 'sample_upload_2',
        'type' => 'upload',
        'button_text' => 'Upload',
        'default'  => '',
    ),
);

new AYA_Framework_Post_Meta($info_meta, $meta_info);
//GET Options
//$meta_value = get_post_meta($post->ID,'text_example',true);
//$meta_value = get_post_meta($post->ID,'textarea_example',true);