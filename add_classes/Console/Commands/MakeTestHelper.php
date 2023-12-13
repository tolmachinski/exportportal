<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Symfony\Component\Console\Input\InputArgument;

class MakeTestHelper extends MakeFileCommand
{
    protected $name = 'make:test:helper';

    protected $description = 'The command that allows to create a test file for a helper method';

    protected $argumentsList = [
        ['name', InputArgument::REQUIRED, 'The name of the helper method to create test for'],
    ];

    /**
     * The output path.
     *
     * @var string
     */
    protected $path = 'tests/Helpers';

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
    protected $template = <<<'TEST'
        <?php

        declare(strict_types=1);

        namespace Tests\Helper;

        use PHPUnit\Framework\TestCase;

        /**
         * @internal
         * @covers ::[[:NAME-ORIGINAL:]]
         */
        class [[:NAME-PREPARED:]] extends TestCase
        {

        }

        // End of file [[:FILENAME:]]
        // Location: [[:RELPATH:]]/[[:FILENAME:]]

        TEST;

    /**
     * {@inheritdoc}
     */
    public function getPreparedName()
    {
        $fileName = ucfirst($this->getOriginalName());

        return "{$fileName}Test";
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

        $this->line("The test file for method \"{$this->getOriginalName()}\" is successfully created under the name \"{$this->getPreparedName()}\"");
    }

    /**
     * {@inheritdoc}
     */
    protected function normalizeName($name)
    {
        $name = $this->getPreparedName();
        $name = preg_replace('/\s+/u', '', ucwords($name));

        $this->info("Normalized file name: \"{$name}\" became \"{$name}\"", 'debug');

        return $name;
    }

    /**
     * Resolve the name argument.
     *
     * @throws \InvalidArgumentException if name of the file is not provided
     */
    protected function resolveName()
    {
        if (
            !$this->hasArgument('name')
            || empty($name = $this->argument('name'))
        ) {
            throw new \InvalidArgumentException('The name is required');
        }

        $this->setOriginalName($name);
    }

}
