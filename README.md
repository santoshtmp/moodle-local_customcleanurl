# customcleanurl

Custom Clean URL converts standard Moodle URLs into more readable, SEO-friendly formats.  
It also supports:
- Defining custom URLs for any existing Moodle page
- Custom 404 page template
- Redirecting old URLs to new ones

## Examples

1. **Course View Page**  
   `your_domain/course/view.php?id=ID` → `your_domain/course/course_short_name`

2. **Course Category Page**  
   `your_domain/course/index.php?categoryid=ID` → `your_domain/course/category/ID/category_name`

3. **Course Edit Page**  
   `your_domain/course/edit.php?id=ID` → `your_domain/course/edit/course_short_name`

4. **User Profile**  
   `your_domain/user/profile.php?id=ID` → `your_domain/user/profile/username`

5. **Custom defined URL**  
   `your_domain/mod/page/view.php?id=11` → `your_domain/about-us`

## Installation

1. Download the plugin as a ZIP from GitHub or the Moodle plugins directory.
2. Extract it into your Moodle installation:

   ```
   your_moodle/local/customcleanurl/
   ```

3. Go to **Site administration → Notifications** to install the plugin.
4. Configure the plugin under **Site administration → Plugins → Local plugins → Custom Clean URL**.
5. Add the required web server rewrite rules (see below).
---

## Web Server Configuration

The plugin requires rewrite rules so that non-existent paths are routed to `local/customcleanurl/route.php`.

### Apache (.htaccess)

Add the following rules to your Moodle root `.htaccess` file (usually at the same level as `config.php`):

```apache
# BEGIN_MOODLE_LOCAL_CUSTOMCLEANURL
# DO NOT EDIT LOCAL_CUSTOMCLEANURL ROUTE

RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /local/customcleanurl/route.php [L]
ErrorDocument 403 /local/customcleanurl/404.php
ErrorDocument 404 /local/customcleanurl/404.php

# DO NOT EDIT LOCAL_CUSTOMCLEANURL ROUTE
# END_MOODLE_LOCAL_CUSTOMCLEANURL
```

> **Note:** Make sure `AllowOverride All` (or at least `AllowOverride FileInfo`) is enabled for the Moodle directory, and that `mod_rewrite` is enabled.

---

### Nginx

Nginx does **not** use `.htaccess`. You must modify the server block configuration.

In your Moodle `location /` block, change the `try_files` directive to:

```nginx
location / {
    # Clean URL routing via customcleanurl
    try_files $uri $uri/ /local/customcleanurl/route.php?$query_string;
    index index.php index.html;
}
```

> After changing the Nginx config, test and reload:
> ```bash
> sudo nginx -t
> sudo systemctl reload nginx
> ```

---

## Screenshot
![Clean custom url setting](./pix/screenshot/general_settings.png)
![Define custom url](./pix/screenshot/define_custom_url.png)
![Define redirect url](./pix/screenshot/define_urlredirect.png)
