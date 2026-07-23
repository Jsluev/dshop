<?php
/**
 * CRM Module
 *
 * @package DShop\Modules\Crm
 */

namespace DShop\Modules\Crm;

use DShop\Core\BaseModule;

/**
 * Class CrmModule
 *
 * Handles customer relationship management
 */
class CrmModule extends BaseModule
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name = 'crm';

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
    protected $description = 'Customer relationship management module';

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

        // Order creation
        add_action('dshop/order/created', [$this, 'onOrderCreated'], 10, 2);

        // User registration
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
            'Группы клиентов',
            'Группы клиентов',
            'manage_options',
            'dshop-customer-groups',
            [$this, 'renderCustomerGroupsPage']
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
            'dshop_customer_data',
            'Данные клиента',
            [$this, 'renderCustomerMetabox'],
            'dshop_customer',
            'normal',
            'high'
        );
    }

    /**
     * Render customer metabox
     *
     * @param \WP_Post $post Post object
     * @return void
     */
    public function renderCustomerMetabox(\WP_Post $post): void
    {
        $email = get_post_meta($post->ID, '_dshop_customer_email', true);
        $phone = get_post_meta($post->ID, '_dshop_customer_phone', true);
        $group_id = get_post_meta($post->ID, '_dshop_customer_group_id', true);
        $points = get_post_meta($post->ID, '_dshop_customer_points', true);
        $total_spent = get_post_meta($post->ID, '_dshop_customer_total_spent', true);
        $orders_count = get_post_meta($post->ID, '_dshop_customer_orders_count', true);

        global $wpdb;
        $groups = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dshop_customer_groups ORDER BY name");
        ?>
        <div class="dshop-customer-metabox">
            <table class="form-table">
                <tr>
                    <th><label for="customer_email">Email</label></th>
                    <td>
                        <input type="email" id="customer_email" name="customer_email" value="<?php echo esc_attr($email); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="customer_phone">Телефон</label></th>
                    <td>
                        <input type="text" id="customer_phone" name="customer_phone" value="<?php echo esc_attr($phone); ?>" class="regular-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="customer_group_id">Группа</label></th>
                    <td>
                        <select id="customer_group_id" name="customer_group_id">
                            <option value="">Без группы</option>
                            <?php foreach ($groups as $group): ?>
                                <option value="<?php echo esc_attr($group->id); ?>" <?php selected($group_id, $group->id); ?>>
                                    <?php echo esc_html($group->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Баллы</th>
                    <td><strong><?php echo esc_html($points ?: 0); ?></strong></td>
                </tr>
                <tr>
                    <th>Всего потрачено</th>
                    <td><strong><?php echo esc_html(number_format($total_spent ?: 0, 2, '.', ' ')); ?> <?php echo get_option('dshop_currency', 'RUB'); ?></strong></td>
                </tr>
                <tr>
                    <th>Заказов</th>
                    <td><strong><?php echo esc_html($orders_count ?: 0); ?></strong></td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Render customer groups page
     *
     * @return void
     */
    public function renderCustomerGroupsPage(): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'dshop_customer_groups';
        $groups = $wpdb->get_results("SELECT * FROM {$table} ORDER BY name");

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dshop_group_nonce'])) {
            check_admin_referer('dshop_group_nonce', 'dshop_group_nonce');
            
            $name = sanitize_text_field($_POST['name'] ?? '');
            $discount = floatval($_POST['discount'] ?? 0);

            if ($name) {
                if (isset($_POST['group_id'])) {
                    $wpdb->update(
                        $table,
                        [
                            'name' => $name,
                            'discount' => $discount,
                        ],
                        ['id' => absint($_POST['group_id'])]
                    );
                } else {
                    $wpdb->insert(
                        $table,
                        [
                            'name' => $name,
                            'discount' => $discount,
                        ]
                    );
                }
                
                wp_redirect(admin_url('admin.php?page=dshop-customer-groups&updated=1'));
                exit;
            }
        }

        include DSHOP_SRC_DIR . 'modules/crm/views/customer-groups.php';
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
        $email = $data['billing_email'] ?? '';
        
        if (!$email) {
            return;
        }

        // Find or create customer
        $customer = $this->findOrCreateCustomer($email, $data);

        if ($customer) {
            // Update customer stats
            $this->updateCustomerStats($customer->id, $order_id);

            // Award points
            $this->awardPoints($customer->id, $order_id);
        }
    }

    /**
     * Find or create customer
     *
     * @param string $email Customer email
     * @param array $data Customer data
     * @return object|null
     */
    private function findOrCreateCustomer(string $email, array $data = []): ?object
    {
        global $wpdb;

        $table = $wpdb->prefix . 'dshop_customers';
        $customer = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE email = %s", $email)
        );

        if (!$customer) {
            $wpdb->insert(
                $table,
                [
                    'email' => $email,
                    'phone' => $data['billing_phone'] ?? '',
                    'first_name' => $data['billing_first_name'] ?? '',
                    'last_name' => $data['billing_last_name'] ?? '',
                    'billing_address' => json_encode([
                        'address_1' => $data['billing_address_1'] ?? '',
                        'address_2' => $data['billing_address_2'] ?? '',
                        'city' => $data['billing_city'] ?? '',
                        'state' => $data['billing_state'] ?? '',
                        'postcode' => $data['billing_postcode'] ?? '',
                        'country' => $data['billing_country'] ?? 'RU',
                    ]),
                ]
            );

            $customer_id = $wpdb->insert_id;
            $customer = $wpdb->get_row(
                $wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $customer_id)
            );

            do_action('dshop/customer/registered', $customer);
        }

        return $customer;
    }

    /**
     * Update customer stats
     *
     * @param int $customer_id Customer ID
     * @param int $order_id Order ID
     * @return void
     */
    private function updateCustomerStats(int $customer_id, int $order_id): void
    {
        global $wpdb;

        $order = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}dshop_orders WHERE id = %d", $order_id)
        );

        if (!$order) {
            return;
        }

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}dshop_customers 
                SET total_spent = total_spent + %f, 
                    orders_count = orders_count + 1,
                    last_order_at = NOW()
                WHERE id = %d",
                $order->total,
                $customer_id
            )
        );
    }

    /**
     * Award points
     *
     * @param int $customer_id Customer ID
     * @param int $order_id Order ID
     * @return void
     */
    private function awardPoints(int $customer_id, int $order_id): void
    {
        global $wpdb;

        $order = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$wpdb->prefix}dshop_orders WHERE id = %d", $order_id)
        );

        if (!$order) {
            return;
        }

        // Award 1 point per 100 RUB spent
        $points = floor($order->total / 100);

        if ($points > 0) {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}dshop_customers 
                    SET points_balance = points_balance + %f
                    WHERE id = %d",
                    $points,
                    $customer_id
                )
            );
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
        // Send welcome email
        $subject = 'Добро пожаловать в наш магазин!';
        $message = sprintf(
            "Привет, %s!\n\nСпасибо за регистрацию в нашем магазине.\n\nС уважением,\nКоманда",
            $customer->first_name ?: $customer->email
        );

        wp_mail($customer->email, $subject, $message);
    }

    /**
     * Get customer by email
     *
     * @param string $email Customer email
     * @return object|null
     */
    public function getCustomerByEmail(string $email): ?object
    {
        global $wpdb;

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dshop_customers WHERE email = %s",
                $email
            )
        );
    }

    /**
     * Get customer orders
     *
     * @param int $customer_id Customer ID
     * @return array
     */
    public function getCustomerOrders(int $customer_id): array
    {
        global $wpdb;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}dshop_orders 
                WHERE billing_email = (SELECT email FROM {$wpdb->prefix}dshop_customers WHERE id = %d)
                ORDER BY created_at DESC",
                $customer_id
            )
        );
    }
}
