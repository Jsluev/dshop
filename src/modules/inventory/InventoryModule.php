<?php
/**
 * Inventory Module
 *
 * @package DShop\Modules\Inventory
 */

namespace DShop\Modules\Inventory;

use DShop\Core\BaseModule;

/**
 * Class InventoryModule
 *
 * Handles inventory management
 */
class InventoryModule extends BaseModule
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name = 'inventory';

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
    protected $description = 'Inventory management module';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerHooks(): void
    {
        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', [$this, 'addAdminMenus']);
            add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
        }

        // Order status change
        add_action('dshop/order/status_changed', [$this, 'onOrderStatusChanged'], 10, 3);
    }

    /**
     * Add admin menus
     *
     * @return void
     */
    public function addAdminMenus(): void
    {
        add_submenu_page(
            'dshop',
            'Склады и остатки',
            'Склады',
            'manage_options',
            'dshop-warehouses',
            [$this, 'renderWarehousesPage']
        );
    }

    /**
     * Add meta boxes
     *
     * @return void
     */
    public function addMetaBoxes(): void
    {
        add_meta_box(
            'dshop_inventory_data',
            'Данные по складу',
            [$this, 'renderInventoryMetabox'],
            'dshop_product',
            'side',
            'default'
        );
    }

    /**
     * Render inventory metabox
     *
     * @param \WP_Post $post Post object
     * @return void
     */
    public function renderInventoryMetabox(\WP_Post $post): void
    {
        $manage_stock = get_post_meta($post->ID, '_dshop_manage_stock', true);
        $stock_quantity = get_post_meta($post->ID, '_dshop_stock_quantity', true);
        $low_stock_threshold = get_option('dshop_low_stock_threshold', 5);
        ?>
        <div class="dshop-inventory-metabox">
            <p>
                <label>
                    <input type="checkbox" name="dshop_manage_stock" value="1" <?php checked($manage_stock, 1); ?>>
                    Учёт остатков
                </label>
            </p>
            <p>
                <label for="dshop_stock_quantity">Количество на складе:</label>
                <input type="number" id="dshop_stock_quantity" name="dshop_stock_quantity" value="<?php echo esc_attr($stock_quantity); ?>" min="0" class="small-text">
            </p>
            <p class="description">
                <?php printf('Порог низкого остатка: %d', $low_stock_threshold); ?>
            </p>
            <?php if ($manage_stock && $stock_quantity <= $low_stock_threshold): ?>
                <p class="dshop-warning">
                    Мало на складе!
                </p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render warehouses page
     *
     * @return void
     */
    public function renderWarehousesPage(): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'dshop_warehouses';
        $warehouses = $wpdb->get_results("SELECT * FROM {$table} ORDER BY priority, name");

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dshop_warehouse_nonce'])) {
            check_admin_referer('dshop_warehouse_nonce', 'dshop_warehouse_nonce');
            
            $name = sanitize_text_field($_POST['name'] ?? '');
            $address = sanitize_text_field($_POST['address'] ?? '');
            $phone = sanitize_text_field($_POST['phone'] ?? '');
            $email = sanitize_email($_POST['email'] ?? '');
            $is_default = isset($_POST['is_default']) ? 1 : 0;
            $priority = absint($_POST['priority'] ?? 0);

            if ($name) {
                if (isset($_POST['warehouse_id'])) {
                    $wpdb->update(
                        $table,
                        [
                            'name' => $name,
                            'address' => $address,
                            'phone' => $phone,
                            'email' => $email,
                            'is_default' => $is_default,
                            'priority' => $priority,
                        ],
                        ['id' => absint($_POST['warehouse_id'])]
                    );
                } else {
                    $wpdb->insert(
                        $table,
                        [
                            'name' => $name,
                            'address' => $address,
                            'phone' => $phone,
                            'email' => $email,
                            'is_default' => $is_default,
                            'priority' => $priority,
                        ]
                    );
                }
                
                wp_redirect(admin_url('admin.php?page=dshop-warehouses&updated=1'));
                exit;
            }
        }

        include DSHOP_SRC_DIR . 'modules/inventory/views/warehouses.php';
    }

    /**
     * On order status changed
     *
     * @param int $order_id Order ID
     * @param string $new_status New status
     * @param string $old_status Old status
     * @return void
     */
    public function onOrderStatusChanged(int $order_id, string $new_status, string $old_status): void
    {
        if ($new_status === 'processing' && $old_status !== 'processing') {
            $this->reserveStock($order_id);
        } elseif ($new_status === 'completed') {
            $this->deductStock($order_id);
        } elseif ($new_status === 'cancelled' && $old_status === 'processing') {
            $this->releaseStock($order_id);
        }
    }

    /**
     * Reserve stock for order
     *
     * @param int $order_id Order ID
     * @return void
     */
    private function reserveStock(int $order_id): void
    {
        global $wpdb;

        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dshop_order_items WHERE order_id = %d",
                $order_id
            )
        );

        foreach ($items as $item) {
            $this->updateStockQuantity($item->product_id, $item->quantity, 'reserve', $order_id);
        }
    }

    /**
     * Deduct stock for order
     *
     * @param int $order_id Order ID
     * @return void
     */
    private function deductStock(int $order_id): void
    {
        global $wpdb;

        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dshop_order_items WHERE order_id = %d",
                $order_id
            )
        );

        foreach ($items as $item) {
            $this->updateStockQuantity($item->product_id, $item->quantity, 'deduct', $order_id);
        }
    }

    /**
     * Release stock for cancelled order
     *
     * @param int $order_id Order ID
     * @return void
     */
    private function releaseStock(int $order_id): void
    {
        global $wpdb;

        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dshop_order_items WHERE order_id = %d",
                $order_id
            )
        );

        foreach ($items as $item) {
            $this->updateStockQuantity($item->product_id, $item->quantity, 'release', $order_id);
        }
    }

    /**
     * Update stock quantity
     *
     * @param int $product_id Product ID
     * @param int $quantity Quantity
     * @param string $action Action type
     * @param int $order_id Order ID
     * @return void
     */
    private function updateStockQuantity(int $product_id, int $quantity, string $action, int $order_id): void
    {
        global $wpdb;

        $current_stock = (int) get_post_meta($product_id, '_dshop_stock_quantity', true);
        $manage_stock = (bool) get_post_meta($product_id, '_dshop_manage_stock', true);

        if (!$manage_stock) {
            return;
        }

        switch ($action) {
            case 'reserve':
            case 'deduct':
                $new_stock = $current_stock - $quantity;
                break;
            case 'release':
                $new_stock = $current_stock + $quantity;
                break;
            default:
                return;
        }

        $new_stock = max(0, $new_stock);
        update_post_meta($product_id, '_dshop_stock_quantity', $new_stock);

        // Log the change
        $wpdb->insert(
            $wpdb->prefix . 'dshop_stock_log',
            [
                'product_id' => $product_id,
                'warehouse_id' => 1, // Default warehouse
                'quantity_change' => -$quantity,
                'reason' => $action,
                'order_id' => $order_id,
                'user_id' => get_current_user_id(),
            ]
        );

        // Check for low stock notification
        $low_stock_threshold = get_option('dshop_low_stock_threshold', 5);
        if ($new_stock <= $low_stock_threshold) {
            $this->sendLowStockNotification($product_id, $new_stock);
        }
    }

    /**
     * Send low stock notification
     *
     * @param int $product_id Product ID
     * @param int $quantity Current quantity
     * @return void
     */
    private function sendLowStockNotification(int $product_id, int $quantity): void
    {
        $product = get_post($product_id);
        if (!$product) {
            return;
        }

        $admin_email = get_option('dshop_admin_email', get_option('admin_email'));
        $subject = sprintf('Мало на складе: %s', $product->post_title);
        $message = sprintf(
            "Товар «%s» заканчивается на складе.\n\nТекущее количество: %d\n\nПожалуйста, пополните запасы.",
            $product->post_title,
            $quantity
        );

        wp_mail($admin_email, $subject, $message);
    }
}
