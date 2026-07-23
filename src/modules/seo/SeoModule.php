<?php
/**
 * SEO Module
 *
 * @package DShop\Modules\Seo
 */

namespace DShop\Modules\Seo;

use DShop\Core\BaseModule;

/**
 * Class SeoModule
 *
 * Handles SEO optimization
 */
class SeoModule extends BaseModule
{
    /**
     * Module name
     *
     * @var string
     */
    protected $name = 'seo';

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
    protected $description = 'SEO optimization module';

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
        add_action('admin_menu', [$this, 'addAdminMenus']);

        // Add meta tags
        add_action('wp_head', [$this, 'addMetaTags'], 1);
        
        // Add Schema.org markup
        add_action('wp_head', [$this, 'addSchemaMarkup'], 2);
        
        // Add breadcrumbs
        add_action('dshop/before_main_content', [$this, 'renderBreadcrumbs']);
        
        // Add sitemap
        add_action('init', [$this, 'registerSitemapEndpoint']);
        
        // Add canonical URLs
        add_action('wp_head', [$this, 'addCanonicalUrl']);
        
        // Admin hooks
        if (is_admin()) {
            add_action('add_meta_boxes', [$this, 'addSeoMetaBoxes']);
            add_action('save_post_dshop_product', [$this, 'saveProductSeo'], 10, 2);
        }
    }

    public function addAdminMenus(): void
    {
        add_submenu_page(
            'dshop',
            'Настройки SEO',
            'SEO',
            'manage_options',
            'dshop-seo',
            [$this, 'renderSeoSettingsPage']
        );
    }

    public function renderSeoSettingsPage(): void
    {
        if (isset($_POST['dshop_save_seo']) && check_admin_referer('dshop_seo_save')) {
            $settings = [
                'meta_title_template' => sanitize_text_field($_POST['meta_title_template'] ?? '{product_name} — {site_name}'),
                'meta_description_template' => sanitize_textarea_field($_POST['meta_description_template'] ?? '{product_excerpt}'),
                'enable_schema' => isset($_POST['enable_schema']) ? 1 : 0,
                'enable_breadcrumbs' => isset($_POST['enable_breadcrumbs']) ? 1 : 0,
                'enable_sitemap' => isset($_POST['enable_sitemap']) ? 1 : 0,
                'og_image_default' => esc_url_raw($_POST['og_image_default'] ?? ''),
            ];
            update_option('dshop_seo_settings', $settings);
            echo '<div class="notice notice-success"><p>Настройки SEO сохранены.</p></div>';
        }

        $settings = get_option('dshop_seo_settings', [
            'meta_title_template' => '{product_name} — {site_name}',
            'meta_description_template' => '{product_excerpt}',
            'enable_schema' => 1,
            'enable_breadcrumbs' => 1,
            'enable_sitemap' => 1,
            'og_image_default' => '',
        ]);

        include DSHOP_SRC_DIR . 'modules/seo/views/seo-settings.php';
    }

    /**
     * Add meta tags to head
     *
     * @return void
     */
    public function addMetaTags(): void
    {
        if (!is_singular('dshop_product')) {
            return;
        }
        
        global $post;
        
        $meta_title = get_post_meta($post->ID, '_dshop_seo_title', true);
        $meta_description = get_post_meta($post->ID, '_dshop_seo_description', true);
        
        if (!$meta_title) {
            $meta_title = get_the_title($post) . ' - ' . get_bloginfo('name');
        }
        
        if (!$meta_description) {
            $meta_description = wp_strip_all_tags(get_the_excerpt($post));
            $meta_description = substr($meta_description, 0, 160);
        }
        
        echo '<title>' . esc_html($meta_title) . '</title>' . "\n";
        echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
        
        // Open Graph
        echo '<meta property="og:title" content="' . esc_attr($meta_title) . '">' . "\n";
        echo '<meta property="og:description" content="' . esc_attr($meta_description) . '">' . "\n";
        echo '<meta property="og:type" content="product">' . "\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink($post)) . '">' . "\n";
        
        $thumbnail = get_the_post_thumbnail_url($post, 'large');
        if ($thumbnail) {
            echo '<meta property="og:image" content="' . esc_url($thumbnail) . '">' . "\n";
        }
        
        // Product specific
        $price = get_post_meta($post->ID, '_dshop_price', true);
        if ($price) {
            echo '<meta property="product:price:amount" content="' . esc_attr($price) . '">' . "\n";
            echo '<meta property="product:price:currency" content="' . esc_attr(get_option('dshop_currency', 'RUB')) . '">' . "\n";
        }
    }

    /**
     * Add Schema.org markup
     *
     * @return void
     */
    public function addSchemaMarkup(): void
    {
        if (!is_singular('dshop_product')) {
            return;
        }
        
        global $post;
        
        $product = [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => get_the_title($post),
            'description' => wp_strip_all_tags(get_the_excerpt($post)),
            'image' => get_the_post_thumbnail_url($post, 'large'),
            'url' => get_permalink($post),
            'sku' => get_post_meta($post->ID, '_dshop_sku', true),
            'brand' => [
                '@type' => 'Brand',
                'name' => get_bloginfo('name'),
            ],
        ];
        
        $price = get_post_meta($post->ID, '_dshop_price', true);
        if ($price) {
            $product['offers'] = [
                '@type' => 'Offer',
                'price' => $price,
                'priceCurrency' => get_option('dshop_currency', 'RUB'),
                'availability' => 'https://schema.org/InStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => get_bloginfo('name'),
                ],
            ];
        }
        
        // Reviews
        $rating = get_post_meta($post->ID, '_dshop_rating', true);
        $review_count = get_post_meta($post->ID, '_dshop_review_count', true);
        if ($rating && $review_count) {
            $product['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $rating,
                'reviewCount' => $review_count,
            ];
        }
        
        echo '<script type="application/ld+json">' . wp_json_encode($product, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
    }

    /**
     * Render breadcrumbs
     *
     * @return void
     */
    public function renderBreadcrumbs(): void
    {
        if (!is_singular('dshop_product') && !is_post_type_archive('dshop_product')) {
            return;
        }
        
        $breadcrumbs = $this->getBreadcrumbs();
        
        if (empty($breadcrumbs)) {
            return;
        }
        
        echo '<nav class="dshop-breadcrumbs" aria-label="Breadcrumb">';
        echo '<div class="dshop-breadcrumbs__container">';
        
        foreach ($breadcrumbs as $index => $crumb) {
            if ($index > 0) {
                echo ' <span class="dshop-breadcrumbs__separator">/</span> ';
            }
            
            if (!empty($crumb['url'])) {
                echo '<a href="' . esc_url($crumb['url']) . '" class="dshop-breadcrumbs__link">' . esc_html($crumb['name']) . '</a>';
            } else {
                echo '<span class="dshop-breadcrumbs__current">' . esc_html($crumb['name']) . '</span>';
            }
        }
        
        echo '</div>';
        echo '</nav>';
        
        // Schema.org BreadcrumbList
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [],
        ];
        
        foreach ($breadcrumbs as $index => $crumb) {
            $item = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['name'],
            ];
            
            if (!empty($crumb['url'])) {
                $item['item'] = $crumb['url'];
            }
            
            $schema['itemListElement'][] = $item;
        }
        
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES) . '</script>' . "\n";
    }

    /**
     * Get breadcrumbs
     *
     * @return array
     */
    private function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        
        // Home
        $breadcrumbs[] = [
            'name' => 'Главная',
            'url' => home_url(),
        ];
        
        // Shop
        $breadcrumbs[] = [
            'name' => 'Магазин',
            'url' => get_post_type_archive_link('dshop_product'),
        ];
        
        if (is_singular('dshop_product')) {
            global $post;
            
            // Product category
            $terms = get_the_terms($post->ID, 'dshop_product_cat');
            if ($terms && !is_wp_error($terms)) {
                $term = reset($terms);
                $breadcrumbs[] = [
                    'name' => $term->name,
                    'url' => get_term_link($term),
                ];
            }
            
            // Product
            $breadcrumbs[] = [
                'name' => get_the_title($post),
                'url' => '',
            ];
        }
        
        return $breadcrumbs;
    }

    /**
     * Register sitemap endpoint
     *
     * @return void
     */
    public function registerSitemapEndpoint(): void
    {
        add_rewrite_endpoint('sitemap-products', EP_ROOT);
    }

    /**
     * Add canonical URL
     *
     * @return void
     */
    public function addCanonicalUrl(): void
    {
        if (!is_singular('dshop_product')) {
            return;
        }
        
        $canonical = get_permalink();
        echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";
    }

    /**
     * Add SEO meta boxes
     *
     * @return void
     */
    public function addSeoMetaBoxes(): void
    {
        add_meta_box(
            'dshop_seo_data',
            'Данные SEO',
            [$this, 'renderSeoMetabox'],
            'dshop_product',
            'side',
            'default'
        );
    }

    /**
     * Render SEO metabox
     *
     * @param \WP_Post $post Post object
     * @return void
     */
    public function renderSeoMetabox(\WP_Post $post): void
    {
        $seo_title = get_post_meta($post->ID, '_dshop_seo_title', true);
        $seo_description = get_post_meta($post->ID, '_dshop_seo_description', true);
        ?>
        <div class="dshop-seo-metabox">
            <p>
                <label for="dshop_seo_title"><?php echo 'SEO Title'; ?></label>
                <input type="text" id="dshop_seo_title" name="dshop_seo_title" value="<?php echo esc_attr($seo_title); ?>" class="widefat" placeholder="<?php echo 'Оставьте пустым для значения по умолчанию'; ?>">
            </p>
            <p>
                <label for="dshop_seo_description"><?php echo 'Meta Description'; ?></label>
                <textarea id="dshop_seo_description" name="dshop_seo_description" class="widefat" rows="3" placeholder="<?php echo 'Оставьте пустым для значения по умолчанию'; ?>"><?php echo esc_textarea($seo_description); ?></textarea>
            </p>
        </div>
        <?php
    }

    /**
     * Save product SEO data
     *
     * @param int $post_id Post ID
     * @param \WP_Post $post Post object
     * @return void
     */
    public function saveProductSeo(int $post_id, \WP_Post $post): void
    {
        if (!isset($_POST['dshop_seo_nonce']) ||
            !wp_verify_nonce($_POST['dshop_seo_nonce'], 'dshop_seo_data')) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        if (isset($_POST['dshop_seo_title'])) {
            update_post_meta($post_id, '_dshop_seo_title', sanitize_text_field($_POST['dshop_seo_title']));
        }
        
        if (isset($_POST['dshop_seo_description'])) {
            update_post_meta($post_id, '_dshop_seo_description', sanitize_textarea_field($_POST['dshop_seo_description']));
        }
    }
}
