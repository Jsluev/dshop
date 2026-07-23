<?php
defined('ABSPATH') || exit;

$cart_module = \DShop\Core\DShop::getInstance()->getModule('cart');
$cart = $cart_module->getCart();
$items = $cart->getItems();
?>

<div class="dshop-checkout">
    <div class="dshop-checkout__form">
        <form id="dshop-checkout-form" method="post">
            <input type="hidden" name="action" value="dshop_process_checkout">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('dshop_nonce'); ?>">
            <input type="hidden" name="shipping_cost" id="dshop-shipping-cost" value="0">

            <div class="dshop-checkout__section">
                <h2 class="dshop-checkout__section-title"><?php echo 'Контактные данные'; ?></h2>

                <div class="dshop-form-row">
                    <div class="dshop-form-group">
                        <label for="billing_first_name"><?php echo 'Имя'; ?> *</label>
                        <input type="text" id="billing_first_name" name="billing_first_name" required>
                    </div>
                    <div class="dshop-form-group">
                        <label for="billing_last_name"><?php echo 'Фамилия'; ?> *</label>
                        <input type="text" id="billing_last_name" name="billing_last_name" required>
                    </div>
                </div>

                <div class="dshop-form-row">
                    <div class="dshop-form-group">
                        <label for="billing_phone"><?php echo 'Телефон'; ?> *</label>
                        <input type="tel" id="billing_phone" name="billing_phone" required placeholder="+7 (___) ___-__-__">
                    </div>
                    <div class="dshop-form-group">
                        <label for="billing_email"><?php echo 'Email'; ?> *</label>
                        <input type="email" id="billing_email" name="billing_email" required>
                    </div>
                </div>
            </div>

            <div class="dshop-checkout__section" id="dshop-shipping-fields">
                <h2 class="dshop-checkout__section-title"><?php echo 'Адрес доставки'; ?></h2>

                <div class="dshop-form-group">
                    <label for="billing_city"><?php echo 'Город'; ?> *</label>
                    <input type="text" id="billing_city" name="billing_city" required>
                </div>

                <div class="dshop-form-group">
                    <label for="billing_address_1"><?php echo 'Адрес'; ?> *</label>
                    <input type="text" id="billing_address_1" name="billing_address_1" required placeholder="<?php echo 'Улица, дом'; ?>">
                </div>

                <div class="dshop-form-row">
                    <div class="dshop-form-group">
                        <label for="billing_address_2"><?php echo 'Квартира / офис'; ?></label>
                        <input type="text" id="billing_address_2" name="billing_address_2">
                    </div>
                    <div class="dshop-form-group">
                        <label for="billing_postcode"><?php echo 'Индекс'; ?> *</label>
                        <input type="text" id="billing_postcode" name="billing_postcode" required>
                    </div>
                </div>

                <div class="dshop-form-group">
                    <label for="billing_state"><?php echo 'Регион'; ?></label>
                    <input type="text" id="billing_state" name="billing_state">
                </div>
            </div>

            <div class="dshop-checkout__section">
                <h2 class="dshop-checkout__section-title"><?php echo 'Способ доставки'; ?></h2>
                <div class="dshop-checkout__shipping-methods">
                    <label class="dshop-radio">
                        <input type="radio" name="shipping_method" value="pickup" checked
                               data-cost="0">
                        <div>
                            <span class="dshop-radio__label"><?php echo 'Самовывоз'; ?></span>
                            <span class="dshop-radio__description"><?php echo 'Бесплатно — забрать из магазина'; ?></span>
                        </div>
                    </label>
                    <label class="dshop-radio">
                        <input type="radio" name="shipping_method" value="city_transport"
                               data-cost="350">
                        <div>
                            <span class="dshop-radio__label"><?php echo 'Городская доставка'; ?></span>
                            <span class="dshop-radio__description"><?php echo '350 ₽ — курьером по городу'; ?></span>
                        </div>
                    </label>
                    <label class="dshop-radio">
                        <input type="radio" name="shipping_method" value="cdek"
                               data-cost="500">
                        <div>
                            <span class="dshop-radio__label"><?php echo 'СДЭК'; ?></span>
                            <span class="dshop-radio__description"><?php echo '500 ₽ — транспортной компанией'; ?></span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="dshop-checkout__section">
                <h2 class="dshop-checkout__section-title"><?php echo 'Способ оплаты'; ?></h2>
                <div class="dshop-payment-methods">
                    <label class="dshop-payment-method">
                        <input type="radio" name="payment_method" value="yookassa" checked>
                        <span class="dshop-payment-method__label"><?php echo 'ЮKassa'; ?></span>
                    </label>
                    <label class="dshop-payment-method">
                        <input type="radio" name="payment_method" value="cloudpayments">
                        <span class="dshop-payment-method__label"><?php echo 'CloudPayments'; ?></span>
                    </label>
                    <label class="dshop-payment-method">
                        <input type="radio" name="payment_method" value="free">
                        <span class="dshop-payment-method__label"><?php echo 'Оплата при получении'; ?></span>
                    </label>
                </div>
            </div>

            <div class="dshop-checkout__section">
                <h2 class="dshop-checkout__section-title"><?php echo 'Комментарий к заказу'; ?></h2>
                <div class="dshop-form-group">
                    <textarea id="customer_note" name="customer_note" rows="3" placeholder="<?php echo 'Особые пожелания по доставке или заказу'; ?>"></textarea>
                </div>
            </div>
        </form>
    </div>

    <div class="dshop-checkout__sidebar">
        <div class="dshop-order-summary">
            <h3 class="dshop-order-summary__title"><?php echo 'Ваш заказ'; ?></h3>

            <div class="dshop-order-summary__items">
                <?php foreach ($items as $item): ?>
                    <div class="dshop-order-summary__item">
                        <span class="dshop-order-summary__item-name">
                            <?php echo esc_html($item['name']); ?> x <?php echo esc_html($item['quantity']); ?>
                        </span>
                        <span class="dshop-order-summary__item-total">
                            <?php echo esc_html(number_format($item['price'] * $item['quantity'], 0, '', ' ')); ?> ₽
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="dshop-order-summary__totals">
                <div class="dshop-order-summary__total">
                    <span><?php echo 'Подытог'; ?></span>
                    <span><?php echo esc_html(number_format($cart->getSubtotal(), 0, '', ' ')); ?> ₽</span>
                </div>
                <?php if ($cart->getDiscount() > 0): ?>
                    <div class="dshop-order-summary__total">
                        <span><?php echo 'Скидка'; ?></span>
                        <span>-<?php echo esc_html(number_format($cart->getDiscount(), 0, '', ' ')); ?> ₽</span>
                    </div>
                <?php endif; ?>
                <div class="dshop-order-summary__total dshop-order-summary__shipping">
                    <span><?php echo 'Доставка'; ?></span>
                    <span id="dshop-shipping-display"><?php echo 'Бесплатно'; ?></span>
                </div>
                <div class="dshop-order-summary__total dshop-order-summary__total--final">
                    <span><?php echo 'Итого'; ?></span>
                    <span id="dshop-total-display"><?php echo esc_html(number_format($cart->getTotal(), 0, '', ' ')); ?> ₽</span>
                </div>
            </div>

            <button type="submit" form="dshop-checkout-form" class="dshop-order-summary__place-order">
                <?php echo 'Оформить заказ'; ?>
            </button>
        </div>
    </div>
</div>
