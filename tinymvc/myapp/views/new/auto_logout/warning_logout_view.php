<div class="modal-flex__content autologout-modal">
    <p>Your session is about to expire due to inactivity. To stay logged in, please select “Continue Session”.</p>
    <br/>
    <p>Otherwise, you will be automatically logged out in <span id="js-countdown-time"></span>.</p>
</div>

<?php
    echo dispatchDynamicFragment(
        'warning-logout:boot',
        ['#js-countdown-time', config('env.POPUP_LOGOUT_COUNTDOWN_IN_SECONDS', 90)],
        true
    );
?>
