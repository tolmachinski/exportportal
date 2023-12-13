//function to update the days based on the current values of month and year
function updateNumberOfDays() {
    var month = $('#js-months-value').val();
    var year = $('#js-years-value').val();
    var days = daysInMonth(month, year);
    var lastDay = $('#js-days-value').val() || 0;

    if ($('#js-days-value').val() && $('#js-days-value').val() < 28) {
        return;
    }

    $('#js-days-value').html('<option>Day</option>');

    for (var i = 1; i <= days; i++) {
        if (lastDay === i) {
            $('#js-days-value').append($('<option />').val(i).html(i).attr("selected", "selected"));
        } else {
            $('#js-days-value').append($('<option />').val(i).html(i));
        }
    }
}

//helper function
function daysInMonth(month, year) {
    return new Date(year, month, 0).getDate();
}

function ageVerification(form) {
    $.ajax({
        type: 'POST',
        url: 'categories/ajax_category_group_operation/check_age',
        data: form.serialize(),
        dataType: 'JSON',
        beforeSend: function() {
            showLoader(form);
            $(".js-submit-form").addClass('disabled');
        },
        success: function(resp) {
            if (resp.mess_type == 'success') {
                if ($(".js-submit-form").data("redirect")) {
                    window.location.href = $(".js-submit-form").data("redirect");
                } else {
                    location.reload();
                }
            } else if (!resp.date){
                systemMessages( resp.message, resp.mess_type );
            } else {
                $(".js-show-warning").css("display", "block")
            }
        },
        complete: function() {
            hideLoader(form);
            $(".js-submit-form").removeClass('disabled');
        },
    });
}

//"listen" for change events
$('#js-years-value, #js-months-value').on("change", function() {
    updateNumberOfDays();
});
