# 🔧 Production Fix Guide - Auto-Tagging Issues

## 📋 Проблема

На продакшене не работает автоматическое присвоение тегов (`BatchAutoTagContacts` jobs failing).

**Симптомы:**
- Jobs `BatchAutoTagContacts` падают с ошибкой `sh: 1: source: not found`
- Контакты импортируются, но теги не присваиваются
- В логах видно: `FAIL` для `BatchAutoTagContacts` (11-24ms)

**Причина:**
1. Команда `source` недоступна в shell-окружении на production
2. Недостаточно памяти (200MB free) для обработки большого количества сообщений

## ✅ Решение

### 1️⃣ Обновить код на production

```bash
cd /var/www/RemiCRM
git pull origin master
```

### 2️⃣ Проверить Python окружение

```bash
# Запустить диагностику
php artisan diagnose:production
```

Эта команда проверит:
- ✅ Установлен ли Python
- ✅ Существует ли virtual environment
- ✅ Работает ли multilingual analyzer
- ✅ Какой метод активации venv работает

### 3️⃣ Если venv не создан

```bash
# Создать virtual environment
python3 -m venv venv

# Установить зависимости
venv/bin/pip install nltk scikit-learn numpy langdetect

# Скачать NLTK данные
venv/bin/python -c "import nltk; nltk.download('punkt'); nltk.download('stopwords')"
```

### 4️⃣ Перезапустить queue worker

```bash
# Остановить старый worker
sudo supervisorctl stop remicrm-worker:*

# Перезапустить supervisor
sudo supervisorctl reread
sudo supervisorctl update

# Запустить worker
sudo supervisorctl start remicrm-worker:*

# Проверить статус
sudo supervisorctl status
```

### 5️⃣ Очистить failed jobs и перезапустить tagging

```bash
# Очистить failed jobs
php artisan queue:flush

# Запустить force tagging для всех контактов
php artisan force:tagging

# Или для конкретного аккаунта через tinker
php artisan tinker --execute="
\$account = \App\Modules\Integration\Models\IntegratedAccount::latest()->first();
\App\Modules\Integration\Jobs\BatchAutoTagContacts::dispatch(\$account);
echo 'Tagging dispatched for account: ' . \$account->provider->value . PHP_EOL;
"
```

### 6️⃣ Мониторинг

```bash
# Следить за логами worker
tail -f /var/www/RemiCRM/storage/logs/worker.log

# Следить за Laravel логами
tail -f /var/www/RemiCRM/storage/logs/laravel.log

# Проверить статус тегирования через tinker
php artisan tinker --execute="
echo 'Контактов без тегов: ' . \App\Modules\Contact\Models\Contact::where(function(\$q) {
    \$q->whereNull('tags')->orWhere('tags', '[]')->orWhereJsonLength('tags', 0);
})->count() . PHP_EOL;

echo 'Контактов с тегами: ' . \App\Modules\Contact\Models\Contact::whereNotNull('tags')->whereJsonLength('tags', '>', 0)->count() . PHP_EOL;
"
```

## 🔍 Что изменилось

### ChatAnalysisService.php
**До:**
```php
// Использовал 'source venv/bin/activate' (не работает в sh)
$command = sprintf('cd %s && source venv/bin/activate && python %s --file %s 2>&1', ...);
```

**После:**
```php
// Использует прямой путь к python из venv (работает везде)
$venvPython = $crmBackendRoot . '/venv/bin/python';
$command = sprintf('%s %s --file %s 2>&1', escapeshellarg($venvPython), ...);
```

### BatchAutoTagContacts.php
**Оптимизации для low-memory servers:**
- `$batchSize` = 1 (обрабатывает по 1 контакту)
- `maxMessages` = 500 (вместо 2000)
- `batchSize` = 100 (для getAllChatMessages)
- Добавлены `unset($batch); gc_collect_cycles();` после каждого контакта
- Добавлена пауза 100ms между контактами
- `$timeout` = 600s (10 минут)

### UnipileService.php
**Новый метод `getAllChatMessages`:**
- Постраничная загрузка сообщений
- Лимит на максимальное количество батчей (10)
- Более частая очистка памяти
- Паузы каждые 5 батчей (100ms)

## 🚨 Troubleshooting

### Проблема: "sh: 1: source: not found"
**Решение:** Обновите код (уже исправлено в последнем коммите)

### Проблема: Jobs падают с timeout
**Решение:** Уменьшите количество сообщений:
```php
// В UnipileService.php, метод getAllChatMessages
$unipileService->getAllChatMessages($accountId, $chatId, 250, 50); // Еще меньше
```

### Проблема: Python analyzer возвращает null
**Решение:** Проверьте, что NLTK данные скачаны:
```bash
venv/bin/python -c "import nltk; print(nltk.data.find('tokenizers/punkt'))"
```

### Проблема: Недостаточно памяти
**Решение:**
1. Уменьшите `maxMessages` в `BatchAutoTagContacts`
2. Перезапустите MySQL и PHP-FPM
3. Проверьте, что другие процессы не жрут память:
```bash
free -h
ps aux --sort=-%mem | head -10
```

## 📊 Ожидаемые результаты

После применения фиксов:
- ✅ Jobs `BatchAutoTagContacts` должны выполняться успешно (1-7s DONE)
- ✅ Теги должны присваиваться контактам
- ✅ Нет ошибок `sh: 1: source: not found` в логах
- ✅ Нет failed jobs для `BatchAutoTagContacts`

## 📞 Дополнительная помощь

Если проблема сохраняется, запустите:
```bash
php artisan diagnose:production > diagnostics.txt
```

И пришлите файл `diagnostics.txt` для анализа.

---

**Дата последнего обновления:** 4 октября 2025  
**Версия:** 1.0

