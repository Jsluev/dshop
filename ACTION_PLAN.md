# DShop - План действий для начала разработки

## ПРИОРЕТЫ

### Немедленно (Сегодня-Завтра):
1. **Настройка окружения** — установка PHP, Composer, WordPress
2. **Создание базовой структуры** — основные папки и файлы
3. **Инициализация Git** — создание репозитория

### На этой неделе:
4. **Реализация ядра** — главный класс, загрузчик модулей
5. **Реализация каталога** — товары, категории, атрибуты
6. **Создание базы данных** — таблицы для товаров

### В ближайшие 2 недели:
7. **Реализация корзины** — сессия, AJAX, купоны
8. **Реализация checkout** — форма, статусы заказов
9. **Подключение платежей** — ЮKassa, CloudPayments

---

## ШАГ 1: НАСТРОЙКА ОКРУЖЕНИЯ

### 1.1 Установка PHP
```bash
# Windows (через Chocolatey)
choco install php

# Или скачать с https://windows.php.net/download/
```

### 1.2 Установка Composer
```bash
# Скачать с https://getcomposer.org/download/
# Запустить installer
```

### 1.3 Установка WordPress
```bash
# Локальная разработка (через Local by Flywheel или XAMPP)
# Или Docker
docker-compose up -d
```

### 1.4 Настройка IDE
- PhpStorm с плагином WordPress
- Настройка PHPStan
- Настройка PHPCodeSniffer

---

## ШАГ 2: СОЗДАНИЕ БАЗОВОЙ СТРУКТУРЫ

### 2.1 Создание папок
```bash
mkdir -p dshop/{core,modules,templates,assets,languages,includes,api,tests,docs}
mkdir -p dshop/core/Interfaces
mkdir -p dshop/modules/{catalog,cart,checkout,payment,shipping,inventory,crm,discounts,email,analytics,seo,notifications,crm_integration}
```

### 2.2 Создание файлов
```bash
# Ядро
touch dshop/core/DShop.php
touch dshop/core/Loader.php
touch dshop/core/Hooks.php
touch dshop/core/Config.php
touch dshop/core/Database.php
touch dshop/core/Cache.php
touch dshop/core/Logger.php

# Интерфейсы
touch dshop/core/Interfaces/ModuleInterface.php
touch dshop/core/Interfaces/HookableInterface.php
touch dshop/core/Interfaces/CacheableInterface.php

# Точка входа
touch dshop/dshop.php
touch dshop/composer.json
```

---

## ШАГ 3: ИНИЦИАЛИЗАЦИЯ GIT

### 3.1 Создание репозитория
```bash
cd dshop
git init
```

### 3.2 Создание .gitignore
```bash
# Composer
/vendor/
composer.lock

# IDE
/.idea/
/.vscode/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# WordPress
wp-config.php
/wp-content/uploads/
```

### 3.3 Первый коммит
```bash
git add .
git commit -m "Initial commit: DShop plugin structure"
```

---

## ШАГ 4: НАСТРОЙКА COMPOSER

### 4.1 composer.json
```json
{
    "name": "dshop/dshop",
    "description": "Modular e-commerce plugin for WordPress",
    "type": "wordpress-plugin",
    "license": "proprietary",
    "require": {
        "php": ">=7.4",
        "composer/installers": "^2.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.0",
        "phpstan/phpstan": "^1.0",
        "squizlabs/php_codesniffer": "^3.0"
    },
    "autoload": {
        "psr-4": {
            "DShop\\": "src/"
        }
    },
    "scripts": {
        "test": "phpunit",
        "phpcs": "phpcs",
        "phpstan": "phpstan analyse"
    }
}
```

### 4.2 Установка зависимостей
```bash
composer install
```

---

## ШАГ 5: СОЗДАНИЕ ТОЧКИ ВХОДА

### 5.1 dshop.php
```php
<?php
/**
 * Plugin Name: DShop
 * Plugin URI: https://example.com/dshop
 * Description: Modular e-commerce plugin for WordPress
 * Version: 1.0.0
 * Author: Your Studio
 * Author URI: https://example.com
 * Text Domain: dshop
 * Domain Path: /languages
 * Requires at least: 5.9
 * Requires PHP: 7.4
 */

defined('ABSPATH') || exit;

define('DSHOP_VERSION', '1.0.0');
define('DSHOP_PATH', plugin_dir_path(__FILE__));
define('DSHOP_URL', plugin_dir_url(__FILE__));
define('DSHOP_BASENAME', plugin_basename(__FILE__));

// Автозагрузка
require_once DSHOP_PATH . 'vendor/autoload.php';

// Инициализация
add_action('plugins_loaded', function() {
    \DShop\Core\DShop::getInstance()->boot();
});

// Активация
register_activation_hook(__FILE__, function() {
    \DShop\Core\DShop::getInstance()->activate();
});

// Деактивация
register_deactivation_hook(__FILE__, function() {
    \DShop\Core\DShop::getInstance()->deactivate();
});
```

---

## ШАГ 6: РЕАЛИЗАЦИЯ ЯДРА

### 6.1 Главный класс (DShop.php)
```php
<?php
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
        $this->hooks = new Hooks();
        $this->database = new Database();
        $this->cache = new Cache();
        
        $this->loadModules();
        $this->initModules();
    }
    
    public function activate(): void {
        $this->database->createTables();
        $this->createOptions();
    }
    
    public function deactivate(): void {
        // Cleanup
    }
    
    private function loadModules(): void {
        $loader = new Loader();
        $this->modules = $loader->loadModules();
    }
    
    private function initModules(): void {
        foreach ($this->modules as $module) {
            $module->init();
        }
    }
    
    public function getModule(string $name) {
        return $this->modules[$name] ?? null;
    }
}
```

### 6.2 Загрузчик модулей (Loader.php)
```php
<?php
namespace DShop\Core;

class Loader {
    public function loadModules(): array {
        $modules = [];
        
        $moduleClasses = [
            'catalog' => \DShop\Modules\Catalog\CatalogModule::class,
            'cart' => \DShop\Modules\Cart\CartModule::class,
            // ... другие модули
        ];
        
        foreach ($moduleClasses as $name => $class) {
            if ($this->isModuleEnabled($name)) {
                $modules[$name] = new $class();
            }
        }
        
        return $modules;
    }
    
    private function isModuleEnabled(string $name): bool {
        $enabled = get_option("dshop_module_{$name}_enabled", true);
        return apply_filters("dshop/module/{$name}/enabled", $enabled);
    }
}
```

---

## ШАГ 7: СОЗДАНИЕ БАЗЫ ДАННЫХ

### 7.1 Database.php
```php
<?php
namespace DShop\Core;

class Database {
    public function createTables(): void {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Таблица товаров
        $sql_products = "CREATE TABLE {$wpdb->prefix}dshop_products (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            sku varchar(100) NOT NULL,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description longtext,
            short_description text,
            price decimal(10,2) NOT NULL,
            sale_price decimal(10,2),
            stock_quantity int(11) DEFAULT 0,
            stock_status varchar(20) DEFAULT 'instock',
            manage_stock tinyint(1) DEFAULT 0,
            weight decimal(10,2),
            length decimal(10,2),
            width decimal(10,2),
            height decimal(10,2),
            category_id bigint(20),
            type varchar(20) DEFAULT 'simple',
            status varchar(20) DEFAULT 'publish',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY sku (sku),
            UNIQUE KEY slug (slug),
            KEY category_id (category_id),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_products);
        
        // Другие таблицы...
    }
}
```

---

## ШАГ 8: ПЕРВЫЙ ТЕСТ

### 8.1 Создание теста
```bash
mkdir -p tests/unit/Core
touch tests/unit/Core/DShopTest.php
```

### 8.2 Тест DShop
```php
<?php
namespace DShop\Tests\Unit\Core;

use PHPUnit\Framework\TestCase;
use DShop\Core\DShop;

class DShopTest extends TestCase {
    public function testSingleton() {
        $dshop1 = DShop::getInstance();
        $dshop2 = DShop::getInstance();
        
        $this->assertSame($dshop1, $dshop2);
    }
}
```

### 8.3 Запуск тестов
```bash
composer test
```

---

## СЛЕДУЮЩИЕ ДЕЙСТВИЯ

1. **Сегодня**: Настроить окружение, создать базовую структуру
2. **Завтра**: Реализовать ядро (DShop, Loader, Hooks)
3. **На этой неделе**: Реализовать каталог товаров
4. **В следующую неделю**: Реализовать корзину и checkout

---

## КОНТРОЛЬНЫЕ ТОЧКИ

### Неделя 1:
- [ ] Окружение настроено
- [ ] Базовая структура создана
- [ ] Ядро работает
- [ ] Первый тест проходит

### Неделя 2:
- [ ] Каталог товаров работает
- [ ] Товары создаются и отображаются
- [ ] Категории работают
- [ ] Атрибуты работают

### Неделя 3:
- [ ] Корзина работает
- [ ] Товары добавляются в корзину
- [ ] Купоны работают
- [ ] Checkout работает

### Неделя 4:
- [ ] Платежи подключены
- [ ] ЮKassa работает
- [ ] CloudPayments работает
- [ ] Заказы создаются
