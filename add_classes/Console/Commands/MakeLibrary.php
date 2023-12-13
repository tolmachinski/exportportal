<?php

namespace App\Console\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\String\UnicodeString;

use function Symfony\Component\String\u;

class MakeLibrary extends MakeFileCommand
{
    protected $name = 'make:library';

    protected $description = 'The command that allows to create library';

    protected $argumentsList = [
        ['name', InputArgument::REQUIRED, 'The library name'],
    ];

    protected $optionsList = [
        ['extends', 'E', InputOption::VALUE_OPTIONAL, 'The base library class to extend from'],
    ];

    /**
     * The output path.
     *
     * @var string
     */
    protected $path = 'tinymvc/myapp/plugins';

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
    protected $template = <<<'LIBRARY'
    <?php

    declare(strict_types=1);

    use Symfony\Component\DependencyInjection\ContainerInterface;

    /**
     * Library [[:NAME-ORIGINAL:]]
     *
     */
    class [[:NAME-PREPARED:]] [[:EXTENDS:]]
    {
        /**
         * The service container.
         */
        protected ContainerInterface $container;

        /**
         * Library [[:NAME-ORIGINAL:]] constructor
         *
         * @param ContainerInterface $container The service container.
         */
        public function __construct(ContainerInterface $container)
        {
            $this->container = $container;

            // HIC SVNT DRACONES
        }
    }

    // End of file [[:FILENAME:]]
    // Location: [[:RELPATH:]]/[[:FILENAME:]]

    LIBRARY;

    /**
     * Flag that indicates which name to use - original or prepared.
     *
     * @var bool
     */
    protected $preferOriginalName = false;

    /**
     * The base class to extend from.
     *
     * @var string
     */
    private $baseLibrary = '';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $extends = $this->option('extends');
        if (!empty($extends)) {
            $this->baseLibrary = trim($this->option('extends'));
        }

        return parent::handle();
    }

    /**
     * Get the base class to extend from.
     *
     * @return string
     */
    public function getBaseLibrary()
    {
        return $this->baseLibrary;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreparedName()
    {
        return "TinyMVC_Library_{$this->getOriginalName()}";
    }

    /**
     * {@inheritdoc}
     */
    protected function resolveName()
    {
        parent::resolveName();

        $this->setOriginalName(
            (string) u(\implode('_', \array_map(fn (UnicodeString $str) => $str->title(), u($this->getOriginalName())->snake()->split('_'))))
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeName($name)
    {
        $normalized = str_replace('tiny_m_v_c_', 'tinymvc_', parent::normalizeName($name));

        $this->info("Normalized file name: \"{$name}\" became \"{$normalized}\"", 'debug');

        return $normalized;
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

        $this->line("The library \"{$this->getOriginalName()}\" is successfully created under the name \"{$this->getPreparedName()}\"");
    }

    /**
     * {@inheritdoc}
     */
    protected function makeContents()
    {
        $baseLibrary = $this->getBaseLibrary();

        return str_replace(
            [
                '[[:EXTENDS:]]',
            ],
            [
                !empty($baseLibrary) ? "extends {$baseLibrary}" : '',
            ],
            parent::makeContents()
        );
    }
}
