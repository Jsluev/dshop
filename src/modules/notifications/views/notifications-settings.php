<?php
defined('ABSPATH') || exit;

$telegram_enabled = get_option('dshop_notification_telegram_enabled', 0);
$telegram_bot_token = get_option('dshop_notification_telegram_bot_token', '');
$telegram_chat_id = get_option('dshop_notification_telegram_chat_id', '');
$sms_enabled = get_option('dshop_notification_sms_enabled', 0);
$sms_api_key = get_option('dshop_notification_sms_api_key', '');
$sms_sender = get_option('dshop_notification_sms_sender', '');
$push_enabled = get_option('dshop_notification_push_enabled', 0);
$push_api_key = get_option('dshop_notification_push_api_key', '');
?>
<div class="wrap">
    <h1>Настройки уведомлений</h1>

    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success is-dismissible"><p>Настройки сохранены.</p></div>
    <?php endif; ?>

    <form method="post">
        <?php wp_nonce_field('dshop_notifications_nonce', 'dshop_notifications_nonce'); ?>
        
        <h2>Telegram</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="telegram_enabled">Включить Telegram</label></th>
                <td>
                    <input type="checkbox" id="telegram_enabled" name="telegram_enabled" value="1" <?php checked($telegram_enabled, 1); ?>>
                    <label for="telegram_enabled">Отправлять уведомления в Telegram</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="telegram_bot_token">Токен бота</label></th>
                <td>
                    <input type="text" id="telegram_bot_token" name="telegram_bot_token" value="<?php echo esc_attr($telegram_bot_token); ?>" class="regular-text">
                    <p class="description">Получите токен у @BotFather</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="telegram_chat_id">Chat ID</label></th>
                <td>
                    <input type="text" id="telegram_chat_id" name="telegram_chat_id" value="<?php echo esc_attr($telegram_chat_id); ?>" class="regular-text">
                    <p class="description">ID чата или канала для уведомлений</p>
                </td>
            </tr>
        </table>
        
        <h2>SMS</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="sms_enabled">Включить SMS</label></th>
                <td>
                    <input type="checkbox" id="sms_enabled" name="sms_enabled" value="1" <?php checked($sms_enabled, 1); ?>>
                    <label for="sms_enabled">Отправлять SMS-уведомления</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="sms_api_key">API Key</label></th>
                <td><input type="text" id="sms_api_key" name="sms_api_key" value="<?php echo esc_attr($sms_api_key); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th scope="row"><label for="sms_sender">Имя отправителя</label></th>
                <td><input type="text" id="sms_sender" name="sms_sender" value="<?php echo esc_attr($sms_sender); ?>" class="regular-text"></td>
            </tr>
        </table>
        
        <h2>Push-уведомления</h2>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="push_enabled">Включить Push</label></th>
                <td>
                    <input type="checkbox" id="push_enabled" name="push_enabled" value="1" <?php checked($push_enabled, 1); ?>>
                    <label for="push_enabled">Отправлять push-уведомления</label>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="push_api_key">API Key</label></th>
                <td><input type="text" id="push_api_key" name="push_api_key" value="<?php echo esc_attr($push_api_key); ?>" class="regular-text"></td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary">Сохранить</button>
        </p>
    </form>
</div>
