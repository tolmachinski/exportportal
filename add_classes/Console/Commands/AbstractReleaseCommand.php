<?php

namespace App\Console\Commands;

use App\Common\Console\Command\Command;
use App\Console\Traits\SingleProcessCommandTrait;
use Closure;
use DateTimeImmutable;
use RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\String\AbstractString;
use Symfony\Component\String\UnicodeString;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractReleaseCommand extends Command
{
    use SingleProcessCommandTrait;

    protected const DEFAULT_SOURCE = 'master';
    protected const DEFAULT_TARGET = 'stable';
    protected const DEFAULT_CONFIG = 'release-config.yml';
    protected const GIT_ERROR_TAG = 'error:';
    protected const GIT_FATAL_TAG = 'fatal:';
    protected const GIT_DEFAULT_REMOTE = 'origin';
    protected const GIT_PULL_NO_BRANCH_PATTERN = '/couldn\'t find remote ref/';
    protected const GIT_CHECKOUT_NO_BRANCH_PATTERN = "/pathspec '(.*)' did not match any file\\(s\\) known to git/";

    /**
     * The command source directory.
     *
     * @var string
     */
    private $cwd;

    public function __construct()
    {
        parent::__construct();

        // Determine CWD.
        $rootDir = constant('APP_ROOT') ?? null;
        $this->cwd = null === $rootDir ? '' : "{$rootDir}/";
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->ensureHasArgument($input, 'source', null, static::DEFAULT_SOURCE);
        $this->ensureHasArgument($input, 'target', null, static::DEFAULT_TARGET);
        $this->ensureHasOption($input, 'remote');
    }

    /**
     * Returns CWD value.
     */
    protected function getCwd(): string
    {
        return $this->cwd;
    }

    /**
     * Returns the release configurations.
     */
    protected function getConfigs(): array
    {
        try {
            return Yaml::parseFile($this->getCwd() . $this->option('config') ?? static::DEFAULT_CONFIG);
        } catch (ParseException $exception) {
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array_merge(
            [
                ['source', InputArgument::REQUIRED, 'The source branch for release.'],
                ['target', InputArgument::REQUIRED, 'The target branch for release.'],
            ],
            $this->argumentsList
        );
    }

    /**
     * Get console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(
            [
                ['remote', 'r', InputOption::VALUE_OPTIONAL, 'The repository upstream.', static::GIT_DEFAULT_REMOTE],
                ['config', 'c', InputOption::VALUE_OPTIONAL, 'Realtive path to the file with release configurations in the YAML format.', self::DEFAULT_CONFIG],
                ['tag', 't', InputOption::VALUE_OPTIONAL, 'The version tag of the release.'],
                ['tag-message', 'm', InputOption::VALUE_OPTIONAL, 'The version tag message.'],
                ['date-format', 'D', InputOption::VALUE_OPTIONAL, 'Format of the date in the final commit', 'd/m/Y H.i'],
                ['create-not-found', null, InputOption::VALUE_NONE, 'Indicates if not found target branch must be created.'],
                ['push-not-found', null, InputOption::VALUE_NONE, 'Indicates if not found target branch must be pushed to upstream.'],
            ],
            $this->optionsList
        );
    }

    /**
     * Checks out to the branch.
     */
    protected function checkoutBranch(string $branch, bool $createNotFound = false): void
    {
        $checkout = new Process(['git', 'checkout', $branch]);

        $this->displayCommand($checkout);
        $checkout->start();
        $checkout->wait(function ($type, $buffer) use ($checkout, $branch, $createNotFound) {
            $output = new UnicodeString($buffer);
            $errorHandler = $this->getGitCommandErrorHahdler($checkout, static::GIT_ERROR_TAG, static::GIT_FATAL_TAG, true);
            if (
                ($output->startsWith(static::GIT_ERROR_TAG) || $output->match('/' . static::GIT_ERROR_TAG . ' ?/'))
                && $output->match(static::GIT_CHECKOUT_NO_BRANCH_PATTERN)
                && $createNotFound
            ) {
                $this->displayWarning("The branch '{$branch}' is not found. Creating branch.");
                $this->createBranch($branch);
            } else {
                Closure::fromCallable($this->getProcessHandler($checkout, $errorHandler))->call($this, $type, $buffer);
            }
        });
    }

    /**
     * Creates the new branch.
     */
    protected function createBranch(string $branch): void
    {
        if (empty($branch)) {
            return;
        }

        $this->runCommand(new Process(array_filter(['git', 'branch', $branch])));
    }

    /**
     * Pulls the remote branch.
     */
    protected function pullBranch(string $branch, string $remote, bool $createNotFound = false, bool $pushNotFound = false): void
    {
        $pull = new Process(['git', 'pull', $remote, $branch]);

        $this->checkoutBranch($branch, $createNotFound);
        $this->displayCommand($pull);
        $pull->start();
        $pull->wait(function ($type, $buffer) use ($pull, $branch, $remote, $pushNotFound) {
            $output = new UnicodeString($buffer);
            $errorHandler = $this->getGitCommandErrorHahdler($pull, static::GIT_ERROR_TAG, static::GIT_FATAL_TAG);
            if (
                ($output->startsWith(static::GIT_FATAL_TAG) || $output->match('/' . static::GIT_FATAL_TAG . ' ?/'))
                && $output->match(static::GIT_PULL_NO_BRANCH_PATTERN)
                && $pushNotFound
            ) {
                $this->displayWarning("The branch '{$branch}' is not found in remote '{$remote}'. Upstream branch.");
                $this->pushBranch($branch, $remote);
            } else {
                Closure::fromCallable($this->getProcessHandler($pull, $errorHandler, true))->call($this, $type, $buffer);
            }
        });
    }

    /**
     * Pushed the remote branch.
     */
    protected function pushBranch(string $branch, string $remote, bool $withTags = false): void
    {
        $this->runCommand(new Process(array_filter(['git', 'push', $remote, $branch, $withTags ? '--follow-tags' : null])), true);
    }

    /**
     * Merges target branch into the source branch.
     */
    protected function mergeBranch(string $sourceBranch, string $targetBranch): void
    {
        $this->checkoutBranch($targetBranch);
        $this->runCommand(new Process(['git', 'merge', '-X', 'theirs', $sourceBranch]), true);
    }

    /**
     * Checks for conflicts.
     */
    protected function checkForConflicts(string $sourceBranch, string $targetBranch, bool $autoresolve = false): void
    {
        $this->runCommand($conflicts = new Process(['git', 'diff', '--name-only', '--diff-filter=U']));
        if (!empty($conflicts->getOutput())) {
            if ($autoresolve) {
                $resolveQueue = array_filter(array_map('trim', explode("\n", $conflicts->getOutput())));
                if (empty($resolveQueue)) {
                    return;
                }

                $this->resoveConflicts($resolveQueue, $sourceBranch, $targetBranch);

                return;
            }

            throw new RuntimeException(
                sprintf("The merge of the branch '%s' into '%s' failed with conflicts. Resolve conflicts to continue.", $sourceBranch, $targetBranch)
            );
        }
    }

    /**
     * Resolves merge conflict.
     */
    protected function resoveConflicts(array $conflicts, string $sourceBranch, string $targetBranch): void
    {
        $conflicts = array_map(
            function ($index, $value) { return ["{$index})", $value]; },
            range(1, count($conflicts)),
            $conflicts
        );

        $output = $this->getOutput();
        $output->section('The merge failed with conflict. Please resolve conflicts in the following files to continue');
        $output->table(['', 'Conflict'], $conflicts);
        $resolution = $output->choice(
            'Do you want to resolve this conflict now? Y(es) or N(o)',
            ['Yes', 'No', 'All theirs', 'All ours'],
            'Yes'
        );

        if ('No' === $resolution) {
            throw new RuntimeException(
                sprintf("The merge of the branch '%s' into '%s' failed with conflicts. Resolve conflicts to continue.", $sourceBranch, $targetBranch)
            );
        }

        $resolved = [];
        foreach ($conflicts as $conflict) {
            list($key, $file) = $conflict;
            $useCheckout = false;

            if ('Yes' !== $resolution) {
                $strategy = 'All theirs' === $resolution ? '--theirs' : '--ours';
                $useCheckout = true;
            } else {
                $this->runCommand($diff = new Process(['git', 'diff', $file]));

                $output->section("{$key} {$file}");
                $output->write($diff->getOutput() ?? '');
                $choise = $output->choice('Which version to choose', ['Theirs', 'Ours', 'As is'], 'Theirs');

                $strategy = null;
                if (in_array($choise, ['Theirs', 'Ours'])) {
                    $strategy = 'Theirs' === $choise ? '--theirs' : '--ours';
                    $useCheckout = true;
                }
            }

            try {
                if ($useCheckout) {
                    $this->runCommand(new Process(['git', 'checkout', $strategy, $file]), true);
                }

                $this->runCommand(new Process(['git', 'add', $file]));

                $resolved[] = $file;
            } catch (\Throwable $th) {
                $this->displayError("Failed to resolve conflict for file '{$file}' due to error '{$th->getMessage()}'");
            }
        }

        if (count($resolved) !== count($conflicts)) {
            throw new RuntimeException(
                sprintf("The merge of the branch '%s' into '%s' failed. Not all conflicts were resolved.", $sourceBranch, $targetBranch)
            );
        }
    }

    /**
     * Compares two branches.
     */
    protected function compareBranches(string $sourceBranch, string $targetBranch): void
    {
        $this->runCommand($compare = new Process(['git', 'diff', "{$sourceBranch}..{$targetBranch}"]));
        if (!empty($compare->getOutput())) {
            if ($this->getOutput()->isDebug()) {
                $this->getOutput()->write($compare->getOutput());
            }

            throw new RuntimeException(
                sprintf("The merge of the branch '%s' into '%s' failed. Branches have differences.", $sourceBranch, $targetBranch)
            );
        }
    }

    /**
     * Commits the changes.
     *
     * @param string $message
     */
    protected function commitChanges(?string $message = null): void
    {
        $this->runCommand(new Process(['git', 'commit', '--allow-empty', '-m', $message ?? '']), true);
    }

    /**
     * Adds version tag.
     */
    protected function addVersioning(string $versionTag, ?string $message = null): void
    {
        if (empty($versionTag)) {
            $this->displayWarning('Version tag is empty.');
        }

        $this->runCommand(new Process(['git', 'tag', '-a', $versionTag, '-m', $message ?? '']), true);
    }

    /**
     * Returns the default process handler.
     */
    protected function getProcessHandler(Process $command, ?callable $errorHandler = null, bool $appendOutput = false): callable
    {
        return function (string $type, string $buffer) use ($command, $errorHandler, $appendOutput) {
            $output = new UnicodeString($buffer);
            if (
                ($appendOutput && $this->getOutput()->isVeryVerbose()) || $this->getOutput()->isDebug()
            ) {
                $this->getOutput()->write($buffer);
            }

            if (null !== $errorHandler) {
                Closure::fromCallable($errorHandler)->call($this, $type, $buffer, $output);
            }
        };
    }

    /**
     * Returns the git-specific command error handler.
     */
    protected function getGitCommandErrorHahdler(
        Process $command,
        string $errorTag = self::GIT_ERROR_TAG,
        string $fatalTag = self::GIT_FATAL_TAG,
        bool $appendOutput = false
    ): callable {
        return function (string $type, string $buffer, AbstractString $output) use ($command, $errorTag, $fatalTag, $appendOutput) {
            $isError = $output->startsWith($errorTag) || $output->match("/{$errorTag} ?/");
            $isFatalError = $output->startsWith($fatalTag) || $output->match("/{$fatalTag} ?/");
            if ($isError || $isFatalError) {
                if (
                    ($appendOutput && $this->getOutput()->isVeryVerbose()) || $this->getOutput()->isDebug()
                ) {
                    $this->getOutput()->write($buffer);
                }

                if ($isFatalError) {
                    throw new RuntimeException(sprintf('The command "%s" failed with fatal error.', $command->getCommandLine()));
                }
                if ($isError) {
                    throw new RuntimeException(sprintf('The command "%s" failed with error.', $command->getCommandLine()));
                }
            }
        };
    }

    /**
     * Displays the command in STDOUT.
     */
    protected function displayCommand(Process $command): void
    {
        if ($this->getOutput()->getVerbosity() <= OutputInterface::VERBOSITY_NORMAL) {
            return;
        }
        $now = new DateTimeImmutable();

        $this->info("[{$now->format('M d H:i:s Y')}] COMMAND > {$command->getCommandLine()}");
    }

    /**
     * Displays formatted warning message.
     */
    protected function displayWarning(string $message): void
    {
        if ($this->getOutput()->getVerbosity() <= OutputInterface::VERBOSITY_NORMAL) {
            return;
        }
        $now = new DateTimeImmutable();

        $this->warn("[{$now->format('M d H:i:s Y')}] WARNING > {$message}");
    }

    /**
     * Displays formatted error message.
     */
    protected function displayError(string $message): void
    {
        if ($this->getOutput()->getVerbosity() <= OutputInterface::VERBOSITY_NORMAL) {
            return;
        }
        $now = new DateTimeImmutable();

        $this->error("[{$now->format('M d H:i:s Y')}] ERROR   > {$message}");
    }

    /**
     * Runs the prepared command.
     */
    protected function runCommand(Process $command, bool $showCommandOutput = false, bool $showErrorOutput = false): void
    {
        $this->displayCommand($command);
        $command->start();
        $command->wait(
            $this->getProcessHandler(
                $command,
                $this->getGitCommandErrorHahdler($command, static::GIT_ERROR_TAG, static::GIT_FATAL_TAG, $showErrorOutput),
                $showCommandOutput
            )
        );
    }
}
