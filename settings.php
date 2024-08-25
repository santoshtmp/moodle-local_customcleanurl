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


if ($hassiteconfig) {
    // // Heading.
    $settings = new admin_settingpage('local_customcleanurl', get_string('pluginname', 'local_customcleanurl'));
    $ADMIN->add('localplugins', $settings);


    // $name = 'local_customcleanurl/page_title';
    // $title = 'TITLE';
    // $description = '';
    // $setting = new admin_setting_heading($name, $title, $description);
    // $settings->add($setting);

    $name = 'local_customcleanurl/course_edit_page';
    $title = 'Course Edit Page';
    $description = 'This will change the default course url to clean url with shortname <br> Example: /course/view.php?id=7  to  /course/course_shortname ';
    $default = 'default';
    $options = [
        'default' => 'Moodle Default',
        'course_shortname_url' => 'Course Shortname URL'
    ];
    $setting = new admin_setting_configselect($name, $title, $description, $default, $options);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);
}
