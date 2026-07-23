<?php
/**
 * Single Product Template — standalone (no theme header/footer)
 */

defined('ABSPATH') || exit;

$product_id = get_the_ID();
$product = get_post($product_id);

if (!$product || $product->post_type !== 'dshop_product') {
    status_header(404);
    nocache_headers();
    echo '<!DOCTYPE html><html lang="ru"><head><meta charset="UTF-8"><title>Товар не найден</title></head><body style="font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;background:#f9fafb"><div style="text-align:center"><h1 style="font-size:24px;color:#1f2937">Товар не найден</h1><a href="/" style="color:#2563eb;margin-top:12px;display:inline-block">На главную</a></div></body></html>';
    return;
}

$price = (float) get_post_meta($product_id, '_dshop_price', true);
$sale_price = get_post_meta($product_id, '_dshop_sale_price', true);
$sku = get_post_meta($product_id, '_dshop_sku', true);
$stock_quantity = (int) get_post_meta($product_id, '_dshop_stock_quantity', true);
$manage_stock = (bool) get_post_meta($product_id, '_dshop_manage_stock', true);
$weight = get_post_meta($product_id, '_dshop_weight', true);
$length = get_post_meta($product_id, '_dshop_length', true);
$width = get_post_meta($product_id, '_dshop_width', true);
$height = get_post_meta($product_id, '_dshop_height', true);
$short_description = get_post_meta($product_id, '_dshop_short_description', true);
$content = apply_filters('the_content', $product->post_content);

// Image
$thumbnail_id = get_post_thumbnail_id($product_id);
$gallery_ids = get_post_meta($product_id, '_dshop_gallery', true);

if ($thumbnail_id) {
    $main_image = wp_get_attachment_image_url($thumbnail_id, 'large') ?: dshop_get_placeholder($product_id);
} else {
    $main_image = dshop_get_placeholder($product_id);
}

if ($manage_stock && $stock_quantity <= 0) {
    $in_stock = false;
    $stock_text = 'Нет в наличии';
    $stock_class = 'out';
} else {
    $in_stock = true;
    $stock_text = $manage_stock ? "В наличии: $stock_quantity шт." : 'В наличии';
    $stock_class = 'in';
}

// Gallery
$gallery_images = [];
if ($thumbnail_id) {
    $gallery_images[] = ['id' => $thumbnail_id, 'url' => $main_image];
}
if (!empty($gallery_ids) && is_array($gallery_ids)) {
    foreach ($gallery_ids as $gid) {
        $url = wp_get_attachment_image_url($gid, 'large');
        if ($url) {
            $gallery_images[] = ['id' => $gid, 'url' => $url];
        }
    }
}

// Related
$categories = get_the_terms($product_id, 'dshop_product_cat');
$related_args = [
    'post_type' => 'dshop_product',
    'posts_per_page' => 4,
    'post__not_in' => [$product_id],
    'post_status' => 'publish',
];
if (!empty($categories) && !is_wp_error($categories)) {
    $related_args['tax_query'] = [[
        'taxonomy' => 'dshop_product_cat',
        'field' => 'term_id',
        'terms' => wp_list_pluck($categories, 'term_id'),
    ]];
}
$related_query = new WP_Query($related_args);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($product->post_title); ?> — <?php echo esc_html(get_bloginfo('name')); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('dshop-page'); ?>>

<div class="dshop-page__wrapper">

    <?php include DSHOP_TEMPLATE_DIR . 'parts/header.php'; ?>

    <main class="dshop-single">

        <div class="dshop-single__breadcrumbs">
            <a href="<?php echo esc_url(home_url('/')); ?>">Главная</a>
            <span>/</span>
            <a href="<?php echo dshop_shop_url(); ?>">Каталог</a>
            <span>/</span>
            <?php if (!empty($categories) && !is_wp_error($categories)): ?>
                <a href="<?php echo esc_url(get_term_link($categories[0])); ?>"><?php echo esc_html($categories[0]->name); ?></a>
                <span>/</span>
            <?php endif; ?>
            <span class="dshop-page__current"><?php echo esc_html($product->post_title); ?></span>
        </div>

        <div class="dshop-single__top">
            <div class="dshop-single__gallery">
                <div class="dshop-single__main-image-wrap">
                    <img class="dshop-single__main-image" src="<?php echo esc_attr($main_image); ?>" alt="<?php echo esc_attr($product->post_title); ?>">
                </div>
                <?php if (count($gallery_images) > 1): ?>
                    <div class="dshop-single__thumbs">
                        <?php foreach ($gallery_images as $i => $img): ?>
                            <img class="dshop-single__thumb <?php echo $i === 0 ? 'active' : ''; ?>"
                             src="<?php echo esc_attr($img['url']); ?>"
                             data-full="<?php echo esc_attr($img['url']); ?>"
                                 alt="">
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="dshop-single__details">
                <h1 class="dshop-single__title"><?php echo esc_html($product->post_title); ?></h1>

                <?php if ($short_description): ?>
                    <p class="dshop-single__short-desc"><?php echo esc_html($short_description); ?></p>
                <?php endif; ?>

                <div class="dshop-single__price-block">
                    <?php if (!empty($sale_price) && (float) $sale_price < $price): ?>
                        <span class="dshop-single__price dshop-single__price--sale"><?php echo number_format((float) $sale_price, 0, '', ' '); ?> ₽</span>
                        <span class="dshop-single__price dshop-single__price--old"><?php echo number_format($price, 0, '', ' '); ?> ₽</span>
                        <span class="dshop-single__discount">-<?php echo round((1 - (float) $sale_price / $price) * 100); ?>%</span>
                    <?php else: ?>
                        <span class="dshop-single__price"><?php echo number_format($price, 0, '', ' '); ?> ₽</span>
                    <?php endif; ?>
                </div>

                <div class="dshop-single__stock <?php echo 'dshop-single__stock--' . $stock_class; ?>">
                    <span class="dshop-single__stock-dot"></span>
                    <?php echo $stock_text; ?>
                </div>

                <div class="dshop-single__buy">
                    <div class="dshop-single__qty">
                        <button type="button" class="dshop-quantity__button dshop-quantity__button--minus">−</button>
                        <input type="number" class="dshop-quantity__input" id="dshop-quantity" value="1" min="1" max="<?php echo $in_stock ? ($manage_stock ? $stock_quantity : 999) : 0; ?>" <?php echo !$in_stock ? 'disabled' : ''; ?>>
                        <button type="button" class="dshop-quantity__button dshop-quantity__button--plus">+</button>
                    </div>
                    <button type="button" class="dshop-single__add-to-cart dshop-add-to-cart__button" data-product-id="<?php echo esc_attr($product_id); ?>" <?php echo !$in_stock ? 'disabled' : ''; ?>>
                        <?php echo $in_stock ? 'В корзину' : 'Нет в наличии'; ?>
                    </button>
                </div>

                <div class="dshop-single__specs">
                    <h3 class="dshop-single__specs-title">Характеристики</h3>
                    <table class="dshop-single__specs-table">
                        <?php if ($sku): ?>
                            <tr><td>Артикул</td><td><?php echo esc_html($sku); ?></td></tr>
                        <?php endif; ?>
                        <?php if ($weight): ?>
                            <tr><td>Вес</td><td><?php echo esc_html($weight); ?> кг</td></tr>
                        <?php endif; ?>
                        <?php if ($length && $width && $height): ?>
                            <tr><td>Размеры</td><td><?php echo esc_html($length . ' × ' . $width . ' × ' . $height); ?> см</td></tr>
                        <?php endif; ?>
                        <?php if (!empty($categories) && !is_wp_error($categories)): ?>
                            <tr>
                                <td>Категория</td>
                                <td>
                                    <?php
                                    $cat_names = [];
                                    foreach ($categories as $cat) {
                                        $cat_names[] = $cat->name;
                                    }
                                    echo esc_html(implode(', ', $cat_names));
                                    ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($content): ?>
            <div class="dshop-single__description">
                <h2 class="dshop-single__section-title">Описание</h2>
                <div class="dshop-single__description-content">
                    <?php echo wp_kses_post($content); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($related_query->have_posts()): ?>
            <div class="dshop-single__related">
                <h2 class="dshop-single__section-title">Похожие товары</h2>
                <div class="dshop-products-grid">
                    <?php while ($related_query->have_posts()): $related_query->the_post();
                        $rid = get_the_ID();
                        $rprice = (float) get_post_meta($rid, '_dshop_price', true);
                        $rsale = get_post_meta($rid, '_dshop_sale_price', true);
                        $rthumb_id = get_post_thumbnail_id($rid);
                        if ($rthumb_id) {
                            $rimg = wp_get_attachment_image_url($rthumb_id, 'medium') ?: dshop_get_placeholder($rid);
                        } else {
                            $rimg = dshop_get_placeholder($rid);
                        }
                    ?>
                        <div class="dshop-product-card">
                            <div class="dshop-product-card__image">
                                <a href="<?php the_permalink(); ?>">
                                    <img src="<?php echo esc_attr($rimg); ?>" alt="<?php echo esc_attr(get_the_title()); ?>">
                                </a>
                            </div>
                            <div class="dshop-product-card__content">
                                <h3 class="dshop-product-card__title">
                                    <a href="<?php the_permalink(); ?>"><?php echo esc_html(get_the_title()); ?></a>
                                </h3>
                                <div class="dshop-product-card__price">
                                    <?php if (!empty($rsale) && (float) $rsale < $rprice): ?>
                                        <span class="dshop-product-card__price--sale"><?php echo number_format((float) $rsale, 0, '', ' '); ?> ₽</span>
                                        <span class="dshop-product-card__price--regular"><?php echo number_format($rprice, 0, '', ' '); ?> ₽</span>
                                    <?php else: ?>
                                        <span class="dshop-price"><?php echo number_format($rprice, 0, '', ' '); ?> ₽</span>
                                    <?php endif; ?>
                                </div>
                                <div class="dshop-product-card__actions">
                                    <button type="button" class="dshop-add-to-cart__button" data-product-id="<?php echo esc_attr($rid); ?>">В корзину</button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
            </div>
        <?php endif; ?>

    </main>

</div>

<?php wp_footer(); ?>
</body>
</html>
