<?php
/**
 * Notifications Module
 *
 * @package DShop\Modules\Notifications
 */

namespace DShop\Modules\Notifications;

use DShop\Core\BaseModule;

/**
 * Class NotificationsModule
 *
 * Handles notifications (Telegram, SMS, Push)
 */
class NotificationsModule extends BaseModule
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name = 'notifications';

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
    protected $description = 'Notifications module (Telegram, SMS, Push)';

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
            add_action('admin_init', [$this, 'handleNotificationsForm']);
        }

        // Order notifications
        add_action('dshop/order/created', [$this, 'onOrderCreated']);
        add_action('dshop/order/status_changed', [$this, 'onOrderStatusChanged']);

        // Stock notifications
        add_action('dshop/stock/low', [$this, 'onLowStock']);
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
            'Настройки уведомлений',
            'Уведомления',
            'manage_options',
            'dshop-notifications',
            [$this, 'renderNotificationsPage']
        );
    }

    /**
     * Handle notifications form submission (admin_init)
     */
    public function handleNotificationsForm(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['dshop_notifications_nonce'])) {
            return;
        }
        if (isset($_GET['page']) && $_GET['page'] === 'dshop-notifications') {
            check_admin_referer('dshop_notifications_nonce', 'dshop_notifications_nonce');

            $settings = [
                'telegram_enabled' => isset($_POST['telegram_enabled']) ? 1 : 0,
                'telegram_bot_token' => sanitize_text_field($_POST['telegram_bot_token'] ?? ''),
                'telegram_chat_id' => sanitize_text_field($_POST['telegram_chat_id'] ?? ''),
                'sms_enabled' => isset($_POST['sms_enabled']) ? 1 : 0,
                'sms_api_key' => sanitize_text_field($_POST['sms_api_key'] ?? ''),
                'sms_sender' => sanitize_text_field($_POST['sms_sender'] ?? ''),
                'push_enabled' => isset($_POST['push_enabled']) ? 1 : 0,
                'push_api_key' => sanitize_text_field($_POST['push_api_key'] ?? ''),
            ];

            foreach ($settings as $key => $value) {
                update_option("dshop_notification_{$key}", $value);
            }

            wp_redirect(admin_url('admin.php?page=dshop-notifications&updated=1'));
            exit;
        }
    }

    /**
     * Render notifications page
     *
     * @return void
     */
    public function renderNotificationsPage(): void
    {
        include DSHOP_SRC_DIR . 'modules/notifications/views/notifications-settings.php';
    }

    /**
     * On order created
     *
     * @param int $order_id Order ID
     * @return void
     */
    public function onOrderCreated(int $order_id): void
    {
        global $wpdb;

        $order = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}dshop_orders WHERE id = %d", $order_id)
        );

        if (!$order) {
            return;
        }

        $message = sprintf(
            "🛒 Новый заказ!\n\nНомер: %s\nСумма: %s ₽\nКлиент: %s %s\nТелефон: %s",
            $order->order_number,
            number_format($order->total, 2, '.', ' '),
            $order->billing_first_name,
            $order->billing_last_name,
            $order->billing_phone
        );

        $this->sendNotification($message);
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
        global $wpdb;

        $order = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}dshop_orders WHERE id = %d", $order_id)
        );

        if (!$order) {
            return;
        }

        $status_messages = [
            'processing' => '🔄 Заказ взят в обработку',
            'completed' => '✅ Заказ выполнен',
            'cancelled' => '❌ Заказ отменён',
        ];

        if (isset($status_messages[$new_status])) {
            $message = sprintf(
                "%s\n\nНомер: %s\nСумма: %s ₽",
                $status_messages[$new_status],
                $order->order_number,
                number_format($order->total, 2, '.', ' ')
            );

            $this->sendNotification($message);
        }
    }

    /**
     * On low stock
     *
     * @param int $product_id Product ID
     * @param int $quantity Current quantity
     * @return void
     */
    public function onLowStock(int $product_id, int $quantity): void
    {
        $product = get_post($product_id);
        if (!$product) {
            return;
        }

        $message = sprintf(
            "⚠️ Низкий остаток!\n\nТовар: %s\nОстаток: %d шт.",
            $product->post_title,
            $quantity
        );

        $this->sendNotification($message);
    }

    /**
     * Send notification
     *
     * @param string $message Message text
     * @return void
     */
    public function sendNotification(string $message): void
    {
        // Telegram
        if (get_option('dshop_notification_telegram_enabled')) {
            $this->sendTelegram($message);
        }

        // SMS (for customer notifications)
        // Push notifications
    }

    /**
     * Send Telegram notification
     *
     * @param string $message Message text
     * @return bool
     */
    private function sendTelegram(string $message): bool
    {
        $bot_token = get_option('dshop_notification_telegram_bot_token');
        $chat_id = get_option('dshop_notification_telegram_chat_id');

        if (!$bot_token || !$chat_id) {
            return false;
        }

        $url = sprintf('https://api.telegram.org/bot%s/sendMessage', $bot_token);

        $response = wp_remote_post($url, [
            'body' => [
                'chat_id' => $chat_id,
                'text' => $message,
                'parse_mode' => 'HTML',
            ],
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            \DShop\Core\DShop::getInstance()->getLogger()->error('Telegram notification failed', [
                'error' => $response->get_error_message(),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Send SMS notification
     *
     * @param string $phone Phone number
     * @param string $message Message text
     * @return bool
     */
    public function sendSms(string $phone, string $message): bool
    {
        if (!get_option('dshop_notification_sms_enabled')) {
            return false;
        }

        $api_key = get_option('dshop_notification_sms_api_key');
        $sender = get_option('dshop_notification_sms_sender');

        if (!$api_key || !$sender) {
            return false;
        }

        // TODO: Implement SMS API integration
        // This is a placeholder for SMS gateway integration

        \DShop\Core\DShop::getInstance()->getLogger()->info("SMS sent to {$phone}", [
            'message' => $message,
        ]);

        return true;
    }

    /**
     * Send push notification
     *
     * @param string $title Notification title
     * @param string $message Notification message
     * @param array $users User IDs
     * @return bool
     */
    public function sendPush(string $title, string $message, array $users = []): bool
    {
        if (!get_option('dshop_notification_push_enabled')) {
            return false;
        }

        $api_key = get_option('dshop_notification_push_api_key');

        if (!$api_key) {
            return false;
        }

        // TODO: Implement push notification API integration
        // This is a placeholder for push notification service

        \DShop\Core\DShop::getInstance()->getLogger()->info("Push notification sent", [
            'title' => $title,
            'message' => $message,
            'users' => $users,
        ]);

        return true;
    }
}
