$(function(){
    checkMaintenanceMode();
});

function checkMaintenanceMode(){
    if (window.Worker) {
        var mmWorker = new Worker(__current_sub_domain_url + "public/plug/js/maintenance-mode/maintenance_worker.js");

        mmWorker.postMessage({url:__site_url, messtype: 'init'});
        mmWorker.onmessage = function(e) {
            if (e.data.mode === 'on') {
                if (e.data.is_started === false) {
                    if ($('#js-maintenance-banner').length === 0) {
                        showMaintenanceBanner();
                    }
                    mmWorker.postMessage({messtype: 'close'});
                } else if (e.data.is_started === true && e.data.reload === true) {
                    location.reload(true);
                }
            }
        }
    }
}

var showMaintenanceBanner = function(){
    $.ajax({
        url: 'maintenance/show_maintenance_banner',
        type: 'POST',
        dataType: 'JSON',
        success: function (resp) {
            $("#js-maintenance-banner-container").html(resp.html).addClass("animate");
            $(".ep-header").addClass("ep-header--maintenance");
            $("html").addClass("html--maintenance");
        }
    });
}
