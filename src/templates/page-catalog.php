<?php
/**
 * DShop Catalog Page — standalone template for the WP page with [dshop_products] shortcode
 */
defined('ABSPATH') || exit;

global $post;
$page_content = $post->post_content;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог — <?php echo esc_html(get_bloginfo('name')); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('dshop-page'); ?>>

<div class="dshop-page__wrapper">

    <?php include DSHOP_TEMPLATE_DIR . 'parts/header.php'; ?>

    <main class="dshop-archive">
        <div class="dshop-single__breadcrumbs">
            <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
            <span>/</span>
            <span class="dshop-page__current">Каталог</span>
        </div>

        <div class="dshop-archive__header">
            <h1 class="dshop-archive__title">Каталог товаров</h1>
        </div>

        <div class="dshop-catalog-content">
            <?php echo do_shortcode($page_content); ?>
        </div>
    </main>

</div>

<?php wp_footer(); ?>
</body>
</html>
