<?php
/**
 * DShop Order Confirmation — standalone template
 */
defined('ABSPATH') || exit;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Заказ оформлен — <?php echo esc_html(get_bloginfo('name')); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('dshop-page'); ?>>

<div class="dshop-page__wrapper">
    <?php include DSHOP_TEMPLATE_DIR . 'parts/header.php'; ?>

    <main class="dshop-confirmation-page">
        <div class="dshop-single__breadcrumbs">
            <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
            <span>/</span>
            <a href="<?php echo dshop_shop_url(); ?>">Каталог</a>
            <span>/</span>
            <span class="dshop-page__current">Заказ оформлен</span>
        </div>

        <?php include DSHOP_SRC_DIR . 'modules/checkout/views/order-confirmation.php'; ?>
    </main>
</div>

<?php wp_footer(); ?>
</body>
</html>
