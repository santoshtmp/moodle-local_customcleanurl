<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade local_customcleanurl.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_local_customcleanurl_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026092803) {
        $table = new xmldb_table('local_customcleanurl');
        $defaulturlkey = new xmldb_key('default_url_unique', XMLDB_KEY_UNIQUE, ['default_url']);
        $customurlkey = new xmldb_key('custom_url_unique', XMLDB_KEY_UNIQUE, ['custom_url']);

        if (!$dbman->find_key_name($table, $defaulturlkey)) {
            $dbman->add_key($table, $defaulturlkey);
        }
        if (!$dbman->find_key_name($table, $customurlkey)) {
            $dbman->add_key($table, $customurlkey);
        }

        upgrade_plugin_savepoint(true, 2026092803, 'local', 'customcleanurl');
    }

    return true;
}