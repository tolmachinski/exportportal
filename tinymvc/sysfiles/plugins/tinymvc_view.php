<?php

/*
 * Name:       TinyMVC
 * About:      An MVC application framework for PHP
 * Copyright:  (C) 2007-2008 Monte Ohrt, All rights reserved.
 * Author:     Monte Ohrt, monte [at] ohrt [dot] com
 * License:    LGPL, see included license file
 */

// ------------------------------------------------------------------------

use App\Seo\SeoPageService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * TinyMVC_View.
 *
 * @author		Monte Ohrt
 */
class TinyMVC_View
{
    /**
     * $view_vars.
     *
     * vars for view file assignment
     */
    public $view_vars = array();

    /**
     * The anount of displayed views.
     *
     * @var int
     */
    private $displayedViews = 0;

    /**
     * The flag that indicates if fetch mode is enabled.
     *
     * @var bool
     */
    private $fetchMode = false;
    /**
     *
     * Seo page URL service.
     */
    protected SeoPageService $seoPageService;

    public function __construct(ContainerInterface $container)
    {
        $this->seoPageService = $container->get(SeoPageService::class);
    }

    /**
     * Determine if view files were displayed.
     */
    public function hasDisplayedViews(): bool
    {
        return $this->displayedViews > 0;
    }

    /**
     * assign.
     *
     * assign view variables
     *
     * @param mixed $key   key of assignment, or value to assign
     * @param mixed $value value of assignment
     */
    public function assign($key, $value = null)
    {
        if (isset($value)) {
            $this->view_vars[$key] = $value;
        } else {
            foreach ($key as $k => $v) {
                if (is_int($k)) {
                    $this->view_vars[] = $v;
                } else {
                    $this->view_vars[$k] = $v;
                }
            }
        }
    }

    /**
     * display.
     *
     * display a view file
     *
     * @param string     $filename       the name of the view file
     * @param mixed      $_tmvc_filename
     * @param null|mixed $view_vars
     *
     * @return bool
     */
    public function display_template($view_vars = null)
    {
        $path = TMVC_MYAPPDIR . 'views' . DS . \App\Common\THEME_MAP . DS . 'template_views' . DS;

        try {
            $this->assign($view_vars);
            return $this->loadView($path . "index_view.php", $view_vars);
        } finally {
            if (!$this->fetchMode) {
                ++$this->displayedViews;
            }
        }
    }
    /**
     * display.
     *
     * display a view file
     *
     * @param string     $filename       the name of the view file
     * @param null|mixed $view_vars
     *
     * @return bool
     */
    public function displayWebpackTemplate($viewVars = null)
    {
        $path = TMVC_MYAPPDIR . 'views' . DS . \App\Common\THEME_MAP . DS . 'template' . DS;

        try {
            $this->assign($viewVars);
            return $this->loadView($path . 'index_view.php', $viewVars);
        } finally {
            if (!$this->fetchMode) {
                ++$this->displayedViews;
            }
        }
    }

    /**
     * display.
     *
     * display a view file
     *
     * @param string     $filename       the name of the view file
     * @param mixed      $_tmvc_filename
     * @param null|mixed $view_vars
     *
     * @return bool
     */
    public function display($_tmvc_filename, $view_vars = null)
    {
        try {
            return $this->loadView(TMVC_MYAPPDIR . 'views' . DS . "{$_tmvc_filename}.php", $view_vars);
        } finally {
            if (!$this->fetchMode) {
                ++$this->displayedViews;
            }
        }
    }

    /**
     * fetch.
     *
     * return the contents of a view file
     *
     * @param string     $filename
     * @param null|mixed $view_vars
     *
     * @return string contents of view
     */
    public function fetch($filename, $view_vars = null)
    {
        try {
            ob_start();
            $this->fetchMode = true;
            $this->display($filename, $view_vars);
            $results = ob_get_contents();
            ob_end_clean();

            return $results;
        } finally {
            $this->fetchMode = false;
        }
    }

    /**
     * sysview.
     *
     * internal: view a system file
     *
     * @param string     $filename
     * @param null|mixed $view_vars
     *
     * @return bool
     */
    public function sysview($filename, $view_vars = null)
    {
        try {
            return $this->loadView(TMVC_BASEDIR . 'sysfiles' . DS . 'views' . DS . "{$filename}.php", $view_vars);
        } finally {
            if (!$this->fetchMode) {
                ++$this->displayedViews;
            }
        }
    }

    /**
     * _view.
     *
     * internal: display a view file
     *
     * @param string $_tmvc_filepath
     * @param array  $view_vars
     */
    private function loadView($_tmvc_filepath, $view_vars = null)
    {
        if (!file_exists($_tmvc_filepath)) {
            throw new Exception("Unknown file '{$_tmvc_filepath}'");
        }
        // bring view vars into view scope
        extract($this->view_vars);
        if (isset($view_vars)) {
            extract($view_vars);
        }

        include $_tmvc_filepath;
    }
}
