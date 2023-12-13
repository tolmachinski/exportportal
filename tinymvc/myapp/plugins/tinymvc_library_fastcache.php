<?php

use App\Common\Cache\Contracts\PoolFactoryInterface;
use Phpfastcache\CacheManager;
use Phpfastcache\Config\Config;
use Phpfastcache\Core\Pool\ExtendedCacheItemPoolInterface;
use Phpfastcache\Helper\Psr16Adapter;
use Psr\SimpleCache\CacheInterface;

/**
 *
 * @author Bendiucov Tatiana
 * @todo Refactoring [01.12.2021]
 * library refactoring code style
 *
 * @link https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/d.-Libraries/j.-Fastcache
 */
class TinyMVC_Library_Fastcache
{
    /**
     * The fastchache manager' configs.
     *
     * @var array
     */
    private $configs = array(
        'pools'   => array(),
    );

    /**
     * Default pool name.
     *
     * @var string
     */
    private $defaultPool;

    /**
     * List of created pool.
     *
     * @var array
     */
    private $pools = array();

    public function __construct()
    {
        $configs = array();
        if (file_exists($path = \App\Common\CONFIG_PATH . '/cache.php')) {
            $configs = include $path;
        }

        if (is_array($configs)) {
            $this->configs = isset($configs['fastcache']) ? $configs['fastcache'] : array();
        }

        if (!empty($this->configs['globals']) && is_array($this->configs['globals'])) {
            CacheManager::setDefaultConfig(new Config($this->configs['globals']));
        }
    }

    /**
     * Creates a pool instance.
     *
     * @param null|string $name
     *
     * @throws InvalidArgumentException if factory returns not instance of ExtendedCacheItemPoolInterface or CacheInterface
     *
     * @return ExtendedCacheItemPoolInterface|CacheInterface
     */
    public function pool($name = null)
    {
        if (null === $name) {
            $name = $this->getDefaultPool();
        }

        if (isset($this->pools[$name])) {
            return $this->pools[$name];
        }

        return $this->pools[$name] = $this->getPool(
            $this->getPoolConfiguration($name)
        );
    }

    /**
     * Get pool configuration.
     *
     * @throws \UnexpectedValueException if pool with such name is not cconfigured
     */
    private function getPoolConfiguration(string $name): array
    {
        if (empty($this->configs['pools'][$name])) {
            throw new \UnexpectedValueException("The pool with name \"{$name}\" is not configured.");
        }

        return $this->configs['pools'][$name];
    }

    /**
     * Returns the default disk name.
     */
    private function getDefaultPool(): string
    {
        if (null === $this->defaultPool) {
            if (!isset($this->configs['default'])) {
                throw new RuntimeException('Default pool is not set.');
            }

            $this->defaultPool = $this->configs['default'];
        }

        return $this->defaultPool;
    }

    /**
     * Returns pool for the cache from provided configuration.
     *
     * @throws \UnexpectedValueException if driver is undefined
     *
     * @return ExtendedCacheItemPoolInterface|CacheInterface
     */
    private function getPool(array $config)
    {
        if (!isset($config['driver'])) {
            throw new \UnexpectedValueException('Invalid pool configuration: the driver is undefined');
        }

        $factory = $driver = $config['driver'];
        if ($driver instanceof ExtendedCacheItemPoolInterface) {
            return $driver;
        }

        $isPsr = $config['psr16'] ?? false;
        if (is_string($driver) && !class_exists($driver)) {
            return $this->getPoolFromName($driver, $isPsr, $config['options'] ?? array());
        }

        return $this->getPoolFromFactory($factory, $isPsr, $config['options'] ?? array());
    }

    /**
     * Returns the pool from factory and options.
     *
     * @return ExtendedCacheItemPoolInterface|CacheInterface
     */
    private function getPoolFromName(string $name, bool $isPsr = false, array $options = array())
    {
        $configInstance = null;
        $configClassName = 'Phpfastcache\\Drivers\\' . CacheManager::standardizeDriverName($name) . '\\Config';
        if (class_exists($configClassName)) {
            $configInstance = new $configClassName(
                array_merge(
                    CacheManager::getDefaultConfig()->toArray(),
                    $options
                )
            );
        }

        if ($isPsr) {
            return new Psr16Adapter($name, $configInstance);
        }

        return CacheManager::getInstance($name, $configInstance);
    }

    /**
     * Returns the pool from factory and options.
     *
     * @param mixed $factory
     *
     * @throws \InvalidArgumentException if factory class does not exists
     * @throws \InvalidArgumentException if factory is not of valid type
     * @throws \InvalidArgumentException if factory returns instance which does not implements the interface
     *
     * @return ExtendedCacheItemPoolInterface|CacheInterface
     */
    private function getPoolFromFactory($factory, bool $isPsr = false, array $options = array())
    {
        if (is_string($factory)) {
            if (!class_exists($factory)) {
                throw new \InvalidArgumentException(sprintf('Factory "%s" is not found', $factory));
            }

            $factory = new $factory();
        }

        if (
            !$factory instanceof PoolFactoryInterface &&
            !$factory instanceof \Closure
        ) {
            throw new \InvalidArgumentException('Invalid factory provided');
        }

        if ($factory instanceof \Closure) {
            $pool = $factory($options, $isPsr);
        } else {
            $pool = $factory->make($options, $isPsr);
        }

        if (!$pool instanceof ExtendedCacheItemPoolInterface) {
            throw new \InvalidArgumentException(sprintf("Factory must return pool which is instance of '%s'.", ExtendedCacheItemPoolInterface::class));
        }

        return $pool;
    }
}
