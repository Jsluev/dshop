<?php
/**
 * DShop Cart Page — standalone template
 */
defined('ABSPATH') || exit;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Корзина — <?php echo esc_html(get_bloginfo('name')); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('dshop-page'); ?>>

<div class="dshop-page__wrapper">
    <?php include DSHOP_TEMPLATE_DIR . 'parts/header.php'; ?>

    <main class="dshop-cart-page">
        <div class="dshop-single__breadcrumbs">
            <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
            <span>/</span>
            <span class="dshop-page__current">Корзина</span>
        </div>

        <h1 class="dshop-archive__title">Корзина</h1>

        <?php include DSHOP_TEMPLATE_DIR . 'parts/cart-content.php'; ?>
    </main>
</div>

<?php wp_footer(); ?>
</body>
</html>
