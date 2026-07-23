<?php
defined('ABSPATH') || exit;

global $wpdb;

$order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
$order = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}dshop_orders WHERE id = %d",
        $order_id
    )
);

if (!$order) {
    echo '<div class="dshop-empty-state">';
    echo '<h2>' . 'Заказ не найден' . '</h2>';
    echo '</div>';
    return;
}

$order_items = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}dshop_order_items WHERE order_id = %d",
        $order_id
    )
);

$shipping_labels = [
    'pickup' => 'Самовывоз',
    'city_transport' => 'Городская доставка',
    'cdek' => 'СДЭК',
];

$payment_labels = [
    'yookassa' => 'ЮKassa',
    'cloudpayments' => 'CloudPayments',
    'free' => 'Оплата при получении',
];

$is_pickup = ($order->shipping_method === 'pickup');
$shipping_label = $shipping_labels[$order->shipping_method] ?? $order->shipping_method;
$payment_label = $payment_labels[$order->payment_method] ?? $order->payment_method;
?>

<div class="dshop-order-confirmation">
    <div class="dshop-order-confirmation__header">
        <h1><?php echo 'Спасибо за заказ!'; ?></h1>
        <p><?php printf('Заказ %s оформлен', esc_html($order->order_number)); ?></p>
    </div>

    <div class="dshop-order-confirmation__section">
        <h2><?php echo 'Детали заказа'; ?></h2>
        <table class="dshop-order-confirmation__table">
            <tr>
                <td><?php echo 'Номер'; ?></td>
                <td><?php echo esc_html($order->order_number); ?></td>
            </tr>
            <tr>
                <td><?php echo 'Дата'; ?></td>
                <td><?php echo date_i18n(get_option('date_format') . ', H:i', strtotime($order->created_at)); ?></td>
            </tr>
            <tr>
                <td><?php echo 'Статус'; ?></td>
                <td><span class="dshop-status dshop-status--<?php echo esc_attr($order->status); ?>"><?php echo esc_html(ucfirst($order->status)); ?></span></td>
            </tr>
            <tr>
                <td><?php echo 'Доставка'; ?></td>
                <td><?php echo esc_html($shipping_label); ?></td>
            </tr>
            <tr>
                <td><?php echo 'Оплата'; ?></td>
                <td><?php echo esc_html($payment_label); ?></td>
            </tr>
        </table>
    </div>

    <div class="dshop-order-confirmation__section">
        <h2><?php echo 'Состав заказа'; ?></h2>
        <table class="dshop-order-confirmation__items">
            <thead>
                <tr>
                    <th><?php echo 'Товар'; ?></th>
                    <th><?php echo 'Кол-во'; ?></th>
                    <th><?php echo 'Цена'; ?></th>
                    <th><?php echo 'Сумма'; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?php echo esc_html($item->name); ?></td>
                        <td><?php echo esc_html($item->quantity); ?></td>
                        <td><?php echo esc_html(number_format($item->price, 0, '', ' ')); ?> ₽</td>
                        <td><?php echo esc_html(number_format($item->total, 0, '', ' ')); ?> ₽</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3"><?php echo 'Подытог'; ?></td>
                    <td><?php echo esc_html(number_format($order->subtotal, 0, '', ' ')); ?> ₽</td>
                </tr>
                <?php if ($order->discount > 0): ?>
                    <tr>
                        <td colspan="3"><?php echo 'Скидка'; ?></td>
                        <td>-<?php echo esc_html(number_format($order->discount, 0, '', ' ')); ?> ₽</td>
                    </tr>
                <?php endif; ?>
                <?php if ($order->shipping_cost > 0): ?>
                    <tr>
                        <td colspan="3"><?php echo 'Доставка'; ?></td>
                        <td><?php echo esc_html(number_format($order->shipping_cost, 0, '', ' ')); ?> ₽</td>
                    </tr>
                <?php endif; ?>
                <tr class="dshop-order-confirmation__total">
                    <td colspan="3"><?php echo 'Итого'; ?></td>
                    <td><?php echo esc_html(number_format($order->total, 0, '', ' ')); ?> ₽</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <?php if ($is_pickup): ?>
        <div class="dshop-order-confirmation__section">
            <h2><?php echo 'Пункт выдачи'; ?></h2>
            <address>
                <?php echo esc_html($order->billing_first_name . ' ' . $order->billing_last_name); ?><br>
                <?php echo esc_html($order->billing_phone); ?><br>
                <?php echo esc_html($order->billing_email); ?>
            </address>
        </div>
    <?php else: ?>
        <div class="dshop-order-confirmation__section">
            <h2><?php echo 'Адрес доставки'; ?></h2>
            <address>
                <?php echo esc_html($order->billing_first_name . ' ' . $order->billing_last_name); ?><br>
                <?php if ($order->billing_company): ?>
                    <?php echo esc_html($order->billing_company); ?><br>
                <?php endif; ?>
                <?php echo esc_html($order->billing_address_1); ?>
                <?php if ($order->billing_address_2): ?>
                    <?php echo esc_html(', ' . $order->billing_address_2); ?>
                <?php endif; ?><br>
                <?php echo esc_html($order->billing_city . ', ' . $order->billing_postcode); ?><br>
                <?php echo esc_html($order->billing_phone); ?><br>
                <?php echo esc_html($order->billing_email); ?>
            </address>
        </div>
    <?php endif; ?>

    <div class="dshop-order-confirmation__actions">
        <a href="<?php echo dshop_shop_url(); ?>" class="dshop-button">
            <?php echo 'Продолжить покупки'; ?>
        </a>
    </div>
</div>
