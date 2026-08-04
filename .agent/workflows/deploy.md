---
description: Deployment Workflow for Amter English (Hostinger VPS + CyberPanel)
---

This workflow guides you through deploying the Amter English Laravel project to a Hostinger VPS managed with CyberPanel, including switching SSL from ZeroSSL to Let's Encrypt.

### 1. Prepare Local Project
Ensure your project is ready for €production.
- [ ] Run `npm run build` to generate production assets.
- [ ] Ensure `composer.json` and `package.json` are up to date.
- [ ] Commit all changes to your repository.

### 2. CyberPanel Configuration (amter.in)
1. Log in to your **CyberPanel Dashboard** (usually at `https://your-vps-ip:8090`).
2. Go to **Websites** > **Create Website** (if not already created).
   - Domain: `amter.in`
   - Email: `admin@amter.in`
   - PHP: `8.2` (or your preferred version)
   - Features: Select `SSL` and `OpenLiteSpeed`.
3. If the website already exists and has ZeroSSL, proceed to Step 3.

### 3. Switch SSL from ZeroSSL to Let's Encrypt
In CyberPanel, Let's Encrypt is the default engine for "Issue SSL".
1. Go to **SSL** > **Manage SSL**.
2. Select **amter.in**.
3. Click **Issue SSL**. 
   - *Note: CyberPanel will prefer Let's Encrypt for new issues if auto-renew/issue is triggered.*
4. If it persists with ZeroSSL, you may need to:
   - Go to **Websites** > **List Websites** > **Manage (amter.in)**.
   - Look for the **SSL** section.
   - Delete existing certs if possible or just click **Issue SSL** to overwrite with a Let's Encrypt certificate.
   - *Ensure your domain is pointing to the VPS IP via A records first.*

### 4. Deploy Code via SSH
1. SSH into your VPS: `ssh root@your-vps-ip`
2. Navigate to the website directory: `cd /home/amter.in/public_html`
3. Clone your repo or pull changes:
   ```bash
   git clone <your-repo-url> .
   ```
4. Install dependencies:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
5. Set up `.env`:
   ```bash
   cp .env.example .env
   nano .env
   ```
   *Update DB_DATABASE, DB_USERNAME, DB_PASSWORD, and APP_URL=https://amter.in.*

6. Generate Key and Migrate:
   ```bash
   php artisan key:generate
   php artisan migrate --force
   ```

### 5. OpenLiteSpeed Optimization
1. In CyberPanel Manage Website, go to **Rewrite Rules**.
2. Ensure standard Laravel rewrite rules are present:
   ```apacheconf
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteRule ^ index.php [L]
   ```
3. Set **Document Root** to `/home/amter.in/public_html/public`.

### 6. File Permissions
```bash
chown -R lscpd:lscpd /home/amter.in/public_html
find /home/amter.in/public_html -type f -exec chmod 644 {} \;
find /home/amter.in/public_html -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
```