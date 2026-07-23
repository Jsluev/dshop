<?php
/**
 * Discounts Module
 *
 * @package DShop\Modules\Discounts
 */

namespace DShop\Modules\Discounts;

use DShop\Core\BaseModule;

/**
 * Class DiscountsModule
 *
 * Handles discounts, coupons, and promotions
 */
class DiscountsModule extends BaseModule
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name = 'discounts';

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
    protected $description = 'Discounts, coupons, and promotions module';

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
        add_action('init', [$this, 'registerPostTypes']);

        // Admin hooks
        if (is_admin()) {
            add_action('admin_menu', [$this, 'addAdminMenus']);
            add_action('add_meta_boxes', [$this, 'addMetaBoxes']);
            add_action('save_post_dshop_coupon', [$this, 'saveCoupon'], 10, 2);
            add_action('transition_post_status', [$this, 'onCouponStatusChange'], 10, 3);
        }

        // Apply product discounts
        add_filter('dshop/product/price', [$this, 'applyProductDiscount'], 10, 2);
    }

    public function registerPostTypes(): void
    {
        register_post_type('dshop_coupon', [
            'labels' => [
                'name' => 'Купоны',
                'singular_name' => 'Купон',
                'add_new_item' => 'Добавить купон',
                'edit_item' => 'Редактировать купон',
                'all_items' => 'Все купоны',
                'search_items' => 'Поиск купонов',
                'not_found' => 'Купонов не найдено',
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'supports' => ['title'],
            'capability_type' => 'post',
        ]);
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
            'Купоны и скидки',
            'Купоны',
            'manage_options',
            'dshop-coupons',
            [$this, 'renderCouponsPage']
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
            'dshop_coupon_data',
            'Данные купона',
            [$this, 'renderCouponMetabox'],
            'dshop_coupon',
            'normal',
            'high'
        );
    }

    /**
     * Render coupon metabox
     *
     * @param \WP_Post $post Post object
     * @return void
     */
    public function renderCouponMetabox(\WP_Post $post): void
    {
        wp_nonce_field('dshop_coupon_data', 'dshop_coupon_nonce');

        $code = get_post_meta($post->ID, '_dshop_coupon_code', true);
        $type = get_post_meta($post->ID, '_dshop_coupon_type', true) ?: 'percent';
        $amount = get_post_meta($post->ID, '_dshop_coupon_amount', true);
        $minimum_spend = get_post_meta($post->ID, '_dshop_coupon_minimum_spend', true);
        $maximum_spend = get_post_meta($post->ID, '_dshop_coupon_maximum_spend', true);
        $usage_limit = get_post_meta($post->ID, '_dshop_coupon_usage_limit', true);
        $used_count = get_post_meta($post->ID, '_dshop_coupon_used_count', true);
        $expires_at = get_post_meta($post->ID, '_dshop_coupon_expires_at', true);
        $exclude_sale_items = get_post_meta($post->ID, '_dshop_coupon_exclude_sale_items', true);
        ?>
        <div class="dshop-coupon-metabox">
            <table class="form-table">
                <tr>
                    <th><label for="coupon_code">Код купона *</label></th>
                    <td>
                        <input type="text" id="coupon_code" name="coupon_code" value="<?php echo esc_attr($code); ?>" class="regular-text" required>
                        <button type="button" class="button" id="generate-coupon-code">Сгенерировать</button>
                    </td>
                </tr>
                <tr>
                    <th><label for="coupon_type">Тип скидки *</label></th>
                    <td>
                        <select id="coupon_type" name="coupon_type" required>
                            <option value="percent" <?php selected($type, 'percent'); ?>>Процентная скидка</option>
                            <option value="fixed" <?php selected($type, 'fixed'); ?>>Фиксированная скидка</option>
                            <option value="free_shipping" <?php selected($type, 'free_shipping'); ?>>Бесплатная доставка</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="coupon_amount">Размер скидки *</label></th>
                    <td>
                        <input type="number" id="coupon_amount" name="coupon_amount" value="<?php echo esc_attr($amount); ?>" step="0.01" min="0" required class="small-text">
                        <span class="description">Значение зависит от типа скидки</span>
                    </td>
                </tr>
                <tr>
                    <th><label for="coupon_minimum_spend">Минимальная сумма заказа</label></th>
                    <td>
                        <input type="number" id="coupon_minimum_spend" name="coupon_minimum_spend" value="<?php echo esc_attr($minimum_spend); ?>" step="0.01" min="0" class="small-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="coupon_maximum_spend">Максимальная сумма заказа</label></th>
                    <td>
                        <input type="number" id="coupon_maximum_spend" name="coupon_maximum_spend" value="<?php echo esc_attr($maximum_spend); ?>" step="0.01" min="0" class="small-text">
                    </td>
                </tr>
                <tr>
                    <th><label for="coupon_usage_limit">Лимит использований</label></th>
                    <td>
                        <input type="number" id="coupon_usage_limit" name="coupon_usage_limit" value="<?php echo esc_attr($usage_limit); ?>" min="0" class="small-text">
                        <span class="description">Оставьте пустым для безлимитного использования</span>
                    </td>
                </tr>
                <tr>
                    <th>Уже использовано</th>
                    <td><strong><?php echo esc_html($used_count ?: 0); ?></strong></td>
                </tr>
                <tr>
                    <th><label for="coupon_expires_at">Дата окончания</label></th>
                    <td>
                        <input type="date" id="coupon_expires_at" name="coupon_expires_at" value="<?php echo esc_attr($expires_at); ?>">
                    </td>
                </tr>
                <tr>
                    <th><label for="coupon_exclude_sale_items">Исключить товары со скидкой</label></th>
                    <td>
                        <input type="checkbox" id="coupon_exclude_sale_items" name="coupon_exclude_sale_items" value="1" <?php checked($exclude_sale_items, 1); ?>>
                        <label for="coupon_exclude_sale_items">Не применять к товарам, которые уже продаются со скидкой</label>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Save coupon
     *
     * @param int $post_id Post ID
     * @param \WP_Post $post Post object
     * @return void
     */
    public function saveCoupon(int $post_id, \WP_Post $post): void
    {
        if (!isset($_POST['dshop_coupon_nonce']) ||
            !wp_verify_nonce($_POST['dshop_coupon_nonce'], 'dshop_coupon_data')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = [
            '_dshop_coupon_code' => ['coupon_code', 'sanitize_text_field'],
            '_dshop_coupon_type' => ['coupon_type', 'sanitize_text_field'],
            '_dshop_coupon_amount' => ['coupon_amount', 'floatval'],
            '_dshop_coupon_minimum_spend' => ['coupon_minimum_spend', 'floatval'],
            '_dshop_coupon_maximum_spend' => ['coupon_maximum_spend', 'floatval'],
            '_dshop_coupon_usage_limit' => ['coupon_usage_limit', 'intval'],
            '_dshop_coupon_expires_at' => ['coupon_expires_at', 'sanitize_text_field'],
            '_dshop_coupon_exclude_sale_items' => ['coupon_exclude_sale_items', 'intval'],
        ];

        foreach ($fields as $meta_key => $field_info) {
            list($field_name, $sanitize_func) = $field_info;
            if (isset($_POST[$field_name])) {
                update_post_meta($post_id, $meta_key, $sanitize_func($_POST[$field_name]));
            }
        }

        $used_count = (int) get_post_meta($post_id, '_dshop_coupon_used_count', true);
        update_post_meta($post_id, '_dshop_coupon_used_count', $used_count);

        $this->syncCouponToTable($post_id);
    }

    /**
     * Sync coupon data from postmeta to the dshop_coupons table
     */
    private function syncCouponToTable(int $post_id): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'dshop_coupons';
        $code = get_post_meta($post_id, '_dshop_coupon_code', true);
        if (!$code) {
            return;
        }

        $data = [
            'code' => $code,
            'type' => get_post_meta($post_id, '_dshop_coupon_type', true) ?: 'percent',
            'amount' => floatval(get_post_meta($post_id, '_dshop_coupon_amount', true)),
            'minimum_spend' => floatval(get_post_meta($post_id, '_dshop_coupon_minimum_spend', true)),
            'maximum_spend' => floatval(get_post_meta($post_id, '_dshop_coupon_maximum_spend', true)),
            'usage_limit' => intval(get_post_meta($post_id, '_dshop_coupon_usage_limit', true)) ?: null,
            'used_count' => intval(get_post_meta($post_id, '_dshop_coupon_used_count', true)),
            'exclude_sale_items' => intval(get_post_meta($post_id, '_dshop_coupon_exclude_sale_items', true)),
            'expires_at' => get_post_meta($post_id, '_dshop_coupon_expires_at', true) ?: null,
            'status' => get_post_status($post_id) === 'publish' ? 'active' : 'inactive',
        ];

        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE code = %s", $code));

        if ($existing) {
            $wpdb->update($table, $data, ['code' => $code]);
        } else {
            $wpdb->insert($table, $data);
        }
    }

    /**
     * Sync coupon status when post status changes
     */
    public function onCouponStatusChange(string $new_status, string $old_status, \WP_Post $post): void
    {
        if ($post->post_type !== 'dshop_coupon') {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'dshop_coupons';
        $code = get_post_meta($post->ID, '_dshop_coupon_code', true);

        if (!$code) {
            return;
        }

        $status = $new_status === 'publish' ? 'active' : 'inactive';
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE code = %s", $code));

        if ($existing) {
            $wpdb->update($table, ['status' => $status], ['code' => $code]);
        }
    }

    /**
     * Apply product discount
     *
     * @param float $price Product price
     * @param int $product_id Product ID
     * @return float
     */
    public function applyProductDiscount(float $price, int $product_id): float
    {
        global $wpdb;

        $table = $wpdb->prefix . 'dshop_coupons';
        $coupons = $wpdb->get_results(
            "SELECT * FROM {$table} WHERE status = 'active' AND type != 'free_shipping'"
        );

        $max_discount = 0;

        foreach ($coupons as $coupon) {
            // Check expiration
            if ($coupon->expires_at && strtotime($coupon->expires_at) < time()) {
                continue;
            }

            // Check usage limit
            if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                continue;
            }

            // Check if product is excluded
            $exclude_product_ids = array_map('intval', explode(',', $coupon->exclude_product_ids ?? ''));
            if (in_array($product_id, $exclude_product_ids, true)) {
                continue;
            }

            // Check if product is in allowed list
            $product_ids = array_map('intval', explode(',', $coupon->product_ids ?? ''));
            if (!empty($product_ids) && !in_array($product_id, $product_ids, true)) {
                continue;
            }

            // Calculate discount
            if ($coupon->type === 'percent') {
                $discount = $price * ($coupon->amount / 100);
            } elseif ($coupon->type === 'fixed') {
                $discount = min($coupon->amount, $price);
            } else {
                continue;
            }

            $max_discount = max($max_discount, $discount);
        }

        return $price - $max_discount;
    }

    /**
     * Render coupons page
     *
     * @return void
     */
    public function renderCouponsPage(): void
    {
        $coupons_query = new \WP_Query([
            'post_type' => 'dshop_coupon',
            'posts_per_page' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $coupons = [];
        foreach ($coupons_query->posts as $post) {
            $obj = new \stdClass();
            $obj->id = $post->ID;
            $obj->code = get_post_meta($post->ID, '_dshop_coupon_code', true);
            $obj->type = get_post_meta($post->ID, '_dshop_coupon_type', true) ?: 'percent';
            $obj->amount = floatval(get_post_meta($post->ID, '_dshop_coupon_amount', true));
            $obj->used_count = intval(get_post_meta($post->ID, '_dshop_coupon_used_count', true));
            $obj->usage_limit = intval(get_post_meta($post->ID, '_dshop_coupon_usage_limit', true)) ?: null;
            $obj->expires_at = get_post_meta($post->ID, '_dshop_coupon_expires_at', true);
            $obj->status = $post->post_status === 'publish' ? 'active' : 'inactive';
            $coupons[] = $obj;
        }

        include DSHOP_SRC_DIR . 'modules/discounts/views/coupons.php';
    }

    /**
     * Validate coupon
     *
     * @param string $code Coupon code
     * @param float $cart_total Cart total
     * @return bool|WP_Error
     */
    public function validateCoupon(string $code, float $cart_total = 0)
    {
        global $wpdb;

        $table = $wpdb->prefix . 'dshop_coupons';
        $coupon = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE code = %s AND status = 'active'", $code)
        );

        if (!$coupon) {
            return new \WP_Error('invalid_coupon', 'Неверный код купона');
        }

        // Check expiration
        if ($coupon->expires_at && strtotime($coupon->expires_at) < time()) {
            return new \WP_Error('coupon_expired', 'Срок действия купона истёк');
        }

        // Check usage limit
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return new \WP_Error('coupon_limit', 'Купон уже использован максимальное количество раз');
        }

        // Check minimum spend
        if ($coupon->minimum_spend > 0 && $cart_total < $coupon->minimum_spend) {
            return new \WP_Error('coupon_minimum', sprintf('Минимальная сумма заказа для этого купона: %s ₽', number_format($coupon->minimum_spend, 2, '.', ' ')));
        }

        // Check maximum spend
        if ($coupon->maximum_spend > 0 && $cart_total > $coupon->maximum_spend) {
            return new \WP_Error('coupon_maximum', sprintf('Максимальная сумма заказа для этого купона: %s ₽', number_format($coupon->maximum_spend, 2, '.', ' ')));
        }

        return $coupon;
    }

    /**
     * Calculate coupon discount
     *
     * @param object $coupon Coupon object
     * @param float $subtotal Cart subtotal
     * @return float
     */
    public function calculateDiscount(object $coupon, float $subtotal): float
    {
        switch ($coupon->type) {
            case 'percent':
                return $subtotal * ($coupon->amount / 100);
            case 'fixed':
                return min($coupon->amount, $subtotal);
            default:
                return 0;
        }
    }

    /**
     * Increment coupon usage
     *
     * @param string $code Coupon code
     * @return void
     */
    public function incrementUsage(string $code): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'dshop_coupons';
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table} SET used_count = used_count + 1 WHERE code = %s",
                $code
            )
        );
    }
}
