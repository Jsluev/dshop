<?php
/**
 * Bitrix24 Integration
 *
 * @package DShop\Modules\CrmIntegration
 */

namespace DShop\Modules\CrmIntegration;

/**
 * Class Bitrix24
 *
 * Bitrix24 integration
 */
class Bitrix24 extends BaseIntegration
{
    /**
     * Integration ID
     *
     * @var string
     */
    protected $id = 'bitrix24';

    /**
     * Integration title
     *
     * @var string
     */
    protected $title = 'Битрикс24';

    /**
     * {@inheritdoc}
     */
    public function createContact(array $data)
    {
        $response = $this->apiRequest('/crm.contact.add.json', 'POST', [
            'fields' => [
                'NAME' => $data['name'] ?? '',
                'EMAIL' => [['VALUE' => $data['email'] ?? '', 'VALUE_TYPE' => 'WORK']],
                'PHONE' => [['VALUE' => $data['phone'] ?? '', 'VALUE_TYPE' => 'WORK']],
                'COMPANY_TITLE' => $data['company'] ?? '',
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        return ['id' => $response['result'] ?? 0];
    }

    /**
     * {@inheritdoc}
     */
    public function createDeal(array $data)
    {
        $response = $this->apiRequest('/crm.deal.add.json', 'POST', [
            'fields' => [
                'TITLE' => $data['name'] ?? '',
                'OPPORTUNITY' => $data['value'] ?? 0,
                'STAGE_ID' => $this->mapStatus($data['status'] ?? 'new'),
                'CONTACT_ID' => $data['contact_id'] ?? 0,
                'CURRENCY_ID' => 'RUB',
            ],
        ]);

        if (is_wp_error($response)) {
            return $response;
        }

        return ['id' => $response['result'] ?? 0];
    }

    /**
     * {@inheritdoc}
     */
    public function updateDealStatus(int $deal_id, string $status): bool
    {
        $bitrix_status = $this->mapStatus($status);

        $response = $this->apiRequest('/crm.deal.update.json', 'POST', [
            'id' => $deal_id,
            'fields' => [
                'STAGE_ID' => $bitrix_status,
            ],
        ]);

        return !is_wp_error($response) && ($response['result'] ?? false);
    }

    /**
     * Map DShop status to Bitrix24 status
     *
     * @param string $status DShop status
     * @return string Bitrix24 status ID
     */
    private function mapStatus(string $status): string
    {
        $status_map = [
            'new' => 'NEW',
            'processing' => 'PREPARATION',
            'completed' => 'WON',
            'cancelled' => 'LOSE',
        ];

        return $status_map[$status] ?? 'NEW';
    }
}
