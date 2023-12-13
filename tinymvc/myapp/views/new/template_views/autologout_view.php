<?php
    if (logged_in() && session()->__get('not_auto_logout_date') !== date('Y-m-d')) {
        echo dispatchDynamicFragment('idle-autologout-worker:boot', [config('env.AUTO_LOGOUT_USER_TIMEOUT_MINUTES', 30)], true);
    } elseif (!empty(request()->query->get('reason'))) {
        echo dispatchDynamicFragment('autologout:show-reason', [], true);
    }
?>
