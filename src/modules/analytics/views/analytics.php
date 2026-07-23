<?php
defined('ABSPATH') || exit;
?>
<div class="wrap">
    <h1>Аналитика</h1>

    <div class="dshop-analytics">
        <div class="dshop-analytics__stats">
            <div class="dshop-stat-card">
                <h3>Всего заказов</h3>
                <p class="dshop-stat-card__value"><?php echo esc_html(number_format($stats['total_orders'])); ?></p>
            </div>
            <div class="dshop-stat-card">
                <h3>Общая выручка</h3>
                <p class="dshop-stat-card__value"><?php echo esc_html(number_format($stats['total_revenue'], 2, '.', ' ')); ?> <?php echo get_option('dshop_currency', 'RUB'); ?></p>
            </div>
            <div class="dshop-stat-card">
                <h3>Клиенты</h3>
                <p class="dshop-stat-card__value"><?php echo esc_html(number_format($stats['total_customers'])); ?></p>
            </div>
            <div class="dshop-stat-card">
                <h3>Товары</h3>
                <p class="dshop-stat-card__value"><?php echo esc_html(number_format($stats['total_products'])); ?></p>
            </div>
            <div class="dshop-stat-card">
                <h3>Заказов сегодня</h3>
                <p class="dshop-stat-card__value"><?php echo esc_html(number_format($stats['orders_today'])); ?></p>
            </div>
            <div class="dshop-stat-card">
                <h3>Выручка сегодня</h3>
                <p class="dshop-stat-card__value"><?php echo esc_html(number_format($stats['revenue_today'], 2, '.', ' ')); ?> <?php echo get_option('dshop_currency', 'RUB'); ?></p>
            </div>
            <div class="dshop-stat-card">
                <h3>Средний чек</h3>
                <p class="dshop-stat-card__value"><?php echo esc_html(number_format($stats['avg_order'], 2, '.', ' ')); ?> <?php echo get_option('dshop_currency', 'RUB'); ?></p>
            </div>
        </div>

        <div class="dshop-analytics__chart">
            <h2>График продаж</h2>
            <div id="dshop-sales-chart" style="height: 300px;"></div>
        </div>

        <div class="dshop-analytics__tables">
            <div class="dshop-analytics__recent-orders">
                <h2>Последние заказы</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Заказ</th>
                            <th>Статус</th>
                            <th>Сумма</th>
                            <th>Дата</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                            <tr><td colspan="4">Заказов пока нет</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $order): ?>
                                <tr>
                                    <td><a href="<?php echo admin_url('post.php?post=' . $order->id . '&action=edit'); ?>">#<?php echo esc_html($order->order_number); ?></a></td>
                                    <td><span class="dshop-status dshop-status--<?php echo esc_attr($order->status); ?>"><?php echo esc_html(ucfirst($order->status)); ?></span></td>
                                    <td><?php echo esc_html(number_format($order->total, 2, '.', ' ')); ?> ₽</td>
                                    <td><?php echo date_i18n(get_option('date_format'), strtotime($order->created_at)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="dshop-analytics__top-products">
                <h2>Топ товаров</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Товар</th>
                            <th>Цена</th>
                            <th>Продаж</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($top_products)): ?>
                            <tr><td colspan="3">Товаров пока нет</td></tr>
                        <?php else: ?>
                            <?php foreach ($top_products as $product): ?>
                                <tr>
                                    <td><a href="<?php echo admin_url('post.php?post=' . $product->id . '&action=edit'); ?>"><?php echo esc_html($product->name); ?></a></td>
                                    <td><?php echo esc_html(number_format($product->price, 2, '.', ' ')); ?> ₽</td>
                                    <td><?php echo esc_html($product->sales_count); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
