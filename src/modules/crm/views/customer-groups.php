<?php
defined('ABSPATH') || exit;
?>
<div class="wrap">
    <h1>Группы клиентов</h1>

    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success is-dismissible">
            <p>Группа сохранена.</p>
        </div>
    <?php endif; ?>

    <div class="dshop-customer-groups">
        <div class="dshop-customer-groups__list">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Скидка</th>
                        <th>Клиентов</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($groups)): ?>
                        <tr><td colspan="4">Групп пока нет</td></tr>
                    <?php else: ?>
                        <?php foreach ($groups as $group): ?>
                            <tr>
                                <td><strong><?php echo esc_html($group->name); ?></strong></td>
                                <td><?php echo esc_html($group->discount); ?>%</td>
                                <td><?php echo esc_html($group->count ?? 0); ?></td>
                                <td><a href="?page=dshop-customer-groups&edit=<?php echo esc_attr($group->id); ?>" class="button button-small">Редактировать</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="dshop-customer-groups__form">
            <h2><?php echo isset($_GET['edit']) ? 'Редактировать группу' : 'Добавить группу'; ?></h2>
            
            <?php
            $edit_id = isset($_GET['edit']) ? absint($_GET['edit']) : 0;
            $group = null;
            if ($edit_id) {
                global $wpdb;
                $group = $wpdb->get_row(
                    $wpdb->prepare("SELECT * FROM {$wpdb->prefix}dshop_customer_groups WHERE id = %d", $edit_id)
                );
            }
            ?>
            
            <form method="post">
                <?php wp_nonce_field('dshop_group_nonce', 'dshop_group_nonce'); ?>
                <?php if ($group): ?>
                    <input type="hidden" name="group_id" value="<?php echo esc_attr($group->id); ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="name">Название *</label></th>
                        <td><input type="text" id="name" name="name" value="<?php echo esc_attr($group->name ?? ''); ?>" required class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="discount">Скидка (%)</label></th>
                        <td>
                            <input type="number" id="discount" name="discount" value="<?php echo esc_attr($group->discount ?? 0); ?>" min="0" max="100" step="0.01" class="small-text">
                            <span class="description">Процентная скидка для этой группы</span>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary"><?php echo $group ? 'Обновить' : 'Добавить группу'; ?></button>
                    <?php if ($group): ?>
                        <a href="?page=dshop-customer-groups" class="button">Отмена</a>
                    <?php endif; ?>
                </p>
            </form>
        </div>
    </div>
</div>
