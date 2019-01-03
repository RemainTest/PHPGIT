$(function () {

    $('#tabs, #tabs2').tabs();

    $('#datepicker, #datepicker2').datepicker({
        inline: true, 
        dateFormat: 'dd-mm-yy',
        showButtonPanel: true
    });

    $("input:submit").button();

    //$(".resultset tr").mouseover(function () { $(this).addClass("over"); }).mouseout(function () { $(this).removeClass("over"); });
    $(".resultset tr:even").addClass("alt");
    
    $('#popup_gmail').dialog({
        dialogClass: 'no-close', 
        modal: true, 
        draggable: false, 
        resizable: false, 
        autoOpen: false, 
        width: 200, 
        height: 100
    });
    $( "#pb1, #pb2" ).progressbar({
        value: 100
    });

});