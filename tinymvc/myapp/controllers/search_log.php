<?php

/**
 * Controller Search_log.
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [18.12.2021]
 * Controller Refactoring
 *
 * @property \TinyMVC_Load              $load
 * @property \TinyMVC_View              $view
 * @property \TinyMVC_Library_URI       $uri
 * @property \TinyMVC_Library_Session   $session
 * @property \TinyMVC_Library_Cookies   $cookies
 * @property \TinyMVC_Library_Upload    $upload
 * @property \TinyMVC_Library_validator $validator
 * @property \Translations_Model        $translations
 */
class Search_log_Controller extends TinyMVC_Controller
{
    /**
     * Controller Search_log index page.
     */
    public function index()
    {
        // Here be dragons
    }

    public function administration()
    {
        checkAdmin('moderate_content');

        $data['title'] = 'Moderate search log';

        switch ($this->uri->segment(3)) {
            case 'by_query':
                $data['pages'] = model('pages')->get_pages(array('with' => array('search_log' => true)));
                $this->view->assign($data);
                $this->view->display('admin/header_view');
                $this->view->display('admin/search_log/group_by_query_view');
                $this->view->display('admin/footer_view');
            break;

            default:
                $data['pages'] = model('pages')->get_pages(array('with' => array('search_log' => true)));
                $this->view->assign($data);
                $this->view->display('admin/header_view');
                $this->view->display('admin/search_log/index_view');
                $this->view->display('admin/footer_view');
            break;
        }
    }

    public function ajax_operation()
    {
        checkIsAjax();
        checkIsLoggedAjax();

        switch ($this->uri->segment(3)) {
            case 'administration_list_dt':
                checkAdminAjaxDT('moderate_content');

                $conditions = array();

                if (isset($_POST['page'])) {
                    $conditions['page'] = (int) $_POST['page'];
                }
                if (isset($_POST['date_to']) && validateDate($_POST['date_to'], 'm/d/Y')) {
                    $conditions['date_to'] = getDateFormat($_POST['date_to'], 'm/d/Y', 'Y-m-d');
                }
                if (isset($_POST['date_from']) && validateDate($_POST['date_from'], 'm/d/Y')) {
                    $conditions['date_from'] = getDateFormat($_POST['date_from'], 'm/d/Y', 'Y-m-d');
                }

                $conditions['limit'] = (int) $_POST['iDisplayLength'];
                $conditions['offset'] = (int) $_POST['iDisplayStart'];

                if ($_POST['iSortingCols'] > 0) {
                    for ($i = 0; $i < $_POST['iSortingCols']; ++$i) {
                        switch ($_POST['mDataProp_' . (int) $_POST['iSortCol_' . $i]]) {
                            case 'dt_date':
                                $conditions['sort_by'][] = 'date-' . $_POST['sSortDir_' . $i];

                            break;
                        }
                    }
                }

                $search_log = model('search_log');
                $records = $search_log->get_logs($conditions);
                $records_total = $search_log->count_logs($conditions);

                $output = array(
                    'sEcho'                => (int) $_POST['sEcho'],
                    'iTotalRecords'        => $records_total,
                    'iTotalDisplayRecords' => $records_total,
                    'aaData'               => array(),
                );

                if (empty($records)) {
                    jsonResponse('', 'success', $output);
                }

                foreach ($records as $record) {
                    $actions = array();

                    if (empty($actions)) {
                        $actions[] = '&mdash;';
                    }

                    $output['aaData'][] = array(
                        'dt_query'       => $record['query'],
                        'dt_page'        => $record['page_name'],
                        'dt_count'       => array(),
                        'dt_date'        => getDateFormat($record['date'], 'Y-m-d H:i:s'),
                        'dt_actions'     => implode(' ', $actions),
                    );
                }

                jsonResponse('', 'success', $output);

            break;
            case 'administration_search_log_group_by_query':
                checkAdminAjaxDT('moderate_content');

                $conditions = array();
                $conditions['limit'] = (int) $_POST['iDisplayLength'];
                $conditions['offset'] = (int) $_POST['iDisplayStart'];

                if (isset($_POST['page'])) {
                    $conditions['page'] = (int) $_POST['page'];
                }

                if ($_POST['iSortingCols'] > 0) {
                    for ($i = 0; $i < $_POST['iSortingCols']; ++$i) {
                        switch ($_POST['mDataProp_' . (int) $_POST['iSortCol_' . $i]]) {
                            case 'dt_count':
                                $conditions['sort_by'][] = 'count-' . $_POST['sSortDir_' . $i];

                            break;
                        }
                    }
                }

                $search_log = model('search_log');
                $records = $search_log->get_logs_group_by_query($conditions);
                $records_total = $search_log->count_logs_group_by_query($conditions);

                $output = array(
                    'sEcho'                => (int) $_POST['sEcho'],
                    'iTotalRecords'        => $records_total,
                    'iTotalDisplayRecords' => $records_total,
                    'aaData'               => array(),
                );

                if (empty($records)) {
                    jsonResponse('', 'success', $output);
                }

                foreach ($records as $record) {
                    $output['aaData'][] = array(
                        'dt_query'       => $record['query'],
                        'dt_page'        => $record['page_name'],
                        'dt_count'       => $record['count'],
                    );
                }

                jsonResponse('', 'success', $output);
            break;
        }
    }
}

// End of file search_log.php
// Location: /tinymvc/myapp/controllers/search_log.php
