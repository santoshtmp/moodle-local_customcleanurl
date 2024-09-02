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
 * Language file.
 * 
 * @package    local_customcleanurl
 * @copyright  2024 https://santoshmagar.com.np/
 * @author     santoshtmp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 */


defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Custom Clean URL';
$string['pluginname_desc'] = 'Local plugin to customize moodle page url';
$string['configtitle'] = 'Custom Clean URL Settings';
// 
$string['cachedef_clean_url'] = 'Clean URL Cache';
$string['cachedef_unclean_url'] = 'Default moodle URL cache';
// 
$string['emable_customcleanurl_desc'] = 'This will change the default moodle url to clean url. 
<br> Example: 
<br>    your_domain/course/view.php?id=ID => your_domain/course/course_shot_name
<br>    your_domain/course/index.php?categoryid=ID => your_domain/course/category/ID/category_name
<br>    your_domain/course/edit.php?id=ID => your_domain/course/edit/course_shot_name
<br>    your_domain/user/profile.php?id=ID => your_domain/user/profile/username
<br>    and other as defined.
';
$string['default_url'] = "default url";
$string['default_url_help'] = 'Default original moodle url. <br> It should be .php url and must start with your_domain or / .<br> Example: your_domain/course/view.php?id=7';
$string['custom_url'] = "clean custom url";
$string['custom_url_help'] = 'Clean custom url. <br> It should not match moodle default url, which is without .php file and moodle dir url.<br> Example: your_domain/course/math';

