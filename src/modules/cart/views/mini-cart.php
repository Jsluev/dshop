<?php
/**
 * Mini Cart View
 *
 * @package DShop\Modules\Cart
 */

defined('ABSPATH') || exit;

$cart_module = \DShop\Core\DShop::getInstance()->getModule('cart');
$cart = $cart_module->getCart();
$count = $cart->getCount();
$total = $cart->formatPrice($cart->getTotal());
?>

<div class="dshop-mini-cart">
    <div class="dshop-mini-cart__toggle">
        <span class="dshop-mini-cart__icon">&#128722;</span>
        <span class="dshop-mini-cart__count"><?php echo esc_html($count); ?></span>
    </div>
    
    <div class="dshop-mini-cart__dropdown">
        <?php if ($cart->isEmpty()): ?>
            <p class="dshop-mini-cart__empty"><?php echo 'Ваша корзина пуста'; ?></p>
        <?php else: ?>
            <ul class="dshop-mini-cart__items">
                <?php foreach ($cart->getItems() as $key => $item): ?>
                    <li class="dshop-mini-cart__item">
                        <?php
                        $image = get_the_post_thumbnail_url($item['product_id'], 'thumbnail');
                        if (!$image) {
                            $image = dshop_get_placeholder($item['product_id'], 100, 100);
                        }
                        ?>
                            <img src="<?php echo esc_attr($image); ?>" alt="<?php echo esc_attr($item['name']); ?>" class="dshop-mini-cart__item-image">
                        <?php endif; ?>
                        <div class="dshop-mini-cart__item-details">
                            <a href="<?php echo esc_url(get_permalink($item['product_id'])); ?>" class="dshop-mini-cart__item-name">
                                <?php echo esc_html($item['name']); ?>
                            </a>
                            <span class="dshop-mini-cart__item-quantity">
                                <?php echo esc_html($item['quantity']); ?> x <?php echo esc_html(number_format($item['price'], 2, '.', ' ')); ?>
                            </span>
                        </div>
                        <button type="button" class="dshop-mini-cart__item-remove" data-cart-key="<?php echo esc_attr($key); ?>">
                            &times;
                        </button>
                    </li>
                <?php endforeach; ?>
            </ul>
            
            <div class="dshop-mini-cart__footer">
                <div class="dshop-mini-cart__total">
                    <span><?php echo 'Подытог:'; ?></span>
                    <span class="dshop-mini-cart__total-amount"><?php echo esc_html(number_format($cart->getSubtotal(), 2, '.', ' ')); ?> <?php echo get_option('dshop_currency', 'RUB'); ?></span>
                </div>
                <div class="dshop-mini-cart__actions">
                    <a href="<?php echo esc_url(get_permalink(get_option('dshop_cart_page_id'))); ?>" class="dshop-mini-cart__view-cart">
                        <?php echo 'Перейти в корзину'; ?>
                    </a>
                    <a href="<?php echo esc_url(get_permalink(get_option('dshop_checkout_page_id'))); ?>" class="dshop-mini-cart__checkout">
                        <?php echo 'Оформить заказ'; ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
