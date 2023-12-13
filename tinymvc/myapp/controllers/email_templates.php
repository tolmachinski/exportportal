<?php

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
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
 *
 * @author Anton Zencenco
 */
class Email_Templates_Controller extends TinyMVC_Controller
{
    const TEMPLATES_PATH = '/tinymvc/myapp/views/emails/';

    public function index()
    {
        headerRedirect('404');
    }

    public function administration()
    {
        checkAdmin('admin_site');

        $this->view->assign(array(
            'langs'     => $this->get_langs(),
            'templates' => $this->get_template_files_map(),
        ));
        $this->view->display('admin/header_view');
        $this->view->display('admin/emails_template/i18n_files_view');
        $this->view->display('admin/footer_view');
    }

    public function administration_dt()
    {
        checkAdminAjaxDT('admin_site');

        $langs = $this->get_langs();
        $templates = $this->get_template_files_map();
        $templates_count = count($templates);

        $length = (int) $_POST['iDisplayLength'];
        $offset = (int) $_POST['iDisplayStart'];
        $templates = array_slice($templates, $offset, $length);
        $output = array(
            'sEcho'                => (int) $_POST['sEcho'],
            'iTotalRecords'        => $templates_count,
            'iTotalDisplayRecords' => $templates_count,
            'aaData'               => array(),
        );
        if (empty($templates)) {
            jsonResponse(null, 'success', $output);
        }

        foreach ($templates as $type => $files) {
            $langs_output = array();
            foreach ($langs as $key) {
                if (isset($files[$key])) {
                    $status = '<i class="ep-icon ep-icon ep-icon_ok txt-green fs-24 lh-24"></i>';
                } else {
                    $status = '<i class="ep-icon ep-icon ep-icon_remove txt-red fs-24 lh-24"></i>';
                }

                $langs_output["dt_{$key}"] = $status;
            }

            $output['aaData'][] = array_merge(array('dt_template' => $type), $langs_output);
        }

        jsonResponse(null, 'success', $output);
    }

    private function get_template_files_map()
    {
        $map = array();
        foreach ($this->get_langs_iterator() as /** @var \SplFileInfo $lang_directory */ $lang_directory) {
            $lang = $lang_directory->getFilename();
            $basepath = rtrim($lang_directory->getRealPath(), '/\\');
            $directory = new \RecursiveDirectoryIterator($basepath, \FilesystemIterator::FOLLOW_SYMLINKS);
            $iterator = new \RecursiveIteratorIterator($directory, \RecursiveIteratorIterator::SELF_FIRST);
            foreach ($iterator as /** @var \SplFileInfo $types_directory */ $types_directory) {
                if ($types_directory->isFile()) {
                    $path = $types_directory->getRealPath();
                    $type = str_replace(array('_view', ".{$types_directory->getExtension()}", $basepath . DS), '', $path);
                    $type = str_replace('\\', '/', $type);
                    $map[$type][$lang] = $path;
                }
            }
        }

        return $map;
    }

    private function get_langs()
    {
        $langs = array();
        foreach ($this->get_langs_iterator() as /** @var \SplFileInfo $lang_directory */ $lang_directory) {
            $langs[] = $lang_directory->getFilename();
        }
        sort($langs);

        return $langs;
    }

    private function get_langs_iterator()
    {
        $root_path = str_replace(array(DS, '\\'), '/', $_SERVER['DOCUMENT_ROOT']);
        $directory_path = $root_path . self::TEMPLATES_PATH;
        $directory = new DirectoryIterator($directory_path);
        foreach ($directory as /** @var \DirectoryIterator $lang_directory */ $lang_directory) {
            if ($lang_directory->isDot() || $lang_directory->isFile() || strlen($lang_directory->getFilename() > 2)) {
                continue;
            }

            yield $lang_directory->getFileInfo();
        }
    }
}
