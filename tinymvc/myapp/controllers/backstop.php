<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Controller Backstop
 */
class Backstop_Controller extends TinyMVC_Controller
{
    private bool $isBackstopEnabled;

    /**
     * {@inheritDoc}
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->isBackstopEnabled = filter_var($this->getContainer()->getParameter('kernel.env.BACKSTOP_TEST_MODE'), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Index page
     */
    public function index(): void
    {
        // Errors
        if (!$this->isBackstopEnabled) {
            show_404();
        }

        views()->display('new/backstop/index');
    }

    public function ajax_generate_test(): void
    {
        if (!$this->isBackstopEnabled) {
            show_404();
        }

        $data = request()->request->all();
        $arguments = "media=[{$data['media']}] pages=[{$data['pages']}] asyncCaptureLimit={$data['asyncCaptureLimit']} asyncCompareLimit={$data['asyncCompareLimit']} debugMode={$data['debugMode']} report=[true]";
        $command = shell_exec("cd tests/front_end/backstop/ && node generator.js {$arguments}");
        jsonResponse($command, "success");
    }
}

// End of file backstop.php
// Location: /tinymvc/myapp/controllers/backstop.php
