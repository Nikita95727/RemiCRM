# 🚀 Remi CRM - Personal Contact Management System

> **Advanced multilingual CRM with AI-powered contact tagging and seamless messaging integration**

[![Laravel](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![Livewire](https://img.shields.io/badge/Livewire-3.x-4E56A6?style=flat&logo=livewire)](https://livewire.laravel.com)
[![Python](https://img.shields.io/badge/Python-3.8+-3776AB?style=flat&logo=python)](https://python.org)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-06B6D4?style=flat&logo=tailwindcss)](https://tailwindcss.com)

## ✨ Features

### 🌍 **Multilingual AI Analysis**
- **12 Language Support**: English, Russian, Spanish, French, German, Chinese, Japanese, Ukrainian, Belarusian, Hindi, Arabic, Bengali, Portuguese
- **Automatic Language Detection**: Smart detection with fallback mapping
- **Semantic Tagging**: AI-powered categorization (crypto, business, banking, technology, gaming, social)
- **Dual Analysis Engine**: TF-IDF + Sentence Transformers (optional)

### 📱 **Messaging Integration**
- **Telegram**: Multiple accounts, full chat history
- **WhatsApp**: Contact sync and message analysis  
- **Gmail**: Email contact management
- **Unipile API**: Unified messaging platform integration

### 🔍 **Smart Search & Management**
- **Command+K Search**: Lightning-fast contact lookup
- **Auto-tagging**: Intelligent categorization from conversations
- **Contact Sync**: Daily synchronization + historical import
- **Advanced Filtering**: By source, tags, and custom criteria

### 🛡️ **Security & Privacy**
- **Self-hosted**: All data stays on your server
- **Environment Variables**: Secure API key management
- **Error Handling**: Comprehensive error management system
- **Data Integrity**: Robust validation and backup systems

## 🏗️ Architecture

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Laravel API    │    │   Integrations  │
│                 │    │                  │    │                 │
│ • Livewire 3    │◄──►│ • TALL Stack     │◄──►│ • Unipile API   │
│ • Alpine.js     │    │ • Modular Design │    │ • Telegram      │
│ • Tailwind CSS  │    │ • Queue System   │    │ • WhatsApp      │
└─────────────────┘    └──────────────────┘    │ • Gmail         │
                                ▲               └─────────────────┘
                                │
                       ┌──────────────────┐
                       │   AI Analysis    │
                       │                  │
                       │ • Python Engine  │
                       │ • Language Det.  │
                       │ • Semantic Tags  │
                       └──────────────────┘
```

## 🚀 Quick Start

### Prerequisites

- **PHP 8.2+** with extensions: `mbstring`, `xml`, `curl`, `zip`, `sqlite3/mysql`
- **Composer 2.x**
- **Node.js 18+** & **npm**
- **Python 3.8+** & **pip3**
- **Git**

### 1️⃣ Clone & Setup

```bash
# Clone the repository
git clone <repository-url> remi-crm
cd remi-crm/crm-backend

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Build assets
npm run build
```

### 2️⃣ Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database and services in .env
```

**Required Environment Variables:**
```env
# Database
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database.sqlite

# Unipile Integration (get from unipile.com)
UNIPILE_DSN=your_dsn_here
UNIPILE_TOKEN=your_token_here
UNIPILE_BASE_URL=https://api18.unipile.com:14862

# Application
APP_URL=http://localhost:8000
APP_ENV=local
APP_DEBUG=true
```

### 3️⃣ Database Setup

```bash
# Create database file (SQLite)
touch database/database.sqlite

# Run migrations
php artisan migrate

# Seed initial data (optional)
php artisan db:seed
```

### 4️⃣ Python Dependencies

```bash
# Install Python packages for AI analysis
php artisan install:python-deps

# Or manually:
pip3 install -r requirements.txt
```

### 5️⃣ Launch Application

```bash
# Start Laravel development server
php artisan serve

# In another terminal, start queue worker
php artisan queue:work

# Access application at: http://localhost:8000
```

## 🔧 Configuration

### Unipile API Setup

1. **Sign up** at [unipile.com](https://unipile.com)
2. **Get your credentials**: DSN and API Token
3. **Add to .env**:
   ```env
   UNIPILE_DSN=your_dsn_here
   UNIPILE_TOKEN=your_token_here
   ```

### Connect Messaging Accounts

1. **Navigate to** `/integrations` in the app
2. **Click "Connect Account"** for each service
3. **Follow OAuth flow** to authorize access
4. **Verify connection** in the dashboard

## 📋 Usage

### Adding Contacts

```bash
# Manual contact creation
# Use the web interface: Click "Add Contact" button

# Import from connected accounts
php artisan sync:contacts

# Force re-tag existing contacts
php artisan force:tagging
```

### Search & Filter

- **Global Search**: Press `Cmd+K` (Mac) or `Ctrl+K` (Windows/Linux)
- **Filter by Source**: Use dropdown filters
- **Filter by Tags**: Click tag badges
- **Advanced Search**: Use search operators

### AI Tagging

The system automatically analyzes conversations and assigns relevant tags:

- **crypto**: Bitcoin, trading, blockchain discussions
- **business**: Meetings, deals, partnerships
- **banking**: Financial services, payments
- **technology**: Development, software, AI
- **gaming**: Games, esports, streaming
- **social**: Family, friends, personal chats

## 🛠️ Development

### Project Structure

```
crm-backend/
├── app/
│   ├── Modules/           # Modular architecture
│   │   ├── Contact/       # Contact management
│   │   └── Integration/   # External integrations
│   └── Console/Commands/  # Artisan commands
├── resources/
│   ├── views/livewire/    # Livewire components
│   └── js/                # Frontend assets
├── database/
│   └── migrations/        # Database schema
├── multilingual_chat_analyzer.py  # AI analysis engine
└── requirements.txt       # Python dependencies
```

### Key Commands

```bash
# Development
php artisan serve              # Start dev server
php artisan queue:work         # Process background jobs
npm run dev                    # Watch assets

# Database
php artisan migrate            # Run migrations
php artisan migrate:fresh      # Fresh migration

# Integration
php artisan sync:accounts      # Sync Unipile accounts
php artisan sync:contacts      # Import contacts
php artisan force:tagging      # Re-analyze contacts

# Maintenance
php artisan optimize           # Optimize for production
php artisan config:cache       # Cache configuration
```

### Adding New Languages

1. **Update** `multilingual_chat_analyzer.py`:
   ```python
   self.categories = {
       'crypto': {
           'your_lang': ['keyword1', 'keyword2', ...]
       }
   }
   ```

2. **Add language mapping** in `detect_language()` method

3. **Test** with sample messages

## 🧪 Testing

```bash
# Run PHP tests
php artisan test

# Test multilingual analysis
echo '[{"text": "Hello world", "from": "user"}]' | python3 multilingual_chat_analyzer.py

# Test specific contact tagging
php artisan force:tagging 123
```

## 📊 Performance

### Optimization Tips

- **Enable caching**: `php artisan optimize`
- **Use queue workers**: For background processing
- **Database indexing**: Contacts are indexed by name, email, tags
- **Asset optimization**: `npm run build` for production

### Monitoring

- **Logs**: Check `storage/logs/laravel.log`
- **Queue status**: Monitor failed jobs in database
- **Integration errors**: View in `/contacts` page error panel

## 🔒 Security

### Best Practices

- **Environment Variables**: Never commit `.env` file
- **API Keys**: Store securely in environment
- **Database**: Use strong passwords and encryption
- **Updates**: Keep dependencies updated regularly

### Data Privacy

- **Self-hosted**: All data remains on your server
- **No external analytics**: No tracking or telemetry
- **Secure transmission**: HTTPS for all API calls
- **Access control**: Simple authentication system

## 🚨 Troubleshooting

### Common Issues

**Python dependencies not installing:**
```bash
# macOS: Install Xcode command line tools
xcode-select --install

# Update pip
pip3 install --upgrade pip

# Use virtual environment
python3 -m venv venv
source venv/bin/activate
pip install -r requirements.txt
```

**Queue jobs not processing:**
```bash
# Check queue worker is running
php artisan queue:work --verbose

# Clear failed jobs
php artisan queue:flush
```

**Unipile connection errors:**
```bash
# Verify credentials in .env
# Check API status at unipile.com
# Review logs: storage/logs/laravel.log
```

**Database connection issues:**
```bash
# SQLite: Check file permissions
chmod 664 database/database.sqlite

# MySQL: Verify credentials and server status
```

## 📚 API Reference

### Contact Management

```php
// Get contacts with filters
GET /api/contacts?search=john&source=telegram&tags=business

// Create contact
POST /api/contacts
{
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+1234567890",
    "source": "manual",
    "tags": ["business", "crypto"]
}

// Update contact
PUT /api/contacts/{id}

// Delete contact
DELETE /api/contacts/{id}
```

### Integration Status

```php
// Get connected accounts
GET /api/integrations/accounts

// Sync specific account
POST /api/integrations/sync/{accountId}

// Get integration errors
GET /api/integrations/errors
```

## 🤝 Contributing

1. **Fork** the repository
2. **Create** feature branch: `git checkout -b feature/amazing-feature`
3. **Commit** changes: `git commit -m 'Add amazing feature'`
4. **Push** to branch: `git push origin feature/amazing-feature`
5. **Open** Pull Request

### Development Guidelines

- **Follow PSR-12** coding standards
- **Write tests** for new features
- **Update documentation** for API changes
- **Use conventional commits** for clear history

## 📄 License

This project is proprietary software. All rights reserved.

## 🆘 Support

- **Documentation**: Check this README and inline code comments
- **Issues**: Create GitHub issue with detailed description
- **Logs**: Include relevant log entries from `storage/logs/`
- **Environment**: Specify PHP, Python, and OS versions

---

**Built with ❤️ using the TALL Stack (Tailwind CSS, Alpine.js, Laravel, Livewire)**

*Last updated: $(date)*
