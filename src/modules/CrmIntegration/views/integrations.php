<?php
defined('ABSPATH') || exit;
?>
<div class="wrap">
    <h1>Интеграции с CRM</h1>

    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success is-dismissible"><p>Настройки сохранены.</p></div>
    <?php endif; ?>

    <div class="dshop-crm-integrations">
        <?php foreach ($integrations as $integration): ?>
            <?php $is_active = $integration->isActive(); ?>
            <div class="dshop-crm-card <?php echo $is_active ? 'active' : ''; ?>">
                <div class="dshop-crm-card__header">
                    <h2><?php echo esc_html($integration->getTitle()); ?></h2>
                    <span class="dshop-status dshop-status--<?php echo $is_active ? 'active' : 'inactive'; ?>">
                        <?php echo $is_active ? 'Активна' : 'Неактивна'; ?>
                    </span>
                </div>
                
                <form method="post">
                    <?php wp_nonce_field('dshop_crm_nonce', 'dshop_crm_nonce'); ?>
                    <input type="hidden" name="crm_type" value="<?php echo esc_attr($integration->getId()); ?>">
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="enabled_<?php echo esc_attr($integration->getId()); ?>">Включить</label></th>
                            <td>
                                <input type="checkbox" id="enabled_<?php echo esc_attr($integration->getId()); ?>" name="enabled" value="1" <?php checked($is_active, 1); ?>>
                                <label for="enabled_<?php echo esc_attr($integration->getId()); ?>">Включить эту интеграцию</label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="api_url_<?php echo esc_attr($integration->getId()); ?>">API URL</label></th>
                            <td><input type="url" id="api_url_<?php echo esc_attr($integration->getId()); ?>" name="api_url" value="<?php echo esc_attr($integration->settings['api_url'] ?? ''); ?>" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="api_key_<?php echo esc_attr($integration->getId()); ?>">API Key</label></th>
                            <td><input type="text" id="api_key_<?php echo esc_attr($integration->getId()); ?>" name="api_key" value="<?php echo esc_attr($integration->settings['api_key'] ?? ''); ?>" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="api_secret_<?php echo esc_attr($integration->getId()); ?>">API Secret</label></th>
                            <td><input type="password" id="api_secret_<?php echo esc_attr($integration->getId()); ?>" name="api_secret" value="<?php echo esc_attr($integration->settings['api_secret'] ?? ''); ?>" class="regular-text"></td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <button type="submit" class="button button-primary">Сохранить</button>
                    </p>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
</div>
