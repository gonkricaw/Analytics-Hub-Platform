# Indonet Analytics Hub Platform - Deployment Guide

This document outlines the deployment process for the Indonet Analytics Hub Platform, including environment setup, build steps, and server configurations.

## Environment Setup

### System Requirements

- PHP 8.1 or higher
- Node.js 16.x or higher
- NPM 8.x or higher
- MySQL 8.0 or higher
- Apache or Nginx web server
- Composer 2.x

### Environment Variables

The following environment variables should be configured in a `.env` file:

```
APP_NAME="Indonet Analytics Hub"
APP_ENV=production
APP_KEY=<your-app-key>
APP_DEBUG=false
APP_URL=https://your-production-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=indonet_analytics
DB_USERNAME=<database-username>
DB_PASSWORD=<database-password>

MAIL_MAILER=smtp
MAIL_HOST=<mail-server-host>
MAIL_PORT=587
MAIL_USERNAME=<mail-username>
MAIL_PASSWORD=<mail-password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Build Steps

### Backend (Laravel)

1. Install PHP dependencies:
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

2. Generate application key (if not already set):
   ```bash
   php artisan key:generate
   ```

3. Run database migrations:
   ```bash
   php artisan migrate --force
   ```

4. Run database seeders:
   ```bash
   php artisan db:seed --force
   ```

5. Optimize Laravel application:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   php artisan optimize
   ```

6. Set proper permissions:
   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

### Frontend (Vue.js/Vite)

1. Install NPM dependencies:
   ```bash
   npm install
   ```

2. Build frontend assets:
   ```bash
   npm run build
   ```

## Server Configuration

### Apache Configuration

Create or modify the Apache virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /path/to/analytics-hub/public
    
    <Directory /path/to/analytics-hub/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/analytics-hub-error.log
    CustomLog ${APACHE_LOG_DIR}/analytics-hub-access.log combined
</VirtualHost>
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/analytics-hub/public;
    
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    error_log /var/log/nginx/analytics-hub-error.log;
    access_log /var/log/nginx/analytics-hub-access.log;
}
```

## Scheduled Tasks

Configure a cron job to run Laravel's scheduled tasks:

```bash
* * * * * cd /path/to/analytics-hub && php artisan schedule:run >> /dev/null 2>&1
```

## Queue Worker

For processing background jobs, set up a supervisor configuration:

```
[program:analytics-hub-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/analytics-hub/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/analytics-hub/storage/logs/worker.log
stopwaitsecs=3600
```

## Additional Configuration Notes

### GSAP Animation Performance

The Indonet Analytics Hub Platform uses GSAP (GreenSock Animation Platform) for animations. For optimal performance:

1. **Bundle Optimization**: The GSAP plugin is lazy-loaded in production builds to improve initial load times.

2. **Animation Performance**: On low-powered devices, complex animations are automatically simplified using feature detection.

3. **Production Settings**: In production mode, some animations are disabled or simplified. To enable all animations for demonstration purposes:
   
   ```javascript
   // In the application code
   app.config.globalProperties.$enableAllAnimations = true;
   ```

### Development Mode Features

In development mode, additional features are available:

1. **Component Showcase**: Access the component showcase at `/dev/component-showcase` to see all available animations and UI components. This route is disabled in production mode.

2. **GSAP Inspector**: The GSAP inspector tool is enabled in development mode to help debug animations.

## Post-Deployment Verification

After deployment, verify the following:

1. User authentication works correctly
2. All API endpoints are accessible and functioning
3. Email notifications are being sent properly
4. Background jobs are being processed
5. Animations and UI components render correctly across different browsers and devices

## Troubleshooting

If you encounter issues with animations or performance:

1. Check browser console for any JavaScript errors
2. Verify that the GSAP library is properly loaded
3. Test on different browsers to isolate browser-specific issues
4. Ensure that the server meets the minimum requirements for memory and CPU
