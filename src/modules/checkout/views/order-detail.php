<?php
/**
 * Order detail view
 *
 * @var object $order
 * @var array $items
 */
if (!defined('ABSPATH')) exit;

$status_labels = [
    'pending' => 'Ожидает',
    'processing' => 'В обработке',
    'completed' => 'Завершен',
    'cancelled' => 'Отменен',
];

$shipping_labels = [
    'pickup' => 'Самовывоз',
    'city_transport' => 'Городская доставка',
    'cdek' => 'СДЭК',
];

$payment_labels = [
    'yookassa' => 'ЮKassa',
    'cloudpayments' => 'CloudPayments',
    'free' => 'Бесплатно',
];
?>
<div class="wrap">
    <h1>
        <a href="<?php echo admin_url('admin.php?page=dshop-orders'); ?>" style="text-decoration:none;">&larr; Заказы</a>
        &mdash; Заказ #<?php echo esc_html($order->order_number); ?>
    </h1>

    <div style="display:flex;gap:32px;flex-wrap:wrap;margin-top:20px;">
        <div style="flex:2;min-width:400px;">
            <form method="post" style="margin-bottom:20px;">
                <?php wp_nonce_field('dshop_order_status_' . $order->id); ?>
                <label><strong>Статус:</strong></label>
                <select name="order_status">
                    <?php foreach ($status_labels as $key => $lbl) : ?>
                        <option value="<?php echo esc_attr($key); ?>" <?php selected($order->status, $key); ?>><?php echo esc_html($lbl); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="dshop_update_order_status" class="button button-primary">Обновить статус</button>
            </form>

            <h2>Товары</h2>
            <table class="wp-list-table widefat fixed striped" style="margin-bottom:24px;">
                <thead>
                    <tr>
                        <th>Товар</th>
                        <th style="width:80px;">Артикул</th>
                        <th style="width:80px;">Кол-во</th>
                        <th style="width:120px;">Цена</th>
                        <th style="width:120px;">Сумма</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($items)) : ?>
                        <tr><td colspan="5">Нет товаров.</td></tr>
                    <?php else : ?>
                        <?php foreach ($items as $item) : ?>
                            <tr>
                                <td><?php echo esc_html($item->name); ?></td>
                                <td><?php echo esc_html($item->sku); ?></td>
                                <td><?php echo esc_html($item->quantity); ?></td>
                                <td><?php echo esc_html(number_format((float) $item->price, 0, '', ' ')); ?> ₽</td>
                                <td><?php echo esc_html(number_format((float) $item->total, 0, '', ' ')); ?> ₽</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <h2>Примечание администратора</h2>
            <form method="post">
                <?php wp_nonce_field('dshop_order_note_' . $order->id); ?>
                <textarea name="admin_note" rows="4" style="width:100%;max-width:600px;"><?php echo esc_textarea($order->admin_note ?? ''); ?></textarea>
                <br><br>
                <button type="submit" name="dshop_save_admin_note" class="button">Сохранить примечание</button>
            </form>
        </div>

        <div style="flex:1;min-width:280px;">
            <h2>Информация о заказе</h2>
            <table style="width:100%;font-size:14px;">
                <tr><td style="padding:6px 0;color:#666;width:120px;">ID:</td><td><?php echo esc_html($order->id); ?></td></tr>
                <tr><td style="padding:6px 0;color:#666;">Номер:</td><td><?php echo esc_html($order->order_number); ?></td></tr>
                <tr><td style="padding:6px 0;color:#666;">Статус:</td><td><span class="dshop-status dshop-status--<?php echo esc_attr($order->status); ?>"><?php echo esc_html($status_labels[$order->status] ?? $order->status); ?></span></td></tr>
                <tr><td style="padding:6px 0;color:#666;">Дата:</td><td><?php echo esc_html(date('d.m.Y H:i', strtotime($order->created_at ?? date('Y-m-d H:i:s')))); ?></td></tr>
                <tr><td style="padding:6px 0;color:#666;">IP:</td><td><?php echo esc_html($order->ip_address); ?></td></tr>
            </table>

            <h2 style="margin-top:20px;">Суммы</h2>
            <table style="width:100%;font-size:14px;">
                <tr><td style="padding:6px 0;color:#666;">Товары:</td><td><?php echo esc_html(number_format((float) $order->subtotal, 2, '.', ' ')); ?> ₽</td></tr>
                <tr><td style="padding:6px 0;color:#666;">Скидка:</td><td>-<?php echo esc_html(number_format((float) $order->discount, 2, '.', ' ')); ?> ₽</td></tr>
                <tr><td style="padding:6px 0;color:#666;">Доставка:</td><td><?php echo esc_html(number_format((float) $order->shipping_cost, 2, '.', ' ')); ?> ₽</td></tr>
                <tr><td style="padding:6px 0;color:#666;">Налог:</td><td><?php echo esc_html(number_format((float) $order->tax, 2, '.', ' ')); ?> ₽</td></tr>
                <tr><td style="padding:10px 0 6px;font-weight:700;">Итого:</td><td style="font-weight:700;font-size:16px;"><?php echo esc_html(number_format((float) $order->total, 2, '.', ' ')); ?> ₽</td></tr>
            </table>

            <h2 style="margin-top:20px;">Покупатель</h2>
            <table style="width:100%;font-size:14px;">
                <tr><td style="padding:6px 0;color:#666;width:120px;">Имя:</td><td><?php echo esc_html($order->billing_first_name . ' ' . $order->billing_last_name); ?></td></tr>
                <?php if (!empty($order->billing_company)) : ?>
                    <tr><td style="padding:6px 0;color:#666;">Компания:</td><td><?php echo esc_html($order->billing_company); ?></td></tr>
                <?php endif; ?>
                <tr><td style="padding:6px 0;color:#666;">Email:</td><td><?php echo esc_html($order->billing_email); ?></td></tr>
                <tr><td style="padding:6px 0;color:#666;">Телефон:</td><td><?php echo esc_html($order->billing_phone); ?></td></tr>
            </table>

            <h2 style="margin-top:20px;">Адрес</h2>
            <table style="width:100%;font-size:14px;">
                <tr><td style="padding:6px 0;color:#666;width:120px;">Адрес:</td><td><?php echo esc_html($order->billing_address_1 . ($order->billing_address_2 ? ', ' . $order->billing_address_2 : '')); ?></td></tr>
                <tr><td style="padding:6px 0;color:#666;">Город:</td><td><?php echo esc_html($order->billing_city); ?></td></tr>
                <tr><td style="padding:6px 0;color:#666;">Индекс:</td><td><?php echo esc_html($order->billing_postcode); ?></td></tr>
                <tr><td style="padding:6px 0;color:#666;">Страна:</td><td><?php echo esc_html($order->billing_country); ?></td></tr>
            </table>

            <h2 style="margin-top:20px;">Доставка и оплата</h2>
            <table style="width:100%;font-size:14px;">
                <tr><td style="padding:6px 0;color:#666;width:120px;">Доставка:</td><td><?php echo esc_html($shipping_labels[$order->shipping_method] ?? $order->shipping_method); ?></td></tr>
                <tr><td style="padding:6px 0;color:#666;">Оплата:</td><td><?php echo esc_html($payment_labels[$order->payment_method] ?? $order->payment_method); ?></td></tr>
            </table>

            <?php if (!empty($order->customer_note)) : ?>
                <h2 style="margin-top:20px;">Примечание клиента</h2>
                <p style="font-size:14px;color:#333;background:#f9f9f9;padding:12px;border-radius:6px;"><?php echo nl2br(esc_html($order->customer_note)); ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>
