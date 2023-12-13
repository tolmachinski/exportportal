<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Common\Console\Command\Command;
use App\Console\Traits\SingleProcessCommandTrait;
use Configs_Model as ConfigsRepository;
use Symfony\Component\Config\ConfigCache;

final class CacheConfiguration extends Command
{
    use SingleProcessCommandTrait;

    /**
     * {@inheritdoc}
     */
    protected $name = 'config:cache';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a cache file for faster custom configuration loading';

    /**
     * {@inheritdoc}
     */
    protected $help = <<<'HELP'
    The <info>%command.name%</info> command creates the cache for configuration for faster loading.

        <info>php %command.full_name%</info>
    HELP;

    /**
     * Inidcates if debug mode is enabled.
     */
    private bool $debug;

    /**
     * The build directory.
     */
    private string $buildDir;

    /**
     * The instance of the custom configs repository.
     */
    private ConfigsRepository $configsRepository;

    /**
     * @param ConfigsRepository $configsRepository the instance of the custom configs repository
     */
    public function __construct(ConfigsRepository $configsRepository, string $buildDir, bool $debug)
    {
        parent::__construct();

        $this->debug = $debug;
        $this->buildDir = $buildDir;
        $this->configsRepository = $configsRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $cache = new ConfigCache($this->buildDir . '/customConfigs.php', $this->debug);
        $cache->write($this->dumpConfigurations(
            $this->collectConfigurations()
        ));

        $this->getOutput()->success('The basic app configurations were successfully cached');
    }

    /**
     * Dumps configuration.
     */
    private function dumpConfigurations(array $configs): string
    {
        return "<?php\n\nreturn " . var_export($configs, true) . ";\n";
    }

    /**
     * Collects all relevant configurations into one set.
     */
    private function collectConfigurations(): array
    {
        return $this->configsRepository->findPairs();
    }
}
