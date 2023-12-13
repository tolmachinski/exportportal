<?php

namespace App\Console\Commands;

use App\Bridge\Matrix\MatrixConnector;
use App\Common\Console\Command\Command;
use App\Console\Traits\SingleProcessCommandTrait;
use RuntimeException;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateMatrixAccessToken extends Command
{
    use SingleProcessCommandTrait;

    /**
     * {@inheritdoc}
     */
    protected $name = 'matrix:access-token';

    /**
     * {@inheritdoc}
     */
    protected $description = 'The command that returns new matrix access token for user\' credentials';

    /**
     * {@inheritdoc}
     */
    protected $help;

    /**
     * {@inheritdoc}
     */
    protected $argumentsList = [
        ['user', InputArgument::REQUIRED, 'The users matrix ID'],
    ];

    /**
     * The matrix connector.
     */
    protected MatrixConnector $matrixConnector;

    /**
     * The user's password.
     */
    private string $userPassword;

    /**
     * {@inheritdoc}
     */
    public function __construct(MatrixConnector $matrixConnector)
    {
        $this->matrixConnector = $matrixConnector;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        // Ensure user ID
        $this->ensureHasArgument($input, 'user', 'Enter the matrix user ID');
        $user = $input->getArgument('user');
        if (null === $user) {
            throw new RuntimeException('Not enough arguments (missing: "user").');
        }

        $this->readPasswordFromCommandLine($input, $output);
    }

    /**
     * {@inheritDoc}
     */
    protected function handle()
    {
        $output = $this->getOutput();
        if (!isset($this->userPassword)) {
            $this->readPasswordFromCommandLine($this->getInput(), $output);
        }

        $doSync = $this->option('first-sync');
        $homeserver = $this->option('homeserver');
        if ($homeserver !== $this->matrixConnector->getConfig()->getHomeserverHost()) {
            // Override homeserver if another one is provided.
            $this->matrixConnector->getConfig()->setHomeserverHost($homeserver);
            $this->matrixConnector->getMatrixClient()->getConfig()->setHost($homeserver);
        }

        $authenticatedUser = $this->matrixConnector->loginUserWithPassword($userId = $this->argument('user'), $this->userPassword, $this->option('device-id'));
        if ($doSync) {
            try {
                $api = $this->matrixConnector->getMatrixClient()->getRoomParticipationApi();
                $api->getConfig()->setAccessToken($authenticatedUser->getAccessToken());
                $api->sync('{}', null, null, null, 0);
            } catch (\Throwable $e) {
                if ($output->isVerbose()) {
                    $output->error($e->getMessage());
                }
            }
        }

        $output->success(\sprintf('The access token for user "%s" is: %s', $userId, $authenticatedUser->getAccessToken()));

        return Command::SUCCESS;
    }

    /**
     * {@inheritDoc}
     */
    protected function getOptions()
    {
        return [
            ['device-id', 'd', InputOption::VALUE_OPTIONAL, 'The device name', null],
            ['homeserver', 'H', InputOption::VALUE_OPTIONAL, 'The homeserver URL', $this->matrixConnector->getConfig()->getHomeserverHost()],
            ['first-sync', null, InputOption::VALUE_NONE, 'Indicate if need to perform the first sync for token'],
        ];
    }

    /**
     * Reads the password from command line.
     */
    private function readPasswordFromCommandLine(InputInterface $input, OutputInterface $output): void
    {
        // Resolve password
        $style = $output instanceof SymfonyStyle ? $output : $this->getOutput();
        /** @var SymfonyQuestionHelper $helper */
        $helper = $this->getHelper('question');
        $passwordQuestion = new Question('Enter password');
        $passwordConfirmationQuestion = new Question('Confirm password');
        $passwordQuestion->setHidden(true);
        $passwordConfirmationQuestion->setHidden(true);

        $password = $helper->ask($input, $style, $passwordQuestion);
        if (null === $password) {
            throw new RuntimeException('The password is required.');
        }
        $passwordConfirmation = $helper->ask($input, $style, $passwordConfirmationQuestion);
        if ($passwordConfirmation !== $password) {
            throw new RuntimeException('The passwords does not match.');
        }

        $this->userPassword = $password;
    }
}
