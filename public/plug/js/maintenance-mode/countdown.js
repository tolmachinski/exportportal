var start_countdown_maintenance = function(time_start){
    var sdate = new Date(time_start);
    var cdate = new Date();
    var ddiff = Math.floor((Date.UTC(sdate.getFullYear(), sdate.getMonth(), sdate.getDate()) - Date.UTC(cdate.getFullYear(), cdate.getMonth(), cdate.getDate()) ) /(1000 * 60 * 60 * 24));

    if(intval(ddiff) !== 0){
        $('#js-maintenance-starte-date-client-text').text("on " + sdate.toLocaleString('en-US', { weekday: 'long', month: 'long', day: 'numeric' }));
    }

    $("#js-getting-started")
        .countdown(time_start, function(event) {
            var daysLeft = event.strftime('%D');
            var hoursLeft = event.strftime('%H');
            var minutesLeft = event.strftime('%M');
            var secondsLeft = event.strftime('%S');

            if (daysLeft == '01') {
                $(".maintenance-mode__days").text("Day")
            }   else if (daysLeft == '00') {
                $(".maintenance-mode__days").addClass( "display-n");
                $("#js-days-left").addClass( "display-n");
            }

            $("#js-days-left").html(daysLeft);
            $("#js-hours-left").html(hoursLeft);
            $("#js-minutes-left").html(minutesLeft);
            $("#js-seconds-left").html(secondsLeft);
        }
    ).on('finish.countdown', function(){
        location.reload(true);
    });
}

