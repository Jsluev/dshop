<?php defined('ABSPATH') || exit; ?>
<div class="wrap">
    <h1>Настройки SEO</h1>
    <form method="post">
        <?php wp_nonce_field('dshop_seo_save'); ?>

        <h2>Шаблоны мета-тегов</h2>
        <table class="form-table">
            <tr>
                <th><label for="meta_title_template">Шаблон title</label></th>
                <td>
                    <input type="text" name="meta_title_template" id="meta_title_template" value="<?php echo esc_attr($settings['meta_title_template']); ?>" class="large-text">
                    <p class="description">Переменные: <code>{product_name}</code>, <code>{site_name}</code>, <code>{product_sku}</code></p>
                </td>
            </tr>
            <tr>
                <th><label for="meta_description_template">Шаблон description</label></th>
                <td>
                    <textarea name="meta_description_template" id="meta_description_template" rows="3" class="large-text"><?php echo esc_textarea($settings['meta_description_template']); ?></textarea>
                    <p class="description">Переменные: <code>{product_excerpt}</code>, <code>{product_name}</code>, <code>{product_price}</code></p>
                </td>
            </tr>
        </table>

        <h2>Функциональность</h2>
        <table class="form-table">
            <tr>
                <th><label for="enable_schema">Schema.org разметка</label></th>
                <td><input type="checkbox" name="enable_schema" id="enable_schema" value="1" <?php checked($settings['enable_schema'], 1); ?>></td>
            </tr>
            <tr>
                <th><label for="enable_breadcrumbs">Хлебные крошки</label></th>
                <td><input type="checkbox" name="enable_breadcrumbs" id="enable_breadcrumbs" value="1" <?php checked($settings['enable_breadcrumbs'], 1); ?>></td>
            </tr>
            <tr>
                <th><label for="enable_sitemap">XML-карта сайта</label></th>
                <td><input type="checkbox" name="enable_sitemap" id="enable_sitemap" value="1" <?php checked($settings['enable_sitemap'], 1); ?>></td>
            </tr>
        </table>

        <h2>OpenGraph</h2>
        <table class="form-table">
            <tr>
                <th><label for="og_image_default">Изображение по умолчанию</label></th>
                <td>
                    <input type="url" name="og_image_default" id="og_image_default" value="<?php echo esc_url($settings['og_image_default']); ?>" class="large-text">
                    <p class="description">URL изображения для социальных сетей, если у товара нет自己的 изображения.</p>
                </td>
            </tr>
        </table>

        <?php submit_button('Сохранить', 'primary', 'dshop_save_seo'); ?>
    </form>
</div>
