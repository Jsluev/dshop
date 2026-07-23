<?php
defined('ABSPATH') || exit;
?>
<div class="wrap">
    <h1>Склады</h1>

    <?php if (isset($_GET['updated'])): ?>
        <div class="notice notice-success is-dismissible"><p>Склад сохранён.</p></div>
    <?php endif; ?>

    <div class="dshop-warehouses">
        <div class="dshop-warehouses__list">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Адрес</th>
                        <th>Телефон</th>
                        <th>Основной</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($warehouses)): ?>
                        <tr><td colspan="5">Складов пока нет</td></tr>
                    <?php else: ?>
                        <?php foreach ($warehouses as $warehouse): ?>
                            <tr>
                                <td><strong><?php echo esc_html($warehouse->name); ?></strong></td>
                                <td><?php echo esc_html($warehouse->address); ?></td>
                                <td><?php echo esc_html($warehouse->phone); ?></td>
                                <td><?php echo $warehouse->is_default ? '&#10004;' : ''; ?></td>
                                <td><a href="?page=dshop-warehouses&edit=<?php echo esc_attr($warehouse->id); ?>" class="button button-small">Редактировать</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="dshop-warehouses__form">
            <h2><?php echo isset($_GET['edit']) ? 'Редактировать склад' : 'Добавить склад'; ?></h2>
            
            <?php
            $edit_id = isset($_GET['edit']) ? absint($_GET['edit']) : 0;
            $warehouse = null;
            if ($edit_id) {
                global $wpdb;
                $warehouse = $wpdb->get_row(
                    $wpdb->prepare("SELECT * FROM {$wpdb->prefix}dshop_warehouses WHERE id = %d", $edit_id)
                );
            }
            ?>
            
            <form method="post">
                <?php wp_nonce_field('dshop_warehouse_nonce', 'dshop_warehouse_nonce'); ?>
                <?php if ($warehouse): ?>
                    <input type="hidden" name="warehouse_id" value="<?php echo esc_attr($warehouse->id); ?>">
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="name">Название *</label></th>
                        <td><input type="text" id="name" name="name" value="<?php echo esc_attr($warehouse->name ?? ''); ?>" required class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="address">Адрес</label></th>
                        <td><input type="text" id="address" name="address" value="<?php echo esc_attr($warehouse->address ?? ''); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="phone">Телефон</label></th>
                        <td><input type="text" id="phone" name="phone" value="<?php echo esc_attr($warehouse->phone ?? ''); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="email">Email</label></th>
                        <td><input type="email" id="email" name="email" value="<?php echo esc_attr($warehouse->email ?? ''); ?>" class="regular-text"></td>
                    </tr>
                    <tr>
                        <th><label for="priority">Приоритет</label></th>
                        <td><input type="number" id="priority" name="priority" value="<?php echo esc_attr($warehouse->priority ?? 0); ?>" min="0" class="small-text"></td>
                    </tr>
                    <tr>
                        <th><label for="is_default">Основной</label></th>
                        <td>
                            <input type="checkbox" id="is_default" name="is_default" value="1" <?php checked($warehouse->is_default ?? 0, 1); ?>>
                            <label for="is_default">Сделать складом по умолчанию</label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" class="button button-primary"><?php echo $warehouse ? 'Обновить' : 'Добавить склад'; ?></button>
                    <?php if ($warehouse): ?>
                        <a href="?page=dshop-warehouses" class="button">Отмена</a>
                    <?php endif; ?>
                </p>
            </form>
        </div>
    </div>
</div>
