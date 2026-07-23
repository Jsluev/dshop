<?php
/**
 * Analytics Module
 *
 * @package DShop\Modules\Analytics
 */

namespace DShop\Modules\Analytics;

use DShop\Core\BaseModule;

/**
 * Class AnalyticsModule
 *
 * Handles analytics and reporting
 */
class AnalyticsModule extends BaseModule
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name = 'analytics';

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
    protected $description = 'Analytics and reporting module';

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
        }

        // Track product views
        add_action('dshop/product/after_display', [$this, 'trackProductView']);

        // Track purchases
        add_action('dshop/order/created', [$this, 'trackPurchase']);
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
            'Аналитика',
            'Аналитика',
            'manage_options',
            'dshop-analytics',
            [$this, 'renderAnalyticsPage']
        );
    }

    /**
     * Render analytics page
     *
     * @return void
     */
    public function renderAnalyticsPage(): void
    {
        $stats = $this->getStats();
        $recent_orders = $this->getRecentOrders();
        $top_products = $this->getTopProducts();
        $sales_chart = $this->getSalesChartData();

        include DSHOP_SRC_DIR . 'modules/analytics/views/analytics.php';
    }

    /**
     * Get store statistics
     *
     * @return array
     */
    public function getStats(): array
    {
        global $wpdb;

        $orders_table = $wpdb->prefix . 'dshop_orders';

        // Total orders
        $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM {$orders_table}");

        // Total revenue
        $total_revenue = $wpdb->get_var("SELECT SUM(total) FROM {$orders_table} WHERE status != 'cancelled'");

        // Total customers
        $total_customers = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}dshop_customers");

        // Total products (from CPT)
        $products = wp_count_posts('dshop_product');
        $total_products = (int) ($products->publish ?? 0);

        // Orders today
        $orders_today = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$orders_table} WHERE DATE(created_at) = %s",
                current_time('mysql', true)
            )
        );

        // Revenue today
        $revenue_today = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(total) FROM {$orders_table} WHERE DATE(created_at) = %s AND status != 'cancelled'",
                current_time('mysql', true)
            )
        );

        // Average order value
        $avg_order = $wpdb->get_var("SELECT AVG(total) FROM {$orders_table} WHERE status != 'cancelled'");

        return [
            'total_orders' => (int) $total_orders,
            'total_revenue' => (float) $total_revenue,
            'total_customers' => (int) $total_customers,
            'total_products' => $total_products,
            'orders_today' => (int) $orders_today,
            'revenue_today' => (float) $revenue_today,
            'avg_order' => (float) $avg_order,
        ];
    }

    /**
     * Get recent orders
     *
     * @param int $limit Number of orders
     * @return array
     */
    public function getRecentOrders(int $limit = 10): array
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dshop_orders 
                ORDER BY created_at DESC 
                LIMIT %d",
                $limit
            )
        );
    }

    /**
     * Get top products
     *
     * @param int $limit Number of products
     * @return array
     */
    public function getTopProducts(int $limit = 10): array
    {
        global $wpdb;

        $posts_table = $wpdb->prefix . 'posts';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT p.ID as id, p.post_title as name, 
                COALESCE(SUM(oi.quantity), 0) as sales_count,
                COALESCE(SUM(oi.total), 0) as revenue
                FROM {$posts_table} p
                LEFT JOIN {$wpdb->prefix}dshop_order_items oi ON p.ID = oi.product_id
                WHERE p.post_type = 'dshop_product' AND p.post_status = 'publish'
                GROUP BY p.ID
                ORDER BY sales_count DESC
                LIMIT %d",
                $limit
            )
        );
    }

    /**
     * Get sales chart data
     *
     * @param int $days Number of days
     * @return array
     */
    public function getSalesChartData(int $limit_days = 30): array
    {
        global $wpdb;

        $data = [];
        for ($i = $limit_days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $revenue = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT SUM(total) FROM {$wpdb->prefix}dshop_orders 
                    WHERE DATE(created_at) = %s AND status != 'cancelled'",
                    $date
                )
            );
            $orders = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*) FROM {$wpdb->prefix}dshop_orders 
                    WHERE DATE(created_at) = %s",
                    $date
                )
            );
            
            $data[] = [
                'date' => $date,
                'revenue' => (float) $revenue,
                'orders' => (int) $orders,
            ];
        }
        
        return $data;
    }

    /**
     * Track product view
     *
     * @param int $product_id Product ID
     * @return void
     */
    public function trackProductView(int $product_id): void
    {
        global $wpdb;

        // Increment view count via postmeta
        $current = (int) get_post_meta($product_id, '_dshop_views', true);
        update_post_meta($product_id, '_dshop_views', $current + 1);

        // Log view
        $wpdb->insert(
            $wpdb->prefix . 'dshop_logs',
            [
                'level' => 'info',
                'message' => 'Product viewed',
                'context' => wp_json_encode(['product_id' => $product_id]),
                'ip_address' => $this->getClientIp(),
            ]
        );
    }

    /**
     * Track purchase
     *
     * @param int $order_id Order ID
     * @return void
     */
    public function trackPurchase(int $order_id): void
    {
        global $wpdb;

        $order = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dshop_orders WHERE id = %d",
                $order_id
            )
        );

        if (!$order) {
            return;
        }

        // Update product sales count
        $items = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dshop_order_items WHERE order_id = %d",
                $order_id
            )
        );

        foreach ($items as $item) {
            // Increment sales count via postmeta
            $current_sales = (int) get_post_meta($item->product_id, '_dshop_sales_count', true);
            update_post_meta($item->product_id, '_dshop_sales_count', $current_sales + $item->quantity);
        }

        // Log purchase
        $wpdb->insert(
            $wpdb->prefix . 'dshop_logs',
            [
                'level' => 'info',
                'message' => 'Purchase completed',
                'context' => wp_json_encode([
                    'order_id' => $order_id,
                    'total' => $order->total,
                ]),
                'ip_address' => $this->getClientIp(),
            ]
        );
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    private function getClientIp(): string
    {
        $ip_keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($ip_keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = explode(',', $_SERVER[$key]);
                $ip = trim($ip[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Export analytics data
     *
     * @param string $format Export format (csv, json)
     * @param array $params Export parameters
     * @return string
     */
    public function exportData(string $format = 'csv', array $params = []): string
    {
        $data = $this->getRecentOrders($params['limit'] ?? 1000);

        if ($format === 'json') {
            return wp_json_encode($data, JSON_PRETTY_PRINT);
        }

        // CSV format
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Order Number', 'Status', 'Total', 'Date']);

        foreach ($data as $order) {
            fputcsv($output, [
                $order->id,
                $order->order_number,
                $order->status,
                $order->total,
                $order->created_at,
            ]);
        }

        fclose($output);
        return '';
    }
}
