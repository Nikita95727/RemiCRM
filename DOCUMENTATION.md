# 📚 Remi CRM - Feature Documentation

## Overview

Remi CRM is a personal contact management system with AI-powered tagging and seamless messaging integration. The system automatically syncs contacts from Telegram, WhatsApp, and Gmail, analyzes conversations in 12+ languages, and categorizes contacts intelligently.

---

## Core Features

### 1. 🔗 Account Integration

**Purpose:** Connect external messaging accounts to CRM.

**Supported Providers:**
- Telegram
- WhatsApp  
- Gmail (via OAuth)

**How it works:**
1. User clicks "Connect Account"
2. System creates Unipile hosted auth link with `user_id` embedded as `name` field
3. User authenticates via OAuth in Unipile
4. Unipile redirects back with `account_id` parameter
5. System syncs ONLY that specific account (isolation guaranteed)
6. Account saved to `integrated_accounts` table with status `active`

**Key Components:**
- `ConnectAccount.php` (Livewire) - UI for connection
- `IntegrationWaitingController.php` - Captures account_id from redirect
- `IntegrationService.php` - Orchestrates account sync
- `UnipileService.php` - API communication with Unipile

**Security:**
- Each user only sees their own accounts
- Two-layer isolation: specific account_id + user_id filtering
- Credentials stored securely in environment variables

---

### 2. 📇 Contact Synchronization

**Purpose:** Import and maintain contacts from connected messaging accounts.

**Process:**
1. **Fetch:** Gets ALL chats from Unipile API (paginated, 25 per batch)
2. **Transform:** Converts chats to ContactDTO objects
3. **Deduplicate:** Smart matching by provider_id → email/phone → name
4. **Save:** Creates new contacts or updates existing ones
5. **Link:** Creates `contact_integrations` records with external_id (chat_id)

**Key Components:**
- `SyncContactsFromAccount.php` (Job) - Main sync logic
- `TelegramContactTransformer.php` - Telegram-specific data transformation
- `WhatsAppContactTransformer.php` - WhatsApp data transformation
- `GmailContactTransformer.php` - Gmail data transformation
- `ContactRepository.php` - Database operations

**Smart Deduplication:**
```
Priority 1: provider_id (e.g., Telegram user ID)
Priority 2: email or phone
Priority 3: name (fallback)
```

**Memory Safety:**
- Batch processing (25 contacts at a time)
- Streaming approach for large contact lists
- Garbage collection after each batch
- Maximum 20 pages per sync (2000 contacts)

**What syncs:**
- Private chats → Contacts
- Groups/Channels → Skipped
- Bots → Included (tagged separately)

---

### 3. 🏷️ AI-Powered Auto-Tagging

**Purpose:** Automatically categorize contacts based on conversation content.

**Categories:**
- `crypto` - Bitcoin, trading, blockchain discussions
- `business` - Meetings, deals, partnerships, work
- `banking` - Financial services, payments, accounts
- `technology` - Development, software, AI, programming
- `gaming` - Games, esports, streaming
- `social` - Family, friends, personal conversations

**Languages Supported:**
English, Russian, Spanish, French, German, Chinese, Japanese, Ukrainian, Belarusian, Hindi, Arabic, Bengali, Portuguese

**How it works:**

**For Telegram & WhatsApp:**
1. `BatchAutoTagContacts` job finds untagged contacts
2. For each contact, fetches last 100 messages from chat
3. Runs Python analyzer (`multilingual_chat_analyzer.py`)
4. Detects conversation language
5. Applies TF-IDF + keyword matching
6. Assigns relevant tags

**For Gmail (NEW!):**
1. Fetches last 50 emails from/to the contact
2. Extracts text from email subject, body, and snippets
3. Transforms emails to message format
4. Uses same Python analyzer as Telegram/WhatsApp
5. AI categorizes based on email content context
6. Fallback: Domain-based tagging if no emails found
   - `@company.com` → `business`
   - Personal emails → `social`

**Key Components:**
- `BatchAutoTagContacts.php` (Job) - Batch orchestrator
- `AutoTagContact.php` (Job) - Single contact tagger
- `UnipileService::getEmailsForAnalysis()` - Gmail content fetcher (NEW)
- `multilingual_chat_analyzer.py` - Python AI analyzer (universal)
- `ChatAnalysisService.php` - PHP bridge to Python

**Performance:**
- Async processing (queue jobs)
- 100 messages/50 emails per contact (optimal for accuracy)
- ~4 seconds per contact
- Non-blocking user experience
- Memory-efficient streaming for Gmail

---

### 4. 🔍 Search & Filtering

**Purpose:** Quickly find contacts using multiple criteria.

**Search Methods:**

**Global Search (Cmd+K):**
- Lightning-fast full-text search
- Searches: name, email, phone, notes
- Real-time results as you type
- Keyboard navigation (↑↓ arrows, Enter)
- Limit: 10 results

**Advanced Filtering:**
- By source (telegram, whatsapp, google_oauth)
- By tags (crypto, business, etc.)
- By search query (case-insensitive)
- Multiple filters combinable

**Key Components:**
- `GlobalSearch.php` (Livewire) - Cmd+K search modal
- `ContactsList.php` (Livewire) - Main contacts list with filters
- `ContactRepository.php` - Query building
- Contact model - Search scopes

**Database Optimization:**
- Indexed columns: user_id, provider_id, name, email
- JSON indexes on sources and tags
- Fulltext index on name and notes (MySQL only)

---

### 5. ⏰ Automatic Daily Sync

**Purpose:** Keep contacts up-to-date automatically.

**Schedule:** Every day at 00:00 (midnight)

**Logic:**
1. Find all users with active accounts
2. Filter accounts:
   - `status = 'active'`
   - `sync_enabled = true`
   - `last_sync_at` > 12 hours ago OR NULL
3. Queue `SyncContactsFromAccount` job for each account
4. Update `last_sync_at` timestamp
5. Process in background via queue worker

**Key Components:**
- `SyncAllContacts.php` (Command) - `php artisan contacts:sync-all`
- `routes/console.php` - Schedule configuration
- Laravel Scheduler - Cron runner

**Manual Triggers:**
```bash
# All accounts
php artisan contacts:sync-all

# Force (ignore 12-hour cooldown)
php artisan contacts:sync-all --force

# Specific user
php artisan contacts:sync-all --user=1

# Specific provider
php artisan contacts:sync-all --provider=telegram
```

**Logging:**
- Output to `storage/logs/sync.log`
- Failed jobs to `failed_jobs` table
- Main log to `storage/logs/laravel.log`

---

### 6. 📊 Import Status Tracking

**Purpose:** Real-time progress updates during contact import.

**How it works:**
1. Import starts → `ImportStatus::startImport()`
2. Progress updates → `ImportStatus::updateProgress()`
3. Import completes → `ImportStatus::completeImport()`
4. On error → `ImportStatus::failImport()`

**Status States:**
- `importing` - Sync in progress
- `completed` - Successfully finished
- `failed` - Error occurred

**UI Display:**
- Progress bar with percentage
- Contact count
- Live updates via polling
- Success/error messages

**Key Components:**
- `ImportStatus.php` (Model) - Status tracking
- `ContactsList.php` (Livewire) - UI polling
- Database table: `import_status`

---

### 7. 🌍 Multilingual Support

**Purpose:** Analyze conversations in multiple languages.

**Implementation:**
- Language detection via `langdetect` Python library
- Fallback mapping for language variants
- Language-specific keyword dictionaries
- TF-IDF analysis per language

**Supported Languages:**
- English (en)
- Russian (ru)
- Spanish (es)
- French (fr)
- German (de)
- Chinese (zh-cn)
- Japanese (ja)
- Ukrainian (uk)
- Belarusian (be)
- Hindi (hi)
- Arabic (ar)
- Bengali (bn)
- Portuguese (pt)

**Keyword Categories per Language:**
Each language has localized keywords for:
- Cryptocurrency terms
- Business terminology
- Banking vocabulary
- Technology jargon
- Gaming slang
- Social phrases

---

### 8. 🔐 Security & Privacy

**Features:**

**Data Isolation:**
- User-level data segregation
- Account_id validation
- User_id filtering
- No cross-user data leakage

**Authentication:**
- Laravel Breeze (email/password)
- Two-Factor Authentication (2FA)
- Session management
- Password hashing (bcrypt)

**API Security:**
- Environment-based credentials
- No hardcoded secrets
- Token-based Unipile auth
- HTTPS communication

**Data Protection:**
- All data self-hosted
- No external analytics
- User owns all data
- GDPR-ready architecture

---

## Database Schema

### Core Tables

**users**
```sql
id, name, email, password, two_factor_secret, two_factor_recovery_codes
```

**integrated_accounts**
```sql
id, user_id, unipile_account_id, provider, account_name, 
status, sync_enabled, last_sync_at, error_message
UNIQUE(user_id, unipile_account_id)
```

**contacts**
```sql
id, user_id, name, email, phone, 
sources JSON, tags JSON, notes, provider_id
INDEX(user_id, provider_id)
```

**contact_integrations** (Pivot)
```sql
contact_id, integrated_account_id, external_id, last_synced_at
UNIQUE(contact_id, integrated_account_id)
```

**import_status**
```sql
id, user_id, provider, status, progress, total_contacts, message
```

---

## Technical Stack

**Backend:**
- Laravel 12 (PHP 8.2+)
- MySQL 8.0+ (Production) / SQLite (Development)
- Queue: Database driver
- Scheduler: Laravel Cron

**Frontend:**
- Livewire 3 (Real-time components)
- Alpine.js (Interactions)
- Tailwind CSS (Styling)

**AI/ML:**
- Python 3.8+
- scikit-learn (TF-IDF)
- langdetect (Language detection)
- Optional: sentence-transformers

**External APIs:**
- Unipile API (Messaging integration)

---

## Architecture Patterns

**Modular Monolith:**
- `app/Modules/` - Business modules
- `app/Shared/` - Shared components
- Clear boundaries, loose coupling

**Repository Pattern:**
- `Repositories/` - Data access layer
- `Contracts/` - Interfaces
- Dependency injection

**Job Queue:**
- Background processing
- Async operations
- Retry logic (3 attempts)
- Timeout: 600 seconds

**DTO Pattern:**
- `DTOs/` - Data transfer objects
- Type safety
- Validation layer

---

## Performance Characteristics

**Contact Sync:**
- 100 contacts: ~1-2 seconds
- 1000 contacts: ~10-15 seconds
- Memory: <50MB per job

**Auto-Tagging:**
- Per contact: ~4 seconds
- 100 contacts: ~6-7 minutes (parallel)
- Memory: <30MB per job

**Search:**
- <100ms response time
- Indexed queries
- Limit results for speed

**Daily Sync:**
- Non-blocking
- Background queue
- Graceful error handling

---

## Error Handling

**API Errors:**
- Custom `UnipileApiException`
- User-friendly messages
- Detailed logging
- Retry logic

**Sync Errors:**
- Account status → `error`
- Error message stored
- Last error timestamp
- UI notification

**Graceful Degradation:**
- Failed tags don't block sync
- Partial sync success possible
- Queue retries failed jobs
- Manual retry available

---

## Monitoring & Logging

**Logs:**
- `storage/logs/laravel.log` - Main application log
- `storage/logs/sync.log` - Sync operations
- Queue jobs logged with context

**Metrics Tracked:**
- Contacts synced
- Tags assigned
- API calls made
- Errors occurred
- Job duration

**Debugging:**
```bash
# Watch logs
tail -f storage/logs/laravel.log

# Check failed jobs
php artisan queue:failed

# Retry failed
php artisan queue:retry all

# Monitor queue
php artisan queue:work --verbose
```

---

## Configuration

**Required Environment Variables:**
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=remi_crm
DB_USERNAME=root
DB_PASSWORD=secret

# Unipile API
UNIPILE_DSN=your_dsn
UNIPILE_TOKEN=your_token
UNIPILE_BASE_URL=https://api18.unipile.com:14862

# Application
APP_URL=http://localhost:8000
QUEUE_CONNECTION=database
```

**Python Dependencies:**
```
scikit-learn>=1.3.0
pandas>=2.0.0
langdetect>=1.0.9
numpy>=1.24.0
```

---

## Deployment Checklist

- [ ] Environment variables configured
- [ ] Database migrated
- [ ] Python dependencies installed
- [ ] Queue worker running (`php artisan queue:work`)
- [ ] Scheduler configured (cron: `* * * * * cd /path && php artisan schedule:run`)
- [ ] Storage permissions set (755)
- [ ] `.env` file secured (not in git)
- [ ] Logs writable
- [ ] Unipile API accessible

---

## API Endpoints (Internal)

**Integration:**
- `GET /integration/waiting` - OAuth callback page
- `POST /integration/check-status` - Poll sync status
- `GET /integration/success` - Success confirmation

**Contacts:**
- `GET /contacts` - Main contacts page
- `GET /contacts/search` - Global search

**User:**
- `GET /profile` - User profile
- `GET /two-factor` - 2FA settings

---

## Common Issues & Solutions

**Issue:** Contacts not syncing
**Solution:** Check queue worker is running, verify Unipile credentials

**Issue:** Duplicate integrations
**Solution:** Fixed in latest version - check for existing before creating

**Issue:** Tags not appearing
**Solution:** Ensure Python dependencies installed, check Python path

**Issue:** Cross-user data leakage
**Solution:** Fixed - specific account sync + user filtering implemented

**Issue:** Memory errors on large syncs
**Solution:** Batch processing implemented, uses streaming approach

---

**Last Updated:** October 5, 2025  
**Version:** 1.0  
**Status:** Production Ready
