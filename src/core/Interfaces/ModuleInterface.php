<?php
/**
 * Module Interface
 *
 * @package DShop\Core\Interfaces
 */

namespace DShop\Core\Interfaces;

/**
 * Interface ModuleInterface
 *
 * All DShop modules must implement this interface
 */
interface ModuleInterface
{
    /**
     * Get module name
     *
     * @return string Module name
     */
    public function getName(): string;

    /**
     * Get module version
     *
     * @return string Module version
     */
    public function getVersion(): string;

    /**
     * Get module description
     *
     * @return string Module description
     */
    public function getDescription(): string;

    /**
     * Get module dependencies
     *
     * @return array Array of dependent module names
     */
    public function getDependencies(): array;

    /**
     * Initialize module
     *
     * @return void
     */
    public function init(): void;

    /**
     * Module activation callback
     *
     * @return void
     */
    public function activate(): void;

    /**
     * Module deactivation callback
     *
     * @return void
     */
    public function deactivate(): void;

    /**
     * Check if module is active
     *
     * @return bool
     */
    public function isActive(): bool;
}
