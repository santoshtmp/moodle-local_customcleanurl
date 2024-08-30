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
 *  
 */

namespace local_customcleanurl;

use cache;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for local_customcleanurl.
 */
class observer
{

    /**
     * hook course_updated event
     * @param \core\event\course_course_updated $event
     */
    public static function course_updated(\core\event\course_updated $event)
    {
        $courseid = $event->get_data()['objectid'];
        $url = new moodle_url('/course/view.php', array('id' => $courseid));
        $cache = cache::make('local_cleanurls', 'outgoing');
        // $cache->delete($url->raw_out(false));
        $cleanedurl = \local_customcleanurl\local\clean_url::clean($url);
        $cache->set($url->raw_out(false), $cleanedurl);
    }
}
