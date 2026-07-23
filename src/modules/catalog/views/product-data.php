<?php
defined('ABSPATH') || exit;
?>
<div class="dshop-product-data-tabs">
    <ul class="nav-tab-wrapper">
        <li class="nav-tab nav-tab-active"><a href="#general">Основные</a></li>
        <li class="nav-tab"><a href="#inventory">Запасы</a></li>
        <li class="nav-tab"><a href="#shipping">Доставка</a></li>
    </ul>

    <div class="tab-content" id="general">
        <table class="form-table">
            <tr>
                <th><label for="dshop_sku">Артикул (SKU)</label></th>
                <td>
                    <input type="text" id="dshop_sku" name="dshop_sku" value="<?php echo esc_attr($sku); ?>" class="regular-text" />
                    <p class="description">Уникальный код товара</p>
                </td>
            </tr>
            <tr>
                <th><label for="dshop_price">Основная цена</label></th>
                <td>
                    <input type="number" id="dshop_price" name="dshop_price" value="<?php echo esc_attr($price); ?>" step="0.01" min="0" class="regular-text" />
                    <span class="currency">₽</span>
                </td>
            </tr>
            <tr>
                <th><label for="dshop_sale_price">Цена со скидкой</label></th>
                <td>
                    <input type="number" id="dshop_sale_price" name="dshop_sale_price" value="<?php echo esc_attr($sale_price); ?>" step="0.01" min="0" class="regular-text" />
                    <span class="currency">₽</span>
                </td>
            </tr>
            <tr>
                <th><label for="dshop_type">Тип товара</label></th>
                <td>
                    <select id="dshop_type" name="dshop_type">
                        <option value="simple" <?php selected($type, 'simple'); ?>>Простой</option>
                        <option value="variable" <?php selected($type, 'variable'); ?>>Вариативный</option>
                        <option value="grouped" <?php selected($type, 'grouped'); ?>>Групповой</option>
                        <option value="external" <?php selected($type, 'external'); ?>>Внешний</option>
                    </select>
                </td>
            </tr>
        </table>
    </div>

    <div class="tab-content" id="inventory" style="display: none;">
        <table class="form-table">
            <tr>
                <th><label for="dshop_manage_stock">Учёт остатков</label></th>
                <td>
                    <input type="checkbox" id="dshop_manage_stock" name="dshop_manage_stock" value="1" <?php checked($manage_stock, 1); ?> />
                    <label for="dshop_manage_stock">Вести учёт остатков</label>
                </td>
            </tr>
            <tr>
                <th><label for="dshop_stock_quantity">Количество на складе</label></th>
                <td>
                    <input type="number" id="dshop_stock_quantity" name="dshop_stock_quantity" value="<?php echo esc_attr($stock_quantity); ?>" min="0" class="small-text" />
                </td>
            </tr>
        </table>
    </div>

    <div class="tab-content" id="shipping" style="display: none;">
        <table class="form-table">
            <tr>
                <th><label for="dshop_weight">Вес</label></th>
                <td>
                    <input type="number" id="dshop_weight" name="dshop_weight" value="<?php echo esc_attr($weight); ?>" step="0.001" min="0" class="small-text" />
                    <span class="unit"><?php echo get_option('dshop_weight_unit', 'kg'); ?></span>
                </td>
            </tr>
            <tr>
                <th><label for="dshop_length">Длина</label></th>
                <td>
                    <input type="number" id="dshop_length" name="dshop_length" value="<?php echo esc_attr($length); ?>" step="0.001" min="0" class="small-text" />
                    <span class="unit"><?php echo get_option('dshop_distance_unit', 'm'); ?></span>
                </td>
            </tr>
            <tr>
                <th><label for="dshop_width">Ширина</label></th>
                <td>
                    <input type="number" id="dshop_width" name="dshop_width" value="<?php echo esc_attr($width); ?>" step="0.001" min="0" class="small-text" />
                    <span class="unit"><?php echo get_option('dshop_distance_unit', 'm'); ?></span>
                </td>
            </tr>
            <tr>
                <th><label for="dshop_height">Высота</label></th>
                <td>
                    <input type="number" id="dshop_height" name="dshop_height" value="<?php echo esc_attr($height); ?>" step="0.001" min="0" class="small-text" />
                    <span class="unit"><?php echo get_option('dshop_distance_unit', 'm'); ?></span>
                </td>
            </tr>
        </table>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('.nav-tab a').on('click', function(e) {
        e.preventDefault();
        var target = $(this).attr('href');
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).parent().addClass('nav-tab-active');
        $('.tab-content').hide();
        $(target).show();
    });
});
</script>
