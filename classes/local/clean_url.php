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
 * Clean URL processor class.
 *
 * @package    local_customcleanurl
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace local_customcleanurl\local;

use moodle_url;

/**
 * Class to clean the default moodle url
 * Responsible for rewriting Moodle's default URLs into cleaner, human-friendly URLs.
 *
 * @package    local_customcleanurl
 * @copyright  2025 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class clean_url {
    /** @var moodle_url Original Moodle URL before processing. */
    private $originalurl;

    /** @var array URL parameters from the original URL. */
    private $params;

    /** @var string Path component of the original URL. */
    private $path;

    /** @var moodle_url|null Final cleaned URL (or null if not rewritten). */
    public $cleanedurl;

    /**
     * Constructor.
     *
     * @param moodle_url $url Original Moodle URL to be processed.
     */
    public function __construct(moodle_url $url) {
        $this->originalurl = $url;
        $this->path = $this->originalurl->get_path(false);
        $this->params = $this->originalurl->params();
        $this->cleanedurl = null;
        $this->execute();
    }

    /**
     * Executes the clean URL process if enabled in plugin settings.
     *
     * @return void
     */
    private function execute() {
        if (!in_array($this->originalurl->get_scheme(), ['http', 'https'])) {
            return;
        }
        if (!helper::is_enable_customcleanurl()) {
            return;
        }
        $this->clean_path();
        $this->create_cleaned_url();
    }

    /**
     * Removes unwanted parts from the URL path.
     *
     * - Removes a specific path ending if provided.
     * - Removes `/index.php` if present at the end.
     * - Removes `.php` extension if present.
     *
     * @param string $removelastpath Optional specific suffix to strip.
     * @return string|null Cleaned path string, or null if no changes made.
     */
    private function remove_index_php($removelastpath = '') {
        // ... removed defined path from the end
        if ($removelastpath) {
            if (substr($this->path, -strlen($removelastpath)) == $removelastpath) {
                return substr($this->path, 0, -strlen($removelastpath));
            }
        }

        // ... remove /index.php from end.
        if (substr($this->path, -10) == '/index.php') {
            return substr($this->path, 0, -10);
        }
        // ... remove .php
        if (substr($this->path, -4) == '.php') {
            return substr($this->path, 0, -4);
        }
    }

    /**
     * Generates the final cleaned Moodle URL object.
     *
     * @return void
     */
    private function create_cleaned_url() {
        // Add back moodle path.
        $this->path = ltrim($this->path, '/');
        if ($this->path) {
            $this->path = "/" . $this->path;
            $originalpath = $this->originalurl->get_path(false);
            if ($this->path == $originalpath) {
                $this->cleanedurl = $this->originalurl;
                return; // ... URL was not rewritten. return original url
            }
            $this->cleanedurl = new moodle_url($this->path, $this->params);
            return;
        }
    }


    /**
     * Cleans the URL path depending on the configured clean URL types.
     *
     * - defineurl: Rewrites URLs defined in custom table.
     * - courseurl: Rewrites course-related URLs.
     * - userurl: Rewrites user profile URLs.
     *
     * @return void
     */
    private function clean_path() {
        global $DB, $CFG;
        $cleanurltype = get_config('local_customcleanurl', 'cleanurl_type');
        $cleanurltype = explode(",", $cleanurltype);
        if (!isset($CFG->subdirpath)) {
            $CFG->subdirpath = (new \moodle_url($CFG->wwwroot))->get_path(false);
        }

        // For cleanurl_type = defineurl.
        if (in_array('defineurl', $cleanurltype)) {
            $defaulturl = str_replace($CFG->wwwroot, '', $this->originalurl->raw_out(false));
            $checkcustomurlpath = $DB->get_record(
                'local_customcleanurl',
                [
                    'default_url' => $defaulturl,
                    'cleanurl_type' => 'defineurl',
                ],
            );
            if ($checkcustomurlpath) {
                $this->path = $checkcustomurlpath->custom_url;
                $this->params = [];
            }
        }

        // For cleanurl_type = courseurl.
        if (in_array('courseurl', $cleanurltype)) {
            // Url path start with /course.
            if (preg_match('#^' . $CFG->subdirpath . '/course#', $this->path, $matches)) {
                $this->clean_course_url();
                return;
            }
        }

        // For cleanurl_type = userurl.
        if (in_array('userurl', $cleanurltype)) {
            // Url path start with /user/profile.php or /local/smartprofile/index.php.
            if (preg_match('#^' . $CFG->subdirpath . '(/user/profile\.php|/local/smartprofile/index\.php)#', $this->path, $matches)) {
                $this->clean_users_profile_url();
                return;
            }
        }

        // For SmartLearn Custom Pages (/theme/smartlearn/page.php or view_page.php).
        if (preg_match('#^' . $CFG->subdirpath . '/theme/smartlearn/(view_)?page\.php#', $this->path, $matches)) {
            $this->clean_smartlearn_page_url();
            return;
        }
    }

    /**
     * Cleans course-related URLs into user-friendly format.
     *
     * Examples:
     * - /course/view.php?id={ID} → /course/{shortname}
     * - /course/edit.php?id={ID} → /course/edit/{shortname}
     * - /course/index.php → /course
     * - /course/index.php?categoryid={ID} → /course/category/{ID}/{categoryname}
     *
     * @return bool False if no match, true if rewritten.
     */
    private function clean_course_url() {

        global $DB, $CFG;
        if (!empty($CFG->subdirpath)) {
            if (strpos($this->path, $CFG->subdirpath) === 0) {
                $this->path = substr($this->path, strlen($CFG->subdirpath));
            }
        }

        $coursepath = [
            '/course/view.php',
            '/course/edit.php',
            '/course/index.php',
        ];
        if (!in_array($this->path, $coursepath)) {
            return;
        }

        // ... params
        $courseid = isset($this->params['id']) ? $this->params['id'] : '';
        // ... If home page course return.
        if ($courseid == '1') {
            return;
        }
        $categoryid = isset($this->params['categoryid']) ? $this->params['categoryid'] : '';
        // ... filter paths
        $cleannewpath = $this->remove_index_php('/view.php');
        if ($courseid) {
            $course = $DB->get_record('course', ['id' => $courseid]);
            if ($course) {
                unset($this->params['id']);
                $cleannewpath = $cleannewpath . '/' . urlencode(strtolower($course->shortname));
                if ($this->check_path_allowed($cleannewpath)) {
                    $this->path = $cleannewpath;
                }
            }
        } else if ($categoryid) {
            $coursecategories = $DB->get_record('course_categories', ['id' => $categoryid]);
            if ($coursecategories) {
                unset($this->params['categoryid']);
                $cleannewpath = $cleannewpath . '/category/' . $coursecategories->id .
                    '/' . urlencode(strtolower($coursecategories->name));
                if ($this->check_path_allowed($cleannewpath)) {
                    $this->path = $cleannewpath;
                }
            }
        }

        return false;
    }



    /**
     * Cleans user profile URLs into user-friendly format.
     *
     * Example:
     * - /user/profile.php?id={ID} → /user/profile/{username}
     *
     * @return \stdClass|null User record if found, null otherwise.
     */
    private function clean_users_profile_url() {
        if (empty($this->params['id'])) {
            return null;
        }

        global $DB, $CFG;
        if (!empty($CFG->subdirpath)) {
            if (strpos($this->path, $CFG->subdirpath) === 0) {
                $this->path = substr($this->path, strlen($CFG->subdirpath));
            }
        }

        $user = $DB->get_record('user', ['id' => $this->params['id']]);
        if ($user) {
            unset($this->params['id']);
            $cleannewpath = '/user/profile/' . urlencode(strtolower($user->username));
            if ($this->check_path_allowed($cleannewpath)) {
                $this->path = $cleannewpath;
            }
        }
        return $user;
    }

    /**
     * Cleans SmartLearn Custom Page URLs into user-friendly format (e.g., /about-us).
     *
     * @return void
     */
    private function clean_smartlearn_page_url() {
        global $DB, $CFG;
        $slug = $this->params['slug'] ?? '';
        $id = $this->params['id'] ?? 0;

        if ($slug) {
            unset($this->params['slug']);
            $cleannewpath = '/' . urlencode(strtolower($slug));
            if ($this->check_path_allowed($cleannewpath)) {
                $this->path = $cleannewpath;
            }
        } else if ($id > 0) {
            try {
                $page = $DB->get_record('theme_smartlearn_pages', ['id' => (int)$id]);
                if ($page && !empty($page->slug)) {
                    unset($this->params['id']);
                    $cleannewpath = '/' . urlencode(strtolower($page->slug));
                    if ($this->check_path_allowed($cleannewpath)) {
                        $this->path = $cleannewpath;
                    }
                }
            } catch (\Throwable $e) {
                // Table might not exist or other error.
            }
        }
    }

    /**
     * Checks if the final cleaned path does not conflict with actual Moodle files/dirs.
     *
     * @param string $path Path to validate.
     * @return bool True if allowed, false if conflicts with a real file/dir.
     */
    private function check_path_allowed($path) {
        global $CFG;

        return (!is_dir($CFG->dirroot . $path) && !is_file($CFG->dirroot . $path . ".php"));
    }
}
