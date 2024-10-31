jQuery.noConflict();
jQuery(document).ready(function($) {
    // Resize a fluid height iframe
    $(".ow-iframe-fluid").load(function() {
        $(this).height( $(this).contents().find("html").height());
    });
});