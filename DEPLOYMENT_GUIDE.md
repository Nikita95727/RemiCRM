# 🚀 Remi CRM - Инструкция по развертыванию

## 📋 Требования к системе

- **PHP** 8.2 или выше
- **Composer** (менеджер пакетов PHP)
- **Node.js** 18+ и **npm**
- **MySQL** 8.0 или выше
- **Python** 3.8+ (для автотегирования)
- **Git**

---

## 🔧 Пошаговая установка

### 1️⃣ **Клонирование репозитория**

```bash
git clone https://github.com/Nikita95727/RemiCRM.git
cd RemiCRM/crm-backend
```

### 2️⃣ **Установка PHP зависимостей**

```bash
composer install
```

### 3️⃣ **Установка Node.js зависимостей**

```bash
npm install
```

### 4️⃣ **Настройка окружения**

```bash
# Копируем файл конфигурации
cp .env.example .env

# Генерируем ключ приложения
php artisan key:generate
```

### 5️⃣ **Настройка базы данных**

Отредактируйте файл `.env`:

```env
# Основные настройки
APP_NAME="Remi CRM"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

# База данных
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=remi_crm
DB_USERNAME=root
DB_PASSWORD=your_password

# Unipile API (для интеграций)
UNIPILE_DSN=your_unipile_dsn
UNIPILE_ACCESS_TOKEN=your_unipile_token
```

### 6️⃣ **Создание базы данных**

```bash
# Создайте базу данных MySQL
mysql -u root -p
CREATE DATABASE remi_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Запустите миграции
php artisan migrate:fresh --seed
```

### 7️⃣ **Настройка Python для автотегирования**

```bash
# Установите Python зависимости для ML анализа
pip3 install nltk scikit-learn numpy langdetect

# Опционально для улучшенного анализа (требует больше ресурсов)
pip3 install sentence-transformers

# Скачайте NLTK данные
python3 -c "import nltk; nltk.download('punkt'); nltk.download('stopwords')"
```

### 8️⃣ **Сборка фронтенда**

```bash
# Для разработки
npm run dev

# Для продакшена
npm run build
```

### 9️⃣ **Настройка очередей (обязательно!)**

```bash
# Запустите обработчик очередей в отдельном терминале
php artisan queue:work --daemon --sleep=3 --tries=3 --max-time=3600
```

### 🔟 **Запуск приложения**

```bash
# Запустите сервер разработки
php artisan serve
```

---

## 🌐 Доступ к приложению

Откройте браузер и перейдите по адресу: **http://127.0.0.1:8000**

**Тестовый пользователь:**
- **Email:** test@example.com
- **Пароль:** password

---

## ⚙️ Настройка интеграций

### 📱 **Unipile API (Telegram, WhatsApp, Gmail)**

1. Зарегистрируйтесь на [Unipile](https://unipile.com)
2. Получите DSN и Access Token
3. Добавьте в `.env`:
   ```env
   UNIPILE_DSN=your_dsn_here
   UNIPILE_ACCESS_TOKEN=your_token_here
   ```

### 🤖 **Автотегирование (Machine Learning)**

Автотегирование работает на основе машинного обучения без внешних API:
- Использует **sklearn** и **nltk** для анализа текста
- Поддерживает **многоязычность** (русский, английский, украинский)
- Анализирует сообщения по ключевым словам и семантике
- **Не требует** OpenAI или других платных API

---

## 🔄 Ежедневная синхронизация

Добавьте в cron для автоматической синхронизации:

```bash
# Откройте crontab
crontab -e

# Добавьте строку (замените путь на ваш)
0 9 * * * cd /path/to/RemiCRM/crm-backend && php artisan contacts:sync-all >> /dev/null 2>&1
```

---

## 🚨 Устранение неполадок

### **Ошибка: "Class not found"**
```bash
composer dump-autoload
php artisan optimize:clear
```

### **Ошибка: "Permission denied"**
```bash
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### **Ошибка: "Queue not working"**
```bash
# Перезапустите обработчик очередей
php artisan queue:restart
php artisan queue:work --daemon
```

### **Ошибка: "Migration failed"**
```bash
php artisan migrate:fresh --seed
```

---

## 📱 Основные функции

✅ **Управление контактами** - создание, редактирование, просмотр  
✅ **Интеграции** - Telegram, WhatsApp, Gmail  
✅ **Автотегирование** - ИИ анализ чатов  
✅ **Быстрый поиск** - Command+K  
✅ **Синхронизация** - автоматический импорт контактов  
✅ **Фильтрация** - по источникам и тегам  

---

## 🎯 Завтрашние задачи

1. **📱 Мобильная оптимизация** - адаптивный дизайн
2. **🔧 Мелкие доработки** - полировка интерфейса

---

## 📞 Поддержка

При возникновении проблем:
1. Проверьте логи: `tail -f storage/logs/laravel.log`
2. Очистите кеш: `php artisan optimize:clear`
3. Перезапустите очереди: `php artisan queue:restart`

**Готово к демонстрации! 🚀✨**
