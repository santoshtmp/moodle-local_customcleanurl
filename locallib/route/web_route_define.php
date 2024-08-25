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
 * define new url path and the actual page url path
 */
function get_customcleanurl_route()
{
    $routes = [
        '/404' => '/local/customcleanurl/locallib/404.php',
    ];

    return $routes;
}

/**
 * customcleanurl error_page
 */
if (!function_exists('customcleanurl_error_page')) {
    function customcleanurl_error_page()
    {
        global $CFG;
        header("HTTP/1.0 404 Not Found");
        http_response_code('404');
        $_SERVER['REDIRECT_STATUS'] = '404';
        $filepath = $CFG->dirroot . '/local/customcleanurl/locallib/404.php';
        chdir(dirname($filepath));
        require($filepath);
        die();
    }
}
