<?php defined('ABSPATH') || exit; ?>
<div class="wrap">
    <h1>Настройки оформления заказа</h1>
    <form method="post">
        <?php wp_nonce_field('dshop_checkout_save'); ?>

        <h2>Обязательные поля</h2>
        <table class="form-table">
            <tr>
                <th><label for="require_phone">Телефон</label></th>
                <td><input type="checkbox" name="require_phone" id="require_phone" value="1" <?php checked($settings['require_phone'], 1); ?>></td>
            </tr>
            <tr>
                <th><label for="require_company">Компания</label></th>
                <td><input type="checkbox" name="require_company" id="require_company" value="1" <?php checked($settings['require_company'], 1); ?>></td>
            </tr>
            <tr>
                <th><label for="require_address">Адрес</label></th>
                <td><input type="checkbox" name="require_address" id="require_address" value="1" <?php checked($settings['require_address'], 1); ?>></td>
            </tr>
            <tr>
                <th><label for="require_comment">Комментарий к заказу</label></th>
                <td><input type="checkbox" name="require_comment" id="require_comment" value="1" <?php checked($settings['require_comment'], 1); ?>></td>
            </tr>
        </table>

        <h2>Прочее</h2>
        <table class="form-table">
            <tr>
                <th><label for="enable_guest_checkout">Заказ без регистрации</label></th>
                <td><input type="checkbox" name="enable_guest_checkout" id="enable_guest_checkout" value="1" <?php checked($settings['enable_guest_checkout'], 1); ?>></td>
            </tr>
            <tr>
                <th><label for="order_prefix">Префикс номера заказа</label></th>
                <td><input type="text" name="order_prefix" id="order_prefix" value="<?php echo esc_attr($settings['order_prefix']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="order_status_after">Статус после создания</label></th>
                <td>
                    <select name="order_status_after" id="order_status_after">
                        <option value="pending" <?php selected($settings['order_status_after'], 'pending'); ?>>Ожидает оплаты</option>
                        <option value="processing" <?php selected($settings['order_status_after'], 'processing'); ?>>В обработке</option>
                        <option value="on-hold" <?php selected($settings['order_status_after'], 'on-hold'); ?>>На удержании</option>
                    </select>
                </td>
            </tr>
        </table>

        <p>Шорткод: <code>[dshop_checkout]</code> — форма оформления заказа</p>

        <?php submit_button('Сохранить', 'primary', 'dshop_save_checkout'); ?>
    </form>
</div>
