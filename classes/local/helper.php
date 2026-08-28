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

        $responsedata = [
            'status' => true,
            'moodleurl' => '',
            'filepath' => '',
            'param' => [],
            'message' => '',
            'urltype' => '',
        ];

        // Bail out early if the feature is disabled site-wide.
        if (!self::is_enable_customcleanurl()) {
            $responsedata['status'] = false;
            $responsedata['message'] = get_string('featureisnotenable', 'local_customcleanurl');
            return $responsedata;
        }

        $responseuri = '';

        $cleanurltype = get_config('local_customcleanurl', 'cleanurl_type');
        $cleanurltype = explode(",", $cleanurltype);

        $subdirpath = (new \moodle_url($CFG->wwwroot))->get_path(false);
        $requestmoodleurl = new moodle_url(rtrim($requesturl, "/"));
        $requestpath = $requestmoodleurl->get_path(false);
        $requestpath = str_replace($subdirpath, '', $requestpath);

        // Case 1: Admin-defined custom mapping (defineurl) — highest priority,
        // checked first so an explicit mapping always wins over pattern matching.
        if (in_array('defineurl', $cleanurltype)) {
            $checkcustomurlpath = $DB->get_record(
                'local_customcleanurl',
                [
                    'custom_url' => $requestpath,
                    'cleanurl_type' => 'defineurl',
                ],
            );
            if (!$checkcustomurlpath) {
                $decoderequestpath = implode(
                    '/',
                    array_map(
                        'rawurldecode',
                        explode('/', $requestpath)
                    )
                );
                $checkcustomurlpath = $DB->get_record(
                    'local_customcleanurl',
                    [
                        'custom_url' => $decoderequestpath,
                        'cleanurl_type' => 'defineurl',
                    ],
                );
            }

            if ($checkcustomurlpath) {
                $responseuri = $checkcustomurlpath->default_url;
            }
        }

        // Only fall through to pattern-based resolution if no explicit
        // mapping was found above.
        if (!$responseuri) {
            $parts = explode("/", trim($requestpath, '/'));
            $uniquename = rawurldecode(end($parts));

            // Case 2: Course-related URLs (courseurl).
            // Supported shapes: course/{shortname}, course/edit/{shortname},
            // course/index/{categoryid}/{name}.
            if (in_array('courseurl', $cleanurltype) && !$responseuri && $parts[0] === 'course') {
                $course = $DB->get_record('course', ['shortname' => $uniquename]);
                if ($course && count($parts) === 2) {
                    $responseuri = "/course/view.php?id=" . $course->id;
                } else if ($course && count($parts) === 3 && $parts[1] === 'edit') {
                    $responseuri = "/course/edit.php?id=" . $course->id;
                } else if (count($parts) === 4) {
                    $coursecategories = $DB->get_record('course_categories', ['id' => $parts[2]]);
                    // Guard against a missing category to avoid a warning
                    // when dereferencing a false result.
                    if ($coursecategories) {
                        $responseuri = "/course/index.php?categoryid=" . $coursecategories->id;
                    }
                }
            }

            // Case 3: User profile URLs (userurl).
            // Supported shape: user/profile/{username}.
            if (in_array('userurl', $cleanurltype) && !$responseuri && $parts[0] === 'user') {
                $user = $DB->get_record('user', ['username' => $uniquename]);
                if ($user && count($parts) === 3) {
                    $responseuri = "/user/profile.php?id=" . $user->id;
                }
            }
        }

        // Return the resolved Moodle URL if found.
        if ($responseuri) {
            $requestparam = $requestmoodleurl->params();

            $responseurl = new moodle_url($responseuri);

            // Reject the match if any param on the resolved URL is also
            // present on the incoming request — that's an ambiguous
            // collision rather than a clean match.
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
        // Fallback 1: request path maps to a real directory.
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

        // Fallback 2: request path already names a real PHP file on disk.
        if (str_contains($requestpath, '.php')) {
            $filepath = $CFG->dirroot . explode('.php', $requestpath)[0] . '.php';
            if (file_exists($filepath)) {
                $responsedata['filepath'] = $filepath;
                $responsedata['urltype'] = 'phppath';
                return $responsedata;
            }
        }

        // Fallback 3: built-in route test endpoint for verifying clean URLs work.
        if ($requestpath == '/customcleanurl/routetest') {
            $responsedata['urltype'] = 'customcleanurl_routetest';
            return $responsedata;
        }

        // Nothing matched — report a 404.
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
            'customcleanurl_userurl' => '/user/profile.php',
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

    /**
     * Add the "Define custom URL" node to a secondary navigation tree.
     *
     * Shared entry point used by both secondary_extend and before_http_headers.
     * Skips pages without secondary navigation, excluded layouts, and when the
     * defineurl feature is disabled. Prevents duplicate nodes.
     *
     * @param \navigation_node $secondaryview Secondary navigation root node.
     */
    public static function add_define_custom_url_node(\navigation_node $secondaryview): void {
        global $PAGE, $CFG;

        if (!$PAGE->has_secondary_navigation()) {
            return;
        }

        if (!has_capability('local/customcleanurl:managecustomcleanurl', $PAGE->context)) {
            return;
        }

        // Prevent duplicate.
        if ($secondaryview->find('local_customcleanurl', null)) {
            return;
        }

        $currentpagelayout = $PAGE->pagelayout;
        if (in_array($currentpagelayout, ['frontpage', 'admin'], true)) {
            return;
        }

        if ($currentpagelayout === 'coursecategory') {
            $categoryid = optional_param('categoryid', 0, PARAM_INT);
            if (empty($categoryid)) {
                return;
            }
        }

        $cleanurltype = get_config('local_customcleanurl', 'cleanurl_type');
        $cleanurltype = explode(',', (string) $cleanurltype);

        // Only when defineurl is enabled.
        if (!in_array('defineurl', $cleanurltype, true)) {
            return;
        }

        $pagerawurl = str_replace($CFG->wwwroot, '', $PAGE->url->raw_out(false));

        $url = new \moodle_url('/local/customcleanurl/define_custom_url.php', [
            'action'      => 'edit',
            'sesskey'     => sesskey(),
            'default_url' => $pagerawurl,
            'returnurl'   => $pagerawurl,
        ]);

        $node = \navigation_node::create(
            get_string('define_custom_url', 'local_customcleanurl'),
            $url,
            \navigation_node::TYPE_SETTING,
            null,
            'local_customcleanurl',
            new \pix_icon('i/settings', '')
        );

        $node->showinsecondarynavigation = true;
        $node->display = true;

        $secondaryview->add_node($node);
    }
}
