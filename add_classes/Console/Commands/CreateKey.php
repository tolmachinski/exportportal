<?php

namespace App\Console\Commands;

use App\Common\Console\Command\Command;
use App\Common\Encryption\Storage\IdentifierGeneratorInterface;
use App\Common\Encryption\Storage\KeyFilePathGenerator;
use App\Common\Encryption\Storage\KeyStorageInterface;
use DomainException;
use ParagonIE\Halite\KeyFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateKey extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'app:create-key';

    /**
     * {@inheritdoc}
     */
    protected $description = 'The command that creates the identity key for the application.';

    /**
     * The key storage interface.
     *
     * @var KeyStorageInterface
     */
    private $keyStorage;

    /**
     * The key identifier generator.
     *
     * @var IdentifierGeneratorInterface
     */
    private $identifierGenerator;

    /**
     * The flag that indicates if execution is topped.
     *
     * @var bool
     */
    private $isStopped = false;

    /**
     * {@inheritdoc}
     */
    public function __construct(KeyStorageInterface $keyStorage)
    {
        parent::__construct();

        $this->identifierGenerator = new KeyFilePathGenerator();
        $this->keyStorage = $keyStorage;
    }

    /**
     * {@inheritdoc}
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (empty($outputDir = $input->getOption('output-dir'))) {
            $outputDir = $this->ask(
                $this->getDefinition()->getOption('output-dir')->getDescription(),
                $this->getDefinition()->getOption('output-dir')->getDefault() ?? null
            );

            $input->setOption('output-dir', $outputDir);
        }
        if (empty($name = $input->getOption('name'))) {
            $name = $this->ask(
                $this->getDefinition()->getOption('name')->getDescription(),
                $this->getDefinition()->getOption('name')->getDefault() ?? null
            );

            $input->setOption('name', $name);
        }

        $skipIfExists = filter_var($input->getOption('skip-existing'), FILTER_VALIDATE_BOOLEAN);
        if ($skipIfExists) {
            $this->isStopped = true;

            return;
        }

        $keyStorage = $this->getKeyStorage((string) $outputDir);
        if ($keyStorage->hasKey($this->getKeyIdentifier((string) $name))) {
            $consent = $this->ask(
                'The key is already exists. Do you want to replace it? ' .
                'IMPORTANT! This choise is irreversible and can heavily impact your application',
                'No'
            );

            $replaceKey = in_array(mb_strtolower($consent), ['y', 'yes']) || filter_var($consent, FILTER_VALIDATE_BOOLEAN);
            if (!$replaceKey) {
                $this->isStopped = true;
                $this->warn('The execution of the command is stopped.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function handle()
    {
        if ($this->isStopped) {
            return 0;
        }

        $name = $this->option('name');
        $outputDir = $this->option('output-dir');
        if (!$this->getInput()->isInteractive()) {
            if (empty($outputDir)) {
                throw new DomainException("The option 'output-dir' cannot be empty.");
            }
            if (empty($name)) {
                throw new DomainException("The option 'name' cannot be empty.");
            }
        }

        $keyStorage = $this->getKeyStorage($outputDir);
        $keyPair = KeyFactory::generateSignatureKeyPair();
        if (!$keyStorage->storeSignatureSecretKey($this->getKeyIdentifier((string) $name), $keyPair->getSecretKey())) {
            $this->error('Failed to store generated identity key.');

            return 0;
        }

        $this->info('The key was successfully generated.');
    }

    /**
     * Get console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['name', 'N', InputOption::VALUE_OPTIONAL, 'The name of the key.', $_ENV['APP_ENCRYPTION_KEY_NAME'] ?? null],
            ['output-dir', 'O', InputOption::VALUE_OPTIONAL, 'The directory where key will be created.', $_ENV['APP_ENCRYPTION_KEY_PATH'] ?? null],
            ['skip-existing', 'S', InputOption::VALUE_OPTIONAL, 'The command stops if key already exists.', 0],
        ];
    }

    /**
     * Returns the key storage.
     */
    private function getKeyStorage(?string $outputDir): KeyStorageInterface
    {
        return $this->keyStorage->withDirectory($outputDir ?? '');
    }

    /**
     * Returns the key identifier.
     */
    private function getKeyIdentifier(string $fileName): string
    {
        return $this->identifierGenerator->createIdentifier($fileName);
    }
}
