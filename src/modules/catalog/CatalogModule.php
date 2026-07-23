<?php
/**
 * Catalog Module
 *
 * @package DShop\Modules\Catalog
 */

namespace DShop\Modules\Catalog;

use DShop\Core\BaseModule;

/**
 * Class CatalogModule
 *
 * Handles product catalog functionality
 */
class CatalogModule extends BaseModule
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name = 'catalog';

    /**
     * Module version
     *
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Module description
     *
     * @var string
     */
    protected $description = 'Product catalog management module';

    /**
     * {@inheritdoc}
     */
    public function init(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function registerHooks(): void
    {
        add_action('init', [$this, 'registerPostTypes']);
        add_action('init', [$this, 'registerTaxonomies']);

        if (is_admin()) {
            add_action('add_meta_boxes', [$this, 'addProductMetaBoxes']);
            add_action('save_post_dshop_product', [$this, 'saveProduct'], 10, 2);
        }

        add_shortcode('dshop_products', [$this, 'productsShortcode']);
        add_shortcode('dshop_product', [$this, 'productShortcode']);
        add_shortcode('dshop_categories', [$this, 'categoriesShortcode']);

        add_filter('template_include', [$this, 'loadTemplates']);

        if (is_admin()) {
            add_action('admin_init', [$this, 'hideInternalPages']);
        }
    }

    /**
     * Hide internal DShop pages from the WP pages list
     */
    public function hideInternalPages(): void
    {
        $internal = array_filter([
            absint(get_option('dshop_checkout_page_id')),
        ]);

        if (empty($internal)) {
            return;
        }

        add_action('pre_get_posts', function($query) use ($internal) {
            if (!is_admin() || !$query->is_main_query()) {
                return;
            }
            $post_type = $query->get('post_type');
            if ($post_type === 'page' || (empty($post_type) && $query->get('post_type') === '')) {
                $existing = $query->get('post__not_in') ?: [];
                $query->set('post__not_in', array_merge($existing, $internal));
            }
        });
    }

    /**
     * Load plugin templates for dshop pages
     */
    public function loadTemplates(string $template): string
    {
        if (is_singular('dshop_product')) {
            $plugin_template = DSHOP_TEMPLATE_DIR . 'single-dshop_product.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }

        if (is_post_type_archive('dshop_product')) {
            $plugin_template = DSHOP_TEMPLATE_DIR . 'archive-dshop_product.php';
            if (file_exists($plugin_template)) {
                return $plugin_template;
            }
        }

        // Standalone page templates for cart/checkout/catalog
        if (is_page()) {
            $page_id = get_the_ID();
            $templates = [
                get_option('dshop_cart_page_id')       => 'page-cart.php',
                get_option('dshop_checkout_page_id')    => 'page-checkout.php',
                get_option('dshop_shop_page_id')        => 'page-catalog.php',
            ];
            // order confirmation is identified by order_id param on checkout page
            if (isset($_GET['order_id']) && $page_id == get_option('dshop_checkout_page_id')) {
                $plugin_template = DSHOP_TEMPLATE_DIR . 'page-confirmation.php';
            } elseif (isset($templates[$page_id])) {
                $plugin_template = DSHOP_TEMPLATE_DIR . $templates[$page_id];
            }

            if (!empty($plugin_template) && file_exists($plugin_template)) {
                return $plugin_template;
            }
        }

        return $template;
    }

    /**
     * Register custom post types
     *
     * @return void
     */
    public function registerPostTypes(): void
    {
        register_post_type('dshop_product', [
            'labels' => [
                'name' => 'Товары',
                'singular_name' => 'Товар',
                'add_new_item' => 'Добавить новый товар',
                'edit_item' => 'Редактировать товар',
                'view_item' => 'Просмотреть товар',
                'search_items' => 'Поиск товаров',
                'not_found' => 'Товары не найдены',
                'not_found_in_trash' => 'Товары не найдены в корзине',
            ],
            'public' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-cart',
            'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields'],
            'rewrite' => ['slug' => 'shop'],
            'capability_type' => 'post',
            'show_in_rest' => true,
        ]);

        register_post_type('dshop_order', [
            'labels' => [
                'name' => 'Заказы',
                'singular_name' => 'Заказ',
                'add_new_item' => 'Добавить новый заказ',
                'edit_item' => 'Редактировать заказ',
                'view_item' => 'Просмотреть заказ',
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'dshop',
            'menu_icon' => 'dashicons-money-alt',
            'supports' => ['title', 'custom-fields'],
            'capability_type' => 'post',
        ]);

        register_post_type('dshop_customer', [
            'labels' => [
                'name' => 'Клиенты',
                'singular_name' => 'Клиент',
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'dshop',
            'menu_icon' => 'dashicons-admin-users',
            'supports' => ['title', 'custom-fields'],
            'capability_type' => 'post',
        ]);
    }

    /**
     * Register custom taxonomies
     *
     * @return void
     */
    public function registerTaxonomies(): void
    {
        register_taxonomy('dshop_product_cat', 'dshop_product', [
            'labels' => [
                'name' => 'Категории товаров',
                'singular_name' => 'Категория товара',
                'add_new_item' => 'Добавить категорию',
                'edit_item' => 'Редактировать категорию',
            ],
            'hierarchical' => true,
            'public' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'product-category'],
        ]);

        register_taxonomy('dshop_attribute', 'dshop_product', [
            'labels' => [
                'name' => 'Атрибуты',
                'singular_name' => 'Атрибут',
            ],
            'hierarchical' => false,
            'public' => true,
            'show_in_rest' => true,
            'rewrite' => ['slug' => 'attribute'],
        ]);
    }

    /**
     * Add product meta boxes
     *
     * @return void
     */
    public function addProductMetaBoxes(): void
    {
        add_meta_box(
            'dshop_product_data',
            'Данные товара',
            [$this, 'renderProductDataMetabox'],
            'dshop_product',
            'normal',
            'high'
        );

        add_meta_box(
            'dshop_product_short_description',
            'Краткое описание',
            [$this, 'renderShortDescriptionMetabox'],
            'dshop_product',
            'side',
            'default'
        );
    }

    /**
     * Render product data metabox
     *
     * @param \WP_Post $post Post object
     * @return void
     */
    public function renderProductDataMetabox(\WP_Post $post): void
    {
        wp_nonce_field('dshop_product_data', 'dshop_product_nonce');

        $sku = get_post_meta($post->ID, '_dshop_sku', true);
        $price = get_post_meta($post->ID, '_dshop_price', true);
        $sale_price = get_post_meta($post->ID, '_dshop_sale_price', true);
        $stock_quantity = get_post_meta($post->ID, '_dshop_stock_quantity', true);
        $manage_stock = get_post_meta($post->ID, '_dshop_manage_stock', true);
        $weight = get_post_meta($post->ID, '_dshop_weight', true);
        $length = get_post_meta($post->ID, '_dshop_length', true);
        $width = get_post_meta($post->ID, '_dshop_width', true);
        $height = get_post_meta($post->ID, '_dshop_height', true);
        $type = get_post_meta($post->ID, '_dshop_type', true) ?: 'simple';

        include DSHOP_SRC_DIR . 'modules/catalog/views/product-data.php';
    }

    /**
     * Render short description metabox
     *
     * @param \WP_Post $post Post object
     * @return void
     */
    public function renderShortDescriptionMetabox(\WP_Post $post): void
    {
        $short_description = get_post_meta($post->ID, '_dshop_short_description', true);
        ?>
        <textarea name="dshop_short_description" rows="4" style="width:100%"><?php
            echo esc_textarea($short_description);
        ?></textarea>
        <?php
    }

    /**
     * Save product data
     *
     * @param int $post_id Post ID
     * @param \WP_Post $post Post object
     * @return void
     */
    public function saveProduct(int $post_id, \WP_Post $post): void
    {
        if (!isset($_POST['dshop_product_nonce']) ||
            !wp_verify_nonce($_POST['dshop_product_nonce'], 'dshop_product_data')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Sanitize and save data
        $fields = [
            '_dshop_sku' => 'sanitize_text_field',
            '_dshop_price' => 'floatval',
            '_dshop_sale_price' => 'floatval',
            '_dshop_stock_quantity' => 'intval',
            '_dshop_manage_stock' => 'intval',
            '_dshop_weight' => 'floatval',
            '_dshop_length' => 'floatval',
            '_dshop_width' => 'floatval',
            '_dshop_height' => 'floatval',
            '_dshop_type' => 'sanitize_text_field',
            '_dshop_short_description' => 'sanitize_textarea_field',
        ];

        foreach ($fields as $meta_key => $sanitize_func) {
            $field_name = str_replace('_dshop_', 'dshop_', $meta_key);
            if (isset($_POST[$field_name])) {
                update_post_meta($post_id, $meta_key, $sanitize_func($_POST[$field_name]));
            }
        }

        // Update product slug
        $slug = sanitize_title($_POST['post_title'] ?? '');
        wp_update_post([
            'ID' => $post_id,
            'post_name' => $slug,
        ]);

        do_action('dshop/product/after_save', $post_id, $post);
    }

    /**
     * Products shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function productsShortcode(array $atts): string
    {
        $atts = shortcode_atts([
            'limit' => 12,
            'columns' => 3,
            'category' => '',
            'orderby' => 'date',
            'order' => 'DESC',
        ], $atts, 'dshop_products');

        $filter_category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : $atts['category'];
        $filter_min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
        $filter_max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 0;
        $filter_sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '';

        switch ($filter_sort) {
            case 'price_asc': $orderby = 'meta_value_num'; $order = 'ASC'; break;
            case 'price_desc': $orderby = 'meta_value_num'; $order = 'DESC'; break;
            case 'name': $orderby = 'title'; $order = 'ASC'; break;
            default: $orderby = 'date'; $order = 'DESC'; break;
        }

        $args = [
            'post_type' => 'dshop_product',
            'post_status' => 'publish',
            'posts_per_page' => intval($atts['limit']),
            'orderby' => $orderby,
            'order' => $order,
        ];

        if ($orderby === 'meta_value_num') {
            $args['meta_key'] = '_dshop_price';
        }

        if (!empty($filter_category)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'dshop_product_cat',
                    'field' => 'slug',
                    'terms' => $filter_category,
                ],
            ];
        }

        if ($filter_min_price > 0 || $filter_max_price > 0) {
            $args['meta_query'] = [];
            if ($filter_min_price > 0) {
                $args['meta_query'][] = [
                    'key' => '_dshop_price',
                    'value' => $filter_min_price,
                    'compare' => '>=',
                    'type' => 'NUMERIC',
                ];
            }
            if ($filter_max_price > 0) {
                $args['meta_query'][] = [
                    'key' => '_dshop_price',
                    'value' => $filter_max_price,
                    'compare' => '<=',
                    'type' => 'NUMERIC',
                ];
            }
        }

        $query = new \WP_Query($args);
        $columns = intval($atts['columns']);

        $is_filtered = !empty($filter_category) || $filter_min_price > 0 || $filter_max_price > 0 || $filter_sort !== '';

        ob_start();

        $categories = get_terms([
            'taxonomy' => 'dshop_product_cat',
            'hide_empty' => true,
        ]);

        $current_url = remove_query_arg(['category', 'min_price', 'max_price', 'sort', 'paged']);
        ?>
        <div class="dshop-catalog-filters">
            <form class="dshop-catalog-filters__form" method="get" action="<?php echo esc_url($current_url); ?>">
                <?php
                $preserve_params = array_diff_key($_GET, array_flip(['category', 'min_price', 'max_price', 'sort', 'paged']));
                foreach ($preserve_params as $key => $val) :
                ?>
                    <input type="hidden" name="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($val); ?>">
                <?php endforeach; ?>

                <div class="dshop-catalog-filters__group">
                    <label for="dshop-filter-category">Категория</label>
                    <select id="dshop-filter-category" name="category">
                        <option value="">Все категории</option>
                        <?php if (!is_wp_error($categories) && !empty($categories)) :
                            foreach ($categories as $cat) : ?>
                                <option value="<?php echo esc_attr($cat->slug); ?>" <?php selected($filter_category, $cat->slug); ?>><?php echo esc_html($cat->name); ?></option>
                            <?php endforeach;
                        endif; ?>
                    </select>
                </div>

                <div class="dshop-catalog-filters__group">
                    <label for="dshop-filter-min-price">Цена от</label>
                    <input type="number" id="dshop-filter-min_price" name="min_price" min="0" step="1" placeholder="0" value="<?php echo $filter_min_price > 0 ? esc_attr($filter_min_price) : ''; ?>">
                </div>

                <div class="dshop-catalog-filters__group">
                    <label for="dshop-filter-max-price">Цена до</label>
                    <input type="number" id="dshop-filter-max_price" name="max_price" min="0" step="1" placeholder="∞" value="<?php echo $filter_max_price > 0 ? esc_attr($filter_max_price) : ''; ?>">
                </div>

                <div class="dshop-catalog-filters__group">
                    <label for="dshop-filter-sort">Сортировка</label>
                    <select id="dshop-filter-sort" name="sort">
                        <option value="">По умолчанию</option>
                        <option value="newest" <?php selected($filter_sort, 'newest'); ?>>Сначала новые</option>
                        <option value="price_asc" <?php selected($filter_sort, 'price_asc'); ?>>Цена ↑</option>
                        <option value="price_desc" <?php selected($filter_sort, 'price_desc'); ?>>Цена ↓</option>
                        <option value="name" <?php selected($filter_sort, 'name'); ?>>По названию</option>
                    </select>
                </div>

                <div class="dshop-catalog-filters__group">
                    <button type="submit" class="dshop-catalog-filters__submit" style="min-width:auto;padding:8px 20px;">Применить</button>
                    <?php if ($is_filtered) : ?>
                        <a href="<?php echo esc_url($current_url); ?>" style="font-size:13px;color:var(--ds-text-light);text-decoration:none;">Сбросить</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        <?php

        if ($is_filtered) {
            echo '<div class="dshop-catalog-results-count">Найдено товаров: ' . intval($query->found_posts) . '</div>';
        }

        if ($query->have_posts()) {
            echo '<div class="dshop-products-grid" style="grid-template-columns: repeat(' . esc_attr($columns) . ', 1fr);">';

            while ($query->have_posts()) {
                $query->the_post();
                $product_id = get_the_ID();
                $price = get_post_meta($product_id, '_dshop_price', true);
                $sale_price = get_post_meta($product_id, '_dshop_sale_price', true);
                $sku = get_post_meta($product_id, '_dshop_sku', true);
                $stock_quantity = get_post_meta($product_id, '_dshop_stock_quantity', true);
                $manage_stock = get_post_meta($product_id, '_dshop_manage_stock', true);

                $thumbnail_id = get_post_thumbnail_id($product_id);
                if ($thumbnail_id) {
                    $image_url = wp_get_attachment_image_url($thumbnail_id, 'medium') ?: dshop_get_placeholder($product_id);
                } else {
                    $image_url = dshop_get_placeholder($product_id);
                }

                $permalink = get_permalink();
                $title = get_the_title();
                $excerpt = get_the_excerpt();
                ?>
                <div class="dshop-product-card" data-product-id="<?php echo esc_attr($product_id); ?>">
                    <div class="dshop-product-card__image">
                        <a href="<?php echo esc_url($permalink); ?>">
                            <img src="<?php echo esc_attr($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
                        </a>
                    </div>
                    <div class="dshop-product-card__content">
                        <h3 class="dshop-product-card__title">
                            <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($title); ?></a>
                        </h3>
                        <p class="dshop-product-card__description"><?php echo esc_html($excerpt); ?></p>
                        <div class="dshop-product-card__price">
                            <?php if (!empty($sale_price)) : ?>
                                <span class="dshop-product-card__price--sale"><?php echo esc_html(number_format(floatval($sale_price), 0, '', ' ')); ?> ₽</span>
                                <span class="dshop-product-card__price--regular"><?php echo esc_html(number_format(floatval($price), 0, '', ' ')); ?> ₽</span>
                            <?php else : ?>
                                <span class="dshop-price"><?php echo esc_html(number_format(floatval($price), 0, '', ' ')); ?> ₽</span>
                            <?php endif; ?>
                        </div>
                        <div class="dshop-product-card__actions">
                            <button type="button" class="dshop-add-to-cart__button" data-product-id="<?php echo esc_attr($product_id); ?>">В корзину</button>
                        </div>
                    </div>
                </div>
                <?php
            }

            echo '</div>';
        } else {
            echo '<div class="dshop-empty-state">';
            echo '<h3 class="dshop-empty-state__title">Товары не найдены</h3>';
            echo '<p class="dshop-empty-state__text">В данный момент товары отсутствуют.</p>';
            echo '</div>';
        }

        wp_reset_postdata();

        return ob_get_clean();
    }

    /**
     * Single product shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function productShortcode(array $atts): string
    {
        $atts = shortcode_atts([
            'id' => 0,
        ], $atts, 'dshop_product');

        if (empty($atts['id'])) {
            return '<div class="dshop-message dshop-message--error">Товар не найден</div>';
        }

        $product_id = intval($atts['id']);
        $product = get_post($product_id);

        if (!$product || $product->post_type !== 'dshop_product' || $product->post_status !== 'publish') {
            return '<div class="dshop-message dshop-message--error">Товар не найден</div>';
        }

        $price = get_post_meta($product_id, '_dshop_price', true);
        $sale_price = get_post_meta($product_id, '_dshop_sale_price', true);
        $sku = get_post_meta($product_id, '_dshop_sku', true);
        $stock_quantity = get_post_meta($product_id, '_dshop_stock_quantity', true);
        $manage_stock = get_post_meta($product_id, '_dshop_manage_stock', true);
        $short_description = get_post_meta($product_id, '_dshop_short_description', true);
        $thumbnail_id = get_post_thumbnail_id($product_id);
        if ($thumbnail_id) {
            $image_url = wp_get_attachment_image_url($thumbnail_id, 'large') ?: dshop_get_placeholder($product_id);
        } else {
            $image_url = dshop_get_placeholder($product_id);
        }

        $permalink = get_permalink();
        $title = get_the_title();

        $content = apply_filters('the_content', $product->post_content);

        if ($manage_stock && $stock_quantity !== '' && intval($stock_quantity) <= 0) {
            $stock_status = 'out_of_stock';
            $stock_text = 'Нет в наличии';
        } else {
            $stock_status = 'in_stock';
            $stock_text = 'В наличии';
        }

        ob_start();
        ?>
        <div class="dshop-single-product">
            <div class="dshop-single-product__gallery">
                <img class="dshop-single-product__main-image" src="<?php echo esc_attr($image_url); ?>" alt="<?php echo esc_attr($title); ?>">
            </div>
            <div class="dshop-single-product__info">
                <h1 class="dshop-single-product__title"><?php echo esc_html($title); ?></h1>
                <div class="dshop-single-product__price">
                    <?php if (!empty($sale_price)) : ?>
                        <span class="dshop-product-card__price--sale"><?php echo esc_html(number_format(floatval($sale_price), 0, '', ' ')); ?> ₽</span>
                        <span class="dshop-product-card__price--regular"><?php echo esc_html(number_format(floatval($price), 0, '', ' ')); ?> ₽</span>
                    <?php else : ?>
                        <?php echo esc_html(number_format(floatval($price), 0, '', ' ')); ?> ₽
                    <?php endif; ?>
                </div>
                <div class="dshop-single-product__meta">
                    <?php if (!empty($sku)) : ?>
                        <div class="dshop-single-product__meta-item">
                            <span class="dshop-single-product__meta-label">Артикул:</span>
                            <span><?php echo esc_html($sku); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="dshop-single-product__meta-item">
                        <span class="dshop-single-product__meta-label">Наличие:</span>
                        <span><?php echo esc_html($stock_text); ?></span>
                    </div>
                </div>
                <div class="dshop-add-to-cart">
                    <div class="dshop-quantity">
                        <span class="dshop-quantity__label">Количество:</span>
                        <button type="button" class="dshop-quantity__button dshop-quantity__button--minus">−</button>
                        <input type="number" class="dshop-quantity__input" name="quantity" value="1" min="1" <?php echo $stock_status === 'out_of_stock' ? 'max="0"' : ''; ?>>
                        <button type="button" class="dshop-quantity__button dshop-quantity__button--plus">+</button>
                    </div>
                    <button type="button" class="dshop-add-to-cart__button" data-product-id="<?php echo esc_attr($product_id); ?>" <?php echo $stock_status === 'out_of_stock' ? 'disabled' : ''; ?>>
                        <?php echo $stock_status === 'out_of_stock' ? 'Нет в наличии' : 'В корзину'; ?>
                    </button>
                </div>
                <?php if (!empty($short_description)) : ?>
                    <div class="dshop-single-product__description">
                        <p><?php echo esc_html($short_description); ?></p>
                    </div>
                <?php endif; ?>
                <div class="dshop-single-product__description">
                    <?php echo wp_kses_post($content); ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Categories shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function categoriesShortcode(array $atts): string
    {
        $atts = shortcode_atts([
            'limit' => 10,
            'columns' => 4,
            'hide_empty' => false,
        ], $atts, 'dshop_categories');

        $terms = get_terms([
            'taxonomy' => 'dshop_product_cat',
            'number' => intval($atts['limit']),
            'hide_empty' => filter_var($atts['hide_empty'], FILTER_VALIDATE_BOOLEAN),
        ]);

        $columns = intval($atts['columns']);

        ob_start();

        if (!is_wp_error($terms) && !empty($terms)) {
            echo '<div class="dshop-products-grid" style="grid-template-columns: repeat(' . esc_attr($columns) . ', 1fr);">';

            foreach ($terms as $term) {
                $category_url = get_term_link($term->term_id, 'dshop_product_cat');
                if (is_wp_error($category_url)) {
                    continue;
                }

                $thumbnail_id = get_term_meta($term->term_id, 'thumbnail_id', true);
                if ($thumbnail_id) {
                    $image_url = wp_get_attachment_image_url($thumbnail_id, 'medium') ?: dshop_get_placeholder($term->term_id);
                } else {
                    $image_url = dshop_get_placeholder($term->term_id);
                }
                ?>
                <div class="dshop-product-card">
                    <div class="dshop-product-card__image">
                        <a href="<?php echo esc_url($category_url); ?>">
                            <img src="<?php echo esc_attr($image_url); ?>" alt="<?php echo esc_attr($term->name); ?>">
                        </a>
                    </div>
                    <div class="dshop-product-card__content">
                        <h3 class="dshop-product-card__title">
                            <a href="<?php echo esc_url($category_url); ?>"><?php echo esc_html($term->name); ?></a>
                        </h3>
                        <p class="dshop-product-card__description"><?php echo esc_html($term->count); ?> товаров</p>
                    </div>
                </div>
                <?php
            }

            echo '</div>';
        } else {
            echo '<div class="dshop-empty-state">';
            echo '<h3 class="dshop-empty-state__title">Категории не найдены</h3>';
            echo '<p class="dshop-empty-state__text">В данный момент категории отсутствуют.</p>';
            echo '</div>';
        }

        return ob_get_clean();
    }
}
