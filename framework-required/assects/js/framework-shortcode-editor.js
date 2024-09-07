jQuery(document).ready(function ($) {

    //$(".scodedit-tabs").tabs();

    $(".scodedit-toggle").each(function () {
        var $this = $(this);
        if ($this.attr('data-id') == 'closed') {
            $this.accordion({ header: '.scodedit-toggle-title', collapsible: true, active: false });
        } else {
            $this.accordion({ header: '.scodedit-toggle-title', collapsible: true });
        }

        $this.on('accordionactivate', function (e, ui) {
            $this.accordion('refresh');
        });

        $(window).on('resize', function () {
            $this.accordion('refresh');
        });
    });

});

//插入短代码
function scodeditInsertShortcode() {
    var select = jQuery('#select-scodedit-shortcode').val(),
        type = select.replace('scodedit-', '').replace('-shortcode', ''),
        template = jQuery('#' + select).data('shortcode-template'),
        childTemplate = jQuery('#' + select).data('shortcode-child-template'),
        tables = jQuery('#' + select).find('table').not('.scodedit-clone-template'),
        attributes = '',
        content = '',
        contentToEditor = '';

    //循环
    for (var i = 0; i < tables.length; i++) {
        var elems = jQuery(tables[i]).find('input, select, textarea');

        //映射属性字符串{{}}
        attributes = jQuery.map(elems, function (el, index) {
            var $el = jQuery(el);

            console.log(el);

            if ($el.attr('id') === 'content') {
                content = $el.val();
                return '';
            } else if ($el.attr('id') === 'last') {
                if ($el.is(':checked')) {
                    return $el.attr('id') + '="true"';
                } else {
                    return '';
                }
            } else {
                return $el.attr('id') + '="' + $el.val() + '"';
            }
        });
        attributes = attributes.join(' ').trim();

        //脱层
        if (childTemplate) {
            //执行替换
            contentToEditor += childTemplate.replace('{{attributes}}', attributes).replace('{{attributes}}', attributes).replace('{{content}}', content);
        } else {
            //执行替换
            contentToEditor += template.replace('{{attributes}}', attributes).replace('{{attributes}}', attributes).replace('{{content}}', content);
        }
    };

    //如果是子循环，脱层
    if (childTemplate) {
        contentToEditor = template.replace('{{child}}', contentToEditor);
    }

    // Send the shortcode to the content editor and reset the fields
    window.send_to_editor(contentToEditor);
    scodeditResetFields();
}

//将输入初始化为空
function scodeditResetFields() {
    jQuery('#scodedit-sub-title').text('');
    jQuery('.scodedit-wrap').find('input[type=text], select').val('');
    jQuery('.codedit-shortcode-wrap').find('textarea').text('');
    jQuery('.scodedit-sub-type').hide();
}

//redraw the thickbox
function scodeditResizeTB() {
    var ajaxCont = jQuery('#TB_ajaxContent'),
        tbWindow = jQuery('#TB_window'),
        scodeditPopup = jQuery('.scodedit-wrap');

    ajaxCont.css({
        height: (tbWindow.outerHeight() - 50),
        overflow: 'auto', // IMPORTANT
        width: (tbWindow.outerWidth() - 30)
    });
}

jQuery(document).ready(function ($) {
    var $shortcodes = $('.scodedit-sub-type').hide(),
        $title = $('#scodedit-sub-title');

    // Show the selected shortcode input fields
    $('#select-scodedit-shortcode').change(function () {
        var text = $(this).find('option:selected').text();

        $shortcodes.hide();
        $title.text(text);
        $('#' + $(this).val()).show();
        scodeditResizeTB();
    });

    // Remove a set of input fields
    $('.scodedit-sub-type').on('click', '.scodedit-remove', function () {
        $(this).closest('table').remove();
    });

    // Make content sortable using the jQuery UI Sortable method
    $('.scodedit-sortable').sortable({
        items: 'table:not(".hidden")',
        placeholder: 'scodedit-sortable-placeholder'
    });
});