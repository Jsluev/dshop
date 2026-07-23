<?php
/**
 * Product Archive Template — standalone
 */

defined('ABSPATH') || exit;
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
        <div class="dshop-archive__header">
            <h1 class="dshop-archive__title">Каталог товаров</h1>
        </div>

        <?php if (have_posts()): ?>
            <div class="dshop-products-grid">
                <?php
                while (have_posts()): the_post();
                    $pid = get_the_ID();
                    $price = (float) get_post_meta($pid, '_dshop_price', true);
                    $sale_price = get_post_meta($pid, '_dshop_sale_price', true);
                    $excerpt = get_the_excerpt();

                    $thumb_id = get_post_thumbnail_id($pid);
                    if ($thumb_id) {
                        $img_url = wp_get_attachment_image_url($thumb_id, 'medium') ?: dshop_get_placeholder($pid);
                    } else {
                        $img_url = dshop_get_placeholder($pid);
                    }
                ?>
                    <div class="dshop-product-card">
                        <div class="dshop-product-card__image">
                            <a href="<?php the_permalink(); ?>">
                                <img src="<?php echo esc_attr($img_url); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
                            </a>
                        </div>
                        <div class="dshop-product-card__content">
                            <h3 class="dshop-product-card__title">
                                <a href="<?php the_permalink(); ?>"><?php echo esc_html(get_the_title()); ?></a>
                            </h3>
                            <?php if ($excerpt): ?>
                                <p class="dshop-product-card__description"><?php echo esc_html($excerpt); ?></p>
                            <?php endif; ?>
                            <div class="dshop-product-card__price">
                                <?php if (!empty($sale_price) && (float) $sale_price < $price): ?>
                                    <span class="dshop-product-card__price--sale"><?php echo number_format((float) $sale_price, 0, '', ' '); ?> ₽</span>
                                    <span class="dshop-product-card__price--regular"><?php echo number_format($price, 0, '', ' '); ?> ₽</span>
                                <?php else: ?>
                                    <span class="dshop-price"><?php echo number_format($price, 0, '', ' '); ?> ₽</span>
                                <?php endif; ?>
                            </div>
                            <div class="dshop-product-card__actions">
                                <button type="button" class="dshop-add-to-cart__button" data-product-id="<?php echo esc_attr($pid); ?>">В корзину</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($max_num_pages > 1): ?>
                <div class="dshop-pagination">
                    <?php
                    echo paginate_links([
                        'prev_text' => '&larr;',
                        'next_text' => '&rarr;',
                    ]);
                    ?>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="dshop-empty-state">
                <div class="dshop-empty-state__icon">📦</div>
                <h2 class="dshop-empty-state__title">Товары не найдены</h2>
                <p class="dshop-empty-state__text">В данный момент товары отсутствуют.</p>
            </div>
        <?php endif; ?>
    </main>

</div>

<?php wp_footer(); ?>
</body>
</html>
