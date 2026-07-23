<?php
/**
 * Cart View
 *
 * @package DShop\Modules\Cart
 */

defined('ABSPATH') || exit;

$cart_module = \DShop\Core\DShop::getInstance()->getModule('cart');
$cart = $cart_module->getCart();
$items = $cart->getItems();
$totals = $cart->getTotals();
?>

<div class="dshop-cart">
    <form id="dshop-cart-form">
        <table class="dshop-cart__table">
            <thead>
                <tr>
                    <th class="dshop-cart__product"><?php echo 'Товар'; ?></th>
                    <th class="dshop-cart__price"><?php echo 'Цена'; ?></th>
                    <th class="dshop-cart__quantity"><?php echo 'Количество'; ?></th>
                    <th class="dshop-cart__total"><?php echo 'Итого'; ?></th>
                    <th class="dshop-cart__actions"></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $key => $item): ?>
                    <tr class="dshop-cart__item" data-cart-key="<?php echo esc_attr($key); ?>">
                        <td class="dshop-cart__product">
                            <div class="dshop-cart__product-info">
                                <?php
                                $image = get_the_post_thumbnail_url($item['product_id'], 'thumbnail');
                                if (!$image) {
                                    $image = dshop_get_placeholder($item['product_id'], 100, 100);
                                }
                                ?>
                                    <img src="<?php echo esc_attr($image); ?>" alt="<?php echo esc_attr($item['name']); ?>" class="dshop-cart__product-image">
                                <?php endif; ?>
                                <div class="dshop-cart__product-details">
                                    <a href="<?php echo esc_url(get_permalink($item['product_id'])); ?>" class="dshop-cart__product-name">
                                        <?php echo esc_html($item['name']); ?>
                                    </a>
                                    <?php if (!empty($item['sku'])): ?>
                                        <span class="dshop-cart__product-sku"><?php echo 'Артикул:'; ?> <?php echo esc_html($item['sku']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="dshop-cart__price">
                            <?php echo esc_html(number_format($item['price'], 2, '.', ' ')); ?> <?php echo get_option('dshop_currency', 'RUB'); ?>
                        </td>
                        <td class="dshop-cart__quantity">
                            <div class="dshop-quantity">
                                <button type="button" class="dshop-quantity__button dshop-quantity__button--minus">-</button>
                                <input type="number" 
                                       class="dshop-quantity__input" 
                                       value="<?php echo esc_attr($item['quantity']); ?>" 
                                       min="1" 
                                       max="999"
                                       data-cart-key="<?php echo esc_attr($key); ?>">
                                <button type="button" class="dshop-quantity__button dshop-quantity__button--plus">+</button>
                            </div>
                        </td>
                        <td class="dshop-cart__total">
                            <?php echo esc_html(number_format($item['price'] * $item['quantity'], 2, '.', ' ')); ?> <?php echo get_option('dshop_currency', 'RUB'); ?>
                        </td>
                        <td class="dshop-cart__actions">
                            <button type="button" class="dshop-cart__remove" data-cart-key="<?php echo esc_attr($key); ?>">
                                <?php echo 'Удалить'; ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>

    <div class="dshop-cart__actions">
        <a href="<?php echo esc_url(get_post_type_archive_link('dshop_product')); ?>" class="dshop-cart__continue">
            <?php echo 'Продолжить покупки'; ?>
        </a>
    </div>

    <div class="dshop-cart__sidebar">
        <div class="dshop-cart__coupon">
            <h3><?php echo 'Код купона'; ?></h3>
            <form id="dshop-coupon-form">
                <input type="text" name="coupon_code" placeholder="<?php echo 'Введите код купона'; ?>" class="dshop-cart__coupon-input">
                <button type="submit" class="dshop-cart__coupon-button"><?php echo 'Применить'; ?></button>
            </form>
            <?php if ($cart->getCoupon()): ?>
                <div class="dshop-cart__coupon-applied">
                    <?php printf('Купон «%s» применён', esc_html($cart->getCoupon()->code)); ?>
                    <button type="button" class="dshop-cart__coupon-remove"><?php echo 'Удалить'; ?></button>
                </div>
            <?php endif; ?>
        </div>

        <div class="dshop-cart__totals">
            <h3><?php echo 'Итого в корзине'; ?></h3>
            <table class="dshop-cart__totals-table">
                <tr>
                    <td><?php echo 'Подытог'; ?></td>
                    <td><?php echo esc_html($totals['subtotal']); ?> <?php echo get_option('dshop_currency', 'RUB'); ?></td>
                </tr>
                <?php if ($totals['discount'] !== '0.00'): ?>
                    <tr>
                        <td><?php echo 'Скидка'; ?></td>
                        <td>-<?php echo esc_html($totals['discount']); ?> <?php echo get_option('dshop_currency', 'RUB'); ?></td>
                    </tr>
                <?php endif; ?>
                <?php if ($totals['tax'] !== '0.00'): ?>
                    <tr>
                        <td><?php echo 'Налог'; ?></td>
                        <td><?php echo esc_html($totals['tax']); ?> <?php echo get_option('dshop_currency', 'RUB'); ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td><strong><?php echo 'Итого'; ?></strong></td>
                    <td><strong><?php echo esc_html($totals['total']); ?> <?php echo get_option('dshop_currency', 'RUB'); ?></strong></td>
                </tr>
            </table>
            <a href="<?php echo esc_url(get_permalink(get_option('dshop_checkout_page_id'))); ?>" class="dshop-cart__checkout-button">
                <?php echo 'Перейти к оформлению'; ?>
            </a>
        </div>
    </div>
</div>
