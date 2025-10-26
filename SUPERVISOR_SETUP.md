# 🚀 Supervisor Configuration for Separate Queues

## 📋 Overview

This configuration separates Laravel queue processing into specialized workers:

- **sync-queue**: Contact synchronization (heavy API calls, 10min timeout)
- **tagging-queue**: AI tagging (moderate processing, 5min timeout)  
- **high-priority**: Critical jobs that need immediate processing

## 🔧 Setup Instructions

### 1. **Update Configuration File**

Replace `/path/to/your/project` with your actual project path:

```bash
# Edit the supervisor configuration
sudo nano /etc/supervisor/conf.d/laravel-queues.conf
```

Update these paths in the config:
- `/path/to/your/project` → `/var/www/your-project` (or your actual path)
- `/path/to/your/project/storage/logs/` → your actual storage path

### 2. **Install Configuration**

```bash
# Copy the configuration file
sudo cp supervisor-queues.conf /etc/supervisor/conf.d/laravel-queues.conf

# Reload supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start the workers
sudo supervisorctl start laravel-sync-worker:*
sudo supervisorctl start laravel-tagging-worker:*
sudo supervisorctl start laravel-high-priority-worker:*
```

### 3. **Verify Workers Are Running**

```bash
# Check status
sudo supervisorctl status

# Should show:
# laravel-sync-worker:laravel-sync-worker_00    RUNNING
# laravel-sync-worker:laravel-sync-worker_01    RUNNING  
# laravel-tagging-worker:laravel-tagging-worker_00    RUNNING
# laravel-tagging-worker:laravel-tagging-worker_01    RUNNING
# laravel-tagging-worker:laravel-tagging-worker_02    RUNNING
# laravel-high-priority-worker:laravel-high-priority-worker_00    RUNNING
```

### 4. **Monitor Logs**

```bash
# Sync worker logs
tail -f storage/logs/sync-worker.log

# Tagging worker logs  
tail -f storage/logs/tagging-worker.log

# High priority worker logs
tail -f storage/logs/high-priority-worker.log
```

## ⚙️ **Queue Configuration Details**

### **Sync Worker (Contact Synchronization)**
- **Queue**: `sync`
- **Processes**: 2 workers
- **Timeout**: 600 seconds (10 minutes)
- **Tries**: 3 attempts
- **Sleep**: 3 seconds between jobs
- **Purpose**: Heavy API calls to Unipile, contact import

### **Tagging Worker (AI Analysis)**  
- **Queue**: `tagging`
- **Processes**: 3 workers
- **Timeout**: 300 seconds (5 minutes)
- **Tries**: 2 attempts
- **Sleep**: 1 second between jobs
- **Purpose**: AI analysis, tag assignment

### **High Priority Worker (Critical Jobs)**
- **Queue**: `high,sync,tagging` (processes in order)
- **Processes**: 1 worker
- **Timeout**: 300 seconds (5 minutes)
- **Tries**: 3 attempts
- **Purpose**: Critical jobs that need immediate processing

## 🔍 **Benefits of This Setup**

1. **No Blocking**: Sync jobs won't block tagging jobs
2. **Scalability**: Can adjust worker count per queue type
3. **Reliability**: Different retry policies for different job types
4. **Monitoring**: Separate logs for each queue type
5. **Performance**: Optimized timeouts and sleep intervals

## 🛠️ **Management Commands**

```bash
# Restart all workers
sudo supervisorctl restart laravel-sync-worker:*
sudo supervisorctl restart laravel-tagging-worker:*
sudo supervisorctl restart laravel-high-priority-worker:*

# Stop all workers
sudo supervisorctl stop laravel-sync-worker:*
sudo supervisorctl stop laravel-tagging-worker:*
sudo supervisorctl stop laravel-high-priority-worker:*

# Check queue status
php artisan queue:monitor
php artisan queue:work --once --queue=sync
php artisan queue:work --once --queue=tagging
```

## 📊 **Monitoring**

```bash
# Check queue sizes
php artisan queue:monitor

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```
