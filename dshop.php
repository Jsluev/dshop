<?php
/**
 * Plugin Name: DShop
 * Plugin URI: https://example.com/dshop
 * Description: Modular e-commerce plugin for WordPress
 * Version: 1.0.0
 * Author: DShop Team
 * Author URI: https://example.com
 * Text Domain: dshop
 * Domain Path: /languages
 * Requires at least: 5.9
 * Requires PHP: 7.4
 * License: Proprietary
 * License URI: https://example.com/license
 */

defined('ABSPATH') || exit;

/**
 * Plugin constants
 */
define('DSHOP_VERSION', '1.0.0');
define('DSHOP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DSHOP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DSHOP_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('DSHOP_SRC_DIR', DSHOP_PLUGIN_DIR . 'src/');
define('DSHOP_INCLUDES_DIR', DSHOP_PLUGIN_DIR . 'src/includes/');
define('DSHOP_TEMPLATE_DIR', DSHOP_PLUGIN_DIR . 'src/templates/');
define('DSHOP_ASSETS_URL', DSHOP_PLUGIN_URL . 'src/assets/');

/**
 * Check PHP version
 */
if (version_compare(PHP_VERSION, '7.4', '<')) {
    add_action('admin_notices', function() {
        echo '<div class="error"><p>';
        echo 'DShop требует PHP 7.4 или выше. Пожалуйста, обновите PHP.';
        echo '</p></div>';
    });
    return;
}

/**
 * Load autoloader
 */
$autoload_file = DSHOP_PLUGIN_DIR . 'vendor/autoload.php';
if (file_exists($autoload_file)) {
    require_once $autoload_file;
} else {
    /**
     * Fallback autoloader if Composer not installed
     */
    spl_autoload_register(function($class) {
        $prefix = 'DShop\\';
        $base_dir = DSHOP_SRC_DIR;

        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }

        $relative_class = substr($class, $len);
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require $file;
        }
    });
}

// Load helpers
require_once DSHOP_SRC_DIR . 'helpers.php';

/**
 * Initialize plugin
 */
add_action('plugins_loaded', function() {
    \DShop\Core\DShop::getInstance()->boot();
});

/**
 * Plugin activation
 */
register_activation_hook(__FILE__, function() {
    \DShop\Core\DShop::getInstance()->activate();
});

/**
 * Plugin deactivation
 */
register_deactivation_hook(__FILE__, function() {
    \DShop\Core\DShop::getInstance()->deactivate();
});

/**
 * Plugin uninstall
 */
register_uninstall_hook(__FILE__, ['\DShop\Core\DShop', 'uninstallStatic']);
