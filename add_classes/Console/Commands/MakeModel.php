<?php

namespace App\Console\Commands;

use App\Common\Database\Model;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\String\UnicodeString;

use function Symfony\Component\String\u;

class MakeModel extends MakeFileCommand
{
    protected $name = 'make:model';

    protected $description = 'The command that allows to create model';

    protected $argumentsList = [
        ['name', InputArgument::REQUIRED, 'The model name'],
    ];

    protected $optionsList = [
        ['table', null, InputOption::VALUE_OPTIONAL, 'The name of the table.'],
        ['alias', null, InputOption::VALUE_OPTIONAL, 'The alias of the table.'],
        ['primary-key', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'One or more primary keys.'],
        ['is-composite-primary', null, InputOption::VALUE_NONE, 'Defines if primary key is simple or composite.'],
    ];

    /**
     * The output path.
     *
     * @var string
     */
    protected $path = 'tinymvc/myapp/models';

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
    protected $template = <<<'MODEL'
        <?php

        declare(strict_types=1);

        use [[:BASE-MODEL:]];

        /**
         * [[:NAME-ORIGINAL:]] model
         */
        final class [[:NAME-PREPARED:]] extends [[:EXTENDS:]]
        {
        [[:CONTENT:]]
        }

        /* End of file [[:FILENAME:]] */
        /* Location: [[:RELPATH:]]/[[:FILENAME:]] */

        MODEL;

    /**
     * Flag that indicates which name to use - original or prepared.
     *
     * @var bool
     */
    protected $preferOriginalName = false;

    /**
     * Indicates if complex primary key is used.
     *
     * @var bool
     */
    private $usesComplexPrimaryKey = false;

    /**
     * {@inheritdoc}
     */
    public function getPreparedName()
    {
        return "{$this->getOriginalName()}_Model";
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
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        // Look until we have a class name
        while (null === $input->getArgument('name') ?: null) {
            $this->ensureHasArgument($input, 'name', 'Enter the model class name');
        }

        // Loop until we have a table name
        while (null === $input->getOption('table') ?: null) {
            $this->ensureHasOption($input, 'table', 'Enter the table name');
        }

        if ($this->usesComplexPrimaryKey = $input->getOption('is-composite-primary')) {
            $this->ensureHasOptionFromList(
                $input,
                'primary-key',
                'Enter the first part of composite primary key or press ENTER to continue execution',
                'Enter another part of composite primary key or press ENTER to continue execution'
            );
        } else {
            $this->ensureHasOption($input, 'primary-key', 'Enter the primary key', 'id');
        }

        if (null !== ($input->getOption('table') ?: null)) {
            $this->ensureHasOption($input, 'alias', 'Enter the table alias', u($input->getOption('table') ?? '')->snake()->upper());
        }
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

        $this->line("The model \"{$this->getOriginalName()}\" is successfully created under the name \"{$this->getPreparedName()}\"");
    }

    /**
     * {@inheritdoc}
     */
    protected function makeContents()
    {
        $baseModel = Model::class;
        $tableName = null !== $this->option('table') ? " = \"{$this->option('table')}\"" : '';
        $tableAlias = null !== $this->option('alias') ? " = \"{$this->option('alias')}\"" : '';
        $primaryKey = '';
        $primaryKeyParts = (array) $this->option('primary-key');
        if (!empty($primaryKeyParts) && !empty(array_filter($primaryKeyParts))) {
            if ($this->usesComplexPrimaryKey) {
                $primaryKey = 'array("' . implode('", "', $primaryKeyParts) . '")';
            } else {
                $primaryKey = '"' . array_shift($primaryKeyParts) . '"';
            }

            $primaryKey = " = {$primaryKey}";
        }
        $content = <<<CONTENT
            /**
             * {@inheritdoc}
             */
            protected string \$table{$tableName};

            /**
             * {@inheritdoc}
             */
            protected string \$alias{$tableAlias};

            /**
             * {@inheritdoc}
             */
            protected \$primaryKey{$primaryKey};
        CONTENT;

        return str_replace(
            ['[[:BASE-MODEL:]]', '[[:EXTENDS:]]', '[[:CONTENT:]]'],
            [$baseModel, basename(str_replace('\\', '/', $baseModel)), $content],
            parent::makeContents()
        );
    }
}
