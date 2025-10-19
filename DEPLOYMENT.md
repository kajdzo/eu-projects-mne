# Deployment Guide - WebSupport.sk Shared Hosting

This guide will help you deploy the EU Projects in MNE dashboard to WebSupport.sk shared hosting.

## Prerequisites

- Active WebSupport.sk hosting account
- FTP/SFTP access credentials
- PostgreSQL database (check with WebSupport.sk if included in your plan)
- Domain configured and pointing to your hosting

## Important Note: Clean URLs

This application uses **clean URLs** (e.g., `/home`, `/public`, `/login` instead of `/home.php`, `/public.php`, `/login.php`) for better user experience.

### Production (WebSupport.sk)
- ✅ Clean URLs work perfectly via Apache `.htaccess` mod_rewrite
- ✅ All internal links use clean URLs and work seamlessly
- ✅ Users see nice URLs without `.php` extensions

### Development (Replit)
- ⚠️ Clean URLs do NOT work with PHP built-in server
- ✅ Use `.php` extensions when typing URLs in browser (e.g., `/home.php`, `/public.php`, `/login.php`)
- ⚠️ Once loaded, internal navigation links won't work (they use clean URLs)
- 💡 This is only a development inconvenience - production works perfectly

**Bottom line**: The application is designed for production use with Apache. Clean URLs work perfectly when deployed to WebSupport.sk.

## Step-by-Step Deployment

### 1. Prepare Your Local Environment

You've already completed:
- ✅ Cloned the project
- ✅ Ran `composer install`

### 2. Configure Database

#### Option A: WebSupport.sk PostgreSQL (if available)
1. Log in to WebSupport.sk Webadmin
2. Navigate to **Databases** section
3. Create a new PostgreSQL database
4. Note down the connection details:
   - Host (usually `localhost` or specific server)
   - Port (default: `5432`)
   - Database name
   - Username
   - Password

#### Option B: External PostgreSQL Service
If WebSupport.sk doesn't support PostgreSQL, use:
- [Neon](https://neon.tech) - Free tier available
- [Supabase](https://supabase.com) - Free tier available
- [ElephantSQL](https://www.elephantsql.com) - Free tier available

### 3. Create .env File

Copy `.env.example` to `.env` and update with your actual credentials:

```bash
cp .env.example .env
```

Edit `.env` and fill in your database details:

```env
# Database Configuration (PostgreSQL)
DB_HOST=localhost
DB_PORT=5432
DB_NAME=your_actual_database_name
DB_USER=your_actual_username
DB_PASSWORD=your_actual_password

# Application Settings
APP_ENV=production
APP_DEBUG=false

# Session Configuration
SESSION_LIFETIME=3600
```

**Important:** Never commit `.env` to Git - it's already in `.gitignore`

### 4. Upload Files to WebSupport.sk

#### Via FTP/SFTP:

1. Connect to your hosting using FTP client (FileZilla, WinSCP)
   - Host: Your domain or FTP host from Webadmin
   - Username: From Webadmin
   - Password: From Webadmin
   - Port: 21 (FTP) or 22 (SFTP)

2. Upload all project files to `/www/your-domain.sk/` directory:
   ```
   /www/your-domain.sk/
   ├── .htaccess          ← Root .htaccess (for Option B below)
   ├── .env               ← Important!
   ├── config/
   ├── includes/
   ├── public/
   │   ├── .htaccess      ← Public .htaccess (for Option A below)
   │   ├── index.php
   │   └── ...
   ├── vendor/
   ├── composer.json
   └── composer.lock
   ```

3. **Verify file permissions:**
   - Files: `644`
   - Folders: `755`
   - `.htaccess` files: `644`
   - `.env`: `600` (most restrictive)

### 5. Configure PHP Version

1. Log in to WebSupport.sk Webadmin
2. Navigate to **Web Hosting** → **PHP Settings**
3. Select **PHP 8.2** or higher
4. Save changes

### 6. Set Up Database Schema

Connect to your database via phpPgAdmin or SSH and import your schema:

1. Export your current schema from development:
   ```bash
   pg_dump -h localhost -U your_user -d your_db --schema-only > schema.sql
   ```

2. Import to production database via:
   - phpPgAdmin (if available in Webadmin)
   - SSH/Terminal access
   - Database management tool

**Alternative:** If you have existing data, export and import both schema and data:
```bash
# Export
pg_dump -h localhost -U your_user -d your_db > full_backup.sql

# Import via psql
psql -h your_host -U your_user -d your_db < full_backup.sql
```

### 7. Configure Domain Web Root

**IMPORTANT:** Choose ONE option below and ensure the correct `.htaccess` file is active.

#### Option A: Point domain to /public directory (Recommended - Most Secure)
In WebSupport.sk Webadmin:
1. Navigate to **Domains** → **Web Settings**
2. Set document root to: `/www/your-domain.sk/public/`
3. **Ensure `/public/.htaccess` file is present** - This provides:
   - HTTPS redirect
   - Security headers  
   - PHP settings
   - Caching rules
   - Directory traversal protection

**With this option:**
- ✅ **config/**, **vendor/**, and **includes/** directories are physically OUTSIDE the web root (inaccessible)
- ✅ **`.env`** file is physically OUTSIDE the web root (completely inaccessible)
- ✅ Directory traversal attempts (../) are blocked
- ✅ Most secure configuration - sensitive files cannot be reached at all
- ✅ Use the `.htaccess` file in `/public/` directory

**Directory structure when using Option A:**
```
/www/your-domain.sk/           ← Parent (NOT web accessible)
├── config/                    ← NOT accessible (outside web root)
├── includes/                  ← NOT accessible (outside web root)
├── vendor/                    ← NOT accessible (outside web root)
├── .env                       ← NOT accessible (outside web root)
└── public/                    ← WEB ROOT (only this is accessible)
    ├── .htaccess
    ├── index.php
    └── *.php (your app files)
```

#### Option B: Keep default root at /www/your-domain.sk/
If you can't change the document root:
1. Keep web root pointing to `/www/your-domain.sk/`
2. **Ensure root `.htaccess` file is present** - This provides:
   - HTTPS redirect
   - Automatic routing to `/public/` directory
   - Protection for config/, vendor/, includes/ directories
   - Security headers

**With this option:**
- ⚠️ Root directories are web-accessible but protected by .htaccess rules
- ⚠️ `.env` file is protected by .htaccess rules
- ✅ Use the `.htaccess` file in the root directory

**Verification:**
Test that `.htaccess` is working:
1. Visit `https://your-domain.sk` - should load
2. Try accessing `https://your-domain.sk/.env` - should be blocked (403 error)
3. Try accessing `https://your-domain.sk/config/` - should be blocked (403 error)
4. HTTP should redirect to HTTPS automatically

### 8. Enable HTTPS (SSL)

1. In Webadmin, navigate to **SSL Certificates**
2. Enable **Let's Encrypt SSL** (usually free)
3. Wait for certificate activation (5-15 minutes)
4. The `.htaccess` will automatically redirect HTTP to HTTPS

### 9. Test Your Deployment

Visit your domain:
- `https://your-domain.sk` - Should load the home page
- `https://your-domain.sk/public.php` - Public dashboard
- `https://your-domain.sk/login.php` - Admin login

Check that:
- ✅ HTTPS is working (green padlock)
- ✅ Database connection works
- ✅ Login functionality works
- ✅ All pages load correctly
- ✅ Excel import/export functions work

### 10. Create Admin User

If you need to create the first admin user, connect to your database and run:

```sql
INSERT INTO "Users" (username, full_name, email, password_hash, role)
VALUES (
    'admin',
    'System Administrator',
    'admin@example.com',
    '$2y$10$YOUR_BCRYPT_HASH_HERE',
    'Administrator'
);
```

To generate a password hash locally:
```php
<?php
echo password_hash('your_password', PASSWORD_DEFAULT);
?>
```

## Security Checklist

After deployment, verify:

- ✅ `.env` file is NOT accessible via browser
- ✅ `/config/` directory is blocked
- ✅ `/vendor/` directory is blocked
- ✅ HTTPS is enforced
- ✅ File permissions are correct (644/755)
- ✅ Default passwords have been changed
- ✅ `APP_DEBUG=false` in production

## Troubleshooting

### Error: "Database connection failed"
- Check `.env` file exists and has correct credentials
- Verify PostgreSQL is running and accessible
- Test connection with database management tool

### Error: "500 Internal Server Error"
- Check `.htaccess` syntax
- Verify PHP version is 8.2+
- Check file permissions (644 for files, 755 for folders)
- Check server error logs in Webadmin

### Files not found / 404 errors
- Verify web root is set to `/public/` directory
- Check `.htaccess` file is uploaded and readable
- Verify mod_rewrite is enabled (usually is on WebSupport.sk)

### Changes not visible
- Clear browser cache (Ctrl+Shift+R or Cmd+Shift+R)
- Check if file was uploaded correctly
- Verify correct file permissions

### Excel import/export not working
- Verify `upload_max_filesize` and `post_max_size` in `.htaccess`
- Check `/vendor/` directory exists with PhpSpreadsheet
- Verify `composer install` was run locally before upload

## Updating the Application

To deploy updates:

1. Make changes locally
2. Test thoroughly
3. Upload changed files via FTP
4. Clear any cache if applicable
5. Test on production

For database schema changes:
1. Export/backup production database first
2. Test migration locally
3. Apply to production database
4. Verify all functionality

## Support Resources

- **WebSupport.sk Support**: https://www.websupport.sk/podpora/
- **WebSupport.sk Webadmin**: Login at websupport.sk
- **PHP Documentation**: https://www.php.net/docs.php
- **PostgreSQL Documentation**: https://www.postgresql.org/docs/

## Backup Strategy

Regular backups are critical:

1. **Database Backups**:
   - Use WebSupport.sk automated backups (if available)
   - Or manually: `pg_dump` weekly/daily
   
2. **File Backups**:
   - Download `/public/` directory (uploaded files)
   - Keep local copy of code in Git

3. **Backup Schedule**:
   - Daily: Database
   - Weekly: Full file backup
   - Before any major update: Complete backup

---

## Quick Reference

### File Permissions
```bash
# Set correct permissions via FTP client or SSH
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 600 .env
```

### Database Connection Test
```php
<?php
require_once 'config/database.php';
try {
    $db = getDbConnection();
    echo "✅ Database connected successfully!";
} catch (Exception $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>
```

### Important URLs
- Admin Dashboard: `/dashboard.php`
- Public Dashboard: `/public.php`
- Login Page: `/login.php`
- Project Import: `/projects-import.php`

---

**Deployment completed?** Test all features and monitor error logs for the first 24 hours.
