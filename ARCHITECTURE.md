# DShop - Архитектура плагина WooCommerce-подобного магазина для WordPress

## 1. ОБЩЕЕ ОПИСАНИЕ

**Название**: DShop (рабочее название)
**Тип**: Модульный плагин для WordPress
**Назначение**: Универсальный инструмент для создания интернет-магазинов
**Целевая аудитория**: Студия разработки, разные проекты и заказчики

## 2. КЛЮЧЕВЫЕ ПРИНЦИПЫ АРХИТЕКТУРЫ

### 2.1 Модульность
- Ядро (Core) - минимальный функционал
- Модули подключаются по необходимости
- Каждый модуль независим и может быть отключен

### 2.2 Расширяемость
- Хуки и фильтры WordPress
- Собственный event system
- API для будущих интеграций

### 2.3 Производительность
- Ленивая загрузка модулей
- Кэширование запросов
- Оптимизация для средних нагрузок (до 10000 товаров)

### 2.4 Совместимость
- Classic Editor + Gutenberg
- WooCommerce совместимость (опционально)
- PHP 7.4+ / PHP 8.x

## 3. СТРУКТУРА МОДУЛЕЙ

```
dshop/
├── core/                          # Ядро плагина
│   ├── DShop.php                  # Главный класс плагина
│   ├── Loader.php                 # Загрузчик модулей
│   ├── Hooks.php                  # Система хуков
│   ├── Database.php               # Работа с БД
│   ├── Cache.php                  # Кэширование
│   ├── Logger.php                 # Логирование
│   └── Updater.php                # Обновление плагина
│
├── modules/                       # Модули
│   ├── catalog/                   # Каталог товаров
│   │   ├── Product.php            # Товар
│   │   ├── Category.php           # Категории
│   │   ├── Attribute.php          # Атрибуты
│   │   ├── Variable.php           # Вариативные товары
│   │   ├── Gallery.php            # Галерея изображений
│   │   └── Import/Export.php      # Импорт/экспорт
│   │
│   ├── inventory/                 # Управление запасами
│   │   ├── Stock.php              # Остатки
│   │   ├── Warehouse.php          # Склады
│   │   ├── Reservation.php        # Резервирование
│   │   └── Notification.php       # Уведомления о запасах
│   │
│   ├── cart/                      # Корзина
│   │   ├── Cart.php               # Основной класс
│   │   ├── Session.php            # Сессия корзины
│   │   ├── Coupon.php             # Купоны
│   │   └── Calculator.php         # Расчет цен
│   │
│   ├── checkout/                  # Оформление заказа
│   │   ├── Checkout.php           # Процесс оформления
│   │   ├── Order.php              # Заказ
│   │   ├── OrderStatus.php        # Статусы заказов
│   │   └── Guest.php              # Гостевой checkout
│   │
│   ├── payment/                   # Платежные системы
│   │   ├── PaymentGateway.php     # Базовый класс
│   │   ├── YooKassa.php           # ЮKassa
│   │   ├── CloudPayments.php      # CloudPayments
│   │   └── FreePay.php            # Оплата при получении
│   │
│   ├── shipping/                  # Доставка
│   │   ├── ShippingMethod.php     # Базовый класс
│   │   ├── Pickup.php             # Самовывоз
│   │   ├── CityTransport.php      # Городская доставка
│   │   ├── CDEK.php               # СДЭК
│   │   ├── Boxberry.php           # BoxBerry
│   │   └── RussianPost.php        # Почта России
│   │
│   ├── discounts/                 # Скидки и промо
│   │   ├── Discount.php           # Скидки
│   │   ├── Coupon.php             # Купоны
│   │   ├── Sale.php               # Акции
│   │   └── Loyalty.php            # Программа лояльности
│   │
│   ├── crm/                       # CRM клиентов
│   │   ├── Customer.php           # Клиент
│   │   ├── Profile.php            # Профиль
│   │   ├── History.php            # История покупок
│   │   ├── Group.php              # Группы клиентов
│   │   └── Points.php             # Бонусные баллы
│   │
│   ├── email/                     # Email маркетинг
│   │   ├── Template.php           # Шаблоны писем
│   │   ├── Newsletter.php         # Рассылки
│   │   ├── Automation.php         # Автоматические письма
│   │   └── Transactional.php      # Транзакционные письма
│   │
│   ├── analytics/                 # Аналитика
│   │   ├── Reports.php            # Отчеты
│   │   ├── Metrics.php            # Метрики
│   │   ├── YandexMetrika.php      # Яндекс.Метрика
│   │   └── GoogleAnalytics.php    # Google Analytics
│   │
│   ├── seo/                       # SEO оптимизация
│   │   ├── MetaTags.php           # Мета-теги
│   │   ├── Schema.php             # Schema.org
│   │   ├── Sitemap.php            # Карта сайта
│   │   ├── Breadcrumbs.php        # Хлебные крошки
│   │   └── Redirects.php          # Редиректы
│   │
│   ├── notifications/             # Уведомления
│   │   ├── Telegram.php           # Telegram бот
│   │   ├── VK.php                 # VK уведомления
│   │   ├── SMS.php                # SMS уведомления
│   │   └── Push.php               # Web push уведомления
│   │
│   └── crm_integration/           # Интеграция с CRM
│       ├── AmoCRM.php             # AmoCRM
│       ├── Bitrix24.php           # Битрикс24
│       └── HubSpot.php            # HubSpot
│
├── templates/                     # Шаблоны
│   ├── default/                   # Дефолтный шаблон
│   │   ├── single-product.php
│   │   ├── archive-product.php
│   │   ├── cart.php
│   │   ├── checkout.php
│   │   └── account/
│   │
│   ├── minimalist/                # Минималистичный шаблон
│   │   └── ...
│   │
│   └── premium/                   # Премиум шаблон
│       └── ...
│
├── assets/                        # Статические файлы
│   ├── css/
│   │   ├── front.css              # Фронтенд стили
│   │   ├── admin.css              # Админ стили
│   │   └── modules/               # Стили модулей
│   │
│   ├── js/
│   │   ├── front.js               # Фронтенд скрипты
│   │   ├── admin.js               # Админ скрипты
│   │   ├── cart.js                # Корзина
│   │   ├── checkout.js            # Оформление
│   │   └── modules/               # Скрипты модулей
│   │
│   └── images/
│       ├── icons/
│       └── placeholders/
│
├── languages/                     # Переводы
│   └── dshop-ru_RU.po
│
├── includes/                      # Вспомогательные функции
│   ├── functions.php              # Основные функции
│   ├── helpers.php                # Хелперы
│   └── template-tags.php         # Теги шаблонов
│
├── api/                           # API (будущее)
│   ├── REST/
│   │   ├── Products.php
│   │   ├── Orders.php
│   │   └── Customers.php
│   └── Webhooks/
│
└── dshop.php                      # Точка входа плагина
```

## 4. ОСНОВНЫЕ КЛАССЫ И ИНТЕРФЕЙСЫ

### 4.1 Ядро (Core)

```php
namespace DShop\Core;

interface ModuleInterface {
    public function getName(): string;
    public function getVersion(): string;
    public function getDependencies(): array;
    public function init(): void;
    public function activate(): void;
    public function deactivate(): void;
}

abstract class BaseModule implements ModuleInterface {
    protected $app;
    protected $config;
    
    public function __construct(DShop $app) {
        $this->app = $app;
    }
}
```

### 4.2 Главный класс плагина

```php
namespace DShop\Core;

class DShop {
    private static $instance;
    private $modules = [];
    private $hooks;
    private $database;
    private $cache;
    
    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function boot(): void {
        $this->loadModules();
        $this->initModules();
    }
    
    public function loadModule(string $name): void {
        // Ленивая загрузка модулей
    }
    
    public function getModule(string $name) {
        return $this->modules[$name] ?? null;
    }
}
```

## 5. СИСТЕМА ХУКОВ

### 5.1 Кастомные хуки плагина

```php
// Действия
do_action('dshop/product/after_save', $product);
do_action('dshop/order/status_changed', $order, $old_status, $new_status);
do_action('dshop/payment/success', $order, $payment);
do_action('dshop/payment/failed', $order, $payment);
do_action('dshop/cart/updated', $cart);
do_action('dshop/customer/registered', $customer);

// Фильтры
apply_filters('dshop/product/price', $price, $product);
apply_filters('dshop/product/title', $title, $product);
apply_filters('dshop/order/total', $total, $order);
apply_filters('dshop/shipping/cost', $cost, $method, $order);
apply_filters('dshop/email/template', $template, $data);
```

## 6. БАЗА ДАННЫХ

### 6.1 Таблицы

```sql
-- Товары
dshop_products (
    id, sku, name, slug, description, short_description,
    price, sale_price, cost_price,
    stock_quantity, stock_status, manage_stock,
    weight, length, width, height,
    category_id, type (simple/variable/grouped),
    status (publish/draft/pending),
    created_at, updated_at
)

-- Варианты товаров
dshop_product_variants (
    id, product_id, sku, attributes (JSON),
    price, stock_quantity, image_id
)

-- Заказы
dshop_orders (
    id, order_number, customer_id,
    status, total, subtotal, tax, shipping_cost,
    billing (JSON), shipping (JSON),
    payment_method, payment_status,
    notes, created_at, updated_at
)

-- Позиции заказа
dshop_order_items (
    id, order_id, product_id, variant_id,
    quantity, price, total
)

-- Клиенты
dshop_customers (
    id, user_id, email, phone,
    first_name, last_name,
    billing_address (JSON), shipping_address (JSON),
    group_id, points_balance,
    created_at, last_order_at
)

-- Купоны
dshop_coupons (
    id, code, type (percent/fixed/free_shipping),
    value, minimum_spend, maximum_spend,
    usage_limit, used_count,
    expires_at, status
)

-- Склады
dshop_warehouses (
    id, name, address, phone,
    is_default, priority
)

-- Остатки по складам
dshop_stock (
    id, product_id, variant_id, warehouse_id,
    quantity, reserved
)

-- Лог изменений
dshop_stock_log (
    id, product_id, warehouse_id,
    quantity_change, reason, order_id,
    created_at
)

-- Рейтинги и отзывы
dshop_reviews (
    id, product_id, customer_id,
    rating, title, content,
    status (approve/pending/spam),
    created_at
)

-- Логи
dshop_logs (
    id, level, message, context (JSON),
    created_at
)
```

## 7. СИСТЕМА НАСТРОЕК

### 7.1 Конфигурация модулей

```php
// Конфигурация по умолчанию
return [
    'general' => [
        'currency' => 'RUB',
        'tax_rate' => 0,
        'weight_unit' => 'kg',
        'distance_unit' => 'm',
    ],
    
    'catalog' => [
        'products_per_page' => 12,
        'enable_reviews' => true,
        'enable_wishlist' => true,
        'enable_compare' => false,
    ],
    
    'inventory' => [
        'manage_stock' => true,
        'low_stock_threshold' => 5,
        'out_of_stock_threshold' => 0,
        'allow_backorders' => false,
    ],
    
    'cart' => [
        'redirect_to_checkout' => false,
        'enable_coupon' => true,
        'minimum_order_amount' => 0,
    ],
    
    'payment' => [
        'enabled_methods' => ['yookassa', 'cloudpayments'],
        'test_mode' => false,
    ],
    
    'shipping' => [
        'enabled_methods' => ['pickup'],
        'free_shipping_threshold' => 0,
    ],
    
    'email' => [
        'from_name' => '',
        'from_email' => '',
        'admin_email' => '',
    ],
    
    'seo' => [
        'enable_schema' => true,
        'enable_breadcrumbs' => true,
        'product_title_format' => '{name} - купить в интернет-магазине',
    ],
];
```

## 8. ШАБЛОНИЗАЦИЯ

### 8.1 Система шаблонов

```php
// Переопределение шаблонов
add_filter('dshop/template_path', function($path) {
    return get_stylesheet_directory() . '/dshop/';
});

// Доступные шаблоны
dshop/template/single-product.php
dshop/template/archive-product.php
dshop/template/cart.php
dshop/template/checkout.php
dshop/template/order-confirmation.php
dshop/template/my-account.php
```

### 8.2 Теги шаблонов

```php
dshop_product_price($product_id)
dshop_product_image($product_id, $size)
dshop_product_add_to_cart_button($product_id)
dshop_cart_total()
dshop_checkout_form()
dshop_account_link()
```

## 9. БЕЗОПАСНОСТЬ

- Nonce verification для всех форм
- Capability checks для админских действий
- Sanitization и validation входных данных
- SQL injection protection через prepared statements
- XSS protection через escaping
- CSRF protection
- Rate limiting для API запросов

## 10. ПРОИЗВОДИТЕЛЬНОСТЬ

- Object caching (Redis/Memcached)
- Transient API для кэширования
- Lazy loading изображений
- AJAX для динамических операций
- Минификация CSS/JS
- CDN поддержка
- Оптимизация запросов к БД

## 11. РОУТЕР

```php
// Основные маршруты
GET  /shop/                     # Каталог
GET  /shop/product/{slug}       # Товар
GET  /shop/category/{slug}      # Категория
GET  /shop/cart                 # Корзина
POST /shop/cart/add             # Добавить в корзину
POST /shop/cart/update          # Обновить корзину
GET  /shop/checkout             # Оформление
POST /shop/checkout/process     # Обработка заказа
GET  /shop/order/{id}           # Подтверждение заказа
GET  /shop/account/             # Личный кабинет
GET  /shop/account/orders       # История заказов
GET  /shop/account/profile      # Профиль
GET  /shop/account/wishlist     # Избранное
GET  /shop/search               # Поиск
```

## 12. ИНТЕГРАЦИИ

### 12.1 Текущие интеграции
- ЮKassa - платежи
- CloudPayments - платежи
- Яндекс.Метрика - аналитика
- Google Analytics - аналитика
- Telegram - уведомления

### 12.2 Будущие интеграции
- REST API для мобильных приложений
- GraphQL API
- AmoCRM / Битрикс24
- Службы доставки (СДЭК, BoxBerry)
- СMS Битрикс

## 13. ТЕСТИРОВАНИЕ

- Unit тесты (PHPUnit)
- Integration тесты
- E2E тесты (Cypress/Playwright)
- Тестирование производительности
- Тестирование безопасности

## 14. ДЕПЛОЙ

- Composer для зависимостей
- Git для контроля версий
- CI/CD пайплайн
- Автоматическое тестирование
- Автоматическая сборка
