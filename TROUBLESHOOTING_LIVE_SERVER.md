# Live Server Import Troubleshooting Guide

## Step 1: Verify Database Connectivity
Run these commands on your live server via SSH:

```bash
# Check if MySQL is running
sudo systemctl status mysql

# Test database connection
php artisan tinker
\DB::connection()->getPdo();
exit;
```

## Step 2: Check Environment Variables
Verify these key settings in your live server's `.env` file:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=librostream
DB_USERNAME=librostream
DB_PASSWORD=your_password_here
```

## Step 3: Review Logs
Check these log files for errors:

```bash
# Laravel application logs
tail -n 100 storage/logs/laravel.log

# MySQL error logs
sudo tail -n 100 /var/log/mysql/error.log

# Forge scheduler logs (if available)
```

## Step 4: Manual Import Test
Try running a small import manually:

```bash
php artisan librivox:fetch --limit=2 --verbose
```

## Step 5: Contact Forge Support
When contacting Laravel Forge support, include:
1. Screenshot of your scheduler configuration
2. Any error messages from the logs
3. Details about when the issue started
4. Steps you've already tried

## Common Issues to Check
1. Database user permissions
2. Available disk space (`df -h`)
3. Memory usage (`free -m`)
4. PHP memory limits (`php -i | grep memory_limit`)
