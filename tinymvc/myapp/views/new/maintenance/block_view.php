<?php
    $checkMaintenance = config('env.MAINTENANCE_MODE') === 'on' && validateDate(config('env.MAINTENANCE_START'), DATE_ATOM) &&
    !isExpiredDate(DateTime::createFromFormat(DATE_ATOM, config('env.MAINTENANCE_START'), new DateTimeZone('UTC')));
?>

<?php
    if ($checkMaintenance) {
?>
    <div id="js-maintenance-banner-container" class="maintenance-banner-container <?php echo $checkMaintenance ? "animate" : ""; ?>">
        <?php views()->display(
            'new/maintenance/countdown_view',
            [
                'time_maintenance_start' => DateTime::createFromFormat(DATE_ATOM, config('env.MAINTENANCE_START'), new DateTimeZone('UTC')),
            ]
        ); ?>
    </div>
<?php } ?>
