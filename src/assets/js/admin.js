/* DShop Admin JS */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Confirm bulk actions
        $('.dshop-bulk-action').on('click', function(e) {
            var action = $(this).closest('form').find('select[name="action"]').val();
            if (action === '-1') return;
            if (!confirm('Вы уверены?')) {
                e.preventDefault();
            }
        });

        // Status toggle
        $('.dshop-toggle-status').on('click', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var id = $btn.data('id');
            var type = $btn.data('type');
            var nonce = $btn.data('nonce');

            $.post(dshop_admin.ajax_url, {
                action: 'dshop_toggle_status',
                id: id,
                type: type,
                _ajax_nonce: nonce
            }, function(response) {
                if (response.success) {
                    $btn.closest('tr').find('.dshop-status').text(response.data.status);
                } else {
                    alert(response.data.message || 'Ошибка');
                }
            });
        });

        // Delete with confirmation
        $('.dshop-delete').on('click', function(e) {
            if (!confirm('Удалить?')) {
                e.preventDefault();
            }
        });

        // Tab switching in settings
        $('.dshop-tabs-nav a').on('click', function(e) {
            e.preventDefault();
            var target = $(this).attr('href');
            $('.dshop-tabs-nav a').removeClass('active');
            $(this).addClass('active');
            $('.dshop-tab-panel').hide();
            $(target).show();
        });

        // Color picker init if available
        if ($.fn.wpColorPicker) {
            $('.dshop-color-field').wpColorPicker();
        }

        // Media uploader for image fields
        $(document).on('click', '.dshop-upload-image', function(e) {
            e.preventDefault();
            var $field = $(this).prev('input');
            var frame = wp.media({
                title: 'Выберите изображение',
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                $field.val(attachment.url);
            });
            frame.open();
        });
    });
})(jQuery);
