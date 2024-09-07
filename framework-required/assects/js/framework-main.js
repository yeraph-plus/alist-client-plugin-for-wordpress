jQuery(document).ready(function ($) {
  //color-picker
  $(".framework-color-picker").find(".quick-color").each(function () {
    $(this).wpColorPicker();
  });
  //upload
  $(".framework-upload").on("click", "a.quick-upload-button", function () {
    let upload_frame;
    event.preventDefault();
    upload_btn = $(this);
    if (upload_frame) {
      upload_frame.open();
      return;
    }
    upload_frame = wp.media({
      multiple: false,
    });
    upload_frame.on("select", function () {
      let attachment = upload_frame.state().get("selection").first().toJSON();

      upload_btn.parent().find(".quick-upload-input").val(attachment.url).trigger("change");
    });
    upload_frame.open();
  });
  //swtich
  $(".framework-switcher").on("click", ".quick-switch", function () {
    let slider = $(this).find(".slider");
    let bool = 0;
    slider.hasClass("active") ? slider.removeClass("active") : ((bool = 1), slider.addClass("active")), $(this).find("input").val(bool).trigger("change");
  });
  //mult mode
  $(".framework-field-mult").ready(function () {
    $(this).find(".field-group-warp").on("click", "a.add-item", function () {
      let mult_count = $(this).closest(".field-group-warp").find(".group-item").length + 1;
      mult_template_load = $("#" + $(this).data("group-name")).html().replace(/({{i}})/g, mult_count);
      event.preventDefault();
      $(this).before(mult_template_load);
    });
    $(".field-group-warp").find(".group-item").on("click", "a.del-item", function () {
      $(this).closest(".group-item").remove();
    });
  });
  //codemirror
  $(".codemirror-editor").find("textarea").each(function (div, obj) {
    let editor = CodeMirror.fromTextArea(obj, $(obj).data("editor"));
  });
});
