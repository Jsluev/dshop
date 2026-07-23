<?php
/**
 * Module Loader
 *
 * @package DShop\Core
 */

namespace DShop\Core;

/**
 * Class Loader
 *
 * Loads and initializes DShop modules
 */
class Loader
{
    /**
     * DShop instance
     *
     * @var DShop
     */
    private $app;

    /**
     * Module classes map
     *
     * @var array
     */
    private $moduleClasses = [
        'catalog' => \DShop\Modules\Catalog\CatalogModule::class,
        'cart' => \DShop\Modules\Cart\CartModule::class,
        'checkout' => \DShop\Modules\Checkout\CheckoutModule::class,
        'payment' => \DShop\Modules\Payment\PaymentModule::class,
        'shipping' => \DShop\Modules\Shipping\ShippingModule::class,
        'inventory' => \DShop\Modules\Inventory\InventoryModule::class,
        'crm' => \DShop\Modules\Crm\CrmModule::class,
        'discounts' => \DShop\Modules\Discounts\DiscountsModule::class,
        'email' => \DShop\Modules\Email\EmailModule::class,
        'analytics' => \DShop\Modules\Analytics\AnalyticsModule::class,
        'seo' => \DShop\Modules\Seo\SeoModule::class,
        'notifications' => \DShop\Modules\Notifications\NotificationsModule::class,
        'crm_integration' => \DShop\Modules\CrmIntegration\IntegrationModule::class,
    ];

    /**
     * Constructor
     *
     * @param DShop $app DShop instance
     */
    public function __construct(DShop $app)
    {
        $this->app = $app;
    }

    /**
     * Load all enabled modules
     *
     * @return array
     */
    public function loadModules(): array
    {
        $modules = [];
        $enabled_modules = $this->getEnabledModules();

        foreach ($enabled_modules as $name) {
            $module = $this->loadModule($name);
            if ($module) {
                $modules[$name] = $module;
            }
        }

        return $modules;
    }

    /**
     * Load a single module
     *
     * @param string $name Module name
     * @return BaseModule|null
     */
    public function loadModule(string $name)
    {
        if (!isset($this->moduleClasses[$name])) {
            $this->getLogger()->warning("Module class not found: {$name}");
            return null;
        }

        $class = $this->moduleClasses[$name];

        if (!class_exists($class)) {
            $this->getLogger()->warning("Module class does not exist: {$class}");
            return null;
        }

        $module = new $class($this->app);

        // Check dependencies
        if (!$this->checkDependencies($module)) {
            $this->getLogger()->warning("Module dependencies not met: {$name}");
            return null;
        }

        return $module;
    }

    /**
     * Check module dependencies
     *
     * @param BaseModule $module Module to check
     * @return bool
     */
    private function checkDependencies(BaseModule $module): bool
    {
        $dependencies = $module->getDependencies();
        $enabled = $this->getEnabledModules();

        foreach ($dependencies as $dependency) {
            if (!in_array($dependency, $enabled, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get list of enabled modules
     *
     * @return array
     */
    private function getEnabledModules(): array
    {
        $defaults = array_keys($this->moduleClasses);
        return apply_filters('dshop/enabled_modules', $defaults);
    }

    /**
     * Get logger instance
     *
     * @return Logger
     */
    private function getLogger(): Logger
    {
        return $this->app->getLogger();
    }

    /**
     * Register a new module class
     *
     * @param string $name Module name
     * @param string $class Module class name
     * @return void
     */
    public function registerModuleClass(string $name, string $class): void
    {
        $this->moduleClasses[$name] = $class;
    }

    /**
     * Get all available module classes
     *
     * @return array
     */
    public function getModuleClasses(): array
    {
        return $this->moduleClasses;
    }
}
