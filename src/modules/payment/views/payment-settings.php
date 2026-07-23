<?php defined('ABSPATH') || exit; ?>
<div class="wrap">
    <h1>Способы оплаты</h1>
    <form method="post">
        <?php wp_nonce_field('dshop_payment_save'); ?>

        <h2>ЮKassa</h2>
        <table class="form-table">
            <tr>
                <th><label for="yookassa_enabled">Включена</label></th>
                <td><input type="checkbox" name="yookassa_enabled" id="yookassa_enabled" value="1" <?php checked($settings['yookassa_enabled'], 1); ?>></td>
            </tr>
            <tr>
                <th><label for="yookassa_shop_id">Shop ID</label></th>
                <td><input type="text" name="yookassa_shop_id" id="yookassa_shop_id" value="<?php echo esc_attr($settings['yookassa_shop_id']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="yookassa_secret_key">Secret Key</label></th>
                <td><input type="password" name="yookassa_secret_key" id="yookassa_secret_key" value="<?php echo esc_attr($settings['yookassa_secret_key']); ?>" class="regular-text"></td>
            </tr>
        </table>

        <h2>CloudPayments</h2>
        <table class="form-table">
            <tr>
                <th><label for="cloudpayments_enabled">Включена</label></th>
                <td><input type="checkbox" name="cloudpayments_enabled" id="cloudpayments_enabled" value="1" <?php checked($settings['cloudpayments_enabled'], 1); ?>></td>
            </tr>
            <tr>
                <th><label for="cloudpayments_public_id">Public ID</label></th>
                <td><input type="text" name="cloudpayments_public_id" id="cloudpayments_public_id" value="<?php echo esc_attr($settings['cloudpayments_public_id']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="cloudpayments_api_key">API Key</label></th>
                <td><input type="password" name="cloudpayments_api_key" id="cloudpayments_api_key" value="<?php echo esc_attr($settings['cloudpayments_api_key']); ?>" class="regular-text"></td>
            </tr>
        </table>

        <h2>Оплата при получении (FreePay)</h2>
        <table class="form-table">
            <tr>
                <th><label for="free_enabled">Включена</label></th>
                <td><input type="checkbox" name="free_enabled" id="free_enabled" value="1" <?php checked($settings['free_enabled'], 1); ?>></td>
            </tr>
            <tr>
                <th><label for="free_title">Название</label></th>
                <td><input type="text" name="free_title" id="free_title" value="<?php echo esc_attr($settings['free_title']); ?>" class="regular-text"></td>
            </tr>
        </table>

        <h2>Активный способ оплаты по умолчанию</h2>
        <table class="form-table">
            <tr>
                <th><label for="active_gateway">Способ оплаты</label></th>
                <td>
                    <select name="active_gateway" id="active_gateway">
                        <option value="yookassa" <?php selected($settings['active_gateway'], 'yookassa'); ?>>ЮKassa</option>
                        <option value="cloudpayments" <?php selected($settings['active_gateway'], 'cloudpayments'); ?>>CloudPayments</option>
                        <option value="free" <?php selected($settings['active_gateway'], 'free'); ?>>Оплата при получении</option>
                    </select>
                </td>
            </tr>
        </table>

        <?php submit_button('Сохранить', 'primary', 'dshop_save_payment'); ?>
    </form>
</div>
