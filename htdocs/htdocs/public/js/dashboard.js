$(function() {

    // Load the Visualization API and the piechart package.      
    google.load('visualization', '1.0', {'packages': ['corechart']});
    // Set a callback to run when the Google Visualization API is loaded.      
    google.setOnLoadCallback(drawChart);

    $('#tabs, #tabs2').tabs();
    $(".resultset tr:even").addClass("alt");
    $("input:submit").button();


    $("input:submit").button();
    $("input:reset").button();

    $("#paging, #paging2").buttonset();


});



