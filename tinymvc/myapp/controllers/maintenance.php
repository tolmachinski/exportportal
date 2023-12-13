<?php

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [14.12.2021]
 * Controller Refactoring
 */
class Maintenance_Controller extends TinyMVC_Controller
{
    public function check_maintenance()
    {
        $mode = new DateTime('now', new DateTimeZone('UTC')) < new DateTime(config('env.MAINTENANCE_END'), new DateTimeZone('UTC')) ? config('env.MAINTENANCE_MODE', 'off') : 'off';
        json(array(
            'mode'       => $mode,
            'reload'     => isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '89.28.49.94' || !isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['REMOTE_ADDR'] != '89.28.49.94',
            'is_started' => isExpiredDate(DateTime::createFromFormat(DATE_ATOM, config('env.MAINTENANCE_START'), new DateTimeZone('UTC')) ?: null) && 'on' === $mode,
            'is_active'  => 'on' === config('env.MAINTENANCE_MODE', 'off')
        ));
    }

    public function show_maintenance_banner()
    {
        //header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        json(array(
            'html' => $this->view->fetch(
                'new/maintenance/countdown_view',
                array('time_maintenance_start' => DateTime::createFromFormat(DATE_ATOM, config('env.MAINTENANCE_START'), new DateTimeZone('UTC')) ?: null)
            ),
        ));
    }

    public function access_dev()
    {
        $hash = uri()->segment(3);

        if(empty($hash)){
            exit('No hash provided');
        }

        if((string) $hash === config('env.DEV_ACCESS_HASH')){
            cookies()->setCookieParam('ep_dev_access', config('env.DEV_ACCESS_HASH'), 60*60*24*365*10);
        }

        headerRedirect();
    }
}
