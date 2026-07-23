<?php
/**
 * Cache Manager
 *
 * @package DShop\Core
 */

namespace DShop\Core;

/**
 * Class Cache
 *
 * Manages caching operations
 */
class Cache
{
    /**
     * Cache group
     *
     * @var string
     */
    private $group = 'dshop';

    /**
     * Default expiration time (1 hour)
     *
     * @var int
     */
    private $defaultExpiration = 3600;

    /**
     * Get cached value
     *
     * @param string $key Cache key
     * @param mixed $default Default value
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $value = wp_cache_get($key, $this->group);

        if (false === $value) {
            return $default;
        }

        return $value;
    }

    /**
     * Set cache value
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $expiration Expiration time in seconds
     * @return bool
     */
    public function set(string $key, $value, int $expiration = 0): bool
    {
        if ($expiration <= 0) {
            $expiration = $this->defaultExpiration;
        }

        return wp_cache_set($key, $value, $this->group, $expiration);
    }

    /**
     * Delete cached value
     *
     * @param string $key Cache key
     * @return bool
     */
    public function delete(string $key): bool
    {
        return wp_cache_delete($key, $this->group);
    }

    /**
     * Flush all cached values
     *
     * @return bool
     */
    public function flush(): bool
    {
        return wp_cache_flush_group($this->group);
    }

    /**
     * Check if key exists in cache
     *
     * @param string $key Cache key
     * @return bool
     */
    public function has(string $key): bool
    {
        return wp_cache_get($key, $this->group) !== false;
    }

    /**
     * Get or set cached value
     *
     * @param string $key Cache key
     * @param callable $callback Callback to generate value
     * @param int $expiration Expiration time in seconds
     * @return mixed
     */
    public function remember(string $key, callable $callback, int $expiration = 0)
    {
        $value = $this->get($key);

        if (null !== $value) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $expiration);

        return $value;
    }

    /**
     * Increment cached value
     *
     * @param string $key Cache key
     * @param int $offset Offset value
     * @return int|false
     */
    public function increment(string $key, int $offset = 1)
    {
        return wp_cache_incr($key, $offset, $this->group);
    }

    /**
     * Decrement cached value
     *
     * @param string $key Cache key
     * @param int $offset Offset value
     * @return int|false
     */
    public function decrement(string $key, int $offset = 1)
    {
        return wp_cache_decr($key, $offset, $this->group);
    }

    /**
     * Set cache group
     *
     * @param string $group Cache group
     * @return void
     */
    public function setGroup(string $group): void
    {
        $this->group = $group;
    }

    /**
     * Get cache group
     *
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * Set default expiration time
     *
     * @param int $expiration Expiration time in seconds
     * @return void
     */
    public function setDefaultExpiration(int $expiration): void
    {
        $this->defaultExpiration = $expiration;
    }

    /**
     * Get multiple cached values
     *
     * @param array $keys Cache keys
     * @return array
     */
    public function getMultiple(array $keys): array
    {
        $results = [];
        foreach ($keys as $key) {
            $results[$key] = $this->get($key);
        }
        return $results;
    }

    /**
     * Set multiple cached values
     *
     * @param array $keyValuePairs Key-value pairs
     * @param int $expiration Expiration time in seconds
     * @return bool
     */
    public function setMultiple(array $keyValuePairs, int $expiration = 0): bool
    {
        $success = true;
        foreach ($keyValuePairs as $key => $value) {
            if (!$this->set($key, $value, $expiration)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * Delete multiple cached values
     *
     * @param array $keys Cache keys
     * @return bool
     */
    public function deleteMultiple(array $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        return $success;
    }
}
