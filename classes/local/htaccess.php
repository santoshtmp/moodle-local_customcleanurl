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
 * @author     santoshtmp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */

namespace local_customcleanurl\local;

/**
 * A class to check and modify htaccess file to rewrite the server route
 *
 * @package    local_customcleanurl
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class htaccess
{

    /** check_rewrite_htaccess */
    public static function check_rewrite_htaccess()
    {
        global $CFG;
        $htaccess_file_path = $CFG->dirroot . '/.htaccess';
        try {
            if (file_exists($htaccess_file_path)) {
                $contents = file_get_contents($htaccess_file_path);
                return str_contains($contents, self::get_default_htaccess_content());
            } else {
                return false;
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
        return false;
    }

    /**
     * to set RewriteRule in htaccess, used during install, upgrade or customcleanurl setting check
     */
    public static function set_htaccess()
    {
        global $CFG;
        $htaccess_file_path = $CFG->dirroot . '/.htaccess';
        try {
            if (file_exists($htaccess_file_path)) {
                $contents = file_get_contents($htaccess_file_path);
                $contents = self::string_except_between_two_string($contents, '# BEGIN_MOODLE_LOCAL_CUSTOMCLEANURL', '# END_MOODLE_LOCAL_CUSTOMCLEANURL');
                $update_content = $contents . "\n" . self::get_default_htaccess_content();
                $update_content = trim($update_content);
                file_put_contents($htaccess_file_path, $update_content);
            } else {
                $default_contents = self::get_default_htaccess_content();
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
     * To remove RewriteRule in htaccess, during uninstall
     */
    public static function unset_htaccess()
    {
        // require_once(dirname(__FILE__) . '/../../../../config.php');
        global $CFG;
        $htaccess_file_path = $CFG->dirroot . '/.htaccess';
        try {
            if (file_exists($htaccess_file_path)) {
                $contents = file_get_contents($htaccess_file_path);
                $contents = self::string_except_between_two_string($contents, '# BEGIN_MOODLE_LOCAL_CUSTOMCLEANURL', '# END_MOODLE_LOCAL_CUSTOMCLEANURL');
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
     * @param string $content_string
     * @param string $starting_word
     * @param string $ending_word
     */
    private static function string_except_between_two_string($content_string, $starting_word, $ending_word)
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
     * get default htaccess RewriteRule
     */
    private static function get_default_htaccess_content()
    {
        $default_contents = "
# BEGIN_MOODLE_LOCAL_CUSTOMCLEANURL
# DO NOT EDIT route
<IfModule mod_rewrite.c>
# Enable RewriteEngine
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

# Deny access to hidden files - files that start with a dot (.)
<FilesMatch \"^\.\">
Order allow,deny
Deny from all
</FilesMatch>

# Deny directory view
Options +FollowSymLinks
Options -MultiViews
Options -Indexes

# END_MOODLE_LOCAL_CUSTOMCLEANURL
    ";
        $default_contents = trim($default_contents);
        return $default_contents;
    }
}
