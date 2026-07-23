# DShop - Modular E-Commerce Plugin for WordPress

## Описание

DShop — это модульный плагин для WordPress, который позволяет быстро создавать интернет-магазины. Плагин разработан как универсальный инструмент для студии разработки.

## Требования

- WordPress 5.9+
- PHP 7.4+
- MySQL 5.7+ или MariaDB 10.3+

## Установка

### Через Composer

```bash
composer require dshop/dshop
```

### Ручная установка

1. Скачайте плагин
2. Закачайте в папку `wp-content/plugins/dshop`
3. Активируйте плагин в админке WordPress
4. Настройте плагин в меню DShop

## Структура проекта

```
dshop/
├── dshop.php              # Точка входа плагина
├── composer.json          # Зависимости Composer
├── src/
│   ├── core/              # Ядро плагина
│   │   ├── DShop.php      # Главный класс
│   │   ├── Loader.php     # Загрузчик модулей
│   │   ├── Hooks.php      # Менеджер хуков
│   │   ├── Config.php     # Конфигурация
│   │   ├── Database.php   # Работа с БД
│   │   ├── Cache.php      # Кэширование
│   │   └── Logger.php     # Логирование
│   │
│   ├── modules/           # Модули
│   │   ├── catalog/       # Каталог товаров
│   │   ├── cart/          # Корзина
│   │   ├── checkout/      # Оформление заказа
│   │   ├── payment/       # Платежные системы
│   │   ├── shipping/      # Доставка
│   │   ├── inventory/     # Управление запасами
│   │   ├── crm/           # CRM клиентов
│   │   ├── discounts/     # Скидки и промо
│   │   ├── email/         # Email маркетинг
│   │   ├── analytics/     # Аналитика
│   │   ├── seo/           # SEO
│   │   ├── notifications/ # Уведомления
│   │   └── crm_integration/ # Интеграции с CRM
│   │
│   ├── templates/         # Шаблоны
│   ├── assets/            # Статические файлы
│   ├── includes/          # Вспомогательные функции
│   └── languages/         # Переводы
│
├── tests/                 # Тесты
└── docs/                  # Документация
```

## Модули

### Каталог товаров
- Товары (простые, вариативные, групповые)
- Категории и атрибуты
- Фильтры и сортировка
- Галерея изображений
- Отзывы и рейтинги

### Корзина
- AJAX корзина
- Купоны и скидки
- Мини-корзина

### Оформление заказа
- Гостевой checkout
- Статусы заказов
- Валидация данных

### Платежные системы
- ЮKassa
- CloudPayments
- Оплата при получении

### Доставка
- Самовывоз
- Городская доставка
- СДЭК
- BoxBerry
- Почта России

### Управление запасами
- Множественные склады
- Резервирование товаров
- Уведомления о запасах

### CRM клиентов
- Профили клиентов
- Группы клиентов
- История покупок
- Бонусные баллы

### Скидки и промо
- Купоны
- Акции
- Программа лояльности

### Email маркетинг
- Транзакционные письма
- Рассылки
- Автоматические серии

### SEO
- Мета-теги
- Schema.org
- XML Sitemap
- Хлебные крошки

### Аналитика
- Встроенные отчеты
- Яндекс.Метрика
- Google Analytics

### Уведомления
- Telegram
- VK
- SMS

### Интеграции с CRM
- AmoCRM
- Битрикс24

## Использование

### Шорткоды

```php
// Вывод товаров
[dshop_products limit="12" columns="3"]

// Один товар
[dshop_product id="123"]

// Категории товаров
[dshop_categories limit="0" columns="3"]
```

### PHP API

```php
// Получить экземпляр DShop
$dshop = \DShop\Core\DShop::getInstance();

// Получить модуль
$catalog = $dshop->getModule('catalog');

// Получить товар
$product = get_post(123);
$price = get_post_meta($product->ID, '_dshop_price', true);
```

## Разработка

### Установка зависимостей

```bash
composer install
```

### Запуск тестов

```bash
composer test
```

### Проверка кода

```bash
composer phpcs
composer phpstan
```

## Лицензия

Proprietary License

## Контакты

- Author: DShop Team
- Author URI: https://example.com
