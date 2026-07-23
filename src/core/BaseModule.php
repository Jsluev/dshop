<?php
/**
 * Base Module Class
 *
 * @package DShop\Core
 */

namespace DShop\Core;

use DShop\Core\Interfaces\ModuleInterface;
use DShop\Core\Interfaces\HookableInterface;

/**
 * Class BaseModule
 *
 * Abstract base class for all DShop modules
 */
abstract class BaseModule implements ModuleInterface, HookableInterface
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name;

    /**
     * Module version
     *
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Module description
     *
     * @var string
     */
    protected $description = '';

    /**
     * Module dependencies
     *
     * @var array
     */
    protected $dependencies = [];

    /**
     * Whether module is active
     *
     * @var bool
     */
    protected $active = false;

    /**
     * DShop instance
     *
     * @var DShop
     */
    protected $app;

    /**
     * Module settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Constructor
     *
     * @param DShop $app DShop instance
     */
    public function __construct(DShop $app)
    {
        $this->app = $app;
        $this->name = $this->getModuleName();
        $this->loadSettings();
    }

    /**
     * Get module name from class name
     *
     * @return string
     */
    protected function getModuleName(): string
    {
        $class = static::class;
        $parts = explode('\\', $class);
        $className = end($parts);
        return strtolower(str_replace('Module', '', $className));
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies(): array
    {
        return $this->dependencies;
    }

    /**
     * {@inheritdoc}
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * {@inheritdoc}
     */
    public function activate(): void
    {
        $this->active = true;
        update_option("dshop_module_{$this->name}_active", true);
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(): void
    {
        $this->active = false;
        update_option("dshop_module_{$this->name}_active", false);
    }

    /**
     * {@inheritdoc}
     */
    public function registerHooks(): void
    {
        // Override in child class
    }

    /**
     * Load module settings
     *
     * @return void
     */
    protected function loadSettings(): void
    {
        $this->settings = get_option("dshop_module_{$this->name}_settings", []);
    }

    /**
     * Get module setting
     *
     * @param string $key Setting key
     * @param mixed $default Default value
     * @return mixed
     */
    public function getSetting(string $key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Update module setting
     *
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return void
     */
    public function updateSetting(string $key, $value): void
    {
        $this->settings[$key] = $value;
        update_option("dshop_module_{$this->name}_settings", $this->settings);
    }

    /**
     * Get module path
     *
     * @return string
     */
    public function getPath(): string
    {
        return DSHOP_SRC_DIR . 'modules/' . $this->name . '/';
    }

    /**
     * Get module URL
     *
     * @return string
     */
    public function getUrl(): string
    {
        return DSHOP_PLUGIN_URL . 'src/modules/' . $this->name . '/';
    }
}
