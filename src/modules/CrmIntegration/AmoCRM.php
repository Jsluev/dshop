<?php
/**
 * AmoCRM Integration
 *
 * @package DShop\Modules\CrmIntegration
 */

namespace DShop\Modules\CrmIntegration;

/**
 * Class AmoCRM
 *
 * AmoCRM integration
 */
class AmoCRM extends BaseIntegration
{
    /**
     * Integration ID
     *
     * @var string
     */
    protected $id = 'amocrm';

    /**
     * Integration title
     *
     * @var string
     */
    protected $title = 'AmoCRM';

    /**
     * {@inheritdoc}
     */
    public function createContact(array $data)
    {
        $response = $this->apiRequest('/api/v4/contacts', 'POST', [
            [
                'first_name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'phone' => $data['phone'] ?? '',
                'company' => $data['company'] ?? '',
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        return $response['_embedded']['contacts'][0] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function createDeal(array $data)
    {
        $response = $this->apiRequest('/api/v4/leads', 'POST', [
            [
                'name' => $data['name'] ?? '',
                'price' => $data['value'] ?? 0,
                'status_id' => $this->mapStatus($data['status'] ?? 'new'),
                '_embedded' => [
                    'contacts' => [
                        ['id' => $data['contact_id'] ?? 0],
                    ],
                ],
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        return $response['_embedded']['leads'][0] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDealStatus(int $deal_id, string $status): bool
    {
        $amo_status = $this->mapStatus($status);

        $response = $this->apiRequest("/api/v4/leads/{$deal_id}", 'PATCH', [
            'status_id' => $amo_status,
        ]);

        return !is_wp_error($response);
    }

    /**
     * Map DShop status to AmoCRM status
     *
     * @param string $status DShop status
     * @return int AmoCRM status ID
     */
    private function mapStatus(string $status): int
    {
        $status_map = [
            'new' => 142, // Новый лид
            'processing' => 143, // В работе
            'completed' => 143, // Выигран
            'cancelled' => 144, // Проигран
        ];

        return $status_map[$status] ?? 142;
    }
}
