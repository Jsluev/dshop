<?php
/**
 * Hookable Interface
 *
 * @package DShop\Core\Interfaces
 */

namespace DShop\Core\Interfaces;

/**
 * Interface HookableInterface
 *
 * For classes that register WordPress hooks
 */
interface HookableInterface
{
    /**
     * Register WordPress hooks
     *
     * @return void
     */
    public function registerHooks(): void;
}
