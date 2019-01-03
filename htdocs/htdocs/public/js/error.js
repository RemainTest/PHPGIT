$(function () {

    $('#tabs, #tabs2').tabs();

    $("input:submit").button();
    $("input:reset").button();
    
    $("#paging, #paging2").buttonset();

});


function close_tab() {
    var win = window.open("", "_self");
    win.close();
}
