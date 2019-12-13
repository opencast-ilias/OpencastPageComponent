$(document).ready(function() {
    $(".ocpc_table_row").click(function() {
        window.location = $(this).data("href");
    });
});
OpencastPageComponent = {
    overwriteResetButton: function(name, url) {
        $('input[name="cmd[resetFilter]"]').replaceWith('<a class="btn btn-default" href="' + url + '">' + name + '</a>');
    }
};