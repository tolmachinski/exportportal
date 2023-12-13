<?php

namespace App\Console\Commands;

use App\Common\Console\Command\Command;
use App\Console\Traits\SingleProcessCommandTrait;

class MakeFileCommand extends Command
{
    use SingleProcessCommandTrait;

    /**
     * The output path.
     *
     * @var string
     */
    protected $path;

    /**
     * The file extension.
     *
     * @var string
     */
    protected $extension;

    /**
     * The template of the file.
     *
     * @var string
     */
    protected $template;

    /**
     * Flag that indicates which name to use - original or prepared.
     *
     * @var bool
     */
    protected $preferOriginalName = true;

    /**
     * The relative path to the file.
     *
     * @var string
     */
    private $relpath;

    /**
     * The full path to the directory.
     *
     * @var string
     */
    private $directory;

    /**
     * The full path to the file.
     *
     * @var string
     */
    private $fullpath;

    /**
     * The full file name.
     *
     * @var string
     */
    private $filename;

    /**
     * The file original (not normalized) name.
     *
     * @var string
     */
    private $originalName;

    public function handle()
    {
        try {
            $this->resolveName();
            $this->resolvePaths();
            $this->ensureFileAbsence($this->fullpath);
            $this->writeFile($this->makeContents(), $this->fullpath);
        } catch (\RuntimeException $exception) {
            $this->getApplication()->renderThrowable($exception, $this->getOutput());

            return 0;
        } catch (\InvalidArgumentException $exception) {
            $this->getApplication()->renderThrowable($exception, $this->getOutput());

            return 0;
        }
    }

    /**
     * Get the relative path to the file.
     *
     * @return string
     */
    public function getRelpath()
    {
        return $this->relpath;
    }

    /**
     * Get the full path to the file.
     *
     * @return string
     */
    public function getFullpath()
    {
        return $this->fullpath;
    }

    /**
     * Get the full file name.
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get the template of the file.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Get the full path to the directory.
     *
     * @return string
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Get the file extension.
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * Get the entity original name.
     *
     * @return string
     */
    public function getOriginalName()
    {
        return $this->originalName;
    }

    /**
     * Get the entity normalized name.
     *
     * @return string
     */
    public function getPreparedName()
    {
        return $this->getOriginalName();
    }

    /**
     * Set the entity original name.
     *
     * @param string $originalName the entity original name
     *
     * @return self
     */
    public function setOriginalName($originalName)
    {
        $this->originalName = $originalName;

        return $this;
    }

    /**
     * Set the full file name.
     *
     * @param string $filename the full file name
     *
     * @return self
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Set the full path to the file.
     *
     * @param string $fullpath the full path to the file
     *
     * @return self
     */
    public function setFullpath($fullpath)
    {
        $this->fullpath = $fullpath;

        return $this;
    }

    /**
     * Resolve the name argument.
     *
     * @throws \InvalidArgumentException if name of the file is not provided
     */
    protected function resolveName()
    {
        if (
            !$this->hasArgument('name') ||
            empty($name = $this->argument('name'))
        ) {
            throw new \InvalidArgumentException('The name is required');
        }

        $this->setOriginalName(ucfirst($name));
    }

    /**
     * Resolve paths to directory and file.
     *
     * @throws \RuntimeException if directory not found, not a directory or it is not writable
     */
    protected function resolvePaths()
    {
        $this->relpath = str_replace('\\', '/', DIRECTORY_SEPARATOR . ltrim($this->path, DIRECTORY_SEPARATOR));
        $this->directory = str_replace('\\', '/', (constant('APP_ROOT') ?? '') . $this->relpath);
        if (!file_exists($this->directory)) {
            throw new \RuntimeException("The output directory \"{$this->directory}\" is not found");
        }
        if (!is_dir($this->directory)) {
            throw new \RuntimeException("The path \"{$this->directory}\" is not a directory");
        }
        if (!is_writable($this->directory)) {
            throw new \RuntimeException('The output directory is not writable');
        }

        $name = $this->preferOriginalName ? $this->getOriginalName() : $this->getPreparedName();
        $filename = $this->normalizeName($name) . '.' . ltrim($this->getExtension(), '.');
        $this->setFilename($filename);
        $this->setFullpath(
            str_replace(
                '\\',
                '/',
                rtrim($this->getDirectory(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename
            )
        );
    }

    /**
     * Normalize the file name.
     *
     * @param string $name
     *
     * @return string
     */
    protected function normalizeName($name)
    {
        $original = $name;
        if (!ctype_lower($name)) {
            $name = preg_replace('/\s+/u', '', ucwords($name));
            $name = preg_replace('/(.)(?=[A-Z])/u', '$1_', $name);
            $name = preg_replace('/[^a-zA-Z0-9_]+/', '_', $name);
            $name = preg_replace('/[_]+/', '_', $name);
            $name = mb_strtolower($name, 'UTF-8');
        }

        $this->info("Normalized file name: \"{$original}\" became \"{$name}\"", 'debug');

        return $name;
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
        if (false === file_put_contents($path, $content)) {
            throw new \RuntimeException("Failed to write the file \"{$path}\"");
        }

        $this->info("Write file into path \"{$path}\"", 'debug');
    }

    /**
     * Creates the file contenst from template.
     */
    protected function makeContents()
    {
        return str_replace(
            array(
                '[[:FILENAME:]]',
                '[[:FULLPATH:]]',
                '[[:RELPATH:]]',
                '[[:NAME-ORIGINAL:]]',
                '[[:NAME-PREPARED:]]',
            ),
            array(
                $this->getFilename(),
                $this->getFullpath(),
                $this->getRelpath(),
                $this->getOriginalName(),
                $this->getPreparedName(),
            ),
            $this->getTemplate()
        );
    }

    /**
     * Ensure that file with such name is not found in the provided path.
     *
     * @param string $path
     *
     * @throws \RuntimeException when the file in the path already exists
     */
    private function ensureFileAbsence($path)
    {
        if (file_exists($path)) {
            throw new \RuntimeException("Failed to write the file \"{$path}\" - it is already exists");
        }

        $this->info("Ready to write the file into path \"{$path}\" - no files with such name found", 'debug');
    }
}
