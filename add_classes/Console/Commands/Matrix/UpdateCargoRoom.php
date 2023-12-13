<?php

namespace App\Console\Commands\Matrix;

use App\Bridge\Matrix\Console\MatrixCommand;
use App\Common\Console\Command\Command;
use App\Common\Database\Relations\RelationInterface;
use App\Messenger\Message\Command\Matrix as Commands;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\BusNameStamp;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/**
 * @author Anton Zencenco
 */
class UpdateCargoRoom extends MatrixCommand
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'matrix:rooms:cargo:update';

    /**
     * {@inheritdoc}
     */
    protected $description = 'The command that updates the Cargo notification room for exsisting users.';

    /**
     * {@inheritdoc}
     */
    protected $help = <<<'HELP'
    The <info>%command.name%</info> command updates the Cargo Notification rooms for the Matrix users.

        <info>php %command.full_name% <user-id></info>

    To update rooms for multiple users at once, pass each user ID:

        <info>php %command.full_name% <user-id-1> <user-id-2></info>

    Use the --all option to update rooms for all users from the application:

        <info>php %command.full_name% --all</info>

        <info>php %command.full_name% -a</info>

    Use the --groups option to update rooms only for users that belongs to the certain group (you must use group ID here):

        <info>php %command.full_name% --all --group=1 --group=2</info>

        <info>php %command.full_name% --all -G 1 -G 2</info>

    Use the --group-alias option to update rooms only for users that belongs to the certain group (you must use group alias here):

        <info>php %command.full_name% --all --group-alias=buyer --group-alias=seller</info>

        <info>php %command.full_name% --all -A "buyer" -A "seller"</info>

    Use the --status option to update rooms only for users that have a certain status:

        <info>php %command.full_name% --all --status=new --status=pending</info>

        <info>php %command.full_name% --all -S "new" -S "pending"</info>

    Use the --delay option to indicate the interval between update commands. The interval is measured in milliseconds:

        <info>php %command.full_name% <user-id-1> <user-id-2> --delay=7500</info>

        <info>php %command.full_name% <user-id-1> <user-id-2> -d 7500</info>

    Use the --limit option to indicate the limit of users in the update queue:

        <info>php %command.full_name% <user-id-1> <user-id-2> --limit=1000</info>

        <info>php %command.full_name% <user-id-1> <user-id-2> -L 1000</info>

    Use the --cycle option to override the export cycle (by default one from ENV is used).
    <comment>Warning!</comment> This is dangerous option and you must use it at your own discretion. Ideally, you must never use it in production mode:

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
        $users = $this->findExistinfUsers($input);
        if (empty($users)) {
            $output->success('Nothing to update.');

            return Command::SUCCESS;
        }

        // Send messages
        $output->title('Start the process.');
        $output->progressStart(\count($users));

        try {
            $delay = \max(0, (int) $input->getOption('delay'));
            foreach ($users as $user) {
                $this->messageBus->dispatch(new Envelope(
                    new Commands\UpdateCargoRoom((int) $user['idu']),
                    [new BusNameStamp('command.bus'), new DelayStamp($delay)]
                ));
                $output->progressAdvance(1);
            }
        } finally {
            $output->progressFinish();
        }
        $output->success('Process finished.');

        return Command::SUCCESS;
    }

    /**
     * Find users for export.
     */
    protected function findExistinfUsers(InputInterface $input): array
    {
        $this->validateInputs($input);
        $this->overrideCycle($input);
        $usersIds = $input->getArgument('user');
        $fetchAll = $input->getOption('all');
        $groupIds = $this->findGroupsIds($input->getOption('group'), $input->getOption('group-alias'));
        $statuses = $this->normalizeUserStatuses($input->getOption('status'));
        $limit = $input->getOption('limit');

        // Prepare the scopes
        $scopes = ['groups' => $groupIds, 'statuses' => $statuses];
        if (!empty($usersIds)) {
            $scopes['ids'] = $usersIds;
        } elseif (!$fetchAll) {
            return [];
        }

        return $this->usersRepository->findAllBy([
            'limit'   => (int) $limit ?: null,
            'scopes'  => $scopes,
            'exists'  => [
                $this->usersRepository->getRelationsRuleBuilder()->whereHas(
                    'matrixReference',
                    function (QueryBuilder $query, RelationInterface $relation) {
                        $relation->getRelated()->getScope('version')($query, $this->matrixConnector->getConfig()->getSyncVersion());
                        $relation->getRelated()->getScope('hasCargoRoom')($query, true);
                    }
                ),
            ],
        ]);
    }
}
