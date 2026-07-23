<?php
/**
 * Orders list view
 *
 * @var array $orders
 * @var int $total
 * @var int $current_page
 * @var int $total_pages
 * @var string $status
 * @var string $search
 */
if (!defined('ABSPATH')) exit;
?>
<div class="wrap">
    <h1 class="wp-heading-inline">Заказы</h1>

    <?php if (isset($_GET['deleted'])) : ?>
        <div class="notice notice-success is-dismissible"><p>Заказ удален.</p></div>
    <?php endif; ?>

    <form method="get" style="margin: 12px 0;">
        <input type="hidden" name="page" value="dshop-orders">
        <select name="status">
            <option value="">Все статусы</option>
            <option value="pending" <?php selected($_GET['status'] ?? '', 'pending'); ?>>Ожидает</option>
            <option value="processing" <?php selected($_GET['status'] ?? '', 'processing'); ?>>В обработке</option>
            <option value="completed" <?php selected($_GET['status'] ?? '', 'completed'); ?>>Завершен</option>
            <option value="cancelled" <?php selected($_GET['status'] ?? '', 'cancelled'); ?>>Отменен</option>
        </select>
        <input type="search" name="search" placeholder="Поиск..." value="<?php echo esc_attr($_GET['search'] ?? ''); ?>">
        <button type="submit" class="button">Фильтр</button>
    </form>

    <p class="description">Всего заказов: <?php echo $total; ?></p>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width:50px;">ID</th>
                <th style="width:120px;">Номер</th>
                <th>Клиент</th>
                <th style="width:120px;">Статус</th>
                <th style="width:100px;">Сумма</th>
                <th style="width:100px;">Доставка</th>
                <th style="width:100px;">Оплата</th>
                <th style="width:100px;">Дата</th>
                <th style="width:100px;">Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($orders)) : ?>
                <tr><td colspan="9">Заказов не найдено.</td></tr>
            <?php else : ?>
                <?php foreach ($orders as $o) : ?>
                    <tr>
                        <td><?php echo esc_html($o->id); ?></td>
                        <td><?php echo esc_html($o->order_number); ?></td>
                        <td>
                            <?php echo esc_html($o->billing_first_name . ' ' . $o->billing_last_name); ?>
                            <br><small style="color:#666;"><?php echo esc_html($o->billing_email); ?></small>
                        </td>
                        <td>
                            <?php
                            $status_labels = [
                                'pending' => 'Ожидает',
                                'processing' => 'В обработке',
                                'completed' => 'Завершен',
                                'cancelled' => 'Отменен',
                            ];
                            $label = $status_labels[$o->status] ?? $o->status;
                            ?>
                            <span class="dshop-status dshop-status--<?php echo esc_attr($o->status); ?>"><?php echo esc_html($label); ?></span>
                        </td>
                        <td><?php echo esc_html(number_format((float) $o->total, 0, '', ' ')); ?> ₽</td>
                        <td><?php echo esc_html($o->shipping_method); ?></td>
                        <td><?php echo esc_html($o->payment_method); ?></td>
                        <td><?php echo esc_html(date('d.m.Y', strtotime($o->created_at ?? $o->id > 0 ? date('Y-m-d') : ''))); ?></td>
                        <td>
                            <a href="<?php echo admin_url('admin.php?page=dshop-orders&id=' . $o->id); ?>">Просмотр</a> |
                            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=dshop-orders&delete_id=' . $o->id), 'dshop_delete_order_' . $o->id); ?>" onclick="return confirm('Удалить заказ?');" style="color:#dc2626;">Удалить</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($total_pages > 1) : ?>
        <div class="tablenav bottom">
            <div class="tablenav-pages">
                <?php
                $pagination_args = [
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'current' => $current_page,
                    'total' => $total_pages,
                ];
                echo paginate_links($pagination_args);
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>
