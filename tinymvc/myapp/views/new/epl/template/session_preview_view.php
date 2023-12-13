<?php
    if(DEBUG_MODE){
        echo "<!--<pre> session - ";print_r(session()->getAll()); echo 'cookies - ';print_r($_COOKIE);echo "</pre>-->";
        echo "<!--<pre>";
        !empty($errors) && print_r($errors);
        echo "</pre>-->";
    }
