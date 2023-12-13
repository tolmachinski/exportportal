
<div class="bootstrap-dialog-content-border-botttom">
    <div
        id="js-getting-started"
    >
        <span id="js-days-left"></span>
        <span id="js-hours-left"></span>
        <span id="js-minutes-left"></span>
        <span id="js-seconds-left"></span>
    </div>

    <div
        id="js-countdown-expire"
        class="countdown-expire"
    >
        <div class="countdown-expire__item">
            <span class="js-days countdown-expire__number">00</span>
            <span class="countdown-expire__txt">days</span>
        </div>
        <div class="countdown-expire__item">
            <span class="js-hours countdown-expire__number">00</span>
            <span class="countdown-expire__txt">Hours</span>
        </div>
        <div class="countdown-expire__item">
            <span class="js-min countdown-expire__number">00</span>
            <span class="countdown-expire__txt">Minutes</span>
        </div>
        <div class="countdown-expire__item">
            <span class="js-sec countdown-expire__number">00</span>
            <span class="countdown-expire__txt">Seconds</span>
        </div>
    </div>
</div>

<?php
    echo dispatchDynamicFragment(
        "popup:certification_expire_soon",
        [$paidUntilCountdown->format(DATE_ATOM)],
        true
    );
?>
