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

defined('MOODLE_INTERNAL') || die();


if ($hassiteconfig) {

    // Create the parent item (your local plugin)
    $ADMIN->add('localplugins', new admin_category(
        'customcleanurl_settings', // Unique identifier for the category.
        get_string('pluginname', 'local_customcleanurl') // Display name for your plugin.
    ));

    // ------------------
    $settings = new admin_settingpage('local_customcleanurl', 'General Setting');

    $name = 'local_customcleanurl/emable_customcleanurl';
    $title = "Enable Customcleanurl";
    $description = get_string('emable_customcleanurl_desc', 'local_customcleanurl');
    if (get_config('local_customcleanurl', 'emable_customcleanurl')) {
        // http://moodle.local/user/profile.php?id=2
        // http://moodle.local/user/profile/admin
        $moodle_default_url = new moodle_url('/user/profile.php', ['id' => $USER->id]);
        $clean_url = new moodle_url('/user/profile/' . $USER->username);
        $moodle_default_clean_url = \local_customcleanurl\local\helper::get_clean_url($moodle_default_url);
        if ($moodle_default_clean_url->raw_out(false) != $clean_url->raw_out(false)) {
            $description .= '<div class="alert alert-danger alert-block fade in  alert-dismissible">
             Clean url cannot be implemented, see the developer and readme file. </div>';
        }
        $check_rewrite_htaccess = \local_customcleanurl\local\htaccess::check_rewrite_htaccess();
        if (!$check_rewrite_htaccess) {
            \local_customcleanurl\local\htaccess::set_htaccess();
        }
        $check_again_rewrite_htaccess = \local_customcleanurl\local\htaccess::check_rewrite_htaccess();
        if (!$check_again_rewrite_htaccess) {
            $description .= '<div class="alert alert-danger alert-block fade in  alert-dismissible"> change the .htaccess accoding to readme file.</div>';
        }
    }
    $setting = new admin_setting_configcheckbox($name, $title, $description, '1');
    $setting->set_updatedcallback('theme_reset_all_caches');
    $settings->add($setting);

    // 
    $ADMIN->add('customcleanurl_settings', $settings);

    // -----------------
    // External link
    $external_link = new moodle_url('/local/customcleanurl/define_custom_url.php');
    $ADMIN->add('customcleanurl_settings', new admin_externalpage(
        'local_define_custom_url', // Unique identifier
        'Define Custom URL', // Link name
        $external_link  // External URL
    ));
}
