<?php defined('ABSPATH') || exit; ?>
<div class="wrap">
    <h1>Настройки корзины</h1>
    <form method="post">
        <?php wp_nonce_field('dshop_cart_save'); ?>

        <table class="form-table">
            <tr>
                <th><label for="redirect_after_add">Редирект после добавления</label></th>
                <td>
                    <select name="redirect_after_add" id="redirect_after_add">
                        <option value="" <?php selected($settings['redirect_after_add'], ''); ?>>Без редиректа</option>
                        <option value="cart" <?php selected($settings['redirect_after_add'], 'cart'); ?>>На страницу корзины</option>
                        <option value="checkout" <?php selected($settings['redirect_after_add'], 'checkout'); ?>>На страницу оформления</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="cart_page_id">Страница корзины</label></th>
                <td><?php
                    wp_dropdown_pages([
                        'name' => 'cart_page_id',
                        'id' => 'cart_page_id',
                        'selected' => $settings['cart_page_id'],
                        'show_option_none' => '-- Выберите страницу --',
                    ]);
                ?></td>
            </tr>
            <tr>
                <th><label for="checkout_page_id">Страница оформления</label></th>
                <td><?php
                    wp_dropdown_pages([
                        'name' => 'checkout_page_id',
                        'id' => 'checkout_page_id',
                        'selected' => $settings['checkout_page_id'],
                        'show_option_none' => '-- Выберите страницу --',
                    ]);
                ?></td>
            </tr>
        </table>

        <p>Шорткоды: <code>[dshop_cart]</code> — полная корзина, <code>[dshop_mini_cart]</code> — мини-корзина</p>

        <?php submit_button('Сохранить', 'primary', 'dshop_save_cart'); ?>
    </form>
</div>
