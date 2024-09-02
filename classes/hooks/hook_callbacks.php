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

namespace local_customcleanurl\hooks;

defined('MOODLE_INTERNAL') || die();

use core\hook\output\before_http_headers;
use local_customcleanurl\local\helper;
use moodle_url;

/**
 * Hook callbacks for local_customcleanurl
 *
 * @package    local_customcleanurl
 * @copyright  2024 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks
{


    /**
     * Callback allowing to before_http_headers
     *
     * @param \core\hook\output\before_http_headers $hook
     */
    public static function before_http_headers(before_http_headers $hook): void
    {
        global $CFG;
        if (during_initial_install() || isset($CFG->upgraderunning)) {
            // Do nothing during installation or upgrade.
            return;
        }
        \local_customcleanurl\local\helper::urlrewriteclass_initialize();
    }


    /**
     * Callback allowing to after_course_updated
     *
     * @param \core_course\hook\after_course_updated $hook
     */
    public static function after_course_updated(\core_course\hook\after_course_updated $hook): void
    {
        global $CFG;
        if (during_initial_install() || isset($CFG->upgraderunning)) {
            // Do nothing during installation or upgrade.
            return;
        }
        if ($hook->course->shortname == $hook->oldcourse->shortname) {
            // Do nothing if old and new short name is same
            return;
        }
        // required param variables
        $course_id = $hook->course->id;
        $course_shortname = $hook->course->shortname;
        $old_course_shortname = $hook->oldcourse->shortname;
        $cache_clean_url = \cache::make('local_customcleanurl', 'clean_url');
        $cache_default_url = \cache::make('local_customcleanurl', 'default_url');
        // default original moodle url
        $course_url = new moodle_url('/course/view.php', array('id' => $course_id));
        $course_edit_url = new moodle_url('/course/edit.php', array('id' => $course_id));
        // clean url
        $clean_course_url = new moodle_url('/course/' . helper::url_slug($course_shortname));
        $clean_course_edit_url = new moodle_url('/course/edit/' . helper::url_slug($course_shortname));
        // old clean url
        $old_clean_course_url = new moodle_url('/course/' . helper::url_slug($old_course_shortname));
        $old_clean_course_edit_url = new moodle_url('/course/edit/' . helper::url_slug($old_course_shortname));
        // delete old default moodle url from clean_url cache
        $cache_clean_url->delete($course_url->raw_out(false));
        $cache_clean_url->delete($course_edit_url->raw_out(false));
        // delete old clean url from default_url cache
        $cache_default_url->delete($old_clean_course_url->raw_out(false));
        $cache_default_url->delete($old_clean_course_edit_url->raw_out(false));
        // set course_edit_url
        \local_customcleanurl\local\helper::set_url_cache($course_url, $clean_course_url);
        \local_customcleanurl\local\helper::set_url_cache($course_edit_url, $clean_course_edit_url);
    }
}
