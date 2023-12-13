<style><?php echo getPublicStyleContent("/css/maintenance_mode.min.css") ?: getPublicStyleContent("/css/maintenance_mode.css"); ?></style>
<?php
    $date = DateTime::createFromFormat(DATE_ATOM, $_ENV['MAINTENANCE_START'], new DateTimeZone('UTC'));
    $diff = $date->diff(new DateTimeImmutable());
    $days  = $time_maintenance_start->diff(new DateTime())->format('%a');
?>
<div class="maintenance-mode" id="js-maintenance-banner">
    <div class="container-center maintenance-mode__inner">
        <div class="maintenance-mode__content">
            <svg class="maintenance-mode__icon" version="1.1" viewBox="0 0 50 50" width="22" height="22" xmlns="http://www.w3.org/2000/svg"><path d="m22.757 49.903c-7.6927-0.6831-14.763-5.0102-18.92-11.579-0.70254-1.1103-1.8864-3.5185-2.3472-4.775-1.7226-4.6969-1.9541-10.032-0.65133-15.014 0.77222-2.953 2.3221-6.1227 4.2216-8.6336 1.7536-2.3182 4.1345-4.5058 6.6056-6.0695 1.1102-0.7025 3.5185-1.8863 4.7749-2.3471 4.697-1.7227 10.032-1.9541 15.014-0.6514 2.953 0.7723 6.1227 2.3221 8.6336 4.2216 9.6919 7.3318 12.705 20.653 7.1063 31.421-1.4298 2.7503-3.0705 4.8852-5.3868 7.0091-5.1094 4.685-12.089 7.0364-19.051 6.4182zm3.5882-10.788c1.3458-0.615 2.0637-1.8897 1.9454-3.4544-0.10062-1.3304-0.74997-2.2905-1.9204-2.8395-0.47101-0.221-0.66996-0.2542-1.5193-0.2536-0.79317 5e-4 -1.0634 0.04-1.4343 0.2112-0.64431 0.2969-1.2671 0.9063-1.602 1.5678-0.2628 0.519-0.28656 0.651-0.28656 1.5921 0 0.9079 0.0297 1.0882 0.25781 1.5637 0.81176 1.6926 2.8257 2.4049 4.5594 1.6127zm-0.60646-9.4423c0.37082-0.1122 0.76526-0.5116 0.95416-0.9663 0.19649-0.4729 1.9745-16.06 1.8855-16.53-0.0903-0.4769-0.42842-0.9583-0.84479-1.2029-0.3196-0.1877-0.49679-0.2007-2.7362-0.2007-2.1103 0-2.4322 0.021-2.7125 0.1737-0.40426 0.2209-0.78052 0.7891-0.85578 1.2923-0.0648 0.4336 1.631 15.549 1.825 16.266 0.15778 0.5833 0.71748 1.1365 1.2719 1.2572 0.42843 0.093 0.66943 0.076 1.2127-0.089z" stroke-width=".097732"/></svg>
            <span class="maintenance-mode__txt">The site will be under construction <span id="js-maintenance-starte-date-client-text">today</span>. Please keep this in mind when performing time-sensitive actions.</span>
            <span class="maintenance-mode__txt-tablet">Maintenance anouncement!</span>
            <span class="maintenance-mode__txt-mobile">Maintenance!</span>
        </div>
        <div
            id="js-getting-started"
            class="maintenance-mode__timer"
        >
            <span id="js-days-left" class="<?php echo $diff->days === 0 ? "display-n" : ""; ?>"><?php echo $diff->days; ?></span>
            <span class="maintenance-mode__days <?php echo $diff->days === 0 ? "display-n" : ""; ?>">Days</span>
            <span id="js-hours-left"><?php echo $diff->format("%H") ?: "00"?></span>
            <span class="txt-gray pl-5 pr-5">:</span>
            <span id="js-minutes-left"><?php echo $diff->format("%I") ?: "00"?></span>
            <span class="txt-gray pl-5 pr-5">:</span>
            <span id="js-seconds-left"><?php echo $diff->format("%S") ?: "00"?></span>
        </div>
    </div>
</div>

<?php
    $date_maintenance = $time_maintenance_start->format(DATE_ATOM);

    echo dispatchDynamicFragmentInCompatMode(
        'maintenance-mode:boot',
        asset('public/plug/js/maintenance-mode/countdown.js', 'legacy'),
        sprintf(
            "function () {
                var script = document.createElement('script');
                script.src = 'public/plug/jquery-countdown-2-2-0/jquery.countdown.min.js';
                script.id = 'countdown';
                document.body.appendChild(script);

                script.onload = function(){
                    start_countdown_maintenance(new Date('%s'));
                }
            }",
            $date_maintenance
        ),
        array($date_maintenance),
        true
    );
?>
