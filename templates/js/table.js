$(document).ready(function() {
    // init clickable table rows
    $(".ocpc_table_row_selectable").click(function() {
        window.location = $(this).data("href");
    });
    // hide page selector
    $('label[for="tab_page_sel_1"]').hide();
    $('label[for="tab_page_sel_2"]').hide();
    $('#tab_page_sel_1').hide();
    $('#tab_page_sel_2').hide();
});
OpencastPageComponent = {
    overwriteResetButton: function(name, url) {
        $('input[name="cmd[resetFilter]"]').replaceWith('<a class="btn btn-default" href="' + url + '">' + name + '</a>');
    }
};