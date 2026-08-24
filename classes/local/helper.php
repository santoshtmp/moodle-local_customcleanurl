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
 * Helper class for local_customcleanurl plugin.
 *
 * @package    local_customcleanurl
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace local_customcleanurl\local;

use moodle_url;
use stdClass;

/**
 * Helper class for local_customcleanurl plugin.
 *
 * Provides utility methods for URL rewriting, routing, and redirect functionality.
 *
 * @package    local_customcleanurl
 * @copyright  2025 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Checks whether custom clean URL feature is enabled in plugin settings.
     *
     * Caches the result in $CFG to avoid repeated database lookups.
     *
     * @return bool True if custom clean URLs are enabled, false otherwise.
     */
    public static function is_enable_customcleanurl() {
        global $CFG;

        try {
            if (isset($CFG->enablecustomcleanurl)) {
                return $CFG->enablecustomcleanurl;
            }
            $enablecustomcleanurl = get_config('local_customcleanurl', 'enable_customcleanurl');
            if ($enablecustomcleanurl && $enablecustomcleanurl == '1') {
                $CFG->enablecustomcleanurl = true;
            } else {
                $CFG->enablecustomcleanurl = false;
            }
        } catch (\Throwable $th) {
            $CFG->enablecustomcleanurl = false;
        }
        return $CFG->enablecustomcleanurl;
    }

    /**
     * Checks if the local_customcleanurl route is accessible.
     *
     * Performs an HTTP request to the route test endpoint and caches
     * the result in $CFG to avoid repeated checks.
     *
     * @return bool True if the route is accessible, false otherwise.
     */
    public static function customcleanurl_routecheck() {
        global $CFG;
        if (isset($CFG->customcleanurlroutecheck)) {
            return $CFG->customcleanurlroutecheck;
        }
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $CFG->wwwroot . '/customcleanurl/routetest');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($httpcode === 200) {
                $response = json_decode($response);
                $customcleanurlroutecheck = isset($response->status) ? (bool)$response->status : false;
            } else {
                $customcleanurlroutecheck = false;
            }
        } catch (\Throwable $th) {
            $customcleanurlroutecheck = false;
        }
        $CFG->customcleanurlroutecheck = $customcleanurlroutecheck;
        return $CFG->customcleanurlroutecheck;
    }

    /**
     * Resolves a given request URL to a Moodle internal path or file.
     *
     * Analyzes the request path and determines the appropriate Moodle URL,
     * file path, and parameters based on configured clean URL types.
     *
     * @param string $requesturl The URL to be resolved (can be relative or absolute).
     * @return array{
     *     status: bool,
     *     moodleurl: string,
     *     filepath: string,
     *     param: array,
     *     message: string,
     *     urltype: string
     * } Response data containing resolution details.
     */
    public static function check_requesturl($requesturl) {
        global $CFG, $DB;
        $subdirpath = (new \moodle_url($CFG->wwwroot))->get_path(false);
        $requestmoodleurl = new moodle_url(rtrim($requesturl, "/"));
        $requestpath = $requestmoodleurl->get_path(false);
        $requestpath = urldecode(str_replace($subdirpath, '', $requestpath));

        $responsedata = [
            'status' => true,
            'moodleurl' => '',
            'filepath' => '',
            'param' => [],
            'message' => '',
            'urltype' => '',
        ];

        if (!self::is_enable_customcleanurl()) {
            $responsedata['status'] = false;
            $responsedata['message'] = get_string('featureisnotenable', 'local_customcleanurl');
            return $responsedata;
        }

        $parts = explode("/", trim($requestpath, '/'));
        $uniquename = urldecode(end($parts));
        $responseuri = '';

        $cleanurltype = get_config('local_customcleanurl', 'cleanurl_type');
        $cleanurltype = explode(",", $cleanurltype);

        // Case 1: Admin-defined custom mapping (defineurl).
        if (in_array('defineurl', $cleanurltype)) {
            $checkcustomurlpath = $DB->get_record(
                'local_customcleanurl',
                [
                    'custom_url' => $requestpath,
                    'cleanurl_type' => 'defineurl',
                ],
            );
            if ($checkcustomurlpath) {
                $responseuri = $checkcustomurlpath->default_url;
            }
        }

        // Case 2: Course-related URLs (courseurl).
        if (in_array('courseurl', $cleanurltype) && !$responseuri && $parts[0] === 'course') {
            $course = $DB->get_record('course', ['shortname' => $uniquename]);
            if ($course && count($parts) === 2) {
                $responseuri = "/course/view.php?id=" . $course->id;
            } else if ($course && count($parts) === 3 && $parts[1] === 'edit') {
                $responseuri = "/course/edit.php?id=" . $course->id;
            } else if (count($parts) === 4) {
                $coursecategories = $DB->get_record('course_categories', ['id' => $parts[2]]);
                $responseuri = "/course/index.php?categoryid=" . $coursecategories->id;
            }
        }

        // Case 3: User profile URLs (userurl).
        if (in_array('userurl', $cleanurltype) && !$responseuri && $parts[0] === 'user') {
            $user = $DB->get_record('user', ['username' => $uniquename]);
            if ($user && count($parts) === 3) {
                $usesmartprofile = (bool)get_config('local_smartprofile', 'enableredirect') &&
                                   file_exists($CFG->dirroot . '/local/smartprofile/index.php');
                if ($usesmartprofile) {
                    $responseuri = "/local/smartprofile/index.php?id=" . $user->id;
                } else {
                    $responseuri = "/user/profile.php?id=" . $user->id;
                }
            }
        }

        // Case 4: SmartLearn Theme Custom Pages (e.g. /about-us, /pricing).
        if (!$responseuri) {
            $slug = trim($requestpath, '/');
            try {
                $checkpage = $DB->get_record('theme_smartlearn_pages', ['slug' => $slug]);
                if (!$checkpage) {
                    $checkpage = $DB->get_record_select('theme_smartlearn_pages', 'LOWER(slug) = ?', [strtolower($slug)]);
                }
                if ($checkpage) {
                    $responseuri = "/theme/smartlearn/view_page.php?slug=" . urlencode($checkpage->slug);
                }
            } catch (\Throwable $e) {
                // Table might not exist or other error.
            }
        }

        // Return the resolved Moodle URL if found.
        if ($responseuri) {
            $requestparam = $requestmoodleurl->params();

            $responseurl = new moodle_url($responseuri);
            foreach ($responseurl->params() as $k => $v) {
                if (array_key_exists($k, $requestparam)) {
                    $a = new stdClass();
                    $a->param = $k;
                    $a->responsepath = $responseuri;
                    $responsedata['status'] = false;
                    $responsedata['message'] = get_string('invalidcustomparam', 'local_customcleanurl', $a);
                    return $responsedata;
                }
            }
            $rawresponsepath = $responseurl->get_path(false);
            $responsepath = str_starts_with($rawresponsepath, $subdirpath)
                ? substr($rawresponsepath, strlen($subdirpath))
                : $rawresponsepath;
            $responsedata['urltype'] = self::geturlpathtype($responsepath);
            $responsedata['filepath'] = $CFG->dirroot . $responsepath;
            $responsedata['param'] = $responseurl->params();
            $responsedata['moodleurl'] = $responseurl->raw_out(false);

            return $responsedata;
        }

        // Directory as path - check for index files.
        $dirpath = $CFG->dirroot . $requestpath;
        if (is_dir($dirpath)) {
            $files = scandir($dirpath);
            foreach ($files as $filename) {
                if ($filename === 'index.html' || $filename === 'index.php') {
                    $pathinfofolder = pathinfo($filename);
                    $filepath = $dirpath . 'index.' . $pathinfofolder['extension'];
                    $responsedata['filepath'] = $filepath;
                    $responsedata['urltype'] = 'dirpath';
                    return $responsedata;
                }
            }
        }

        // Check if PHP file exists in the path.
        if (str_contains($requestpath, '.php')) {
            $filepath = $CFG->dirroot . explode('.php', $requestpath)[0] . '.php';
            if (file_exists($filepath)) {
                $responsedata['filepath'] = $filepath;
                $responsedata['urltype'] = 'phppath';
                return $responsedata;
            }
        }

        // Handle customcleanurl route test endpoint.
        if ($requestpath == '/customcleanurl/routetest') {
            $responsedata['urltype'] = 'customcleanurl_routetest';
            return $responsedata;
        }

        // Return 404 if no matching path is found.
        $responsedata['urltype'] = '404';
        $responsedata['filepath'] = $CFG->dirroot . '/local/customcleanurl/404.php';
        return $responsedata;
    }

    /**
     * Determines the type of a given response path for customcleanurl.
     *
     * Matches the response path against known URL patterns (course, user)
     * and returns a corresponding type identifier.
     *
     * @param string $responsepath The relative URL path to evaluate (e.g., "/course/view.php").
     * @return string The URL type key if matched, otherwise a generated identifier from the path.
     */
    public static function geturlpathtype($responsepath) {
        $urltypes = [
            'customcleanurl_courseurl' => [
                '/course/view.php',
                '/course/edit.php',
                '/course/index.php',
            ],
            'customcleanurl_userurl' => [
                '/user/profile.php',
                '/local/smartprofile/index.php',
            ],
        ];
        foreach ($urltypes as $key => $item) {
            if (is_array($item)) {
                if (in_array($responsepath, $item, true)) {
                    return $key;
                }
            } else {
                if ($item === $responsepath) {
                    return $key;
                }
            }
        }
        $responsepath = str_replace('.php', '', $responsepath);
        return implode("_", explode("/", trim($responsepath, "/")));
    }

    /**
     * Initializes the custom clean URL rewrite class by assigning it
     * to $CFG->urlrewriteclass if enabled.
     *
     * This method is safe during installation and upgrade phases (skips setup).
     *
     * @return void
     */
    public static function urlrewriteclass_initialize() {
        global $CFG;
        if (during_initial_install() || isset($CFG->upgraderunning)) {
            // Do nothing during installation or upgrade.
            return;
        }
        if (self::is_enable_customcleanurl() && class_exists('\local_customcleanurl\customcleanurl')) {
            $CFG->urlrewriteclass = '\\local_customcleanurl\\customcleanurl';
        }
    }


    /**
     * Checks and processes URL.
     *
     * If URL redirect is enabled and the current request matches a defined
     * redirect rule, redirects the user to the configured custom URL.
     *
     * @return void
     */
    public static function urlredirect_initialize() {
        global $CFG, $DB, $PAGE;
        $enableurlredirect = get_config('local_customcleanurl', 'enable_urlredirect');
        if ($enableurlredirect) {
            $requesturi = $_SERVER['REQUEST_URI'];
            $subdirpath = (new \moodle_url($CFG->wwwroot))->get_path(false);
            $requestmoodleurl = new moodle_url(rtrim($requesturi, "/"));
            $requesturl = $requestmoodleurl->out(false);
            $requesturl = str_replace($CFG->wwwroot, '', $requesturl);
            $requesturl = str_replace($subdirpath, '', $requesturl);

            $checkcustomurlpath = $DB->get_record(
                'local_customcleanurl',
                [
                    'default_url' => $requesturl,
                    'cleanurl_type' => 'urlredirect',
                ],
            );
            if ($checkcustomurlpath) {
                redirect(new moodle_url($checkcustomurlpath->custom_url));
            }
        }
    }
}
