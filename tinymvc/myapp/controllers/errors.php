<?php
/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Errors_Controller extends TinyMVC_Controller
{
    public function p404()
    {
        if (!isAjaxRequest()) {
            return show_404();
        }

        json(array('message' => 'Not Found', 'code' => 404, 'status' => 'error'), 404);
    }

    public function p403()
    {
        if (!isAjaxRequest()) {
            return show_403();
        }

        json(array('message' => 'Forbidden', 'code' => 403, 'status' => 'error'), 403);
    }
}
