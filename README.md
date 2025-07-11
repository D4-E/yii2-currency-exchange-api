# Тестовое задание

## Решение задания 1: SQL-запрос

**Условие задачи:**  
Необходимо найти всех посетителей библиотеки, которые:
1. Возраст от 7 до 17 лет
2. Взяли ровно две книги одного автора
3. Держали каждую книгу не более 14 дней

**Структура таблиц:**
```sql
users (id, first_name, last_name, birthday)
books (id, name, author)
user_books (id, user_id, book_id, get_date, return_date)
```

**Решение 1:**

```sql
SELECT
    u.id AS ID,
    CONCAT(u.first_name, ' ', u.last_name) AS Name,
    MIN(b.author) AS Author,
    GROUP_CONCAT(b.name ORDER BY b.name SEPARATOR ', ') AS Books
FROM users u
JOIN user_books ub ON u.id = ub.user_id
JOIN books b ON b.id = ub.book_id
WHERE TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 7 AND 17
    AND DATEDIFF(ub.return_date, ub.get_date) <= 14
GROUP BY u.id
HAVING COUNT(*) = 2
    AND COUNT(DISTINCT b.id) = 2
    AND COUNT(DISTINCT b.author) = 1;
```

**Решение 2:**

```sql
WITH user_books_filtered AS (
   SELECT
      ub.*,
      u.first_name,
      u.last_name,
      b.name AS book_name,
      b.author
   FROM user_books AS ub
   JOIN users AS u ON u.id = ub.user_id
   JOIN books AS b ON b.id = ub.book_id
   WHERE TIMESTAMPDIFF(YEAR, u.birthday, CURDATE()) BETWEEN 7 AND 17
   AND DATEDIFF(ub.return_date, ub.get_date) <= 14
)
SELECT
   user_id AS ID,
   CONCAT(first_name, ' ', last_name) AS Name,
   MIN(author) AS Author,
   GROUP_CONCAT(book_name ORDER BY book_name) AS Books
FROM user_books_filtered
GROUP BY user_id
HAVING COUNT(*) = 2
   AND COUNT(DISTINCT author) = 1;
```

## Решение задания 2: JSON API для обмена валют

### Условие задачи

1. **Задача**  
   Реализовать микросервис на PHP 8 для:
   - Получения курсов валют относительно USD
   - Конвертации между валютами
   - Добавления 2% комиссии к курсам

2. **Требования**:
   - Работа в Docker-контейнере
   - Bearer-аутентификация (64-символьный токен)
   - Использование API CoinGate (merchant) или CoinCap
   - JSON-ответы для всех сценариев

**Формат запроса:**  
```<host>/api/v1?method=<имя_метода>&<параметр>=<значение>```

### Методы API

**1. rates (GET)**  
Получение курсов валют с комиссией 2%

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" 
   "http://localhost:8080/api/v1?method=rates&currency=USD,EUR"
```

Параметр | Описание
--- | ---
currency (опц.) | Фильтр по валютам (через запятую)

**2. convert (POST)**  
Конвертация валюты

```bash
curl -X POST -H "Authorization: Bearer YOUR_TOKEN" 
  -H "Content-Type: application/json" 
  -d '{"currency_from":"USD","currency_to":"BTC","value":"1.00"}' 
  http://localhost:8080/api/v1?method=convert
```

Параметры:
- ```currency_from```: Исходная валюта
- ```currency_to```: Целевая валюта
- ```value```: Сумма (мин. 0.01)

### Быстрый старт

1. Подготовка окружения:
```bash
git clone https://github.com/D4-E/yii2-currency-exchange-api.git
cd yii2-currency-exchange-api
cp .env.example .env
# задайте API_TOKEN в .env
```

2. Запуск сервиса:
```bash
docker-compose up -d --build
```

### Тестирование

**Команды для тестов:**
```bash
# Unit-тесты
docker-compose exec php vendor/bin/codecept run unit

# Functional-тесты
docker-compose exec php vendor/bin/codecept run functional

# Все тесты
docker-compose exec php vendor/bin/codecept run
```

**Покрытие тестами:**
- Авторизация по токену
- Корректность расчета комиссии
- Валидация входных параметров
- Обработка ошибок API
- Форматы JSON-ответов

### Примеры ответов

**Успешный запрос (200):**
```json
{
  "status": "success",
  "code": 200,
  "data": {
    "USD": "1.00",
    "EUR": "0.92"
  }
}
```

**Ошибка авторизации (403):**
```json
{
  "status": "error",
  "code": 403,
  "message": "Invalid token"
}
```