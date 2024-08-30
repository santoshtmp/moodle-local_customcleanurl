# customcleanurl
customcleanurl main idea is to convert the moodle url in more readable format by removing id parameter

## For Example:
1. course View Page URL
    your_domain/course/view.php?id=ID => your_domain/course/course_shot_name
2. Course Category Page URL
    your_domain/course/index.php?categoryid=ID => your_domain/course/category/category_name-ID
3. Course Edit Page URL
    your_domain/course/edit.php?id=ID => your_domain/course/edit/course_shot_name
4. User Profile URL
    your_domain/user/profile.php?id=ID => your_domain/user/profile/username
5. Other as defined.


## Modify Apache .htaccess file
...................................................
    # BEGIN_MOODLE_LOCAL_CUSTOMCLEANURL
    # DO NOT EDIT route
    <IfModule mod_rewrite.c>
    RewriteEngine On
    # All relative URLs are based from root
    RewriteBase /
    # Do not change URLs that point to an existing file and directory.
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ /local/customcleanurl/locallib/route.php [L]
    ErrorDocument 403 /local/customcleanurl/locallib/404.php
    ErrorDocument 404 /local/customcleanurl/locallib/404.php
    </IfModule>
    # DO NOT EDIT route
    # END_MOODLE_LOCAL_CUSTOMCLEANURL
...................................................

