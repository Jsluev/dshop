<?php
defined('ABSPATH') || exit;

$selected_template = isset($_GET['template']) ? sanitize_text_field($_GET['template']) : 'order_confirmation';
$subject = get_option("dshop_email_template_{$selected_template}_subject", '');
$body = get_option("dshop_email_template_{$selected_template}_body", '');
?>
<div class="wrap">
    <h1>Шаблоны email</h1>

    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success is-dismissible"><p>Шаблон сохранён.</p></div>
    <?php endif; ?>

    <div class="dshop-email-templates">
        <div class="dshop-email-templates__sidebar">
            <h2>Шаблоны</h2>
            <ul class="dshop-email-templates__list">
                <?php foreach ($templates as $key => $label): ?>
                    <li class="<?php echo $key === $selected_template ? 'active' : ''; ?>">
                        <a href="?page=dshop-email-templates&template=<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="dshop-email-templates__editor">
            <h2><?php echo esc_html($templates[$selected_template] ?? $selected_template); ?></h2>
            
            <form method="post">
                <?php wp_nonce_field('dshop_email_template_nonce', 'dshop_email_template_nonce'); ?>
                <input type="hidden" name="template" value="<?php echo esc_attr($selected_template); ?>">
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="subject">Тема письма</label></th>
                        <td><input type="text" id="subject" name="subject" value="<?php echo esc_attr($subject); ?>" class="regular-text" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="body">Текст письма</label></th>
                        <td>
                            <textarea id="body" name="body" rows="15" class="large-text code"><?php echo esc_textarea($body); ?></textarea>
                            <p class="description">
                                Доступные переменные:<br>
                                <code>{{site_name}}</code> — название сайта<br>
                                <code>{{order_number}}</code> — номер заказа<br>
                                <code>{{customer_name}}</code> — имя клиента<br>
                                <code>{{order_total}}</code> — сумма заказа<br>
                            </p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary">Сохранить шаблон</button>
                    <button type="button" class="button" id="send-test-email">Отправить тест</button>
                </p>
            </form>
        </div>
    </div>
</div>
