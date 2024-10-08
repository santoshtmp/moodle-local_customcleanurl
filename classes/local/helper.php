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

use moodle_url;

defined('MOODLE_INTERNAL') || die();


/**
 * helper class for customcleanurl local
 *
 * @package    local_customcleanurl
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper
{

    /**
     * get_clean_url for the provided moodle_url url
     */
    public static function get_clean_url(moodle_url $url)
    {
        $clean_url = new \local_customcleanurl\local\clean_url($url);
        return $clean_url->cleanedurl;
    }

    // check if the moodle default original url is present for the clean url or not. 
    // Then return the url if present
    public static function get_default_url()
    {
        global $CFG, $DB;
        // check emable_customcleanurl
        $emable_customcleanurl = get_config('local_customcleanurl', 'emable_customcleanurl');
        if (!$emable_customcleanurl || empty($emable_customcleanurl)) {
            return;
        }
        $request_url =  $_SERVER['REQUEST_URI'];
        $request_moodle_url = new moodle_url($request_url);
        self::check_restricted_param();
        $url = '';
        $cache_default_url = \cache::make('local_customcleanurl', 'default_url');
        $cached_value = $cache_default_url->get($request_moodle_url->out(false));
        if ($cached_value) {
            $url = $cached_value;
        } else {
            $check_custom_url = $DB->get_record('local_customcleanurl', ['custom_url' => $request_moodle_url->out(false)]);
            if ($check_custom_url) {
                $url = $check_custom_url->default_url;
            } else {
                $check_custom_url_path = $DB->get_record('local_customcleanurl', ['custom_url' => $request_moodle_url->get_path(false)]);
                if ($check_custom_url_path) {
                    $url = $check_custom_url_path->default_url;
                }
            }
        }
        // 
        if ($url) {
            $url = new moodle_url($url);
            foreach ($url->params() as $k => $v) {
                $v = str_replace('+', ' ', $v);
                $_GET[$k] = $v;
            }
            return $url;
        }
        return false;
    }

    /**
     * check if clean url CACHE is present for the default original moodle url.
     * @param \moodle_url $moodle_original_url
     */
    public static function check_clean_url_cached(moodle_url $moodle_original_url)
    {
        $cache_clean_url = \cache::make('local_customcleanurl', 'clean_url');
        $cached_value = $cache_clean_url->get($moodle_original_url->raw_out(false));
        if ($cached_value) {
            return new moodle_url($cached_value);
        }
        return $cached_value;
    }

    /**
     * set CACHE clean url for the default original moodle url and vice - versa
     * @param \moodle_url $moodle_original_url
     * @param \moodle_url $cleaned_url
     */
    public static function set_url_cache(moodle_url $moodle_original_url, moodle_url $cleaned_url)
    {
        $cache_clean_url = \cache::make('local_customcleanurl', 'clean_url');
        if ($cache_clean_url) {
            $cache_clean_url->set($moodle_original_url->raw_out(false), $cleaned_url->raw_out(false));
        }
        $cache_default_url = \cache::make('local_customcleanurl', 'default_url');
        if ($cache_default_url) {
            $cache_default_url->set($cleaned_url->raw_out(false), $moodle_original_url->raw_out(false));
        }
    }


    // check_restricted_param
    public static function check_restricted_param()
    {
        if (isset($_GET['id'])) {
            echo "GET id parameter is restricted in this url";
            die;
        }
        if (isset($_GET['categoryid'])) {
            echo "GET categoryid parameter is restricted in this url";
            die;
        }
    }


    // urlrewriteclass initialize
    public static function urlrewriteclass_initialize()
    {
        global $CFG;
        if (during_initial_install() || isset($CFG->upgraderunning)) {
            // Do nothing during installation or upgrade.
            return;
        }
        $CFG->urlrewriteclass = '\\local_customcleanurl\\url_rewriter';
    }

    //url_slug
    public static function url_slug($name)
    {
        return str_replace(' ', '-', strtolower($name));
    }
}
