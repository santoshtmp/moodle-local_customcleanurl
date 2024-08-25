<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * 
 * @package    local_customcleanurl
 * @copyright  2024 https://santoshmagar.com.np/
 * @author     santoshtmp7
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */

defined('MOODLE_INTERNAL') || die();

/**
 * used during install and upgrade
 */
function set_htaccess()
{
    require_once(dirname(__FILE__) . '/../../../../config.php');
    global $CFG;
    $htaccess_file_path = $CFG->dirroot . '/.htaccess';
    try {
        if (file_exists($htaccess_file_path)) {
            $contents = file_get_contents($htaccess_file_path);
            $contents = string_except_between_two_string($contents, '# BEGIN_MOODLE_LOCAL_CUSTOMCLEANURL', '# END_MOODLE_LOCAL_CUSTOMCLEANURL');
            $update_content = $contents . "\n" . get_default_htaccess_content();
            $update_content = trim($update_content);
            // $file = fopen($htaccess_file_path, "w");
            // fwrite($file, $update_content);
            // fclose($file);
            file_put_contents($htaccess_file_path, $update_content);
        } else {
            $default_contents = get_default_htaccess_content();
            file_put_contents($htaccess_file_path, $default_contents);
        }
        return true;
    } catch (\Exception $e) {
        echo $e->getMessage();
        return false;
    }
    return false;
}

/**
 * used during uninstall
 */
function unset_htaccess()
{
    require_once(dirname(__FILE__) . '/../../../../config.php');
    global $CFG;
    $htaccess_file_path = $CFG->dirroot . '/.htaccess';
    try {
        if (file_exists($htaccess_file_path)) {
            $contents = file_get_contents($htaccess_file_path);
            $contents = string_except_between_two_string($contents, '# BEGIN_MOODLE_LOCAL_CUSTOMCLEANURL', '# END_MOODLE_LOCAL_CUSTOMCLEANURL');
            $update_content = trim($contents);
            file_put_contents($htaccess_file_path, $update_content);
        }
        return true;
    } catch (\Exception $e) {
        echo $e->getMessage();
        return false;
    }
    return false;
}

/**
 * return string 
 */
function string_except_between_two_string($content_string, $starting_word, $ending_word)
{
    $start_pos = ($start_pos = strpos($content_string, $starting_word)) ? $start_pos : 0;
    $end_pos = strrpos($content_string, $ending_word);
    if ($end_pos) {
        $end_pos += strlen($ending_word);
        $content_string = substr($content_string, 0, $start_pos) . substr($content_string, $end_pos);
    }
    return $content_string;
}

/**
 * get default rule
 */
function get_default_htaccess_content()
{
    $default_contents = "
# BEGIN_MOODLE_LOCAL_CUSTOMCLEANURL
# DO NOT EDIT route
<IfModule mod_rewrite.c>
# Enable RewriteEngine
Options +FollowSymLinks
Options -MultiViews
Options -Indexes
RewriteEngine on
# All relative URLs are based from root
RewriteBase /
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /local/customcleanurl/locallib/route/index.php [L]
ErrorDocument 403 /local/customcleanurl/locallib/404.php
ErrorDocument 404 /local/customcleanurl/locallib/404.php
</IfModule>
# DO NOT EDIT route
# END_MOODLE_LOCAL_CUSTOMCLEANURL

# Deny access to hidden files - files that start with a dot (.)
<FilesMatch \"^\.\">
Order allow,deny
Deny from all
</FilesMatch>
    ";
    $default_contents = trim($default_contents);
    return $default_contents;
}
