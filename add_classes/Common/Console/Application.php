<?php

declare(strict_types=1);

namespace App\Common\Console;

use App\Common\Database\Console\ConnectionRegistryAdapter;
use Doctrine\DBAL\Tools\Console\ConsoleRunner as DBALConsoleRunner;
use Doctrine\Migrations\Configuration\Connection\ConnectionRegistryConnection;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\DependencyFactory;
use Doctrine\Migrations\Tools\Console\ConsoleRunner as MigrationsConsoleRunner;
use Doctrine\Persistence\ConnectionRegistry;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\SymfonyQuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use tmvc;

/**
 * The project console application.
 */
class Application extends BaseApplication
{
    const NAME = 'ExportPortal CLI';

    const VERSION = '2.3.0';

    private $kernel;
    private $commandsRegistered = false;
    private $externalCommandsRegistered = false;

    private $registrationErrors = [];

    public function __construct(tmvc $kernel)
    {
        $this->kernel = $kernel;

        parent::__construct(static::NAME, static::VERSION);

        $inputDefinition = $this->getDefinition();
        $inputDefinition->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', $kernel->getEnvironment()));
        $inputDefinition->addOption(new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switch off debug mode.'));
    }

    /**
     * Gets the kernel associated with this Console.
     *
     * @return tmvc A kernel instance
     */
    public function getKernel()
    {
        return $this->kernel;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        if ($this->kernel->getContainer()->has('services_resetter')) {
            $this->kernel->getContainer()->get('services_resetter')->reset();
        }
    }

    /**
     * Runs the current application.
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->registerCommands();
        $this->registerExternalCommands();

        if ($this->registrationErrors) {
            $this->renderRegistrationErrors($input, $output);
        }

        $this->setDispatcher($this->kernel->getContainer()->get('event_dispatcher'));
        $this->setHelperSet(new HelperSet([
            'container' => new ContainerHelper($this->kernel->getContainer()),
            'question'  => new SymfonyQuestionHelper(),
        ]));

        return parent::doRun($input, $output);
    }

    /**
     * {@inheritdoc}
     */
    public function find($name): Command
    {
        $this->registerCommands();

        return parent::find($name);
    }

    /**
     * {@inheritdoc}
     */
    public function get($name): Command
    {
        $this->registerCommands();

        $command = parent::get($name);

        if ($command instanceof ContainerAwareInterface) {
            $command->setContainer($this->kernel->getContainer());
        }

        return $command;
    }

    /**
     * {@inheritdoc}
     */
    public function all($namespace = null): array
    {
        $this->registerCommands();

        return parent::all($namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function getLongVersion(): string
    {
        return parent::getLongVersion() . sprintf(' (env: <comment>%s</>, debug: <comment>%s</>)', $this->kernel->getEnvironment(), $this->kernel->isDebug() ? 'true' : 'false');
    }

    public function add(Command $command): ?Command
    {
        $this->registerCommands();

        return parent::add($command);
    }

    /**
     * {@inheritdoc}
     */
    protected function doRunCommand(Command $command, InputInterface $input, OutputInterface $output): int
    {
        if (!$command instanceof ListCommand) {
            if ($this->registrationErrors) {
                $this->renderRegistrationErrors($input, $output);
                $this->registrationErrors = [];
            }

            return parent::doRunCommand($command, $input, $output);
        }

        $returnCode = parent::doRunCommand($command, $input, $output);

        if ($this->registrationErrors) {
            $this->renderRegistrationErrors($input, $output);
            $this->registrationErrors = [];
        }

        return $returnCode;
    }

    protected function registerCommands()
    {
        if ($this->commandsRegistered) {
            return;
        }

        $this->commandsRegistered = true;

        $this->kernel->boot();

        $container = $this->kernel->getContainer();
        if ($container->has('console.command_loader')) {
            $this->setCommandLoader($container->get('console.command_loader'));
        }

        if ($container->hasParameter('console.command.ids')) {
            $lazyCommandIds = $container->hasParameter('console.lazy_command.ids') ? $container->getParameter('console.lazy_command.ids') : [];
            foreach ($container->getParameter('console.command.ids') as $id) {
                if (!isset($lazyCommandIds[$id])) {
                    try {
                        $this->add($container->get($id));
                    } catch (\Throwable $e) {
                        $this->registrationErrors[] = $e;
                    }
                }
            }
        }
    }

    private function registerExternalCommands()
    {
        if ($this->externalCommandsRegistered) {
            return;
        }

        $this->externalCommandsRegistered = true;

        $this->kernel->boot();

        $container = $this->kernel->getContainer();

        // Add doctrine/dbal commands
        DBALConsoleRunner::addCommands($this, new ConnectionRegistryAdapter($registry = $container->get(ConnectionRegistry::class)));
        // Add doctrine/migrations commands
        MigrationsConsoleRunner::addCommands(
            $this,
            DependencyFactory::fromConnection(
                new PhpFile(\App\Common\CONFIG_PATH . 'migrations.php'),
                ConnectionRegistryConnection::withSimpleDefault($registry, $registry->getDefaultConnectionName())
            )
        );
    }

    private function renderRegistrationErrors(InputInterface $input, OutputInterface $output)
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output = $output->getErrorOutput();
        }

        (new SymfonyStyle($input, $output))->warning('Some commands could not be registered:');

        foreach ($this->registrationErrors as $error) {
            $this->doRenderThrowable($error, $output);
        }
    }
}
