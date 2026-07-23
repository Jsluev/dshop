<?php
/**
 * Payment Form View
 *
 * @package DShop\Modules\Payment
 */

defined('ABSPATH') || exit;

$order_id = isset($atts['order_id']) ? absint($atts['order_id']) : 0;

if (!$order_id) {
    return;
}

$payment_module = \DShop\Core\DShop::getInstance()->getModule('payment');
$active_gateways = $payment_module->getActiveGateways();
?>

<div class="dshop-payment-form">
    <h3><?php echo 'Выберите способ оплаты'; ?></h3>
    
    <div class="dshop-payment-methods">
        <?php foreach ($active_gateways as $gateway): ?>
            <label class="dshop-payment-method">
                <input type="radio" 
                       name="dshop_payment_method" 
                       value="<?php echo esc_attr($gateway->getId()); ?>"
                       <?php echo $gateway === reset($active_gateways) ? 'checked' : ''; ?>>
                <span class="dshop-payment-method__label"><?php echo esc_html($gateway->getTitle()); ?></span>
                <span class="dshop-payment-method__description"><?php echo esc_html($gateway->getDescription()); ?></span>
            </label>
        <?php endforeach; ?>
    </div>

    <button type="button" class="dshop-payment-form__submit" data-order-id="<?php echo esc_attr($order_id); ?>">
        <?php echo 'Перейти к оплате'; ?>
    </button>
</div>

<script>
jQuery(document).ready(function($) {
    $('.dshop-payment-form__submit').on('click', function() {
        var $button = $(this);
        var orderId = $button.data('order-id');
        var gatewayId = $('input[name="dshop_payment_method"]:checked').val();
        
        $button.prop('disabled', true).text('<?php echo 'Обработка...'; ?>');
        
        $.ajax({
            url: dshop.ajax_url,
            type: 'POST',
            data: {
                action: 'dshop_process_payment',
                nonce: dshop.nonce,
                order_id: orderId,
                gateway_id: gatewayId
            },
            success: function(response) {
                if (response.success) {
                    if (response.data.redirect) {
                        window.location.href = response.data.redirect;
                    } else {
                        // Show payment form
                        showPaymentForm(response.data);
                    }
                } else {
                    alert(response.data.message);
                    $button.prop('disabled', false).text('<?php echo 'Перейти к оплате'; ?>');
                }
            },
            error: function() {
                alert('<?php echo 'Произошла ошибка. Попробуйте ещё раз.'; ?>');
                $button.prop('disabled', false).text('<?php echo 'Перейти к оплате'; ?>');
            }
        });
    });
    
    function showPaymentForm(data) {
        if (data.public_id) {
            // CloudPayments
            var widget = new cp.CloudPayments();
            widget.pay('charge', {
                publicId: data.public_id,
                description: data.description,
                amount: data.amount,
                currency: data.currency,
                invoiceId: data.order_id,
                callbackUrl: data.callback_url
            }, {
                onSuccess: function(options) {
                    window.location.href = '<?php echo get_permalink(get_option('dshop_checkout_page_id')); ?>?order_id=' + data.order_id;
                },
                onFail: function(reason, options) {
                    console.error('Payment failed:', reason);
                }
            });
        }
    }
});
</script>
