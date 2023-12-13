<?php

namespace App\Console\Commands;

use App\Bridge\Matrix\Console\MatrixCommand;
use App\Common\Console\Command\Command;
use App\Messenger\Message\Command\SyncMatrixUser;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\DelayStamp;

class SyncUsersOnMatrix extends MatrixCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'matrix:sync';

    /**
     * {@inheritdoc}
     */
    protected $description = 'The command that synchronizes users information on the Matrix server.';

    /**
     * {@inheritdoc}
     */
    protected $help = <<<'HELP'
    The <info>%command.name%</info> command synchronizes users information on the Matrix homeserver.

        <info>php %command.full_name% <user-id></info>

    To synchronize multiple users at once, pass each user ID:

        <info>php %command.full_name% <user-id-1> <user-id-2></info>

    Use the --all option to synchronize all users from the application:

        <info>php %command.full_name% --all</info>

        <info>php %command.full_name% -a</info>

    Use the --groups option to synchronize only users that belongs to the certain group (you must use group ID here):

        <info>php %command.full_name% --all --group=1 --group=2</info>

        <info>php %command.full_name% --all -G 1 -G 2</info>

    Use the --group-alias option to synchronize only users that belongs to the certain group (you must use group alias here):

        <info>php %command.full_name% --all --group-alias=buyer --group-alias=seller</info>

        <info>php %command.full_name% --all -A "buyer" -A "seller"</info>

    Use the --status option to synchronize only users that have a certain status:

        <info>php %command.full_name% --all --status=new --status=pending</info>

        <info>php %command.full_name% --all -S "new" -S "pending"</info>

    Use the --delay option to indicate the interval between user synchronizations. The interval is measured in milliseconds:

        <info>php %command.full_name% <user-id-1> <user-id-2> --delay=7500</info>

        <info>php %command.full_name% <user-id-1> <user-id-2> -d 7500</info>

    Use the --limit option to indicate the limit of synchronized users:

        <info>php %command.full_name% <user-id-1> <user-id-2> --limit=1000</info>

        <info>php %command.full_name% <user-id-1> <user-id-2> -L 1000</info>

    Use the --cycle option to override the synchronization cycle (by default one from ENV is used). <comment>Warning!</comment> This is
    dangerous option and you must use it at your own discretion. Ideally, you must never use it in production mode:

        <info>php %command.full_name% <user-id-1> <user-id-2> --cycle=r0</info>

        <info>php %command.full_name% <user-id-1> <user-id-2> -c r0</info>
    HELP;

    /**
     * {@inheritDoc}
     *
     * @param SymfonyStyle $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Fetch users
        $users = $this->findUsers($input);
        if (empty($users)) {
            $output->success('Nothing to sync.');

            return Command::SUCCESS;
        }

        // Send messages
        $output->title('Starting users synchronization.');
        $output->progressStart(\count($users));

        try {
            $delay = \max(0, (int) $input->getOption('delay'));
            foreach ($users as $user) {
                $this->messageBus->dispatch(new Envelope(new SyncMatrixUser((int) $user['idu'], false), [new BusNameStamp('command.bus'), new DelayStamp($delay)]));
                $output->progressAdvance(1);
            }
        } finally {
            $output->progressFinish();
        }
        $output->success('Users synchronization finished.');

        return Command::SUCCESS;
    }
}
