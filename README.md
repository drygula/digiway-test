# Digiway Test Project

Репозиторій містить дочірню тему та дамп бази даних для розгортання WordPress + WooCommerce проєкту з підтримкою мультимовності (Polylang) та кастомним REST API для імпорту товарів.

Розгорнутий проєкт знаходиться тут:

https://digiway.buckey.space/

## Інструкція по розгортанню

1. ### Налаштування WordPress

   Створіть новий сайт на WP.

   Сайт повинен містити стандартну тему Twenty Twenty-Five.

2. ### Тема

   Скопіюйте вміст репозиторію

   git clone https://github.com/drygula/digiway-test.git

   в папку wp-content/themes/digiway-test.

   Після цього активуйте цю тему в адмінці WordPress:

   Зовнішній вигляд → Теми

3. ### Плагіни

   Перед імпортом бази даних потрібно встановити й активувати такі плагіни:
   - WooCommerce
   - Polylang
   - Contact Form 7
   - Multilingual Contact Form 7 with Polylang
   - Hyyan WooCommerce Polylang Integration

4. ### Імпорт бази даних

   Файл бази даних знаходиться тут:

   db-backup/local-20260331-151030.sql

   Імпорт можна зробити через Adminer/phpMyAdmin.

5. ### Оновлення домену (якщо потрібно)

   Після імпорту бази даних потрібно оновити siteurl і home, якщо домен відрізняється від локального оригіналу.

6. ### Перевірка
   Після імпорту переконайтесь, що:
   - активна дочірня тема;
   - активні потрібні плагіни;
   - товари відображаються;
   - Polylang працює коректно;
   - REST API доступний.

## Інструкція для викликів API

1. Для перевірки доступності API скористайтеся

   GET /wp-json/test/v1/ping

   Endpoint для розгорнутого сайту - https://digiway.buckey.space/wp-json/test/v1/ping

2. Для імпорту товарів

   POST /wp-json/test/v1/import-products

   https://digiway.buckey.space/wp-json/test/v1/import-products

   Імпорт працює через авторизацію, доступи додані в кінці документу.

## Приклад JSON для виконання імпорту

```json
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
```

### Логіка імпорту

- якщо товару з таким SKU не існує — він створюється;
- якщо товар із таким SKU уже існує — він оновлюється;
- для товару створюється або оновлюється переклад англійською мовою;
- при повторному імпорті дублікати не створюються.

### Приклад відповіді

```json
{
	"created": 1,
	"updated": 0,
	"skipped": 0,
	"meta": {
		"success": true,
		"total": 1
	}
}
```

## Доступи для перевірки

- Username: digiway-admin
- Password: z26J 2qnr TJoU yDso 9ep0 j0Sj

## Зауваження

Плагін Hyyan WooCommerce Polylang Integration застарілий, я виправив в його коді помилку форматування, але при встановленні з нуля він викликатиме помилку.
