<?php
/**
 * CRM Integration Module
 *
 * @package DShop\Modules\CrmIntegration
 */

namespace DShop\Modules\CrmIntegration;

use DShop\Core\BaseModule;

/**
 * Class IntegrationModule
 *
 * Handles CRM integrations (AmoCRM, Bitrix24, HubSpot)
 */
class IntegrationModule extends BaseModule
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name = 'crm_integration';

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
    protected $description = 'CRM integrations module';

    /**
     * Available CRM integrations
     *
     * @var array
     */
    private $integrations = [];

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
        $this->loadIntegrations();
    }

    /**
     * {@inheritdoc}
     */
    public function registerHooks(): void
    {
        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', [$this, 'addAdminMenus']);
            add_action('admin_init', [$this, 'handleCrmForm']);
        }

        // Order sync
        add_action('dshop/order/created', [$this, 'syncOrderToCrm']);
        add_action('dshop/order/status_changed', [$this, 'syncOrderStatusToCrm']);
    }

    /**
     * Load CRM integrations
     *
     * @return void
     */
    private function loadIntegrations(): void
    {
        $this->integrations = [
            'amocrm' => new AmoCRM(),
            'bitrix24' => new Bitrix24(),
        ];

        foreach ($this->integrations as $integration) {
            if ($integration->isActive()) {
                $integration->init();
            }
        }
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
            'Интеграции с CRM',
            'Интеграции',
            'manage_options',
            'dshop-crm-integrations',
            [$this, 'renderIntegrationsPage']
        );
    }

    /**
     * Handle CRM form submission (runs on admin_init before output)
     */
    public function handleCrmForm(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['dshop_crm_nonce'])) {
            return;
        }

        if (isset($_GET['page']) && $_GET['page'] === 'dshop-crm-integrations') {
            check_admin_referer('dshop_crm_nonce', 'dshop_crm_nonce');

            $crm_type = sanitize_text_field($_POST['crm_type'] ?? '');

            if (isset($this->integrations[$crm_type])) {
                $settings = [
                    'api_url' => esc_url_raw($_POST['api_url'] ?? ''),
                    'api_key' => sanitize_text_field($_POST['api_key'] ?? ''),
                    'api_secret' => sanitize_text_field($_POST['api_secret'] ?? ''),
                    'enabled' => isset($_POST['enabled']) ? 1 : 0,
                ];

                foreach ($settings as $key => $value) {
                    update_option("dshop_crm_{$crm_type}_{$key}", $value);
                }
            }

            wp_redirect(admin_url('admin.php?page=dshop-crm-integrations&updated=1'));
            exit;
        }
    }

    /**
     * Render integrations page
     *
     * @return void
     */
    public function renderIntegrationsPage(): void
    {
        $integrations = $this->integrations;
        include DSHOP_SRC_DIR . 'modules/CrmIntegration/views/integrations.php';
    }

    /**
     * Sync order to CRM
     *
     * @param int $order_id Order ID
     * @return void
     */
    public function syncOrderToCrm(int $order_id): void
    {
        global $wpdb;

        $order = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}dshop_orders WHERE id = %d", $order_id)
        );

        if (!$order) {
            return;
        }

        foreach ($this->integrations as $integration) {
            if ($integration->isActive() && $integration->shouldSyncOrders()) {
                $result = $integration->createContact([
                    'name' => $order->billing_first_name . ' ' . $order->billing_last_name,
                    'email' => $order->billing_email,
                    'phone' => $order->billing_phone,
                    'company' => $order->billing_company,
                ]);

                if ($result && !is_wp_error($result)) {
                    $integration->createDeal([
                        'contact_id' => $result['id'],
                        'name' => sprintf('Заказ #%s', $order->order_number),
                        'value' => $order->total,
                        'status' => $order->status,
                    ]);
                }
            }
        }
    }

    /**
     * Sync order status to CRM
     *
     * @param int $order_id Order ID
     * @param string $new_status New status
     * @param string $old_status Old status
     * @return void
     */
    public function syncOrderStatusToCrm(int $order_id, string $new_status, string $old_status): void
    {
        foreach ($this->integrations as $integration) {
            if ($integration->isActive() && $integration->shouldSyncOrders()) {
                $integration->updateDealStatus($order_id, $new_status);
            }
        }
    }

    /**
     * Get integration by ID
     *
     * @param string $integration_id Integration ID
     * @return BaseIntegration|null
     */
    public function getIntegration(string $integration_id): ?BaseIntegration
    {
        return $this->integrations[$integration_id] ?? null;
    }

    /**
     * Get all integrations
     *
     * @return array
     */
    public function getIntegrations(): array
    {
        return $this->integrations;
    }
}
