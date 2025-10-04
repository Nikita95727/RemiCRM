# ⚡ Quick Production Fix - 5 минут

## 🎯 Что делать прямо сейчас

### 1. Подключиться к серверу
```bash
ssh root@your-server-ip
cd /var/www/RemiCRM
```

### 2. Обновить код
```bash
git pull origin master
```

### 3. Запустить диагностику
```bash
php artisan diagnose:production
```

### 4. Если venv не создан (будет видно в диагностике)
```bash
python3 -m venv venv
venv/bin/pip install nltk scikit-learn numpy langdetect
venv/bin/python -c "import nltk; nltk.download('punkt'); nltk.download('stopwords')"
```

### 5. Перезапустить queue worker
```bash
sudo supervisorctl restart remicrm-worker:*
sudo supervisorctl status
```

### 6. Проверить, что работает
```bash
# Очистить старые failed jobs
php artisan queue:flush

# Запустить тегирование
php artisan tinker --execute="
\$account = \App\Modules\Integration\Models\IntegratedAccount::latest()->first();
\App\Modules\Integration\Jobs\BatchAutoTagContacts::dispatch(\$account);
echo 'Dispatched!' . PHP_EOL;
"

# Подождать 30 секунд и проверить
sleep 30

php artisan tinker --execute="
echo 'С тегами: ' . \App\Modules\Contact\Models\Contact::whereNotNull('tags')->whereJsonLength('tags', '>', 0)->count() . PHP_EOL;
echo 'Без тегов: ' . \App\Modules\Contact\Models\Contact::where(function(\$q) {
    \$q->whereNull('tags')->orWhere('tags', '[]')->orWhereJsonLength('tags', 0);
})->count() . PHP_EOL;
"
```

### 7. Следить за прогрессом
```bash
tail -f /var/www/RemiCRM/storage/logs/worker.log | grep BatchAutoTagContacts
```

## ✅ Что должно получиться

В логах должно быть:
```
BatchAutoTagContacts ... RUNNING
BatchAutoTagContacts ... 1s DONE
```

А НЕ:
```
sh: 1: source: not found
BatchAutoTagContacts ... 11.18ms FAIL
```

## 🚨 Если не работает

1. Проверьте, что venv создан:
```bash
ls -la /var/www/RemiCRM/venv/bin/python
```

2. Проверьте, что есть multilingual_chat_analyzer.py:
```bash
ls -la /var/www/RemiCRM/multilingual_chat_analyzer.py
```

3. Проверьте failed jobs:
```bash
php artisan tinker --execute="
\$failed = \DB::table('failed_jobs')->orderBy('failed_at', 'desc')->limit(1)->get();
if (\$failed->count() > 0) {
    echo 'Last failed job:' . PHP_EOL;
    echo substr(\$failed->first()->exception, 0, 500) . PHP_EOL;
}
"
```

4. Если всё равно не работает, запустите полную диагностику:
```bash
php artisan diagnose:production > diagnostics.txt
cat diagnostics.txt
```

---

**Главное изменение:** Теперь используется прямой путь `venv/bin/python` вместо `source venv/bin/activate`, что работает в любом shell-окружении.

