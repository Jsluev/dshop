<?php defined('ABSPATH') || exit; ?>
<div class="wrap">
    <h1>Настройки доставки</h1>
    <form method="post">
        <?php wp_nonce_field('dshop_shipping_save'); ?>

        <h2>Самовывоз</h2>
        <table class="form-table">
            <tr>
                <th><label for="pickup_enabled">Включена</label></th>
                <td><input type="checkbox" name="pickup_enabled" id="pickup_enabled" value="1" <?php checked($settings['pickup_enabled'], 1); ?>></td>
            </tr>
            <tr>
                <th><label for="pickup_title">Название</label></th>
                <td><input type="text" name="pickup_title" id="pickup_title" value="<?php echo esc_attr($settings['pickup_title']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="pickup_address">Адрес пункта выдачи</label></th>
                <td><textarea name="pickup_address" id="pickup_address" rows="3" class="large-text"><?php echo esc_textarea($settings['pickup_address']); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="pickup_cost">Стоимость (₽)</label></th>
                <td><input type="number" name="pickup_cost" id="pickup_cost" value="<?php echo esc_attr($settings['pickup_cost']); ?>" step="0.01" min="0"></td>
            </tr>
        </table>

        <h2>Городская транспортная компания</h2>
        <table class="form-table">
            <tr>
                <th><label for="city_enabled">Включена</label></th>
                <td><input type="checkbox" name="city_enabled" id="city_enabled" value="1" <?php checked($settings['city_enabled'], 1); ?>></td>
            </tr>
            <tr>
                <th><label for="city_title">Название</label></th>
                <td><input type="text" name="city_title" id="city_title" value="<?php echo esc_attr($settings['city_title']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="city_cost">Стоимость (₽)</label></th>
                <td><input type="number" name="city_cost" id="city_cost" value="<?php echo esc_attr($settings['city_cost']); ?>" step="0.01" min="0"></td>
            </tr>
            <tr>
                <th><label for="city_free_from">Бесплатно от суммы (₽)</label></th>
                <td><input type="number" name="city_free_from" id="city_free_from" value="<?php echo esc_attr($settings['city_free_from']); ?>" step="0.01" min="0"></td>
            </tr>
        </table>

        <h2>СДЭК</h2>
        <table class="form-table">
            <tr>
                <th><label for="cdek_enabled">Включена</label></th>
                <td><input type="checkbox" name="cdek_enabled" id="cdek_enabled" value="1" <?php checked($settings['cdek_enabled'], 1); ?>></td>
            </tr>
            <tr>
                <th><label for="cdek_title">Название</label></th>
                <td><input type="text" name="cdek_title" id="cdek_title" value="<?php echo esc_attr($settings['cdek_title']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="cdek_api_key">API Key</label></th>
                <td><input type="text" name="cdek_api_key" id="cdek_api_key" value="<?php echo esc_attr($settings['cdek_api_key']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="cdek_api_secret">API Secret</label></th>
                <td><input type="password" name="cdek_api_secret" id="cdek_api_secret" value="<?php echo esc_attr($settings['cdek_api_secret']); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="cdek_cost">Стоимость (₽, 0 = расчет через API)</label></th>
                <td><input type="number" name="cdek_cost" id="cdek_cost" value="<?php echo esc_attr($settings['cdek_cost']); ?>" step="0.01" min="0"></td>
            </tr>
        </table>

        <h2>Бесплатная доставка</h2>
        <table class="form-table">
            <tr>
                <th><label for="free_shipping_from">Бесплатная доставка от суммы (₽, 0 = отключено)</label></th>
                <td><input type="number" name="free_shipping_from" id="free_shipping_from" value="<?php echo esc_attr($settings['free_shipping_from']); ?>" step="0.01" min="0"></td>
            </tr>
        </table>

        <?php submit_button('Сохранить', 'primary', 'dshop_save_shipping'); ?>
    </form>
</div>
