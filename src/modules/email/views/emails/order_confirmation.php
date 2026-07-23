<?php
defined('ABSPATH') || exit;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; border-bottom: 3px solid #007cba; }
        .content { padding: 20px 0; }
        .footer { background: #f8f9fa; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; }
        .total { font-size: 18px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php bloginfo('name'); ?></h1>
        </div>
        
        <div class="content">
            <h2>Подтверждение заказа #<?php echo esc_html($order->order_number); ?></h2>
            
            <p><?php echo esc_html($order->billing_first_name); ?>, здравствуйте!</p>
            
            <p>Спасибо за ваш заказ. Мы получили ваш заказ и обрабатываем его.</p>
            
            <h3>Детали заказа</h3>
            
            <table>
                <tr>
                    <th>Номер заказа:</th>
                    <td><?php echo esc_html($order->order_number); ?></td>
                </tr>
                <tr>
                    <th>Дата заказа:</th>
                    <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order->created_at)); ?></td>
                </tr>
                <tr>
                    <th>Способ оплаты:</th>
                    <td><?php echo esc_html($order->payment_method); ?></td>
                </tr>
            </table>
            
            <h3>Адрес для доставки</h3>
            <p>
                <?php echo esc_html($order->billing_first_name . ' ' . $order->billing_last_name); ?><br>
                <?php if ($order->billing_company): ?><?php echo esc_html($order->billing_company); ?><br><?php endif; ?>
                <?php echo esc_html($order->billing_address_1); ?><br>
                <?php if ($order->billing_address_2): ?><?php echo esc_html($order->billing_address_2); ?><br><?php endif; ?>
                <?php echo esc_html($order->billing_city . ', ' . $order->billing_postcode); ?><br>
                <?php echo esc_html($order->billing_phone); ?><br>
                <?php echo esc_html($order->billing_email); ?>
            </p>
            
            <h3>Состав заказа</h3>
            <table>
                <tr>
                    <td>Подытог:</td>
                    <td><?php echo esc_html(number_format($order->subtotal, 2, '.', ' ')); ?> ₽</td>
                </tr>
                <?php if ($order->discount > 0): ?>
                    <tr>
                        <td>Скидка:</td>
                        <td>-<?php echo esc_html(number_format($order->discount, 2, '.', ' ')); ?> ₽</td>
                    </tr>
                <?php endif; ?>
                <?php if ($order->shipping_cost > 0): ?>
                    <tr>
                        <td>Доставка:</td>
                        <td><?php echo esc_html(number_format($order->shipping_cost, 2, '.', ' ')); ?> ₽</td>
                    </tr>
                <?php endif; ?>
                <tr class="total">
                    <td><strong>Итого:</strong></td>
                    <td><strong><?php echo esc_html(number_format($order->total, 2, '.', ' ')); ?> ₽</strong></td>
                </tr>
            </table>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. Все права защищены.</p>
        </div>
    </div>
</body>
</html>
