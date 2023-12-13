<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class MakeController extends MakeFileCommand
{
    protected $name = 'make:controller';

    protected $description = 'The command that allows to create controller';

    protected $argumentsList = array(
        array('name', InputArgument::REQUIRED, 'The controller name'),
    );

    protected $optionsList = array(
        array('extends', 'E', InputOption::VALUE_OPTIONAL, 'The base controller class to extend from'),
    );

    /**
     * The output path.
     *
     * @var string
     */
    protected $path = 'tinymvc/myapp/controllers';

    /**
     * The file extension.
     *
     * @var string
     */
    protected $extension = 'php';

    /**
     * The template of the file.
     *
     * @var string
     */
    protected $template = <<<'CONTROLLER'
        <?php

        declare(strict_types=1);

        /**
         * Controller [[:NAME-ORIGINAL:]]
         */
        class [[:NAME-ORIGINAL:]]_Controller [[:EXTENDS:]]
        {
            /**
             * Index page
             */
            public function index(): void
            {
                // Here be dragons
            }
        }

        // End of file [[:FILENAME:]]
        // Location: [[:RELPATH:]]/[[:FILENAME:]]

        CONTROLLER;

    /**
     * The base controller class to extend from.
     *
     * @var string
     */
    private $baseController = 'TinyMVC_Controller';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $extends = $this->option('extends');
        if (!empty($extends)) {
            $this->baseController = trim($this->option('extends'));
        }

        return parent::handle();
    }

    /**
     * Get the base controller class to extend from.
     *
     * @return string
     */
    public function getBaseController()
    {
        return $this->baseController;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreparedName()
    {
        return "{$this->getOriginalName()}_Controller";
    }

    /**
     * Write generated file to the specific path.
     *
     * @param string $content
     * @param string $path
     *
     * @throws \RuntimeException on the write fail
     */
    protected function writeFile($content, $path)
    {
        parent::writeFile($content, $path);

        $this->line("The controller \"{$this->getOriginalName()}\" is successfully created under the name \"{$this->getPreparedName()}\"");
    }

    /**
     * {@inheritdoc}
     */
    protected function makeContents()
    {
        $baseController = $this->getBaseController();

        return str_replace(
            array(
                '[[:EXTENDS:]]',
            ),
            array(
                !empty($baseController) ? "extends {$baseController}" : '',
            ),
            parent::makeContents()
        );
    }
}
