
// *** BEWARE *** Used For Enquiry Update Only!

$(function() {

//alert('into the js');

    $("#check").button();
    $("#header").tabs({
        //event: "mouseover"
    });


    $("#tab").tabs({
        //event: "mouseover"
    });

    $('#tabs2').tabs({
        //event: "mouseover"
    });

    $("input:submit").button();
    $("input:reset").button();

    $("#paging, #paging2").buttonset();

    $("input:submit, #newlog").button();

    $(".resultset tr:even").addClass("alt");

    // Datepickers
    $('#FOLLOW_UP,#DATE_GIVEN1,#DATE_GIVEN2,#DATE_GIVEN3,#DATE_GIVEN4,#DATE_GIVEN5').datepicker({
        inline: true,
        dateFormat: 'dd M yy',
        showButtonPanel: true,
        minDate: '-0d',
        showAnim: 'slideDown'
    });

    $('#CHASED_DATE').datepicker({
        inline: true,
        dateFormat: 'dd M yy',
        showButtonPanel: true
    });


    //Tab headings
    if ($('#log_no').val() > 0) {
        $('#tabs2 li *:first').text('Activity Log - '.concat($('#log_no').val()));
        $('#tabs2 li *:first').css({color: "#01A9DB"});
    }

    if ($('#crs_no').val() > 0) {
        $('#tabs2 li:eq(1) *:last').text('Courses - '.concat($('#crs_no').val()));
        $('#tabs2 li:eq(1) *:last').css({color: "#01A9DB"});
    }

    if ($('#stu_no').val() > 0) {
        $('#tabs2 li:eq(2) *:last').text('Students - '.concat($('#stu_no').val()));
        $('#tabs2 li:eq(2) *:last').css({color: "#01A9DB"});
    }

    if ($('#soc_no').val() > 0) {
        $('#tabs2 li:eq(3) *:last').text('Students On Courses - '.concat($('#soc_no').val()));
        $('#tabs2 li:eq(3) *:last').css({color: "#01A9DB"});
    }

    if ($('#enr_no').val() > 0) {
        $('#tabs2 li:eq(4) *:last').text('Enquirers - '.concat($('#enr_no').val()));
        $('#tabs2 li:eq(4) *:last').css({color: "#01A9DB"});
    }

    if ($('#pay_no').val() > 0) {
        $('#tabs2 li *:last').text('Payment/Expenses - '.concat($('#pay_no').val()));
        $('#tabs2 li *:last').css({color: "#01A9DB"});
    }


    // Spoilt Popup			
    $('#popup_spoilt').dialog({
        modal: true,
        autoOpen: false,
        resizable: false,
        buttons:
                {
                    "Ok": function() {
                        $(this).dialog("close");
                    }
                }
    });

});

function close_tab() {
    var win = window.open("", "_self");
    win.close();
}
