<?php
defined('ABSPATH') || exit;

$currency = get_option('dshop_currency', 'RUB');
?>
<div class="wrap dshop-analytics-wrap">
    <h1 class="dshop-analytics__page-title">Аналитика</h1>

    <div class="dshop-analytics__cards">
        <div class="dshop-analytics__card dshop-analytics__card--blue">
            <div class="dshop-analytics__card-icon">
                <span class="dashicons dashicons-cart"></span>
            </div>
            <div class="dshop-analytics__card-body">
                <span class="dshop-analytics__card-label">Всего заказов</span>
                <span class="dshop-analytics__card-value"><?php echo esc_html(number_format($stats['total_orders'], 0, '', ' ')); ?></span>
            </div>
        </div>

        <div class="dshop-analytics__card dshop-analytics__card--green">
            <div class="dshop-analytics__card-icon">
                <span class="dashicons dashicons-money-alt"></span>
            </div>
            <div class="dshop-analytics__card-body">
                <span class="dshop-analytics__card-label">Общая выручка</span>
                <span class="dshop-analytics__card-value"><?php echo esc_html(number_format($stats['total_revenue'], 0, '', ' ')); ?> <small><?php echo esc_html($currency); ?></small></span>
            </div>
        </div>

        <div class="dshop-analytics__card dshop-analytics__card--purple">
            <div class="dshop-analytics__card-icon">
                <span class="dashicons dashicons-groups"></span>
            </div>
            <div class="dshop-analytics__card-body">
                <span class="dshop-analytics__card-label">Клиенты</span>
                <span class="dshop-analytics__card-value"><?php echo esc_html(number_format($stats['total_customers'], 0, '', ' ')); ?></span>
            </div>
        </div>

        <div class="dshop-analytics__card dshop-analytics__card--orange">
            <div class="dshop-analytics__card-icon">
                <span class="dashicons dashicons-archive"></span>
            </div>
            <div class="dshop-analytics__card-body">
                <span class="dshop-analytics__card-label">Товары</span>
                <span class="dshop-analytics__card-value"><?php echo esc_html(number_format($stats['total_products'], 0, '', ' ')); ?></span>
            </div>
        </div>
    </div>

    <div class="dshop-analytics__cards dshop-analytics__cards--small">
        <div class="dshop-analytics__card-sm">
            <span class="dshop-analytics__card-sm-label">Заказов сегодня</span>
            <span class="dshop-analytics__card-sm-value"><?php echo esc_html(number_format($stats['orders_today'], 0, '', ' ')); ?></span>
        </div>
        <div class="dshop-analytics__card-sm">
            <span class="dshop-analytics__card-sm-label">Выручка сегодня</span>
            <span class="dshop-analytics__card-sm-value"><?php echo esc_html(number_format($stats['revenue_today'], 0, '', ' ')); ?> <?php echo esc_html($currency); ?></span>
        </div>
        <div class="dshop-analytics__card-sm">
            <span class="dshop-analytics__card-sm-label">Средний чек</span>
            <span class="dshop-analytics__card-sm-value"><?php echo esc_html(number_format($stats['avg_order'], 0, '', ' ')); ?> <?php echo esc_html($currency); ?></span>
        </div>
    </div>

    <div class="dshop-analytics__chart-section">
        <div class="dshop-analytics__section-header">
            <h2 class="dshop-analytics__section-title">График продаж</h2>
        </div>
        <div class="dshop-analytics__chart-container">
            <div id="dshop-sales-chart" class="dshop-analytics__chart"></div>
        </div>
    </div>

    <div class="dshop-analytics__tables-grid">
        <div class="dshop-analytics__table-section">
            <div class="dshop-analytics__section-header">
                <h2 class="dshop-analytics__section-title">Последние заказы</h2>
                <a href="<?php echo admin_url('admin.php?page=dshop-orders'); ?>" class="page-title-action">Все заказы</a>
            </div>
            <table class="wp-list-table widefat fixed striped dshop-analytics__table">
                <thead>
                    <tr>
                        <th class="col-order">Заказ</th>
                        <th class="col-status">Статус</th>
                        <th class="col-total">Сумма</th>
                        <th class="col-date">Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_orders)): ?>
                        <tr><td colspan="4" class="dshop-analytics__empty">Заказов пока нет</td></tr>
                    <?php else: ?>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td><strong>#<?php echo esc_html($order->order_number); ?></strong></td>
                                <td>
                                    <?php
                                    $status_labels = [
                                        'new' => 'Новый',
                                        'processing' => 'В обработке',
                                        'completed' => 'Выполнен',
                                        'cancelled' => 'Отменён',
                                    ];
                                    $label = $status_labels[$order->status] ?? ucfirst($order->status);
                                    ?>
                                    <span class="dshop-status dshop-status--<?php echo esc_attr($order->status); ?>"><?php echo esc_html($label); ?></span>
                                </td>
                                <td><strong><?php echo esc_html(number_format($order->total, 0, '', ' ')); ?> ₽</strong></td>
                                <td><?php echo date_i18n('d M Y, H:i', strtotime($order->created_at)); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="dshop-analytics__table-section">
            <div class="dshop-analytics__section-header">
                <h2 class="dshop-analytics__section-title">Топ товаров</h2>
            </div>
            <table class="wp-list-table widefat fixed striped dshop-analytics__table">
                <thead>
                    <tr>
                        <th class="col-product">Товар</th>
                        <th class="col-price">Цена</th>
                        <th class="col-sales">Продаж</th>
                        <th class="col-revenue">Выручка</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($top_products)): ?>
                        <tr><td colspan="4" class="dshop-analytics__empty">Товаров пока нет</td></tr>
                    <?php else: ?>
                        <?php foreach ($top_products as $product): ?>
                            <tr>
                                <td><strong><?php echo esc_html($product->name); ?></strong></td>
                                <td><?php echo esc_html(number_format($product->price, 0, '', ' ')); ?> ₽</td>
                                <td><?php echo esc_html(number_format($product->sales_count, 0, '', ' ')); ?></td>
                                <td><strong><?php echo esc_html(number_format($product->price * $product->sales_count, 0, '', ' ')); ?> ₽</strong></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
