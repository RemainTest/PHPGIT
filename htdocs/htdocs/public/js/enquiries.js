$(function () {

    $('#tabs, #tabs2').tabs({
      //event: "mouseover"
    });

    $("input:submit").button();
    $("input:reset").button();

    $("#paging, #paging2").buttonset();
    
    $("#ViewA").click(function () {
        redirectPage('/enquiries/index/f/1/sel/A');
        return false;
    });
    $("#ViewB").click(function () {
        redirectPage('/enquiries/index/f/1/sel/B');
        return false;
    });
    $("#ViewC").click(function () {
        redirectPage('/enquiries/index/f/1/sel/C');
        return false;
    });
    $("#ViewD").click(function () {
        redirectPage('/enquiries/index/f/1/sel/D');
        return false;
    });
    
    $(".resultset tr:even").addClass("alt");
        
    // Datepickers
    $('#field1').change(function() {
        date_popup('1');
        $('#text1').val("");
    });

    $('#field2').change(function() {
        date_popup('2');
        $('#text2').val("");
    });

    $('#field3').change(function() {	
        date_popup('3');
        $('#text3').val("");
    });

    $('#field4').change(function() {
        date_popup('4');
        $('#text4').val("");
    });
        
        
    $('#field5').change(function() {
        date_popup('5');
        $('#text5').val("");
    });
        
    $('#field6').change(function() {
        date_popup('6');
        $('#text6').val("");
    });
        
	
    $('#from, #to').datepicker({
        inline: true, 
        dateFormat: 'dd/mm/yy', 
        showButtonPanel: true
    });
    $('#START_DATE').datepicker({
        inline: true, 
        dateFormat: 'dd/mm/yy', 
        showButtonPanel: true
    });
    $('#FOLLOW_UP').datepicker({
        inline: true, 
        dateFormat: 'dd/mm/yy', 
        showButtonPanel: true
    });
    
    $('#DATE_GIVEN1').datepicker({
        inline: true, 
        dateFormat: 'dd/mm/yy', 
        showButtonPanel: true
    });
    
    $('#tabs2 li *:last').text($('#records').val());
    
    if (tab_switch) {
        tab_switch();
    }
    
        
});

function date_popup(id) {
    //alert('date_popup function for id = '  + id);
    if ($('#field' + id).val() == "DATE(ADD_TS)" || $('#field' + id).val() == "START_DATE" || $('#field' + id).val() == "DATE(CLOSED_TS)" || $('#field' + id).val() == "FOLLOW_UP") {
        $('#text' + id).datepicker({
            inline: true, 
            dateFormat: 'dd/mm/yy', 
            showButtonPanel: true
        });
    } else {
        $('#text' + id).datepicker("destroy");
    }
}

// Reset form
function reset_form() {
    $('#text1').val('');
    $('#text2').val('');
    $('#text3').val('');
    $('#field1').val('');
    $('#field2').val('');
    $('#field3').val('');
    $('#ao2').val('');
    $('#ao3').val('');
    $('#op1').val('');
    $('#op2').val('');
    $('#op3').val('');
}
