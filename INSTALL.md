# Установка окружения для DShop

## Быстрая установка (рекомендуется)

### 1. Установите Docker Desktop

Скачайте и установите Docker Desktop:
- https://www.docker.com/products/docker-desktop/

### 2. Запустите скрипт установки

```bash
# Windows
setup.bat

# Или вручную:
docker-compose up -d
```

### 3. Откройте WordPress

- WordPress: http://localhost:8080
- phpMyAdmin: http://localhost:8081

### 4. Установите WordPress

1. Откройте http://localhost:8080
2. Выберите язык (русский)
3. Введите данные базы данных:
   - Имя базы: dshop
   - Имя пользователя: dshop
   - Пароль: dshop
   - Хост: db
4. Нажмите "Установить WordPress"
5. Заполните данные администратора

### 5. Активируйте плагин

1. Войдите в админку WordPress
2. Перейдите в "Плагины"
3. Найдите "DShop" и активируйте его

### 6. Настройте плагин

1. Перейдите в меню "DShop"
2. Настройте основные параметры
3. Добавьте товары
4. Настройте способы оплаты и доставки

---

## Ручная установка (альтернатива)

### Установка PHP

#### Windows:
1. Скачайте PHP с https://windows.php.net/download/
2. Распакуйте в `C:\php`
3. Добавьте `C:\php` в PATH

#### Или через Chocolatey:
```bash
choco install php
```

### Установка Composer

1. Скачайте Composer с https://getcomposer.org/download/
2. Запустите installer

### Установка MySQL

1. Скачайте MySQL с https://dev.mysql.com/downloads/mysql/
2. Установите и настройте

### Установка WordPress

1. Скачайте WordPress с https://wordpress.org/download/
2. Распакуйте в папку проекта
3. Настройте `wp-config.php`

---

## Команды Docker

```bash
# Запуск
docker-compose up -d

# Остановка
docker-compose down

# Логи
docker-compose logs -f

# Войти в контейнер WordPress
docker-compose exec wordpress bash

# Войти в контейнер MySQL
docker-compose exec db mysql -u dshop -p dshop

# Перезапуск
docker-compose restart
```

---

## Установка зависимостей (для разработки)

### Через Docker:

```bash
docker-compose exec wordpress bash
cd /var/www/html/wp-content/plugins/dshop
composer install
```

### Локально (если установлен PHP):

```bash
composer install
```

---

## Тестирование плагина

1. Откройте WordPress: http://localhost:8080
2. Войдите в админку
3. Активируйте плагин DShop
4. Создайте товары
5. Настройте способы оплаты
6. Настройте способы доставки
7. Протестируйте оформление заказа

---

## Решение проблем

### Docker не запускается:
1. Убедитесь, что Docker Desktop запущен
2. Проверьте, что виртуализация включена в BIOS

### WordPress не подключается к БД:
1. Подождите 30 секунд после запуска
2. Проверьте логи: `docker-compose logs db`

### Плагин не активируется:
1. Проверьте логи WordPress: `docker-compose logs wordpress`
2. Убедитесь, что файлы плагина скопированы в `plugins/dshop`

---

## Полезные ссылки

- Docker Desktop: https://www.docker.com/products/docker-desktop/
- WordPress: https://wordpress.org/
- PHP: https://www.php.net/
- Composer: https://getcomposer.org/
