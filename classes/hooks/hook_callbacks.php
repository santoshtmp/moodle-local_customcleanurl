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
 * Hook callbacks for local_customcleanurl.
 *
 * @package    local_customcleanurl
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

namespace local_customcleanurl\hooks;

use core\hook\output\before_http_headers;
use local_customcleanurl\local\helper;

/**
 * Hook callbacks for local_customcleanurl.
 *
 * @package    local_customcleanurl
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Callback for before_http_headers.
     *
     * @param before_http_headers $hook
     */
    public static function before_http_headers(before_http_headers $hook): void {
        global $CFG, $PAGE;

        if (during_initial_install() || isset($CFG->upgraderunning)) {
            // Do nothing during installation or upgrade.
            return;
        }

        if (class_exists(\local_customcleanurl\local\helper::class)) {
            helper::urlrewriteclass_initialize();
        }
        helper::urlredirect_initialize();

        helper::add_define_custom_url_node($PAGE->secondarynav);
    }

    /**
     * Callback for after_config.
     *
     * Ensures the custom URL rewrite class is initialised after config is loaded.
     *
     * @param \core\hook\after_config $hook
     */
    public static function after_config(\core\hook\after_config $hook): void {
        if (class_exists(\local_customcleanurl\local\helper::class)) {
            helper::urlrewriteclass_initialize();
        }
    }

    /**
     * Callback for secondary_extend.
     *
     * Adds the "Define custom URL" node on pages that build secondary
     * navigation through core (course, module, category, site admin, etc.).
     *
     * @param \core\hook\navigation\secondary_extend $hook
     */
    public static function extend_secondary_navigation(\core\hook\navigation\secondary_extend $hook): void {
        helper::add_define_custom_url_node($hook->get_secondaryview());
    }
}
