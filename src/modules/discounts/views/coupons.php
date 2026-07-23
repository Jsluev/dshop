<?php
defined('ABSPATH') || exit;
?>
<div class="wrap">
    <h1>Купоны и скидки</h1>

    <p><a href="<?php echo admin_url('post-new.php?post_type=dshop_coupon'); ?>" class="button button-primary">Добавить купон</a></p>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Код</th>
                <th>Тип</th>
                <th>Сумма</th>
                <th>Использований</th>
                <th>Срок действия</th>
                <th>Статус</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($coupons)): ?>
                <tr><td colspan="7">Купонов пока нет</td></tr>
            <?php else: ?>
                <?php foreach ($coupons as $coupon): ?>
                    <?php
                    $is_expired = $coupon->expires_at && strtotime($coupon->expires_at) < time();
                    $is_used_up = $coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit;
                    $is_active = !$is_expired && !$is_used_up;
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($coupon->code); ?></strong></td>
                        <td><?php
                            switch ($coupon->type) {
                                case 'percent': echo 'Процентная'; break;
                                case 'fixed': echo 'Фиксированная'; break;
                                case 'free_shipping': echo 'Бесплатная доставка'; break;
                            }
                        ?></td>
                        <td><?php
                            switch ($coupon->type) {
                                case 'percent': echo esc_html($coupon->amount) . '%'; break;
                                case 'fixed': echo esc_html(number_format($coupon->amount, 2, '.', ' ')) . ' ₽'; break;
                                case 'free_shipping': echo '—'; break;
                            }
                        ?></td>
                        <td>
                            <?php echo esc_html($coupon->used_count); ?>
                            <?php if ($coupon->usage_limit): ?> / <?php echo esc_html($coupon->usage_limit); ?><?php endif; ?>
                        </td>
                        <td>
                            <?php if ($coupon->expires_at): ?>
                                <?php echo date_i18n(get_option('date_format'), strtotime($coupon->expires_at)); ?>
                                <?php if ($is_expired): ?> <span class="dshop-expired">(истёк)</span><?php endif; ?>
                            <?php else: ?> — <?php endif; ?>
                        </td>
                        <td>
                            <span class="dshop-status dshop-status--<?php echo $is_active ? 'active' : 'inactive'; ?>">
                                <?php echo $is_active ? 'Активен' : 'Неактивен'; ?>
                            </span>
                        </td>
                        <td><a href="<?php echo admin_url('post.php?post=' . $coupon->id . '&action=edit'); ?>" class="button button-small">Редактировать</a></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
