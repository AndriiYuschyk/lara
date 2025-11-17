## Тестове завдання на вакансію PHP Backend Developer (Laravel)

## Використані технології
- PHP 8.4
- Laravel 12
- MariaDB
- PhpMyAdmin
- Nginx
- Docker & Docker Compose
- Swagger (OpenAPI)
- TailwindCSS (для стартової сторінки)

## Системні вимоги
- Git
- Docker & Docker Compose

## Функціонал
- Документація API у форматі Swagger доступна за роутом **api/documentation**
- Створення/Оновлення компанії з підтримкою версійності змін за роутом **api/company**
- Перегляд всіх версій змін даних компанії за її ЄДРПОУ за роутом **api/company/{edrpou}/versions**
- Перегляд список усіх компаній збережених в системі за роутом **api/companies**
- Створено **companies** i **company_versions** міграції для створення таблиць в БД
- Створено моделі **Company** і **CompanyVersion** для взаємодії з відповідними таблицями в БД
- Створено сід **CompanySeeder** для наповнення списку компаній
- Реалізовано функціонал збереження версійності записів за допомогою **HandleVersionsObserver**, **Service VersionComparator** і **HandleVersionsTrait**
- Реалізовано **CompanyRequest** для валідації і попередньої "підготовки" надісланих даних
- Створено **CompanyControllerTest** для тестування роботи функціоналу

## Додатковий функціонал (який не вплинув на результат тестового завдання, але був реалізований для демонстрації навичок роботи з pivot таблицями і зв'язками багато до багатьох)
- Створено додаткову таблицю **kveds** і **pivot** таблицю **company_kved** яка реалізовує зва'язок багато до багатьох між компаніями і їх КВЕДами
- Створено **kveds** i **company_kved** міграції для створення таблиць в БД
- Створено **KvedSeeder** і **CompanyKvedSeeder** для наповнення списку КВЕдів і прив'язки їх до компаній
- Отримати список компаній збережених в системі з їх КВЕДами за роутом **api/companies-kveds**

## Інструкція по розгортанню проєкту за допомогою Docker
1. Клонування репозиторію
```angular2html
git clone [repository_url]
```
2. Запустити скрипт для розгортання Docker-контейнерів (формування env-файлу, збірка образів, запуск контейнерів, встановлення composer, міграції бази даних, наповнення початковими даними):
```angular2html
cd laravel-app

deployment/build.sh
```
3. Дочекайтеся завершення збірки і перевірте статус:
```angular2html
docker-compose ps
```

## Інструкція по розгортанню проєкту за допомогою Docker в ручному режимі без скрипта
1. Створити .env файл з .env.example
```angular2html
cp .env.example .env
```
2. Запускаємо Docker Compose у фоновому режимі з побудовою образів
```angular2html
docker compose up -d --build
```
3. Встановлюємо composer всередині контейнера laravel_app
```angular2html
docker exec -it laravel_app composer install
```
4. Виконуємо міграції та наповнюємо базу даних початковими даними
```angular2html
docker exec -it laravel_app php artisan migrate:fresh --seed
```

## Доступ до проєкту:
- Сайт: http://localhost:8080
- Swagger UI: http://localhost:8080/api/documentation
- phpMyAdmin: http://localhost:8081
- Реалізовані API-ендпоінти описані у Swagger документації:
  - POST: **api/company** - Створення/Оновлення компанії з підтримкою версійності змін
  - GET: **api/company/{edrpou}/versions** - Перегляд всіх версій змін даних компанії за її ЄДРПОУ
  - GET: **api/companies** - Перегляд список усіх компаній збережених в системі
  - GET: **api/companies-kveds** - Перегляд список усіх компаній збережених в системі з їх КВЕДами

## Команда для запуску тестів:
```angular2html
docker exec -it laravel_app php artisan test
```
