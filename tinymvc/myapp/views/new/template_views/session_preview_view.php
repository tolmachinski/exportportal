<?php
    if(DEBUG_MODE && filter_var(config('env.DISPLAY_SESSION_IN_HEAD'), FILTER_VALIDATE_BOOLEAN)){
        echo "<!--<pre> session - ";print_r(session()->getAll()); echo 'cookies - ';print_r($_COOKIE);echo "</pre>-->";
        echo "<!--<pre>";
        !empty($errors) && print_r($errors);
        echo "</pre>-->";
    }
