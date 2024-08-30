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

namespace local_customcleanurl\local;

use moodle_url;

defined('MOODLE_INTERNAL') || die();
class clean_url
{

    /** @var moodle_url */
    private $originalurl;

    /** @var string[] */
    private $params;

    /** @var string */
    private $path;

    /** @var moodle_url */
    public $cleanedurl;

    // clas constructor
    public function __construct(moodle_url $url)
    {
        $this->originalurl = $url;
        $this->path = $this->originalurl->get_path(false);
        $this->params = $this->originalurl->params();
        $this->cleanedurl = null;
        // var_dump($this->url_except_domain); die;
        $this->execute();
    }


    private function execute()
    {
        // check the cache
        $cache_clean_url = \local_customcleanurl\local\helper::check_clean_url_cached($this->originalurl);
        if ($cache_clean_url) {
            $this->cleanedurl = $cache_clean_url;
            return;
        }

        // // The order of the checks below is important.
        // if ($this->check_test_url() || $this->check_cleaner_disabled() || $this->check_cached()) {
        //     return;
        // }
        $this->clean_path();
        $this->create_cleaned_url();
    }


    private function remove_index_php($remove_last_path = '')
    {
        // Remove /index.php from end.
        if (substr($this->path, -10) == '/index.php') {
            $this->path = substr($this->path, 0, -10);
        }
        // remove .php
        if (substr($this->path, -4) == '.php') {
            $this->path = substr($this->path, 0, -4);
        }
        // removed defined path
        if ($remove_last_path) {
            if (substr($this->path, -strlen($remove_last_path)) == $remove_last_path) {
                $this->path = substr($this->path, 0, -strlen($remove_last_path));
            }
        }
    }
    private function create_cleaned_url()
    {
        global $CFG;
        // Add back moodle path.
        $this->path = ltrim($this->path, '/');
        if ($this->path) {
            $this->path = "/" . $this->path;
            $originalpath = $this->originalurl->get_path(false);
            if ($this->path == $originalpath) {
                $this->cleanedurl = $this->originalurl;
                return; // URL was not rewritten. return original url
            }
            // 
            $this->cleanedurl = new moodle_url($this->path);
            // cache
            $cache_clean_url = \cache::make('local_customcleanurl', 'clean_url');
            $cache_clean_url->set($this->originalurl->raw_out(false), $this->cleanedurl->raw_out(false));
            $cache_default_url = \cache::make('local_customcleanurl', 'default_url');
            $cache_default_url->set($this->cleanedurl->raw_out(false), $this->originalurl->raw_out(false));
            return;
        }
    }

    private function clean_path()
    {

        switch ($this->path) {
            case '/user/profile.php':
                // user profile clean url
                $this->clean_users_profile_url();
                return;
        }

        // url path start with /course
        if (preg_match('#^/course#', $this->path, $matches)) {
            $this->clean_course_url();
            return;
        }
        // // course mod activity and resources
        // if (preg_match('#^/mod/(\w+)/view.php$#', $this->path, $matches)) {
        //     // clean_course_module_view($matches[1]);
        //     return;
        // }
    }

    /**
     * Used to convert following urls
     * 
     * /course/view.php => /course/course_short_shortname
     * 
     * /course/edit.php => /course/edit/course_short_shortname
     * 
     * /course/index.php = > /course
     * 
     * /course/index.php?categoryid=ID =>/course/category/category_name-ID
     * 
     */
    private function clean_course_url()
    {
        $allowed_course_path = [
            '/course/view.php',
            '/course/edit.php',
            '/course/index.php'
        ];
        if (!in_array($this->path, $allowed_course_path)) {
            return;
        }

        // params
        $course_id = isset($this->params['id']) ? $this->params['id'] : '';
        $category_id = isset($this->params['categoryid']) ? $this->params['categoryid'] : '';
        // filter paths
        $this->remove_index_php('/view');
        if ($course_id) {
            $course = get_course($course_id);
            if ($course) {
                $clean_newpath = $this->path . '/' . self::url_slug($course->shortname);
                if ($this->check_path_allowed($clean_newpath)) {
                    $this->path = $clean_newpath;
                }
            }
        } else if ($category_id) {
            global $DB;
            $course_categories = $DB->get_record('course_categories', ['id' => $category_id]);
            if ($course_categories) {
                $clean_newpath = $this->path . '/category/' . self::url_slug($course_categories->name) . '-' . $course_categories->id;
                if ($this->check_path_allowed($clean_newpath)) {
                    $this->path = $clean_newpath;
                }
            }
        }

        return false;
    }



    // 
    private function clean_users_profile_url()
    {
        if (empty($this->params['id'])) {
            return null;
        }

        global $DB;
        $user =  $DB->get_record('user', ['id' => $this->params['id']]);
        if ($user) {
            $this->remove_index_php();
            $newpath = $this->path . '/' . urlencode(strtolower($user->username));
            if ($this->check_path_allowed($newpath)) {
                $this->path = $newpath;
            }
        }
        return $user;
    }

    private function check_path_allowed($path)
    {
        global $CFG;

        return (!is_dir($CFG->dirroot . $path) && !is_file($CFG->dirroot . $path . ".php"));
    }
    private function url_slug($name)
    {
        return str_replace(' ', '-', strtolower($name));
    }
}
