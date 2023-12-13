<?php

$http_s = (isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']) ? 'https://' : 'http://';
define('__HTTP_S', $http_s);

$server_http_host_origin = $_SERVER['HTTP_HOST'];
$server_http_host_origin = explode('.', $server_http_host_origin);

$default_site_url = array_slice($server_http_host_origin, -2);
$default_site_url = implode('.', $default_site_url);

define('__HTTP_HOST_ORIGIN', $default_site_url);

define('__SITE_URL', __HTTP_S . (empty($_ENV['WWW_SUBDOMAIN']) ? '' : trim($_ENV['WWW_SUBDOMAIN'], '.')  . '.') . __HTTP_HOST_ORIGIN . '/');

function fileModificationTime($relpath, $base = __SITE_URL){
    if(file_exists($relpath)) {
        clearstatcache(true, $relpath);
        $version = md5(filemtime($relpath));

        return "{$base}{$relpath}?{$version}";
    }

    return "{$base}{$relpath}";
}

http_response_code(503);
if(!empty($_SERVER['HTTP_X_MAINTENANCE_ENABLED']) && strtolower($_SERVER['HTTP_X_MAINTENANCE_ENABLED']) == 'yes')
{
    exit(json_encode(
        array(
            'mode' => 'on',
            'is_started' => true
        )
    ));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, shrink-to-fit=no">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" type="image/png" href="<?php echo __SITE_URL;?>public/img/favicon/favicon-32x32.png" sizes="32x32">
    <link rel="icon" type="image/png" href="<?php echo __SITE_URL;?>public/img/favicon/favicon-16x16.png" sizes="16x16">
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/maintenance_style.min.css');?>" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php // echo asset("public/build/styles_user_pages_general.css");?>" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php echo fileModificationTime('public/css/styles_new.css');?>" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php // echo asset("public/build/styles_user_pages.css");?>" />

    <meta name="description" content="We’re in the process of updating Export Portal with new and exciting features to make your experience even better. " />
    <title>Updating the website</title>
</head>
<body>
    <div class="maintenance-wr">
        <div class="maintenance-content">
            <div class="maintenance-center-block">
                <img class="maintenance-logo" src="<?php echo __SITE_URL;?>public/img/ep-logo/ep-logo.png" alt="ExportPortal">

                <h1 class="maintenance-title">Updates in Progress</h1>
                <p class="maintenance-description">
                    We are busy updating our website with lots of cool new features.<br>
                    The platform should be up and running soon – we apologize for the inconvenience.<br>
                </p>

                <div class="maintenance-mode__counter" id="js-getting-started">
                    <span id="js-days-left"></span>
                    <span class="js-maintenance-mode-days">Days</span>
                    <span id="js-hours-left"></span>
                    <span>:</span>
                    <span id="js-minutes-left"></span>
                    <span>:</span>
                    <span id="js-seconds-left"></span>
                </div>

                <p class="maintenance-description pb-20">
                    Please contact our support team if you have any questions or concerns. Thank you for your patience. We hope you enjoy the updates!
                </p>
            </div>
        </div>
        <div class="maintenance-footer">
            <div class="container-center">
                <div class="maintenance-footer__list">
                    <div class="maintenance-footer__item">
                        <h3 class="maintenance-footer__ttl">Phone Number</h3>
                        <a class="maintenance-footer__txt" href="tel:18186910079">+1 (818) 691-0079</a>
                    </div>
                    <div class="maintenance-footer__item">
                        <h3 class="maintenance-footer__ttl">Toll-Free Number</h3>
                        <a class="maintenance-footer__txt" href="tel:18002890015">+1 (800) 289-0015</a>
                    </div>
                    <div class="maintenance-footer__item">
                        <h3 class="maintenance-footer__ttl">Whats App</h3>
                        <a class="maintenance-footer__txt" href="tel:18182136181">+1 (818) 213-6181</a>
                    </div>
                    <div class="maintenance-footer__item">
                        <h3 class="maintenance-footer__ttl">Our email</h3>
                        <a class="maintenance-footer__txt" href="mailto:info@exportportal.com">info@exportportal.com</a>
                    </div>

                    <div class="maintenance-socials">
                        <a class="maintenance-socials__item" href="https://www.facebook.com/ExportPortal" target="_blank"></a>
                        <a class="maintenance-socials__item maintenance-socials__item--ln" href="https://www.linkedin.com/company/export-portal-los-angeles/" target="_blank"></a>
                        <a class="maintenance-socials__item maintenance-socials__item--tw" href="https://twitter.com/exportportal" target="_blank"></a>
                        <a class="maintenance-socials__item maintenance-socials__item--pi" href="https://www.pinterest.com/exportportal" target="_blank"></a>
                        <a class="maintenance-socials__item maintenance-socials__item--in" href="https://www.instagram.com/export.portal/" target="_blank"></a>
                        <a class="maintenance-socials__item maintenance-socials__item--yt" href="https://www.youtube.com/channel/UClFAlsiSScHTiwpAoDjFWuA" target="_blank"></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-1-12-0/jquery-1.12.0.min.js');?>"></script>
    <script type="text/javascript" src="<?php echo fileModificationTime('public/plug/jquery-countdown-2-2-0/jquery.countdown.min.js');?>"></script>
    <script type="text/javascript">

        $(function() {
            var maintenance_date = new Date("<?php echo DateTime::createFromFormat(DATE_ATOM, $_ENV['MAINTENANCE_END'], new DateTimeZone('UTC'))->format(DATE_ATOM); ?>");

            start_countdown_maintenance(maintenance_date);
        });

        var start_countdown_maintenance = function(time_start){
            $("#js-getting-started").countdown(time_start, function(event) {
                var daysLeft = event.strftime('%D');
                var hoursLeft = event.strftime('%H');
                var minutesLeft = event.strftime('%M');
                var secondsLeft = event.strftime('%S');

                if (daysLeft == '01') {
                    $(".js-maintenance-mode-days").text("Day")
                } else if (daysLeft == '00') {
                    $(".js-maintenance-mode-days").addClass( "display-n");
                    $("#js-days-left").addClass( "display-n");
                }

                $("#js-days-left").html(daysLeft);
                $("#js-hours-left").html(hoursLeft);
                $("#js-minutes-left").html(minutesLeft);
                $("#js-seconds-left").html(secondsLeft);
            }).on('finish.countdown', function(){
                location.reload(true);
            });
        }

    </script>
</body>
</html>
