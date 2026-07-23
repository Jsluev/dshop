<?php
/**
 * DShop Header — shared partial, included by all standalone templates
 */
defined('ABSPATH') || exit;

$cart_module = \DShop\Core\DShop::getInstance()->getModule('cart');
$cart_count = $cart_module ? $cart_module->getCart()->getCount() : 0;
?>
<header class="dshop-header">
    <div class="dshop-header__inner">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="dshop-header__logo"><?php echo esc_html(get_bloginfo('name')); ?></a>
        <nav class="dshop-header__nav">
            <a href="<?php echo dshop_shop_url(); ?>">Каталог</a>
            <a href="<?php echo dshop_cart_url(); ?>" class="dshop-header__cart">
                <svg class="dshop-header__cart-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <span class="dshop-header__cart-badge<?php echo $cart_count > 0 ? ' dshop-header__cart-badge--visible' : ''; ?>"><?php echo esc_html($cart_count); ?></span>
            </a>
        </nav>
    </div>
</header>
