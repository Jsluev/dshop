<?php
/**
 * Email Module
 *
 * @package DShop\Modules\Email
 */

namespace DShop\Modules\Email;

use DShop\Core\BaseModule;

/**
 * Class EmailModule
 *
 * Handles email marketing and transactional emails
 */
class EmailModule extends BaseModule
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name = 'email';

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
    protected $description = 'Email marketing and transactional emails module';

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
            add_action('admin_init', [$this, 'handleEmailSettingsForm']);
            add_action('admin_init', [$this, 'handleEmailTemplateForm']);
        }

        // Order status emails
        add_action('dshop/order/created', [$this, 'onOrderCreated'], 10, 2);
        add_action('dshop/order/status_changed', [$this, 'onOrderStatusChanged'], 10, 3);

        // Customer registration email
        add_action('dshop/customer/registered', [$this, 'onCustomerRegistered']);
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
            'Настройки email',
            'Email',
            'manage_options',
            'dshop-email-settings',
            [$this, 'renderEmailSettingsPage']
        );

        add_submenu_page(
            'dshop',
            'Шаблоны email',
            'Шаблоны email',
            'manage_options',
            'dshop-email-templates',
            [$this, 'renderEmailTemplatesPage']
        );
    }

    /**
     * Handle email settings form submission (admin_init)
     */
    public function handleEmailSettingsForm(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['dshop_email_settings_nonce'])) {
            return;
        }
        if (isset($_GET['page']) && $_GET['page'] === 'dshop-email-settings') {
            check_admin_referer('dshop_email_settings_nonce', 'dshop_email_settings_nonce');

            $settings = [
                'from_name' => sanitize_text_field($_POST['from_name'] ?? ''),
                'from_email' => sanitize_email($_POST['from_email'] ?? ''),
                'admin_email' => sanitize_email($_POST['admin_email'] ?? ''),
                'reply_to' => sanitize_email($_POST['reply_to'] ?? ''),
            ];

            foreach ($settings as $key => $value) {
                update_option("dshop_email_{$key}", $value);
            }

            wp_redirect(admin_url('admin.php?page=dshop-email-settings&updated=1'));
            exit;
        }
    }

    /**
     * Handle email template form submission (admin_init)
     */
    public function handleEmailTemplateForm(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['dshop_email_template_nonce'])) {
            return;
        }
        if (isset($_GET['page']) && $_GET['page'] === 'dshop-email-templates') {
            check_admin_referer('dshop_email_template_nonce', 'dshop_email_template_nonce');

            $template = sanitize_text_field($_POST['template'] ?? '');
            $subject = sanitize_text_field($_POST['subject'] ?? '');
            $body = wp_kses_post($_POST['body'] ?? '');

            if ($template) {
                update_option("dshop_email_template_{$template}_subject", $subject);
                update_option("dshop_email_template_{$template}_body", $body);
            }

            wp_redirect(admin_url('admin.php?page=dshop-email-templates&updated=1'));
            exit;
        }
    }

    /**
     * Render email settings page
     *
     * @return void
     */
    public function renderEmailSettingsPage(): void
    {
        include DSHOP_SRC_DIR . 'modules/email/views/email-settings.php';
    }

    /**
     * Render email templates page
     *
     * @return void
     */
    public function renderEmailTemplatesPage(): void
    {
        $templates = [
            'order_confirmation' => 'Подтверждение заказа',
            'order_processing' => 'Заказ в обработке',
            'order_completed' => 'Заказ выполнен',
            'order_cancelled' => 'Заказ отменён',
            'customer_welcome' => 'Приветствие нового клиента',
            'password_reset' => 'Сброс пароля',
            'low_stock' => 'Уведомление о низком остатке',
        ];

        include DSHOP_SRC_DIR . 'modules/email/views/email-templates.php';
    }

    /**
     * On order created
     *
     * @param int $order_id Order ID
     * @param array $data Order data
     * @return void
     */
    public function onOrderCreated(int $order_id, array $data): void
    {
        global $wpdb;

        $order = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}dshop_orders WHERE id = %d", $order_id)
        );

        if (!$order) {
            return;
        }

        $this->sendEmail($order->billing_email, sprintf('Подтверждение заказа #%s', $order->order_number), 'order_confirmation', [
            'order' => $order,
        ]);
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

        $template = null;
        $subject = '';

        switch ($new_status) {
            case 'processing':
                $template = 'order_processing';
                $subject = sprintf('Ваш заказ #%s в обработке', $order->order_number);
                break;
            case 'completed':
                $template = 'order_completed';
                $subject = sprintf('Ваш заказ #%s выполнен', $order->order_number);
                break;
            case 'cancelled':
                $template = 'order_cancelled';
                $subject = sprintf('Ваш заказ #%s отменён', $order->order_number);
                break;
        }

        if ($template) {
            $this->sendEmail($order->billing_email, $subject, $template, [
                'order' => $order,
            ]);
        }
    }

    /**
     * On customer registered
     *
     * @param object $customer Customer object
     * @return void
     */
    public function onCustomerRegistered(object $customer): void
    {
        $subject = 'Добро пожаловать в наш магазин!';
        $this->sendEmail($customer->email, $subject, 'customer_welcome', [
            'customer' => $customer,
        ]);
    }

    /**
     * Send email
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $template Template name
     * @param array $data Template data
     * @return bool
     */
    public function sendEmail(string $to, string $subject, string $template, array $data = []): bool
    {
        $from_name = get_option('dshop_email_from_name', get_bloginfo('name'));
        $from_email = get_option('dshop_email_from_email', get_option('admin_email'));

        // Get template
        $body = $this->getTemplate($template, $data);

        // Set headers
        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            "From: {$from_name} <{$from_email}>",
        ];

        $reply_to = get_option('dshop_email_reply_to');
        if ($reply_to) {
            $headers[] = "Reply-To: {$reply_to}";
        }

        // Send email
        $sent = wp_mail($to, $subject, $body, $headers);

        // Log email
        \DShop\Core\DShop::getInstance()->getLogger()->info("Email sent to {$to}", [
            'subject' => $subject,
            'template' => $template,
            'success' => $sent,
        ]);

        return $sent;
    }

    /**
     * Get email template
     *
     * @param string $template Template name
     * @param array $data Template data
     * @return string
     */
    private function getTemplate(string $template, array $data = []): string
    {
        // Check for custom template
        $custom_template = get_template_directory() . "/dshop/emails/{$template}.php";
        if (file_exists($custom_template)) {
            ob_start();
            extract($data, EXTR_SKIP);
            include $custom_template;
            return ob_get_clean();
        }

        // Use default template
        $default_template = DSHOP_SRC_DIR . "modules/email/views/emails/{$template}.php";
        if (file_exists($default_template)) {
            ob_start();
            extract($data, EXTR_SKIP);
            include $default_template;
            return ob_get_clean();
        }

        // Fallback
        return '<p>Email template not found: ' . esc_html($template) . '</p>';
    }

    /**
     * Send order confirmation email
     *
     * @param int $order_id Order ID
     * @return bool
     */
    public function sendOrderConfirmation(int $order_id): bool
    {
        global $wpdb;

        $order = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}dshop_orders WHERE id = %d", $order_id)
        );

        if (!$order) {
            return false;
        }

        $subject = sprintf('Подтверждение заказа #%s', $order->order_number);
        return $this->sendEmail($order->billing_email, $subject, 'order_confirmation', [
            'order' => $order,
        ]);
    }

    /**
     * Send newsletter
     *
     * @param string $subject Newsletter subject
     * @param string $content Newsletter content
     * @param array $recipients Recipient emails
     * @return int Number of emails sent
     */
    public function sendNewsletter(string $subject, string $content, array $recipients): int
    {
        $sent_count = 0;

        foreach ($recipients as $email) {
            if ($this->sendEmail($email, $subject, 'newsletter', ['content' => $content])) {
                $sent_count++;
            }
        }

        return $sent_count;
    }

    /**
     * Get email logs
     *
     * @param int $limit Number of logs to retrieve
     * @return array
     */
    public function getLogs(int $limit = 100): array
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dshop_logs 
                WHERE message LIKE %s 
                ORDER BY created_at DESC 
                LIMIT %d",
                '%Email sent%',
                $limit
            )
        );
    }
}
