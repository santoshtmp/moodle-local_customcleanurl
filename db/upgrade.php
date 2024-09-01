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
    global $DB;

    $dbman = $DB->get_manager();

    $new_version = 2024083100;
    if ($oldversion < $new_version) {
        // set updated htaccess
        \local_customcleanurl\local\htaccess::set_htaccess();

        // Define table local_customcleanurl to be created.
        $table = new xmldb_table('local_customcleanurl');

        // Adding fields to table local_customcleanurl.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('default_url', XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, null);
        $table->add_field('custom_url', XMLDB_TYPE_CHAR, '225', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_customcleanurl.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_customcleanurl.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Apply savepoint reached.
        upgrade_plugin_savepoint(true, $new_version, 'local', 'customcleanurl');
    }

    return true;
}
