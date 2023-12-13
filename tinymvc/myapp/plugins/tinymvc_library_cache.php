<?php

use phpFastCache\Helper\Psr16Adapter;
use Psr\SimpleCache\CacheInterface;

/**
 * @author Bendiucov Tatiana
 * @deprecated [01.12.20121]
 */
class TinyMVC_Library_Cache
{
    /**
     * Default configs.
     *
     * @var array
     */
    public $defaultConfig = array(
        'storage' => 'Files',
        'path'    => 'cache',
    );

    /**
     * Cache adapter.
     *
     * @var CacheInterface
     */
    private $adapter;

    /**
     * Call the adapter methods.
     *
     * @param string $name
     * @param array  $args
     *
     * @throws \BadMethodCallException if adapter is not set or method is undefined
     *
     * @return mixed
     */
    public function __call($name, array $arguments)
    {
        if (null === $this->adapter || !method_exists($this->adapter, $name)) {
            throw new \BadMethodCallException(sprintf('Call to undefined method %s::%s()', static::class, $name));
        }

        return $this->adapter->{$name}(...$arguments);
    }

    /**
     * Initialize the cache adapter.
     *
     * @param array $config
     *
     * @return self
     */
    public function init($config)
    {
        $config = array_merge($this->defaultConfig, $config);
        $driver = isset($config['storage']) ? ucfirst($config['storage']) : 'Files';

        $this->adapter = new Psr16Adapter($driver, $config);

        return $this;
    }

    /**
     * Returns the cache adapter.
     *
     * @return \Psr\SimpleCache\CacheInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}
