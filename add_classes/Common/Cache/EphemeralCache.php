<?php

declare(strict_types=1);

namespace App\Common\Cache;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Traversable;

final class EphemeralCache implements CacheInterface
{
    /**
     * The cache elements.
     *
     * @var array
     */
    private $elements = array();

    /**
     * Fetches a value from the cache.
     *
     * @param string $key     the unique key of this item in the cache
     * @param mixed  $default default value to return if the key does not exist
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException MUST be thrown if the $key string is not a legal value
     *
     * @return mixed the value of the item from the cache, or $default in case of cache miss
     */
    public function get($key, $default = null)
    {
        return $this->elements[$key] ?? null;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                 $key   the key of the item to store
     * @param mixed                  $value The value of the item to store. Must be serializable.
     * @param null|\DateInterval|int $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException MUST be thrown if the $key string is not a legal value
     *
     * @return bool true on success and false on failure
     */
    public function set($key, $value, $ttl = null)
    {
        $this->elements[$key] = $value;

        return true;
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key the unique cache key of the item to delete
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException MUST be thrown if the $key string is not a legal value
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     */
    public function delete($key)
    {
        if (isset($this->elements[$key])) {
            $this->elements[$key] = null;
            unset($this->elements);
        }

        return true;
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool true on success and false on failure
     */
    public function clear()
    {
        $this->elements = array();

        return true;
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    a list of keys that can obtained in a single operation
     * @param mixed    $default default value to return for keys that do not exist
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException MUST be thrown if $keys is neither an array nor a Traversable,
     *                                                   or if any of the $keys are not a legal value
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     */
    public function getMultiple($keys, $default = null)
    {
        if (!is_array($keys) && !$keys instanceof Traversable) {
            throw new InvalidArgumentException("The keys must be array or isntance of \Travesable.");
        }

        $output = array();
        foreach ($keys as $key) {
            $output[] = $this->elements[$key] ?? $default;
        }

        return $output;
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values a list of key => value pairs for a multiple-set operation
     * @param null|\DateInterval|int $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException MUST be thrown if $values is neither an array nor a Traversable,
     *                                                   or if any of the $values are not a legal value
     *
     * @return bool true on success and false on failure
     */
    public function setMultiple($values, $ttl = null)
    {
        if (!is_array($values) && !$values instanceof Traversable) {
            throw new InvalidArgumentException("The values must be array or isntance of \Travesable.");
        }

        foreach ($values as $key => $value) {
            $this->elements[$key] = $value;
        }

        return true;
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys a list of string-based keys to be deleted
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException MUST be thrown if $keys is neither an array nor a Traversable,
     *                                                   or if any of the $keys are not a legal value
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     */
    public function deleteMultiple($keys)
    {
        if (!is_array($keys) && !$keys instanceof Traversable) {
            throw new InvalidArgumentException("The keys must be array or isntance of \Travesable.");
        }

        foreach ($keys as $key) {
            if (!isset($this->elements[$key])) {
                continue;
            }

            $this->elements[$key] = null;
            unset($this->elements[$key]);
        }

        return true;
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it, making the state of your app out of date.
     *
     * @param string $key the cache item key
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException MUST be thrown if the $key string is not a legal value
     *
     * @return bool
     */
    public function has($key)
    {
        return isset($this->elements[$key]);
    }
}
