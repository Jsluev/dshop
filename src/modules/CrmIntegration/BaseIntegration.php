<?php
/**
 * Base CRM Integration Class
 *
 * @package DShop\Modules\CrmIntegration
 */

namespace DShop\Modules\CrmIntegration;

/**
 * Class BaseIntegration
 *
 * Abstract base class for CRM integrations
 */
abstract class BaseIntegration
{
    /**
     * Integration ID
     *
     * @var string
     */
    protected $id;

    /**
     * Integration title
     *
     * @var string
     */
    protected $title;

    /**
     * Integration settings
     *
     * @var array
     */
    protected $settings = [];

    /**
     * Get integration ID
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get integration title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Check if integration is active
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) get_option("dshop_crm_{$this->id}_enabled", false);
    }

    /**
     * Initialize integration
     *
     * @return void
     */
    public function init(): void
    {
        $this->loadSettings();
    }

    /**
     * Load settings
     *
     * @return void
     */
    protected function loadSettings(): void
    {
        $this->settings = [
            'api_url' => get_option("dshop_crm_{$this->id}_api_url", ''),
            'api_key' => get_option("dshop_crm_{$this->id}_api_key", ''),
            'api_secret' => get_option("dshop_crm_{$this->id}_api_secret", ''),
        ];
    }

    /**
     * Check if should sync orders
     *
     * @return bool
     */
    public function shouldSyncOrders(): bool
    {
        return (bool) get_option("dshop_crm_{$this->id}_sync_orders", true);
    }

    /**
     * Create contact in CRM
     *
     * @param array $data Contact data
     * @return array|WP_Error
     */
    abstract public function createContact(array $data);

    /**
     * Create deal in CRM
     *
     * @param array $data Deal data
     * @return array|WP_Error
     */
    abstract public function createDeal(array $data);

    /**
     * Update deal status
     *
     * @param int $deal_id Deal ID
     * @param string $status New status
     * @return bool
     */
    abstract public function updateDealStatus(int $deal_id, string $status): bool;

    /**
     * Make API request
     *
     * @param string $endpoint API endpoint
     * @param string $method HTTP method
     * @param array $data Request data
     * @return array|WP_Error
     */
    protected function apiRequest(string $endpoint, string $method = 'GET', array $data = [])
    {
        $url = $this->settings['api_url'] . $endpoint;

        $args = [
            'method' => $method,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->settings['api_key'],
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ];

        if (!empty($data)) {
            $args['body'] = wp_json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return $response;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return new \WP_Error('json_error', 'Invalid JSON response');
        }

        return $data;
    }
}
