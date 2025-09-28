# Remi CRM - Backend

Laravel-based backend for the Remi CRM system with multilingual AI analysis.

## Quick Setup

```bash
# Install dependencies
composer install
npm install && npm run build

# Environment
cp .env.example .env
php artisan key:generate

# Database
touch database/database.sqlite
php artisan migrate

# Python AI Engine
php artisan install:python-deps

# Launch
php artisan serve
php artisan queue:work  # In separate terminal
```

## Key Commands

```bash
# Integration Management
php artisan sync:accounts      # Sync Unipile accounts
php artisan sync:contacts      # Import contacts from integrations
php artisan force:tagging      # Re-analyze contacts with AI

# Development
php artisan optimize           # Cache config/routes for production
php artisan test              # Run test suite
```

## Architecture

- **TALL Stack**: Tailwind, Alpine.js, Laravel, Livewire
- **Modular Design**: `app/Modules/` for organized code
- **AI Analysis**: Python-based multilingual semantic analysis
- **Queue System**: Background processing for integrations
- **Error Handling**: Comprehensive error management

See main [README.md](../README.md) for complete documentation.