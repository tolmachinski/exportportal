<?php

declare(strict_types=1);

namespace App\Bridge\Matrix\Console;

use App\Bridge\Matrix\MatrixConnector;
use App\Common\Console\Command\Command;
use App\Common\Contracts\Group\GroupAlias;
use App\Common\Contracts\User\UserStatus;
use App\Common\Database\Model;
use App\Common\Database\Relations\RelationInterface;
use App\Console\Traits\SingleProcessCommandTrait;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Messenger\MessageBusInterface;
use User_Groups_Model as GroupsRepository;
use Users_Model as UsersRepository;

abstract class MatrixCommand extends Command
{
    use SingleProcessCommandTrait;

    /**
     * {@inheritdoc}
     */
    protected $argumentsList = [
        ['user', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'The users ID'],
    ];

    /**
     * {@inheritdoc}
     */
    protected $optionsList = [
        ['all', 'a', InputOption::VALUE_NONE, 'Process all users'],
        ['delay', 'd', InputOption::VALUE_OPTIONAL, 'Delay between messages (in milliseconds)', 5000],
        ['cycle', 'c', InputOption::VALUE_OPTIONAL, 'Override export cycle', null],
        ['limit', 'L', InputOption::VALUE_OPTIONAL, 'Limit the list of users', null],
        ['group', 'G', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Indicate users groups ID', []],
        ['group-alias', 'A', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Indicate users groups alias', []],
        ['status', 'S', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Indicate users status', []],
    ];

    /**
     * The message bus.
     */
    protected MessageBusInterface $messageBus;

    /**
     * The matrix connector.
     */
    protected MatrixConnector $matrixConnector;

    /**
     * The users repository.
     */
    protected Model $usersRepository;

    /**
     * The groups repository.
     */
    protected GroupsRepository $groupsRepository;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        MatrixConnector $matrixConnector,
        MessageBusInterface $messageBus,
        UsersRepository $usersRepository,
        GroupsRepository $groupsRepository
    ) {
        parent::__construct();

        $this->messageBus = $messageBus;
        $this->matrixConnector = $matrixConnector;
        $this->groupsRepository = $groupsRepository;
        $this->usersRepository = $usersRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $users = $input->getArgument('user');
        $hasAllFlag = $input->hasParameterOption('--all') || $input->hasParameterOption('-a');
        if (empty($users) && !$hasAllFlag) {
            $style = $output instanceof SymfonyStyle ? $output : $this->getOutput();
            $resolution = $style->choice(
                'Do you want to process all users?',
                ['Yes' => 'Process all', 'No' => 'Skip'],
                'No'
            );

            if ('Yes' === $resolution) {
                $input->setOption('all', true);
            } else {
                $this->ensureHasArgumentFromList(
                    $input,
                    'user',
                    'Enter the user ID to process',
                    'Enter the additional user ID to process or press ENTER to continue execution'
                );
            }
        }
    }

    /**
     * Validates the command inputs.
     */
    protected function validateInputs(InputInterface $input): void
    {
        $usersIds = $input->getArgument('user');
        $exportAll = $input->getOption('all');
        $hasAllFlag = $input->hasParameterOption('--all') || $input->hasParameterOption('-a');
        if (!empty($usersIds) && $exportAll && $hasAllFlag) {
            throw new RuntimeException(\sprintf('You cannot use direct user processing and the flag "%s" at the same time', '--all'));
        }
    }

    /**
     * Overrides the internal matrix processing cycle.
     */
    protected function overrideCycle(InputInterface $input): void
    {
        if (null !== $cycle = $input->getOption('cycle')) {
            $this->matrixConnector->getConfig()->setSyncVersion($cycle);
        }
    }

    /**
     * Find users for export.
     */
    protected function findUsers(InputInterface $input, bool $notExported = false, bool $extended = false): array
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

        // Add more limitations
        $exists = [];
        if ($notExported) {
            $exists[] = $this->usersRepository->getRelationsRuleBuilder()->whereHasNot('matrixReference', function (QueryBuilder $query, RelationInterface $relation) {
                $relation->getRelated()->getScope('version')($query, $this->matrixConnector->getConfig()->getSyncVersion());
            });
        } else {
            $exists[] = $this->usersRepository->getRelationsRuleBuilder()->whereHas('matrixReference', function (QueryBuilder $query, RelationInterface $relation) {
                $relation->getRelated()->getScope('version')($query, $this->matrixConnector->getConfig()->getSyncVersion());
            });
        }

        // Add related records
        $with = [];
        if ($extended) {
            $with[] = 'matrixReference';
        }

        return $this->usersRepository->findAllBy([
            'columns' => ['*', "TRIM(CONCAT(`fname`, ' ', `lname`)) AS `full_name`"],
            'limit'   => (int) $limit ?: null,
            'exists'  => $exists,
            'scopes'  => $scopes,
            'with'    => $with,
        ]);
    }

    /**
     * Finds group IDs for users request.
     */
    protected function findGroupsIds(array $groupIds, array $groupAliases): array
    {
        if (empty($groupIds) && empty($groupAliases)) {
            return [];
        }
        $groupAliases = \array_map(fn ($alias) => GroupAlias::from($alias), $groupAliases);
        $groupIds = \array_map(fn ($id)        => (int) $id, $groupIds);
        $groups = $this->groupsRepository->findAllBy([
            'scopes' => \array_filter(['ids' => $groupIds, 'aliases' => $groupAliases]),
        ]);

        return \array_column($groups, 'idgroup');
    }

    /**
     * Transforms the raw usesr' statuses into accespted list of enum cases.
     *
     * @param string[] $statuses
     *
     * @return UserStatus[]
     */
    protected function normalizeUserStatuses(array $statuses): array
    {
        $normalized = [];
        if (!empty($statuses)) {
            foreach ($statuses as $status) {
                try {
                    $normalized[] = UserStatus::from($status);
                } catch (\Throwable $e) {
                    throw new RuntimeException(\sprintf('The status "%s" is not a valid one.', $status), 0, $e);
                }
            }
        }

        return $normalized;
    }
}
