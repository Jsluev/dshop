<?php defined('ABSPATH') || exit;
$settings = [
    'products_per_page' => get_option('dshop_products_per_page', 12),
    'enable_reviews' => get_option('dshop_enable_reviews', 1),
    'enable_wishlist' => get_option('dshop_enable_wishlist', 0),
    'manage_stock' => get_option('dshop_manage_stock', 1),
    'low_stock_threshold' => get_option('dshop_low_stock_threshold', 5),
    'currency' => get_option('dshop_currency', 'RUB'),
    'currency_position' => get_option('dshop_currency_position', 'right'),
    'tax_rate' => get_option('dshop_tax_rate', 0),
    'weight_unit' => get_option('dshop_weight_unit', 'kg'),
];
?>
<div class="wrap">
    <h1>Настройки каталога</h1>
    <form method="post">
        <?php wp_nonce_field('dshop_catalog_save'); ?>

        <h2>Основные</h2>
        <table class="form-table">
            <tr>
                <th><label for="products_per_page">Товаров на странице</label></th>
                <td><input type="number" name="products_per_page" id="products_per_page" value="<?php echo esc_attr($settings['products_per_page']); ?>" min="1" max="100"></td>
            </tr>
            <tr>
                <th><label for="currency">Валюта</label></th>
                <td>
                    <select name="currency" id="currency">
                        <option value="RUB" <?php selected($settings['currency'], 'RUB'); ?>>RUB (₽)</option>
                        <option value="USD" <?php selected($settings['currency'], 'USD'); ?>>USD ($)</option>
                        <option value="EUR" <?php selected($settings['currency'], 'EUR'); ?>>EUR (€)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="currency_position">Позиция символа валюты</label></th>
                <td>
                    <select name="currency_position" id="currency_position">
                        <option value="right" <?php selected($settings['currency_position'], 'right'); ?>>После суммы (100 ₽)</option>
                        <option value="left" <?php selected($settings['currency_position'], 'left'); ?>>Перед суммой (₽ 100)</option>
                        <option value="right_space" <?php selected($settings['currency_position'], 'right_space'); ?>>После суммы с пробелом (100 ₽)</option>
                        <option value="left_space" <?php selected($settings['currency_position'], 'left_space'); ?>>Перед суммой с пробелом (₽ 100)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="tax_rate">Налог (%)</label></th>
                <td><input type="number" name="tax_rate" id="tax_rate" value="<?php echo esc_attr($settings['tax_rate']); ?>" step="0.01" min="0" max="100"></td>
            </tr>
            <tr>
                <th><label for="weight_unit">Единица веса</label></th>
                <td>
                    <select name="weight_unit" id="weight_unit">
                        <option value="kg" <?php selected($settings['weight_unit'], 'kg'); ?>>кг</option>
                        <option value="g" <?php selected($settings['weight_unit'], 'g'); ?>>г</option>
                        <option value="lb" <?php selected($settings['weight_unit'], 'lb'); ?>>фунт</option>
                    </select>
                </td>
            </tr>
        </table>

        <h2>Запасы</h2>
        <table class="form-table">
            <tr>
                <th><label for="manage_stock">Управление остатками</label></th>
                <td><input type="checkbox" name="manage_stock" id="manage_stock" value="1" <?php checked($settings['manage_stock'], 1); ?>></td>
            </tr>
            <tr>
                <th><label for="low_stock_threshold">Порог низкого остатка</label></th>
                <td><input type="number" name="low_stock_threshold" id="low_stock_threshold" value="<?php echo esc_attr($settings['low_stock_threshold']); ?>" min="0"></td>
            </tr>
        </table>

        <h2>Функциональность</h2>
        <table class="form-table">
            <tr>
                <th><label for="enable_reviews">Отзывы на товары</label></th>
                <td><input type="checkbox" name="enable_reviews" id="enable_reviews" value="1" <?php checked($settings['enable_reviews'], 1); ?>></td>
            </tr>
            <tr>
                <th><label for="enable_wishlist">Список желаний</label></th>
                <td><input type="checkbox" name="enable_wishlist" id="enable_wishlist" value="1" <?php checked($settings['enable_wishlist'], 1); ?>></td>
            </tr>
        </table>

        <?php submit_button('Сохранить', 'primary', 'dshop_save_catalog'); ?>
    </form>
</div>
