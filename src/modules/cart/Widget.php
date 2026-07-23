<?php
/**
 * Cart Widget
 *
 * @package DShop\Modules\Cart
 */

namespace DShop\Modules\Cart;

use WP_Widget;

/**
 * Class Widget
 *
 * Mini cart widget
 */
class Widget extends WP_Widget
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct(
            'dshop_cart',
            'Корзина DShop',
            ['description' => 'Отображает мини-корзину']
        );
    }

    /**
     * Front-end display
     *
     * @param array $args Widget args
     * @param array $instance Saved values
     * @return void
     */
    public function widget($args, $instance): void
    {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        include DSHOP_SRC_DIR . 'modules/cart/views/mini-cart.php';

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form
     *
     * @param array $instance Previously saved values
     * @return void
     */
    public function form($instance): void
    {
        $title = !empty($instance['title']) ? $instance['title'] : 'Мини-корзина';
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo 'Заголовок:'; ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    /**
     * Sanitize widget form values
     *
     * @param array $new_instance Values to save
     * @param array $old_instance Previously saved values
     * @return array
     */
    public function update($new_instance, $old_instance): array
    {
        $instance = [];
        $instance['title'] = sanitize_text_field($new_instance['title']);
        return $instance;
    }
}
