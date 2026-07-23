(function($) {
    'use strict';

    var debounceTimers = {};

    var DShop = {

        subtotal: 0,

        shippingCosts: {
            pickup: 0,
            city_transport: 350,
            cdek: 500
        },

        init: function() {
            this.subtotal = parseFloat($('#dshop-total-display').text().replace(/\s/g, '')) || 0;
            this.bindEvents();
            this.initQuantityButtons();
            this.initShippingToggle();
        },

        bindEvents: function() {
            $(document).on('click', '.dshop-add-to-cart__button', this.addToCart.bind(this));
            $(document).on('click', '.dshop-cart__remove', this.removeFromCart.bind(this));
            $(document).on('click', '.dshop-mini-cart__item-remove', this.removeFromCart.bind(this));
            $(document).on('change', '.dshop-quantity__input', this.updateQuantity.bind(this));
            $(document).on('click', '.dshop-quantity__button', this.quantityButton.bind(this));
            $(document).on('submit', '#dshop-checkout-form', this.processCheckout.bind(this));
            $(document).on('change', 'input[name="shipping_method"]', this.onShippingChange.bind(this));
            $(document).on('click', '.dshop-single__thumb', this.switchImage.bind(this));
            $(document).on('submit', '#dshop-coupon-form', this.applyCoupon.bind(this));
            $(document).on('click', '.dshop-cart__coupon-remove', this.removeCoupon.bind(this));
        },

        initQuantityButtons: function() {
            $('.dshop-quantity').each(function() {
                var $input = $(this).find('.dshop-quantity__input');
                $input.data('min', parseInt($input.attr('min')) || 1);
                $input.data('max', parseInt($input.attr('max')) || 999);
            });
        },

        initShippingToggle: function() {
            var $checked = $('input[name="shipping_method"]:checked');
            if ($checked.length) {
                this.toggleAddressFields($checked.val());
                this.updateShippingDisplay($checked.val());
            }
        },

        onShippingChange: function(e) {
            var value = $(e.currentTarget).val();
            this.toggleAddressFields(value);
            this.updateShippingDisplay(value);
        },

        toggleAddressFields: function(method) {
            var $fields = $('#dshop-shipping-fields');
            if (method === 'pickup') {
                $fields.hide();
                $fields.find('input').removeAttr('required');
            } else {
                $fields.show();
                $fields.find('input[type="text"]').attr('required', 'required');
            }
        },

        updateShippingDisplay: function(method) {
            var cost = this.shippingCosts[method] || 0;
            $('#dshop-shipping-cost').val(cost);

            if (cost > 0) {
                $('#dshop-shipping-display').text(cost.toLocaleString('ru-RU') + ' ₽');
            } else {
                $('#dshop-shipping-display').text('Бесплатно');
            }

            var total = this.subtotal + cost;
            $('#dshop-total-display').text(total.toLocaleString('ru-RU') + ' ₽');
        },

        addToCart: function(e) {
            e.preventDefault();
            var $button = $(e.currentTarget);
            var productId = $button.data('product-id');
            if (!productId) return;
            var $qtyInput = $button.closest('.dshop-single__buy, .dshop-product-card__actions, .dshop-single__related')
                .find('.dshop-quantity__input');
            var quantity = $qtyInput.length ? parseInt($qtyInput.val()) || 1 : 1;

            $button.prop('disabled', true);

            $.ajax({
                url: dshop.ajax_url,
                type: 'POST',
                data: {
                    action: 'dshop_add_to_cart',
                    nonce: dshop.nonce,
                    product_id: productId,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        DShop.showNotice('success', response.data.message);
                        DShop.updateCartCount(response.data.cart_count);
                    } else {
                        DShop.showNotice('error', response.data.message);
                    }
                },
                error: function() {
                    DShop.showNotice('error', 'Произошла ошибка. Попробуйте ещё раз.');
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        },

        removeFromCart: function(e) {
            e.preventDefault();
            if (!confirm('Удалить товар из корзины?')) return;

            var $button = $(e.currentTarget);
            var cartKey = $button.data('cart-key');

            $.ajax({
                url: dshop.ajax_url,
                type: 'POST',
                data: {
                    action: 'dshop_remove_from_cart',
                    nonce: dshop.nonce,
                    cart_key: cartKey
                },
                success: function(response) {
                    if (response.success) {
                        var $item = $button.closest('tr, li');
                        $item.fadeOut(200, function() {
                            $(this).remove();
                            if (response.data.totals) {
                                DShop.refreshCartTotals(response.data.totals);
                            }
                            if ($('.dshop-cart__item').length === 0) {
                                location.reload();
                            }
                        });
                        DShop.updateCartCount(response.data.cart_count);
                    }
                }
            });
        },

        updateQuantity: function(e) {
            var $input = $(e.currentTarget);
            var cartKey = $input.data('cart-key');
            var quantity = parseInt($input.val());
            var min = $input.data('min') || 1;

            if (isNaN(quantity) || quantity < min) {
                $input.val(min);
                return;
            }

            var timerKey = 'qty_' + cartKey;
            if (debounceTimers[timerKey]) {
                clearTimeout(debounceTimers[timerKey]);
            }

            debounceTimers[timerKey] = setTimeout(function() {
                DShop.sendQuantityUpdate(cartKey, quantity, $input);
            }, 400);
        },

        sendQuantityUpdate: function(cartKey, quantity, $input) {
            $.ajax({
                url: dshop.ajax_url,
                type: 'POST',
                data: {
                    action: 'dshop_update_cart',
                    nonce: dshop.nonce,
                    cart_key: cartKey,
                    quantity: quantity
                },
                success: function(response) {
                    if (response.success) {
                        var $row = $input.closest('tr');
                        var price = parseFloat($row.find('.dshop-cart__price').text().replace(/\s/g, '')) || 0;
                        var lineTotal = (price * quantity).toLocaleString('ru-RU');
                        $row.find('.dshop-cart__total').text(lineTotal + ' ₽');

                        if (response.data.totals) {
                            DShop.refreshCartTotals(response.data.totals);
                        }

                        DShop.updateCartCount(response.data.count);
                    } else {
                        DShop.showNotice('error', response.data.message);
                    }
                }
            });
        },

        quantityButton: function(e) {
            e.preventDefault();
            var $button = $(e.currentTarget);
            var $input = $button.siblings('.dshop-quantity__input');
            var min = $input.data('min') || 1;
            var max = $input.data('max') || 999;
            var value = parseInt($input.val()) || 1;

            if ($button.hasClass('dshop-quantity__button--minus')) {
                value = Math.max(min, value - 1);
            } else {
                value = Math.min(max, value + 1);
            }

            $input.val(value).trigger('change');
        },

        refreshCartTotals: function(totals) {
            if (totals.subtotal !== undefined) {
                $('#dshop-cart-subtotal').text(totals.subtotal + ' ₽');
            }
            if (totals.total !== undefined) {
                $('#dshop-cart-total').text(totals.total + ' ₽');
            }
        },

        applyCoupon: function(e) {
            e.preventDefault();
            var code = $('#dshop-coupon-form input[name="coupon_code"]').val();
            if (!code) return;

            $.ajax({
                url: dshop.ajax_url,
                type: 'POST',
                data: {
                    action: 'dshop_apply_coupon',
                    nonce: dshop.nonce,
                    coupon_code: code
                },
                success: function(response) {
                    if (response.success) {
                        DShop.showNotice('success', response.data.message);
                        location.reload();
                    } else {
                        DShop.showNotice('error', response.data.message);
                    }
                }
            });
        },

        removeCoupon: function(e) {
            e.preventDefault();
            $.ajax({
                url: dshop.ajax_url,
                type: 'POST',
                data: {
                    action: 'dshop_remove_coupon',
                    nonce: dshop.nonce
                },
                success: function() {
                    location.reload();
                }
            });
        },

        processCheckout: function(e) {
            e.preventDefault();
            var $form = $(e.currentTarget);
            var $button = $('.dshop-order-summary__place-order');

            if (!$form[0].checkValidity()) {
                $form[0].reportValidity();
                return;
            }

            $button.prop('disabled', true).text('Обработка...');

            $.ajax({
                url: dshop.ajax_url,
                type: 'POST',
                data: $form.serialize(),
                success: function(response) {
                    if (response.success) {
                        window.location.href = response.data.redirect;
                    } else {
                        DShop.showNotice('error', response.data.message);
                        $button.prop('disabled', false).text('Оформить заказ');
                    }
                },
                error: function() {
                    DShop.showNotice('error', 'Произошла ошибка. Попробуйте ещё раз.');
                    $button.prop('disabled', false).text('Оформить заказ');
                }
            });
        },

        switchImage: function(e) {
            e.preventDefault();
            var $thumb = $(e.currentTarget);
            var imageUrl = $thumb.data('full');
            $('.dshop-single__main-image').attr('src', imageUrl);
            $('.dshop-single__thumb').removeClass('active');
            $thumb.addClass('active');
        },

        showNotice: function(type, message) {
            var $notice = $('<div class="dshop-message dshop-message--' + type + '"></div>').text(message);
            $('.dshop-message').remove();
            $('body').prepend($notice);
            setTimeout(function() {
                $notice.fadeOut(200, function() { $(this).remove(); });
            }, 4000);
        },

        updateCartCount: function(count) {
            var $badge = $('.dshop-header__cart-badge');
            $badge.text(count);
            if (count > 0) {
                $badge.addClass('dshop-header__cart-badge--visible');
            } else {
                $badge.removeClass('dshop-header__cart-badge--visible');
            }
            $badge.removeClass('dshop-header__cart-badge--bounce');
            void $badge[0].offsetWidth;
            $badge.addClass('dshop-header__cart-badge--bounce');

            $('.dshop-mini-cart__count').text(count);
        }
    };

    $(document).ready(function() {
        DShop.init();
    });

})(jQuery);
