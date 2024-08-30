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

defined('MOODLE_INTERNAL') || die();

function xmldb_local_customcleanurl_upgrade($oldversion)
{
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    $new_version = 2024071605;
    if ($oldversion < $new_version) {
        \local_customcleanurl\local\htaccess::set_htaccess();
         // Apply savepoint reached.
         upgrade_plugin_savepoint(true, $new_version, 'local', 'customcleanurl');
    }

    return true;
}