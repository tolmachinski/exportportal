var url;
var checkMaintenanceInterval = null;
var checkMaintenanceIntervalTimer = 10 * 1000;
// is_active

var checkMaintenance = function(){
    var xhr;

    xhr = new XMLHttpRequest();
    xhr.withCredentials = true;
    xhr.open("POST", url + 'maintenance/check_maintenance');
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.setRequestHeader("Accept", "application/json");
    xhr.setRequestHeader("X-Maintenance-Enabled", 'yes');
    xhr.onload = function() {
        try {
            var response = JSON.parse(this.response);
            postMessage(response);

            if (response.is_active === false) {
                checkMaintenanceIntervalTimer = 86400 * 1000;
            } else{
                checkMaintenanceIntervalTimer = 10 * 1000;
            }

            clearInterval(checkMaintenanceInterval);
            checkMaintenanceInterval = setInterval(checkMaintenance, checkMaintenanceIntervalTimer);
        } catch (e) {
            return false;
        }
    }

    xhr.onerror = function() {
        console.log('ERROR');
    };

    xhr.send();
}

onmessage = function(e) {
    if(undefined !== e.data.messtype){
        switch (e.data.messtype) {
            case 'init':
                url = e.data.url;

                checkMaintenanceInterval = setInterval(checkMaintenance, checkMaintenanceIntervalTimer);
            break;
            case 'close':
                close();
            break;
        }
    }
}
