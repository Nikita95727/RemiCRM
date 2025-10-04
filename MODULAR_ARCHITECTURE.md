# Модульная архитектура Laravel CRM

## Обзор архитектуры

Данный проект использует архитектуру **модульного монолита**, которая сочетает преимущества монолитной архитектуры с модульной организацией кода.

## Структура модулей

```
app/
├── Core/                    # Ядро системы
│   └── Providers/          # Основные провайдеры
├── Modules/                # Бизнес-модули
│   ├── Contact/           # Модуль управления контактами
│   ├── User/              # Модуль пользователей
│   └── Integration/       # Модуль интеграций
└── Shared/                # Общие компоненты
    ├── Contracts/         # Интерфейсы
    ├── DTOs/              # Data Transfer Objects
    ├── Enums/             # Перечисления
    ├── Services/          # Общие сервисы
    └── Traits/            # Общие трейты
```

## Принципы модульности

### 1. Слабая связанность (Loose Coupling)
- Модули взаимодействуют через интерфейсы (Contracts)
- Минимальные зависимости между модулями
- Использование Dependency Injection

### 2. Высокая сплоченность (High Cohesion)
- Связанная функциональность находится в одном модуле
- Каждый модуль отвечает за конкретный домен

### 3. Инверсия зависимостей (Dependency Inversion)
- Зависимости от абстракций, а не от конкретных реализаций
- Использование интерфейсов в Shared/Contracts

## Структура модуля

Каждый модуль содержит:

```
ModuleName/
├── Models/                # Eloquent модели
├── Services/              # Бизнес-логика
├── Http/                  # HTTP слой
│   ├── Controllers/       # Контроллеры
│   ├── Requests/          # Form Requests (валидация)
│   └── Resources/         # API Resources
├── Livewire/              # Livewire компоненты
├── Database/              # База данных
│   └── Migrations/        # Миграции модуля
├── Routes/                # Маршруты
│   ├── web.php           # Web маршруты
│   └── api.php           # API маршруты
├── Resources/             # Ресурсы
│   └── Views/            # Blade шаблоны
└── Providers/             # Service Providers
    └── ModuleServiceProvider.php
```

## Автоматическая регистрация модулей

Система автоматически регистрирует модули через `ModuleServiceProvider`:

1. **Service Providers** - регистрируются автоматически
2. **Маршруты** - загружаются из Routes/web.php и Routes/api.php
3. **Миграции** - загружаются из Database/Migrations
4. **Представления** - регистрируются для Blade

## Создание нового модуля

### 1. Создайте структуру директорий:
```bash
mkdir -p app/Modules/NewModule/{Models,Services,Http/{Controllers,Requests,Resources},Livewire,Database/Migrations,Routes,Providers}
```

### 2. Создайте Service Provider:
```php
<?php
namespace App\Modules\NewModule\Providers;

use Illuminate\Support\ServiceProvider;

class NewModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Регистрация сервисов модуля
    }

    public function boot(): void
    {
        // Инициализация модуля
    }
}
```

### 3. Создайте маршруты:
- `app/Modules/NewModule/Routes/web.php`
- `app/Modules/NewModule/Routes/api.php`

### 4. Модуль будет автоматически зарегистрирован!

## Использование Shared компонентов

### Contracts (Интерфейсы)
```php
// app/Shared/Contracts/RepositoryInterface.php
interface RepositoryInterface
{
    public function findById(int $id);
}
```

### DTOs (Data Transfer Objects)
```php
// app/Shared/DTOs/ContactDTO.php
readonly class ContactDTO
{
    public function __construct(
        public string $name,
        public ?string $email = null,
    ) {}
}
```

### Enums
```php
// app/Shared/Enums/Status.php
enum Status: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
```

## Преимущества данной архитектуры

1. **Масштабируемость** - легко добавлять новые модули
2. **Поддерживаемость** - четкое разделение ответственности
3. **Тестируемость** - модули можно тестировать изолированно
4. **Переиспользование** - общие компоненты в Shared
5. **Миграция к микросервисам** - модули легко выделить в отдельные сервисы

## Рекомендации

1. **Один домен = один модуль**
2. **Избегайте прямых зависимостей между модулями**
3. **Используйте Events для межмодульного взаимодействия**
4. **Выносите общую логику в Shared**
5. **Документируйте интерфейсы модулей**

## Примеры взаимодействия модулей

### Через Events:
```php
// В модуле Contact
event(new ContactCreated($contact));

// В модуле Integration
class ContactCreatedListener
{
    public function handle(ContactCreated $event): void
    {
        // Обработка события
    }
}
```

### Через Service Container:
```php
// Регистрация в ServiceProvider
$this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);

// Использование в другом модуле
public function __construct(
    private ContactRepositoryInterface $contactRepository
) {}
```
