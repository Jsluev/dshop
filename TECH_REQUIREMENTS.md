# DShop - Технические требования для разработчиков

## СОДЕРЖАНИЕ

1. [Требования к окружению](#требования-к-окружению)
2. [Стандарты кода](#стандарты-кода)
3. [Архитектурные паттерны](#архитектурные-паттерны)
4. [Работа с БД](#работа-с-бд)
5. [Безопасность](#безопасность)
6. [Производительность](#производительность)
7. [Тестирование](#тестирование)
8. [Документирование](#документирование)

---

## ТРЕБОВАНИЯ К ОКРУЖЕНИЮ

### PHP:
- Версия: 7.4+ (рекомендуется 8.1+)
- Расширения:
  - php-mysql
  - php-json
  - php-curl
  - php-gd
  - php-mbstring
  - php-zip
  - php-xml

### Composer:
- Версия: 2.0+
- Автозагрузка: PSR-4

### WordPress:
- Версия: 5.9+
- Редакторы: Classic Editor + Gutenberg

### База данных:
- MySQL: 5.7+ или MariaDB 10.3+

---

## СТАНДАРТЫ КОДА

### PHP:
- Следовать PSR-12 (Extended Coding Style)
- Использовать типизацию (strict_types)
- Именование классов: PascalCase
- Именование методов: camelCase
- Именование констант: UPPER_SNAKE_CASE
- Именование переменных: camelCase

### JavaScript:
- ES6+ синтаксис
- jQuery для совместимости
- AJAX для динамических операций

### CSS:
- BEM методология
- CSS-переменные для темизации
- Адаптивный дизайн

---

## АРХИТЕКТУРНЫЕ ПАТТЕРНЫ

### 1. Singleton для главного класса
```php
class DShop {
    private static $instance;
    
    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

### 2. Factory для создания объектов
```php
class PaymentGatewayFactory {
    public static function create(string $type): PaymentGateway {
        switch ($type) {
            case 'yookassa':
                return new YooKassa();
            case 'cloudpayments':
                return new CloudPayments();
            default:
                throw new \InvalidArgumentException("Unknown gateway: $type");
        }
    }
}
```

### 3. Observer для событий
```php
// Регистрация обработчика
add_action('dshop/order/created', function($order) {
    // Логика обработки
});

// Вызов события
do_action('dshop/order/created', $order);
```

### 4. Strategy для разных алгоритмов
```php
interface ShippingStrategy {
    public function calculate($order): float;
    public function getEstimate(): string;
}

class CDEKShipping implements ShippingStrategy {
    public function calculate($order): float {
        // Логика расчета СДЭК
    }
}
```

---

## РАБОТА С БД

### Создание таблиц:
```php
global $wpdb;
$table_name = $wpdb->prefix . 'dshop_products';

$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE $table_name (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    sku varchar(100) NOT NULL,
    name varchar(255) NOT NULL,
    slug varchar(255) NOT NULL,
    price decimal(10,2) NOT NULL,
    stock_quantity int(11) DEFAULT 0,
    status varchar(20) DEFAULT 'publish',
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    updated_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY sku (sku),
    UNIQUE KEY slug (slug)
) $charset_collate;";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);
```

### Запросы к БД:
```php
global $wpdb;

// Prepared statements
$product = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}dshop_products WHERE id = %d",
    $product_id
));

// Вставка
$wpdb->insert(
    "{$wpdb->prefix}dshop_products",
    [
        'sku' => $sku,
        'name' => $name,
        'price' => $price,
    ],
    ['%s', '%s', '%f']
);

// Обновление
$wpdb->update(
    "{$wpdb->prefix}dshop_products",
    ['stock_quantity' => $quantity],
    ['id' => $product_id],
    ['%d'],
    ['%d']
);
```

---

## БЕЗОПАСНОСТЬ

### 1. Nonce Verification
```php
// В форме
wp_nonce_field('dshop_save_product', 'dshop_nonce');

// При обработке
if (!isset($_POST['dshop_nonce']) || !wp_verify_nonce($_POST['dshop_nonce'], 'dshop_save_product')) {
    wp_die('Security check');
}
```

### 2. Capability Checks
```php
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized access');
}
```

### 3. Sanitization
```php
$name = sanitize_text_field($_POST['name']);
$description = wp_kses_post($_POST['description']);
$email = sanitize_email($_POST['email']);
$url = esc_url_raw($_POST['url']);
```

### 4. Validation
```php
if (empty($name)) {
    $errors->add('empty_name', 'Name is required');
}

if (!is_email($email)) {
    $errors->add('invalid_email', 'Invalid email address');
}
```

### 5. XSS Protection
```php
// Вывод данных
echo esc_html($product->name);
echo esc_attr($product->slug);
echo esc_url($product->url);
echo wp_kses_post($product->description);
```

---

## ПРОИЗВОДИТЕЛЬНОСТЬ

### 1. Кэширование
```php
// Object Cache
$products = wp_cache_get('dshop_products', 'dshop');
if (false === $products) {
    $products = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dshop_products");
    wp_cache_set('dshop_products', $products, 'dshop', 3600);
}

// Transients
$products = get_transient('dshop_products');
if (false === $products) {
    $products = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}dshop_products");
    set_transient('dshop_products', $products, HOUR_IN_SECONDS);
}
```

### 2. Lazy Loading
```php
// Изображения
echo '<img loading="lazy" src="..." alt="...">';

// AJAX подгрузка
add_action('wp_ajax_dshop_load_products', 'dshop_load_products');
add_action('wp_ajax_nopriv_dshop_load_products', 'dshop_load_products');
```

### 3. Минификация
```php
// CSS
wp_enqueue_style('dshop-front', DSHOP_URL . 'assets/css/front.css', [], DSHOP_VERSION);

// JS
wp_enqueue_script('dshop-front', DSHOP_URL . 'assets/js/front.js', ['jquery'], DSHOP_VERSION, true);
```

---

## ТЕСТИРОВАНИЕ

### Unit тесты:
```php
class ProductTest extends \PHPUnit\Framework\TestCase {
    public function testProductCreation() {
        $product = new Product();
        $product->setName('Test Product');
        $product->setPrice(100.00);
        
        $this->assertEquals('Test Product', $product->getName());
        $this->assertEquals(100.00, $product->getPrice());
    }
}
```

### Integration тесты:
```php
class CartTest extends \WP_UnitTestCase {
    public function testAddToCart() {
        $cart = new Cart();
        $cart->addProduct(123, 2);
        
        $this->assertCount(1, $cart->getItems());
        $this->assertEquals(2, $cart->getItemQuantity(123));
    }
}
```

### E2E тесты:
```javascript
// Cypress
describe('Cart Flow', () => {
    it('should add product to cart', () => {
        cy.visit('/shop/product/test-product');
        cy.get('.add-to-cart').click();
        cy.get('.cart-count').should('contain', '1');
    });
});
```

---

## ДОКУМЕНТИРОВАНИЕ

### PHPDoc:
```php
/**
 * Product class for DShop
 *
 * @package DShop\Modules\Catalog
 * @since 1.0.0
 */
class Product {
    /**
     * Product name
     *
     * @var string
     */
    private $name;
    
    /**
     * Get product name
     *
     * @return string Product name
     */
    public function getName(): string {
        return $this->name;
    }
}
```

### README:
- Описание модуля
- Установка
- Использование
- Примеры
- FAQ

---

## СТРУКТУРА КОДА

### Файл модуля:
```php
<?php
namespace DShop\Modules\Catalog;

use DShop\Core\BaseModule;
use DShop\Core\ModuleInterface;

class CatalogModule extends BaseModule implements ModuleInterface {
    public function getName(): string {
        return 'catalog';
    }
    
    public function init(): void {
        $this->registerPostTypes();
        $this->registerTaxonomies();
        $this->registerHooks();
    }
    
    private function registerPostTypes(): void {
        // Регистрация кастомных постов
    }
    
    private function registerTaxonomies(): void {
        // Регистрация таксономий
    }
    
    private function registerHooks(): void {
        // Регистрация хуков
    }
}
```

---

## ЧЕК-ЛИСТ ДЛЯ РАЗРАБОТЧИКА

### Перед началом работы:
- [ ] Изучить ARCHITECTURE.md
- [ ] Изучить PLAN.md
- [ ] Настроить окружение (PHP, Composer, WordPress)
- [ ] Клонировать репозиторий
- [ ] Установить зависимости: `composer install`

### При разработке:
- [ ] Следовать стандартам кода
- [ ] Использовать типизацию
- [ ] Писать тесты
- [ ] Документировать код
- [ ] Проверять безопасность

### Перед коммитом:
- [ ] Запустить тесты: `composer test`
- [ ] Проверить стиль кода: `composer phpcs`
- [ ] Проверить типы: `composer phpstan`
- [ ] Обновить документацию

---

## ПОЛЕЗНЫЕ КОМАНДЫ

```bash
# Установка зависимостей
composer install

# Запуск тестов
composer test

# Проверка стиля кода
composer phpcs

# Проверка типов
composer phpstan

# Сборка ассетов
npm run build

# Разработка ассетов
npm run dev
```
