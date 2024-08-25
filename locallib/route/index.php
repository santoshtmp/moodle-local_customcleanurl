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

require_once(dirname(__FILE__) . '/../../../../config.php');
defined('MOODLE_INTERNAL') || die();
require_once('web_route_define.php');

global $CFG;
$url = $_SERVER['REQUEST_URI'];
$url_path = parse_url($url, PHP_URL_PATH);
$url_query = ($url_query = parse_url($url, PHP_URL_QUERY)) ? '?' . $url_query : '';
/**
 * check if new path and file exist or not
 */
$get_file_exists = false;
$route_define = get_customcleanurl_route();
foreach ($route_define as $new_path => $actual_path) {
    if (($url_path == $new_path) || ($url_path == $new_path . '/')) {
        $filepath = $CFG->dirroot . $actual_path;
        if (file_exists($filepath)) {
            chdir(dirname($filepath));
            require($filepath);
            die();
        }
    }
}

/**
 * check if php file is present in path
 */
if (str_contains($url_path, '.php')) {
    $filepath = $CFG->dirroot . explode('.php', $url_path)[0] . '.php';
    if (file_exists($filepath)) {
        chdir(dirname($filepath));
        require($filepath);
        die();
    }
}

/**
 * dir_path the path as directory 
 */
$dir_path = $CFG->dirroot . $url_path;
if (is_dir($dir_path)) {
    $files = scandir($dir_path);
    foreach ($files as $filename) {
        if ($filename === 'index.html' || $filename === 'index.php') {
            $path_info_folder = pathinfo($filename);
            $filepath = $dir_path . 'index.' . $path_info_folder['extension'];
            chdir(dirname($filepath));
            require($filepath);
            die();
        }
    }
}

/**
 * at last redirect to 404 page if the path is not found
 */
// if (!$get_file_exists) {
// if (!file_exists($dir_path) && !is_dir($dir_path)) {}
// }
customcleanurl_error_page();
