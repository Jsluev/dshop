<?php
/**
 * DShop Cart Content — shared partial for standalone page and shortcode
 */
defined('ABSPATH') || exit;

$cart_module = \DShop\Core\DShop::getInstance()->getModule('cart');
$cart = $cart_module->getCart();
$items = $cart->getItems();
?>

<?php if ($cart->isEmpty()): ?>
    <div class="dshop-empty-state">
        <div class="dshop-empty-state__icon">&#128722;</div>
        <h2 class="dshop-empty-state__title">Корзина пуста</h2>
        <p class="dshop-empty-state__text">Добавьте товары из каталога</p>
        <a href="<?php echo dshop_shop_url(); ?>" class="dshop-button">Перейти в каталог</a>
    </div>
<?php else: ?>
    <div class="dshop-cart">
        <div>
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
                                    <?php echo esc_html(number_format($item['price'], 0, '', ' ')); ?> <?php echo get_option('dshop_currency', 'RUB'); ?>
                                </td>
                                <td class="dshop-cart__quantity">
                                    <div class="dshop-quantity">
                                        <button type="button" class="dshop-quantity__button dshop-quantity__button--minus" data-cart-key="<?php echo esc_attr($key); ?>">−</button>
                                        <input type="number" class="dshop-quantity__input" value="<?php echo esc_attr($item['quantity']); ?>" min="1" max="999" data-cart-key="<?php echo esc_attr($key); ?>">
                                        <button type="button" class="dshop-quantity__button dshop-quantity__button--plus" data-cart-key="<?php echo esc_attr($key); ?>">+</button>
                                    </div>
                                </td>
                                <td class="dshop-cart__total">
                                    <?php echo esc_html(number_format($item['price'] * $item['quantity'], 0, '', ' ')); ?> <?php echo get_option('dshop_currency', 'RUB'); ?>
                                </td>
                                <td class="dshop-cart__actions">
                                    <button type="button" class="dshop-cart__remove" data-cart-key="<?php echo esc_attr($key); ?>">&times;</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>

        <div class="dshop-cart__sidebar">
            <div class="dshop-cart__totals">
                <h3>Итого</h3>

                <div class="dshop-cart__coupon">
                    <form id="dshop-coupon-form">
                        <input type="text" name="coupon_code" placeholder="Код купона" class="dshop-cart__coupon-input">
                        <button type="submit" class="dshop-cart__coupon-button"><?php echo 'Применить'; ?></button>
                    </form>
                </div>

                <table class="dshop-cart__totals-table">
                    <tr>
                        <td>Товаров:</td>
                        <td><?php echo $cart->getCount(); ?></td>
                    </tr>
                    <tr>
                        <td>Подытог:</td>
                        <td id="dshop-cart-subtotal"><?php echo esc_html(number_format($cart->getSubtotal(), 0, '', ' ')); ?> <?php echo get_option('dshop_currency', 'RUB'); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Итого</strong></td>
                        <td><strong id="dshop-cart-total"><?php echo esc_html(number_format($cart->getTotal(), 0, '', ' ')); ?> <?php echo get_option('dshop_currency', 'RUB'); ?></strong></td>
                    </tr>
                </table>

                <a href="<?php echo dshop_checkout_url(); ?>" class="dshop-cart__checkout-button">
                    <?php echo 'Оформить заказ'; ?>
                </a>
                <a href="<?php echo dshop_shop_url(); ?>" class="dshop-cart__continue">
                    &larr; <?php echo 'Продолжить покупки'; ?>
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
