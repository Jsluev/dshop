<?php
/**
 * DShop Main Class
 *
 * @package DShop\Core
 */

namespace DShop\Core;

/**
 * Class DShop
 *
 * Main plugin class (Singleton)
 */
class DShop
{
    /**
     * Single instance
     *
     * @var DShop|null
     */
    private static $instance = null;

    /**
     * Loaded modules
     *
     * @var array
     */
    private $modules = [];

    /**
     * Hooks manager
     *
     * @var Hooks
     */
    private $hooks;

    /**
     * Database manager
     *
     * @var Database
     */
    private $database;

    /**
     * Cache manager
     *
     * @var Cache
     */
    private $cache;

    /**
     * Logger
     *
     * @var Logger
     */
    private $logger;

    /**
     * Config
     *
     * @var Config
     */
    private $config;

    /**
     * Module loader
     *
     * @var Loader
     */
    private $loader;

    /**
     * Plugin booted
     *
     * @var bool
     */
    private $booted = false;

    /**
     * Get single instance
     *
     * @return DShop
     */
    public static function getInstance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->initServices();
    }

    /**
     * Initialize core services
     *
     * @return void
     */
    private function initServices(): void
    {
        $this->config = new Config();
        $this->hooks = new Hooks();
        $this->database = new Database();
        $this->cache = new Cache();
        $this->logger = new Logger();
        $this->loader = new Loader($this);
    }

    /**
     * Boot the plugin
     *
     * @return void
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }

        // Load modules
        $this->modules = $this->loader->loadModules();

        // Initialize modules
        $this->initModules();

        // Register global hooks
        $this->registerHooks();

        $this->booted = true;

        do_action('dshop/booted');
    }

    /**
     * Initialize all loaded modules
     *
     * @return void
     */
    private function initModules(): void
    {
        foreach ($this->modules as $module) {
            $module->init();
            $module->registerHooks();
        }
    }

    /**
     * Register global hooks
     *
     * @return void
     */
    private function registerHooks(): void
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueueFrontendAssets']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAdminAssets']);
        add_action('admin_menu', [$this, 'registerAdminMenu'], 1);
        add_action('widgets_init', [$this, 'registerWidgets']);
    }

    public function registerAdminMenu(): void
    {
        add_menu_page(
            'DShop',
            'DShop',
            'manage_options',
            'dshop',
            [$this, 'renderDashboardPage'],
            'dashicons-cart',
            56
        );

        add_submenu_page(
            'dshop',
            'DShop — Обзор',
            'Обзор',
            'manage_options',
            'dshop',
            [$this, 'renderDashboardPage']
        );
    }

    public function renderDashboardPage(): void
    {
        echo '<div class="wrap">';
        echo '<h1>DShop</h1>';
        echo '<p>Добро пожаловать в DShop — модульную систему электронной коммерции.</p>';

        $modules = $this->getModules();
        if (!empty($modules)) {
            echo '<h2>Загруженные модули</h2>';
            echo '<table class="widefat fixed" style="max-width:800px">';
            echo '<thead><tr><th>Модуль</th><th>Статус</th></tr></thead><tbody>';
            foreach ($modules as $name => $module) {
                $active = $module->isActive();
                $status = $active ? '<span style="color:green">Активен</span>' : '<span style="color:red">Отключён</span>';
                echo '<tr><td>' . esc_html($name) . '</td><td>' . $status . '</td></tr>';
            }
            echo '</tbody></table>';
        }

        echo '<p><a href="' . admin_url('admin.php?page=dshop-analytics') . '" class="button button-primary">Аналитика</a> ';
        echo '<a href="' . admin_url('edit.php?post_type=dshop_product') . '" class="button">Товары</a> ';
        echo '<a href="' . admin_url('edit.php?post_type=dshop_order') . '" class="button">Заказы</a></p>';
        echo '</div>';
    }

    /**
     * Enqueue frontend assets
     *
     * @return void
     */
    public function enqueueFrontendAssets(): void
    {
        wp_enqueue_style(
            'dshop-front',
            DSHOP_ASSETS_URL . 'css/front.css',
            [],
            DSHOP_VERSION
        );

        wp_enqueue_script(
            'dshop-front',
            DSHOP_ASSETS_URL . 'js/front.js',
            ['jquery'],
            DSHOP_VERSION,
            true
        );

        wp_localize_script('dshop-front', 'dshop', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dshop_nonce'),
            'version' => DSHOP_VERSION,
        ]);
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page
     * @return void
     */
    public function enqueueAdminAssets(string $hook): void
    {
        wp_enqueue_style(
            'dshop-admin',
            DSHOP_ASSETS_URL . 'css/admin.css',
            [],
            DSHOP_VERSION
        );

        wp_enqueue_script(
            'dshop-admin',
            DSHOP_ASSETS_URL . 'js/admin.js',
            ['jquery', 'wp-util'],
            DSHOP_VERSION,
            true
        );

        wp_localize_script('dshop-admin', 'dshop_admin', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dshop_admin_nonce'),
            'version' => DSHOP_VERSION,
        ]);
    }

    /**
     * Register custom post types
     *
     * @return void
     */
    public function registerPostTypes(): void
    {
        do_action('dshop/register_post_types');
    }

    /**
     * Register custom taxonomies
     *
     * @return void
     */
    public function registerTaxonomies(): void
    {
        do_action('dshop/register_taxonomies');
    }

    /**
     * Register widgets
     *
     * @return void
     */
    public function registerWidgets(): void
    {
        do_action('dshop/register_widgets');
    }

    /**
     * Handle AJAX requests
     *
     * @return void
     */
    public function handleAjax(): void
    {
        check_ajax_referer('dshop_nonce', 'nonce');

        $action = isset($_POST['dshop_action']) ? sanitize_text_field($_POST['dshop_action']) : '';

        if (empty($action)) {
            wp_send_json_error(['message' => 'No action specified']);
        }

        do_action("dshop/ajax/{$action}");
    }

    /**
     * Plugin activation
     *
     * @return void
     */
    public function activate(): void
    {
        // Create database tables
        $this->database->createTables();

        // Set default options
        $this->setDefaults();

        // Activate modules
        foreach ($this->modules as $module) {
            $module->activate();
        }

        // Flush rewrite rules
        flush_rewrite_rules();

        do_action('dshop/activated');
    }

    /**
     * Plugin deactivation
     *
     * @return void
     */
    public function deactivate(): void
    {
        // Deactivate modules
        foreach ($this->modules as $module) {
            $module->deactivate();
        }

        // Flush rewrite rules
        flush_rewrite_rules();

        do_action('dshop/deactivated');
    }

    /**
     * Plugin uninstall
     *
     * @return void
     */
    public function uninstall(): void
    {
        $this->database->dropTables();
        delete_option('dshop_settings');
        delete_option('dshop_modules');
        do_action('dshop/uninstalled');
    }

    public static function uninstallStatic(): void
    {
        global $wpdb;
        $prefix = $wpdb->prefix . 'dshop_';
        $tables = [
            'products', 'product_variants', 'categories', 'attributes',
            'orders', 'order_items', 'customers', 'coupons',
            'warehouses', 'stock', 'stock_log', 'reviews', 'logs', 'customer_groups',
        ];
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$prefix}{$table}");
        }
        delete_option('dshop_settings');
        delete_option('dshop_modules');
    }

    /**
     * Set default options
     *
     * @return void
     */
    private function setDefaults(): void
    {
        $defaults = [
            'currency' => 'RUB',
            'tax_rate' => 0,
            'weight_unit' => 'kg',
            'distance_unit' => 'm',
            'products_per_page' => 12,
            'enable_reviews' => true,
            'enable_wishlist' => false,
            'manage_stock' => true,
            'low_stock_threshold' => 5,
        ];

        foreach ($defaults as $key => $value) {
            if (false === get_option("dshop_{$key}")) {
                update_option("dshop_{$key}", $value);
            }
        }
    }

    /**
     * Get module by name
     *
     * @param string $name Module name
     * @return \DShop\Core\BaseModule|null
     */
    public function getModule(string $name)
    {
        return $this->modules[$name] ?? null;
    }

    /**
     * Get all loaded modules
     *
     * @return array
     */
    public function getModules(): array
    {
        return $this->modules;
    }

    /**
     * Check if module is loaded
     *
     * @param string $name Module name
     * @return bool
     */
    public function hasModule(string $name): bool
    {
        return isset($this->modules[$name]);
    }

    /**
     * Get hooks manager
     *
     * @return Hooks
     */
    public function getHooks(): Hooks
    {
        return $this->hooks;
    }

    /**
     * Get database manager
     *
     * @return Database
     */
    public function getDatabase(): Database
    {
        return $this->database;
    }

    /**
     * Get cache manager
     *
     * @return Cache
     */
    public function getCache(): Cache
    {
        return $this->cache;
    }

    /**
     * Get logger
     *
     * @return Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * Get config
     *
     * @return Config
     */
    public function getConfig(): Config
    {
        return $this->config;
    }

    /**
     * Check if plugin is booted
     *
     * @return bool
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }
}
