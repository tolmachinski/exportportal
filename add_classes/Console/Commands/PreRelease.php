<?php

namespace App\Console\Commands;

use DateTimeImmutable;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

final class PreRelease extends AbstractReleaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'pre-release';

    /**
     * {@inheritdoc}
     */
    protected $description = 'The command that creates the pre-release version of application.';

    /**
     * {@inheritdoc}
     */
    protected $optionsList = [
        ['stop-before-commit', null, InputOption::VALUE_NONE, 'Indicates if the execution must be stopped before commit.'],
    ];

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
        $remote = $this->option('remote');
        $target = $this->argument('target');
        $source = $this->argument('source');
        $tag = $this->option('tag');

        $input = $this->getInput();
        $addVersionTag = null !== $tag;
        $commitDateFormat = $input->getOption('date-format') ?? $configs['dateFormat'] ?? DATE_ATOM;
        $pushNotFoundBranches = $input->hasParameterOption('--push-not-found') ? $input->getOption('push-not-found') : $configs['pushNotFound'] ?? false;
        $createNotFoundBranches = $input->hasParameterOption('--create-not-found') ? $input->getOption('create-not-found') : $configs['createNotFound'] ?? false;
        $stopBeforeCommit = $input->getOption('stop-before-commit');

        // Let's start
        $this->pullBranch($source, $remote);
        $this->pullBranch($target, $remote, $createNotFoundBranches, $pushNotFoundBranches);
        $this->mergeBranch($source, $target, $remote);
        $this->checkForConflicts($source, $target, true);
        if ($stopBeforeCommit) {
            $this->getOutput()->warning('The execution was stopped before commit.');

            return;
        }

        $this->commitChanges(sprintf("Bump pre-release to '%s' at %s.", $target, (new DateTimeImmutable())->format($commitDateFormat)));
        if ($this->hasDiffs($source, $target) && !$this->resolveDiffs($source, $target, $remote, $tag, $this->option('tag-message'))) {
            return;
        }
        if ($addVersionTag) {
            $this->addVersioning($tag, $this->option('tag-message')); // Put it on
        }

        // Push me...
        $this->pushBranch($target, $remote, $addVersionTag);
        $this->checkoutBranch($source);
        $this->getOutput()->block('The new version of application is pre-released', 'OK', 'fg=black;bg=green', ' ', true);
    }

    /**
     * Check if source and target branches have diffs.
     */
    protected function hasDiffs(string $sourceBranch, string $targetBranch): bool
    {
        $this->runCommand($compare = new Process(['git', 'diff', "{$sourceBranch}..{$targetBranch}", '--summary']));
        if (!empty($compare->getOutput())) {
            if ($this->getOutput()->isDebug()) {
                $this->getOutput()->write($compare->getOutput());
            }

            return true;
        }

        return false;
    }

    /**
     * Resolves difference between branches.
     */
    protected function resolveDiffs(string $sourceBranch, string $targetBranch, string $remote, ?string $tag, ?string $tagMessage): bool
    {
        $this->runCommand($diffs = new Process(['git', 'diff', "{$sourceBranch}..{$targetBranch}", '--summary']));
        $queue = array_filter(array_map('trim', explode("\n", $diffs->getOutput())));
        $queue = array_map(
            function ($index, $value) { return ["{$index})", $value]; },
            range(1, count($queue)),
            $queue
        );

        $output = $this->getOutput();
        $output->section('The branches have differences. Please resolve them in the following files to continue.');
        $output->table(['', 'Diffs'], $queue);
        $resolution = $output->choice(
            'Do you want to resolve those differences manually or you want to skip this step?',
            ['Resolve manually', 'Skip'],
            'Stop'
        );

        if ('Skip' === $resolution) {
            return true;
        }

        $followTagsPostfix = '';
        $commandList = [];
        if (!empty($tag)) {
            $commandList[] = "git tag -a {$tag} -m {$tagMessage}";
            $followTagsPostfix = ' --follow-tags';
        }
        $commandList[] = "git push {$remote} {$targetBranch}{$followTagsPostfix}";
        $commandList[] = "git checkout {$sourceBranch}";

        $this->getOutput()->block(
            <<<INFO
            You need to resolve diffrences between branches "{$sourceBranch}" and "{$targetBranch}" manually and run the following commands afterwards:
            INFO,
            '!',
            'fg=black;bg=yellow',
            ' ',
            true
        );
        $this->getOutput()->listing($commandList);

        return false;
    }
}
