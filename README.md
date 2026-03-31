Digiway Test Project
Репозиторій містить дочірню тему, для розгортання WordPress + WooCommerce проєкту з підтримкою мультимовності (Polylang) та кастомним REST API для імпорту товарів.
Розгорнутий проєкт знаходиться тут:
https://digiway.buckey.space/

Інструкція по розгортанню
1. Клонування репозиторію
git clone https://github.com/drygula/digiway-test.git

2. Налаштування WordPress
Створіть новий сайт на WP.  та підключи файли проєкту.

4. Імпорт бази даних
wp db import database.sql

або через phpMyAdmin.

4. Оновлення домену (якщо потрібно)
wp search-replace 'old-url' 'new-url'
5. Перевірка

Переконайся, що:

WooCommerce активний
Polylang активний
Дочірня тема активована
📡 Виклик API
Endpoint
POST /wp-json/test/v1/import-products
Повний URL (локально)
http://your-site.local/wp-json/test/v1/import-products
🔐 Авторизація

Для локального тесту:

'permission_callback' => '__return_true'

У продакшені рекомендується використовувати:

Application Passwords
або Bearer Token
📦 Приклад запиту
Headers
Content-Type: application/json
Body
[
  {
    "sku": "CHAIR-001",
    "name": "Стілець",
    "price": 49.99,
    "stock": 10,
    "translations": {
      "en": {
        "name": "Chair"
      }
    }
  }
]
📊 Відповідь API
{
  "created": 1,
  "updated": 0,
  "skipped": 0,
  "meta": {
    "success": true,
    "total": 1
  }
}
🔁 Логіка імпорту
Якщо SKU не існує → створюється товар
Якщо SKU існує → товар оновлюється
Створюється або оновлюється переклад (EN)
Дублікатів не створюється
🧠 Особливості реалізації
SKU використовується як унікальний ідентифікатор
Переклади товарів звʼязуються через Polylang
SKU не дублюється в перекладах (уникнення конфліктів)
📁 Структура проєкту
wp-content/
└── themes/
    └── your-child-theme/
        ├── functions.php
        └── inc/
            └── api/
                └── import-products.php
⚠️ Важливо
Git НЕ містить базу даних
Git НЕ містить uploads
Для повного відновлення потрібні:
database.sql
wp-content/uploads/
🔑 Доступи

Для перевірки можна використовувати:

WordPress Admin
URL: http://your-site.local/wp-admin
Login: admin
Password: admin

(змінити при необхідності)

🧪 Тестування
1. Перший запуск

→ створює товар

2. Повторний запуск

→ оновлює товар (не створює дубль)

📌 Рекомендації

Для продакшена:

використовувати Composer для плагінів
винести API у окремий плагін
додати авторизацію
обробку помилок і логування
