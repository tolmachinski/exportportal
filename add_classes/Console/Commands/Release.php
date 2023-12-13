<?php

namespace App\Console\Commands;

use DateTimeImmutable;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\ErrorHandler\ErrorHandler;
use Symfony\Component\Process\Process;

final class Release extends AbstractReleaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'release';

    /**
     * {@inheritdoc}
     */
    protected $description = 'The command that creates the release version of application.';

    /**
     * {@inheritdoc}
     */
    protected $optionsList = [
        ['ignore-entries', 'I', InputOption::VALUE_OPTIONAL, 'Comma-separated list of the entries that will be ignored.'],
        ['remove-entries', 'R', InputOption::VALUE_OPTIONAL, 'Comma-separated list of the entries that will be removed.'],
        ['clean-directories', 'C', InputOption::VALUE_OPTIONAL, 'Comma-separated list of the directories that will be cleaned.', null],
    ];

    /**
     * Directories that will be cleared.
     *
     * @var string[]
     */
    private $cleanupList = [];

    /**
     * Directories that will be deleted.
     *
     * @var string[]
     */
    private $deletionList = [];

    /**
     * Entries that will be ignored.
     *
     * @var string[]
     */
    private $ignoreList = [];

    /**
     * Cached original .gitignore file contents.
     *
     * @var null|string
     */
    private $cachedGitignore;

    /**
     * Flag that indicates if source branch has its own .gitignore file.
     *
     * @var bool
     */
    private $hasOriginalGitignore = false;

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        $this->ensureHasOption($input, 'config');
    }

    /**
     * {@inheritdoc}
     */
    protected function handle()
    {
        // Fetch configs, options and arguments
        $configs = $this->getConfigs();
        $ignoreList = $this->option('ignore-entries');
        $deleteList = $this->option('remove-entries');
        $cleanList = $this->option('clean-directories');
        $remote = $this->option('remote');
        $target = $this->argument('target');
        $source = $this->argument('source');
        $tag = $this->option('tag');

        $this->ignoreList = null !== $ignoreList ? array_filter(array_map('trim', explode(',', $ignoreList ?? ''))) : $configs['ignore'] ?? [];
        $this->cleanupList = null !== $cleanList ? array_filter(array_map('trim', explode(',', $cleanList ?? ''))) : $configs['clean'] ?? [];
        $this->deletionList = null !== $deleteList ? array_filter(array_map('trim', explode(',', $deleteList ?? ''))) : $configs['delete'] ?? [];

        $input = $this->getInput();
        $addVersionTag = null !== $tag;
        $commitDateFormat = $input->getOption('date-format') ?? $configs['dateFormat'] ?? DATE_ATOM;
        $pushNotFoundBranches = $input->hasParameterOption('--push-not-found') ? $input->getOption('push-not-found') : $configs['pushNotFound'] ?? false;
        $createNotFoundBranches = $input->hasParameterOption('--create-not-found') ? $input->getOption('create-not-found') : $configs['createNotFound'] ?? false;

        // Let's start
        $this->cacheOriginalGitignore();
        $this->pullBranch($source, $remote);
        $this->pullBranch($target, $remote, $createNotFoundBranches, $pushNotFoundBranches);
        $this->mergeBranch($source, $target);
        $this->checkForConflicts($source, $target, true);
        $this->deleteDirectories();
        $this->cleanFiles();
        $this->createGitignoreFile();
        $this->commitChanges(sprintf("Release new version to '%s' at %s.", $target, (new DateTimeImmutable())->format($commitDateFormat)));
        if ($addVersionTag) {
            $this->addVersioning($tag, $this->option('tag-message')); // Put it on
        }

        // Push me...
        $this->pushBranch($target, $remote, $addVersionTag);
        $this->checkoutBranch($source);
        $this->restoreOriginalGitignore();
        $this->getOutput()->block('The new version of application is released', 'OK', 'fg=black;bg=green', ' ', true);
    }

    /**
     * Deletes directories.
     */
    private function deleteDirectories(): void
    {
        foreach ($this->deletionList as $entry) {
            try {
                $this->deleteFromTree($entry, false, true);
            } catch (\Exception $exception) {
                $this->displayError($exception->getMessage());
            }
        }
    }

    /**
     * Clean-up the directories.
     */
    private function cleanFiles(): void
    {
        foreach ($this->cleanupList as $entry) {
            try {
                $this->deleteFromTree($entry, true, true);
            } catch (\Exception $exception) {
                $this->displayError($exception->getMessage());
            }
        }

        $restore = Process::fromShellCommandline('git status -s | grep ".clean\|.htaccess\|index.html" | cut -c4-');
        $restore->run();
        if ('' !== $restore->getOutput()) {
            $restoreQueue = array_filter(explode("\n", $restore->getOutput()));
            foreach ($restoreQueue as $entry) {
                try {
                    $this->restoreInTree($entry);
                } catch (\Exception $exception) {
                    $this->displayError($exception->getMessage());
                }
            }
        }
    }

    /**
     * Caches original .gitignore file.
     */
    private function cacheOriginalGitignore(): void
    {
        $path = "{$this->getCwd()}.gitignore";
        $hasGitignore = $this->hasOriginalGitignore = ErrorHandler::call('file_exists', $path);
        if (!$hasGitignore) {
            return;
        }

        $this->displayWarning("Found source '.gitignore' file. Storing file content.");

        try {
            $this->cachedGitignore = ErrorHandler::call('file_get_contents', $path) ?? '';
        } catch (\Exception $exception) {
            $this->displayError($exception->getMessage());
        }
    }

    /**
     * Restores original .gitignore file.
     */
    private function restoreOriginalGitignore(): void
    {
        if (!$this->hasOriginalGitignore || null === $this->cachedGitignore) {
            return;
        }

        $this->displayWarning("Restoring original '.gitignore' file content.");

        try {
            ErrorHandler::call('file_put_contents', "{$this->getCwd()}.gitignore", $this->cachedGitignore ?? '');
        } catch (\Exception $exception) {
            $this->displayError($exception->getMessage());
        }
    }

    /**
     * Creates target .gitignore file.
     */
    private function createGitignoreFile(): void
    {
        if (empty($this->ignoreList)) {
            return;
        }

        $this->runCommand(new Process(['git', 'rm', '--cached', '.gitignore']));
        $this->displayWarning("Creating target '.gitignore' file.");

        try {
            ErrorHandler::call('file_put_contents', "{$this->getCwd()}.gitignore", implode("\n", $this->ignoreList));
        } catch (\Exception $exception) {
            $this->displayError($exception->getMessage());
        }

        $this->runCommand(new Process(['git', 'add', '.']));
    }

    /**
     * Deletes entry from git tree.
     */
    private function deleteFromTree(string $entry, bool $forcedDelete = false, bool $ignoreNotFound = false): void
    {
        if (empty($entry)) {
            return;
        }
        $force = $forcedDelete ? '-f' : null;
        $ignore = $ignoreNotFound ? '--ignore-unmatch' : null;

        $this->runCommand(new Process(array_filter(['git', 'rm', '-r', $force, $ignore, $entry])));
    }

    /**
     * Restore deleted entry in tree.
     */
    private function restoreInTree(string $entry): void
    {
        if (empty($entry)) {
            return;
        }

        $this->runCommand(new Process(array_filter(['git', 'reset', 'HEAD', $entry])));
        $this->runCommand(new Process(array_filter(['git', 'checkout', '--', $entry])));
    }
}
