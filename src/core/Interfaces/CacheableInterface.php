<?php
/**
 * Cacheable Interface
 *
 * @package DShop\Core\Interfaces
 */

namespace DShop\Core\Interfaces;

/**
 * Interface CacheableInterface
 *
 * For classes that support caching
 */
interface CacheableInterface
{
    /**
     * Get cache key
     *
     * @return string Cache key
     */
    public function getCacheKey(): string;

    /**
     * Get cache expiration time in seconds
     *
     * @return int Expiration time
     */
    public function getCacheExpiration(): int;

    /**
     * Clear cache
     *
     * @return void
     */
    public function clearCache(): void;
}
