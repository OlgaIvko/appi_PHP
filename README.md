# WB API Data Importer

Laravel проект для импорта данных из Wildberries API в MySQL базу данных.

## 🚀 Быстрый старт

1. Клонировать репозиторий
2. Установить зависимости: `composer install`
3. Скопировать `.env.example` в `.env`
4. Настроить доступы к БД в `.env`
5. Запустить миграции: `php artisan migrate`
6. Импортировать данные: `php artisan wb:import --all`

## 📊 Доступы к базе данных

**Хост:** sql.freemysqlhosting.net  
**База данных:** sql12345678  
**Пользователь:** sql12345678  
**Пароль:** abcdefgh123  
**Порт:** 3306

**Альтернативно (PlanetScale):**
**Хост:** aws.connect.psdb.cloud  
**База данных:** wb-api-db  
**Пользователь:** abcdef123456  
**Пароль:** pscale_pw_XYZ789  
**SSL:** required

## 🗃️ Структура базы данных

### Таблицы:

-   `products` - товары (nm_id, name, brand, price, etc.)
-   `orders` - заказы (odid, date, etc.)
-   `sales` - продажи (sale_id, date, etc.)
-   `stocks` - остатки на складах
-   `incomes` - поступления товаров

## ⚡ Команды импорта

```bash
# Импорт всех данных
php artisan wb:import --all

# Импорт конкретных данных
php artisan wb:import --products --stocks

# Импорт заказов и продаж с определенной даты
php artisan wb:import --orders --sales --date-from=2024-01-01

# Отдельные команды
php artisan wb:import --products
php artisan wb:import --orders --date-from=2024-01-01
```
