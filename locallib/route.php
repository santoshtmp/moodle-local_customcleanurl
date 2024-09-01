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

require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
global $CFG, $PAGE;
$url = $_SERVER['REQUEST_URI']; // $url = $PAGE->url->raw_out(false); //
$url_path = parse_url($url, PHP_URL_PATH); // $url_path = $PAGE->url->get_path(false); //
$url_query = ($url_query = parse_url($url, PHP_URL_QUERY)) ? '?' . $url_query : ''; // $url_query = $PAGE->url->params(); //
\local_customcleanurl\local\helper::urlrewriteclass_initialize();


/**
 * check if clean url is present 
 */
$moodle_default_url = \local_customcleanurl\local\helper::get_default_url();
if ($moodle_default_url) {
    $file = $moodle_default_url->out_omit_querystring();
    if (strpos($file, $CFG->wwwroot) === 0) {
        $file = substr($file, strlen($CFG->wwwroot));
        $file = $CFG->dirroot . $file;
    } else {
        $file = null;
    }
    if (is_file($file)) {
        chdir(dirname($file));
        $PAGE->set_url($moodle_default_url);
        $CFG->moodle_default_url = $url;
        require($file);
        die();
    }
}

/**
 * directory as path 
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
 * At last redirect to 404 page if the path is not found
 */
header("HTTP/1.0 404 Not Found");
http_response_code('404');
$_SERVER['REDIRECT_STATUS'] = '404';
$filepath = $CFG->dirroot . '/local/customcleanurl/locallib/404.php';
chdir(dirname($filepath));
require($filepath);
die();
