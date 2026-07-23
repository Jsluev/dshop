# DShop - Дерево файлов проекта

```
dshop/
│
├── dshop.php                          # Точка входа плагина
├── composer.json                      # Зависимости Composer
├── README.md                          # Документация
├── LICENSE.txt                         # Лицензия
│
├── core/                              # ЯДРО ПЛАГИНА
│   ├── DShop.php                      # Главный класс (Singleton)
│   ├── Loader.php                     # Загрузчик модулей
│   ├── Hooks.php                      # Менеджер хуков
│   ├── Config.php                     # Конфигурация
│   ├── Database.php                   # Работа с БД
│   ├── Cache.php                      # Кэширование
│   ├── Logger.php                     # Логирование
│   ├── Updater.php                    # Обновление плагина
│   ├── Router.php                     # Маршрутизатор
│   ├── Queue.php                      # Очередь задач
│   │
│   └── Interfaces/                    # Интерфейсы
│       ├── ModuleInterface.php
│       ├── HookableInterface.php
│       ├── CacheableInterface.php
│       └── ServiceInterface.php
│
├── modules/                           # МОДУЛИ
│   │
│   ├── catalog/                       # КАТАЛОГ ТОВАРОВ
│   │   ├── CatalogModule.php          # Главный класс модуля
│   │   ├── Product.php                # Товар
│   │   ├── ProductType.php            # Типы товаров
│   │   ├── Category.php               # Категории
│   │   ├── Attribute.php              # Атрибуты
│   │   ├── Variable.php               # Вариативные товары
│   │   ├── Gallery.php                # Галерея изображений
│   │   ├── Review.php                 # Отзывы
│   │   ├── Compare.php                # Сравнение
│   │   ├── Wishlist.php               # Избранное
│   │   ├── Import.php                 # Импорт товаров
│   │   ├── Export.php                 # Экспорт товаров
│   │   ├── Shortcodes.php             # Шорткоды
│   │   └── Gutenberg.php              # Блоки Gutenberg
│   │
│   ├── cart/                          # КОРЗИНА
│   │   ├── CartModule.php             # Главный класс модуля
│   │   ├── Cart.php                   # Основной класс корзины
│   │   ├── Session.php                # Сессия корзины
│   │   ├── Calculator.php             # Расчет цен
│   │   ├── Coupon.php                 # Купоны
│   │   ├── MiniCart.php               # Мини-корзина
│   │   └── Widget.php                 # Виджет корзины
│   │
│   ├── checkout/                      # ОФОРМЛЕНИЕ ЗАКАЗА
│   │   ├── CheckoutModule.php         # Главный класс модуля
│   │   ├── Checkout.php               # Процесс оформления
│   │   ├── Order.php                  # Заказ
│   │   ├── OrderStatus.php            # Статусы заказов
│   │   ├── GuestCheckout.php          # Гостевой checkout
│   │   ├── FormHandler.php            # Обработка форм
│   │   └── Validation.php             # Валидация данных
│   │
│   ├── payment/                       # ПЛАТЕЖНЫЕ СИСТЕМЫ
│   │   ├── PaymentModule.php          # Главный класс модуля
│   │   ├── PaymentGateway.php         # Базовый класс
│   │   ├── YooKassa.php               # ЮKassa
│   │   ├── CloudPayments.php          # CloudPayments
│   │   ├── FreePay.php                # Оплата при получении
│   │   └── TestGateway.php            # Тестовый шлюз
│   │
│   ├── shipping/                      # ДОСТАВКА
│   │   ├── ShippingModule.php         # Главный класс модуля
│   │   ├── ShippingMethod.php         # Базовый класс
│   │   ├── Pickup.php                 # Самовывоз
│   │   ├── CityTransport.php          # Городская доставка
│   │   ├── CDEK.php                   # СДЭК
│   │   ├── Boxberry.php               # BoxBerry
│   │   ├── RussianPost.php            # Почта России
│   │   └── Tracker.php                # Отслеживание
│   │
│   ├── inventory/                     # УПРАВЛЕНИЕ ЗАПАСАМИ
│   │   ├── InventoryModule.php        # Главный класс модуля
│   │   ├── Stock.php                  # Остатки
│   │   ├── Warehouse.php              # Склады
│   │   ├── Reservation.php            # Резервирование
│   │   ├── Notification.php           # Уведомления о запасах
│   │   └── Log.php                    # Лог изменений
│   │
│   ├── crm/                           # CRM КЛИЕНТОВ
│   │   ├── CRMModule.php              # Главный класс модуля
│   │   ├── Customer.php               # Клиент
│   │   ├── Profile.php                # Профиль
│   │   ├── History.php                # История покупок
│   │   ├── Group.php                  # Группы клиентов
│   │   ├── Points.php                 # Бонусные баллы
│   │   ├── Auth.php                   # Авторизация
│   │   └── SocialAuth.php             # Социальный вход
│   │
│   ├── discounts/                     # СКИДКИ И ПРОМО
│   │   ├── DiscountsModule.php        # Главный класс модуля
│   │   ├── Discount.php               # Скидки
│   │   ├── Coupon.php                 # Купоны (расширенный)
│   │   ├── Sale.php                   # Акции
│   │   ├── Bundle.php                 # Наборы товаров
│   │   ├── Loyalty.php                # Программа лояльности
│   │   └── Generator.php              # Генератор купонов
│   │
│   ├── email/                         # EMAIL МАРКЕТИНГ
│   │   ├── EmailModule.php            # Главный класс модуля
│   │   ├── Template.php               # Шаблоны писем
│   │   ├── Newsletter.php             # Рассылки
│   │   ├── Automation.php             # Автоматические письма
│   │   ├── Transactional.php          # Транзакционные письма
│   │   ├── Segment.php                # Сегментация
│   │   └── Stats.php                  # Статистика рассылок
│   │
│   ├── analytics/                     # АНАЛИТИКА
│   │   ├── AnalyticsModule.php        # Главный класс модуля
│   │   ├── Reports.php                # Отчеты
│   │   ├── Metrics.php                # Метрики
│   │   ├── Dashboard.php              # Дашборд
│   │   ├── YandexMetrika.php          # Яндекс.Метрика
│   │   ├── GoogleAnalytics.php        # Google Analytics
│   │   └── Export.php                 # Экспорт данных
│   │
│   ├── seo/                           # SEO
│   │   ├── SEOModule.php              # Главный класс модуля
│   │   ├── MetaTags.php               # Мета-теги
│   │   ├── Schema.php                 # Schema.org
│   │   ├── Sitemap.php                # Карта сайта
│   │   ├── Breadcrumbs.php            # Хлебные крошки
│   │   ├── Redirects.php              # Редиректы
│   │   └── Canonical.php              # Canonical URLs
│   │
│   ├── notifications/                 # УВЕДОМЛЕНИЯ
│   │   ├── NotificationsModule.php    # Главный класс модуля
│   │   ├── Telegram.php               # Telegram бот
│   │   ├── VK.php                     # VK уведомления
│   │   ├── SMS.php                    # SMS уведомления
│   │   ├── Push.php                   # Web push уведомления
│   │   └── Email.php                  # Email уведомления
│   │
│   └── crm_integration/               # ИНТЕГРАЦИИ С CRM
│       ├── IntegrationModule.php      # Главный класс модуля
│       ├── BaseIntegration.php        # Базовый класс
│       ├── AmoCRM.php                 # AmoCRM
│       ├── Bitrix24.php               # Битрикс24
│       ├── HubSpot.php                # HubSpot
│       └── Sync.php                   # Синхронизация данных
│
├── templates/                         # ШАБЛОНЫ
│   │
│   ├── default/                       # Дефолтный шаблон
│   │   ├── archive-product.php        # Архив товаров
│   │   ├── single-product.php         # Страница товара
│   │   ├── cart.php                   # Корзина
│   │   ├── checkout.php               # Оформление заказа
│   │   ├── order-confirmation.php     # Подтверждение заказа
│   │   ├── search.php                 # Поиск
│   │   ├── 404.php                    # Страница не найдена
│   │   │
│   │   ├── partials/                  # Части шаблонов
│   │   │   ├── product-card.php       # Карточка товара
│   │   │   ├── product-gallery.php    # Галерея товара
│   │   │   ├── product-tabs.php       # Табы товара
│   │   │   ├── cart-item.php          # Элемент корзины
│   │   │   ├── cart-totals.php        # Итоги корзины
│   │   │   ├── checkout-form.php      # Форма оформления
│   │   │   ├── order-summary.php      # Итоги заказа
│   │   │   └── pagination.php         # Пагинация
│   │   │
│   │   └── account/                   # Личный кабинет
│   │       ├── dashboard.php          # Дашборд
│   │       ├── orders.php             # История заказов
│   │       ├── order-details.php      # Детали заказа
│   │       ├── profile.php            # Профиль
│   │       ├── addresses.php          # Адреса
│   │       ├── wishlist.php           # Избранное
│   │       └── points.php             # Бонусные баллы
│   │
│   ├── minimalist/                    # Минималистичный шаблон
│   │   └── ... (структура аналогична default)
│   │
│   └── premium/                       # Премиум шаблон
│       └── ... (структура аналогична default)
│
├── assets/                            # СТАТИЧЕСКИЕ ФАЙЛЫ
│   │
│   ├── css/
│   │   ├── front.css                  # Фронтенд стили
│   │   ├── admin.css                  # Админ стили
│   │   ├── catalog.css                # Стили каталога
│   │   ├── cart.css                   # Стили корзины
│   │   ├── checkout.css               # Стили оформления
│   │   ├── account.css                # Стили личного кабинета
│   │   ├── responsive.css             # Адаптивные стили
│   │   │
│   │   └── modules/                   # Стили модулей
│   │       ├── payments.css
│   │       ├── shipping.css
│   │       ├── discounts.css
│   │       └── notifications.css
│   │
│   ├── js/
│   │   ├── front.js                   # Фронтенд скрипты
│   │   ├── admin.js                   # Админ скрипты
│   │   ├── catalog.js                 # Скрипты каталога
│   │   ├── cart.js                    # Скрипты корзины
│   │   ├── checkout.js                # Скрипты оформления
│   │   ├── account.js                 # Скрипты личного кабинета
│   │   ├── utils.js                   # Утилиты
│   │   │
│   │   └── modules/                   # Скрипты модулей
│   │       ├── payments.js
│   │       ├── shipping.js
│   │       ├── discounts.js
│   │       └── notifications.js
│   │
│   └── images/
│       ├── icons/                     # Иконки
│       ├── placeholders/              # Плейсхолдеры
│       └── admin/                     # Изображения админки
│
├── includes/                          # ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ
│   ├── functions.php                  # Основные функции
│   ├── helpers.php                    # Хелперы
│   ├── template-tags.php             # Теги шаблонов
│   ├── form-functions.php             # Функции форм
│   ├── ajax-functions.php             # AJAX функции
│   └── upgrade-functions.php          # Функции обновления
│
├── languages/                         # ПЕРЕВОДЫ
│   ├── dshop.pot                      # Шаблон перевода
│   ├── dshop-ru_RU.po                 # Русский перевод
│   └── dshop-ru_RU.mo                 # Скомпилированный перевод
│
├── api/                               # API (БУДУЩЕЕ)
│   │
│   ├── REST/                          # REST API
│   │   ├── ProductsController.php
│   │   ├── OrdersController.php
│   │   ├── CustomersController.php
│   │   ├── CartController.php
│   │   └── SettingsController.php
│   │
│   ├── GraphQL/                       # GraphQL API
│   │   ├── Schema.php
│   │   ├── Types/
│   │   ├── Resolvers/
│   │   └── Directives/
│   │
│   └── Webhooks/                      # Webhooks
│       ├── WebhookManager.php
│       └── handlers/
│
├── tests/                             # ТЕСТЫ
│   │
│   ├── unit/                          # Unit тесты
│   │   ├── Core/
│   │   ├── Catalog/
│   │   ├── Cart/
│   │   ├── Checkout/
│   │   └── ...
│   │
│   ├── integration/                   # Integration тесты
│   │   ├── Database/
│   │   ├── Payment/
│   │   ├── Shipping/
│   │   └── ...
│   │
│   └── e2e/                           # E2E тесты
│       ├── cart-flow.test.js
│       ├── checkout-flow.test.js
│       └── payment-flow.test.js
│
├── docs/                              # ДОКУМЕНТАЦИЯ
│   ├── installation.md                # Установка
│   ├── configuration.md               # Настройка
│   ├── modules/                       # Документация модулей
│   │   ├── catalog.md
│   │   ├── cart.md
│   │   ├── checkout.md
│   │   └── ...
│   ├── hooks/                         # Хуки и фильтры
│   │   ├── actions.md
│   │   └── filters.md
│   ├── api/                           # API документация
│   └── faq.md                         # Часто задаваемые вопросы
│
├── bin/                               # СКРИПТЫ
│   ├── install.sh                     # Установка
│   ├── build.sh                       # Сборка
│   └── deploy.sh                      # Деплой
│
└── .github/                           # GitHub
    └── workflows/
        ├── ci.yml                     # CI пайплайн
        └── release.yml                # Релиз
```

## ОБЩАЯ СТАТИСТИКА

- **Количество файлов**: ~150+
- **Количество строк кода**: ~20000+
- **Количество модулей**: 13
- **Количество шаблонов**: 3
- **Количество тестов**: ~200+
