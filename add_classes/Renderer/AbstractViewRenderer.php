<?php

declare(strict_types=1);

namespace App\Renderer;

use TinyMVC_View as Renderer;

/**
 * The abstract view renderer class.
 *
 * @author Anton Zencenco
 */
abstract class AbstractViewRenderer
{
    /**
     * The page renderer.
     */
    private Renderer $renderer;

    /**
     * @param Renderer $renderer the page renderer
     */
    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Render arbitrary template.
     *
     * @param string $template the view template
     * @param array  $viewVars the view vars
     */
    protected function render(string $template, array $viewVars): void
    {
        $this->renderer->assign($viewVars);
        $this->renderer->display($template);
    }

    /**
     * Rendere page.
     *
     * @param string $template the page content view template
     * @param array  $viewVars the view vars
     */
    protected function renderPage(string $template, array $viewVars = [])
    {
        $viewVars = \array_replace_recursive($viewVars, [
            'templateViews'    => ['mainOutContent' => $template],
        ]);

        $this->renderer->assign($viewVars);
        $this->renderer->display('new/template_views/index_view');
    }

    /**
     * Rendere EPL page.
     *
     * @param string $template the page content view template
     * @param array  $viewVars the view vars
     */
    protected function renderEplPage(string $template, array $viewVars = [])
    {
        $viewVars = \array_replace_recursive($viewVars, [
            'templateViews'    => ['mainOutContent' => $template],
        ]);

        $this->renderer->assign($viewVars);
        $this->renderer->display('new/epl/template/index_view');
    }

    /**
     * Rendere admin page.
     *
     * @param string $template the page content view template
     * @param array  $viewVars the view vars
     */
    protected function renderAdminPage(string $template, array $viewVars = [])
    {
        $this->renderer->assign($viewVars);
        foreach (['admin/header_view', $template, 'admin/footer_view'] as $path) {
            $this->renderer->display($path);
        }
    }

    /**
     * Render legacy page.
     *
     * @param string $template the page content view template
     * @param array  $viewVars the view vars
     */
    protected function renderLegacyPage(string $template, array $viewVars = [])
    {
        $this->renderer->assign($viewVars);
        foreach (['new/header_view', $template, 'new/footer_view'] as $path) {
            $this->renderer->display($path);
        }
    }
}
