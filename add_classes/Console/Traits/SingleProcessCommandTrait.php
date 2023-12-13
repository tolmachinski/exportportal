<?php

namespace App\Console\Traits;

use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait SingleProcessCommandTrait
{
    use LockableTrait;

    /**
     * {@inheritdoc}
     *
     * By default command execution is locked in the process
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Prevent simultaneous execution of the command
        if (!$this->lock()) {
            if (!$output->isQuiet()) {
                $this->error('The command is already running in another process.');
            }

            return 0;
        }

        return parent::execute($input, $output);
    }
}