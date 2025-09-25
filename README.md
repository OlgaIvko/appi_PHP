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
MySQL доступен на порту 3307 на хостовой машине.

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
docker compose exec mysql mysql -u root wb_api -e "DROP TABLE tokens;"
docker compose exec mysql mysql -u root wb_api -e "DROP TABLE tokens;"
# Отдельные команды
php artisan wb:import --products
php artisan wb:import --orders --date-from=2024-01-01
php artisan test:debug
 docker compose up -d
 php artisan migrate:status
docker сompose exec mysql netstat -tlnp | grep mysql
 docker compose ps
 php artisan app:verify-tz
 php artisan db:seed

 pwd
ls -la


 # Проверка здоровья приложения
curl -X GET http://localhost:8000/api/health

# Проверка списка компаний
curl -X GET http://localhost:8000/api/companies

# Проверка импорта данных
curl -X POST http://localhost:8000/api/import \
  -H "Content-Type: application/json" \
  -d '{"account_id": 1, "api_service_id": 1, "data_type": "products"}'

  # Проверим, что MySQL слушает на нестандартном порту
docker compose exec mysql netstat -tln | grep 3307

# Проверим подключение извне
mysql -h 127.0.0.1 -P 3307 -u wb_user -pwb_password wb_api -e "SELECT 1;"
```

http://64.188.94.175:8080

# Удалить все таблицы

php artisan db:wipe

# Запустить все миграции заново

php artisan migrate
.PHONY: up down build restart logs bash composer

up:
docker-compose up -d

down:
docker-compose down

build:
docker-compose build

restart:
docker-compose restart

logs:
docker-compose logs -f

bash:
docker-compose exec app bash

composer:
docker-compose exec app composer $(filter-out $@,$(MAKECMDGOALS))

artisan:
docker-compose exec app php artisan $(filter-out $@,$(MAKECMDGOALS))

mysql:
docker-compose exec mysql mysql -u wb_user -pwb_password wb_api

%:
@:
