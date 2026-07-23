<?php
defined('ABSPATH') || exit;

$from_name = get_option('dshop_email_from_name', get_bloginfo('name'));
$from_email = get_option('dshop_email_from_email', get_option('admin_email'));
$admin_email = get_option('dshop_email_admin_email', get_option('admin_email'));
$reply_to = get_option('dshop_email_reply_to', '');
?>
<div class="wrap">
    <h1>Настройки email</h1>

    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success is-dismissible"><p>Настройки сохранены.</p></div>
    <?php endif; ?>

    <form method="post">
        <?php wp_nonce_field('dshop_email_settings_nonce', 'dshop_email_settings_nonce'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row"><label for="from_name">Имя отправителя</label></th>
                <td>
                    <input type="text" id="from_name" name="from_name" value="<?php echo esc_attr($from_name); ?>" class="regular-text" required>
                    <p class="description">Имя, отображаемое в поле «От кого» в письмах</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="from_email">Email отправителя</label></th>
                <td>
                    <input type="email" id="from_email" name="from_email" value="<?php echo esc_attr($from_email); ?>" class="regular-text" required>
                    <p class="description">Email, отображаемый в поле «От кого» в письмах</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="admin_email">Email администратора</label></th>
                <td>
                    <input type="email" id="admin_email" name="admin_email" value="<?php echo esc_attr($admin_email); ?>" class="regular-text">
                    <p class="description">Email для уведомлений администратора</p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="reply_to">Reply-To</label></th>
                <td>
                    <input type="email" id="reply_to" name="reply_to" value="<?php echo esc_attr($reply_to); ?>" class="regular-text">
                    <p class="description">Необязательно. Email для ответа в заголовке Reply-To</p>
                </td>
            </tr>
        </table>
        
        <p class="submit">
            <button type="submit" class="button button-primary">Сохранить</button>
        </p>
    </form>
</div>
