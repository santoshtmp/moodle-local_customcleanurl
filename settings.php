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
 * Admin settings for local_customcleanurl.
 *
 * Registers a category node under "Local plugins" in the admin tree, containing:
 * - A "General settings" page (admin_settingpage) with the plugin's config options.
 * - A "Define Custom URL" external page, shown only when that clean-url type is enabled.
 * - A "Define URL Redirect" external page, shown only when url redirect is enabled.
 *
 * @package    local_customcleanurl
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

use core\output\html_writer;
use local_customcleanurl\local\helper;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');

$componentname = 'local_customcleanurl';

if ($hassiteconfig) {
    $checkrewritehtaccess = '';
    $isenablecustomcleanurl = helper::is_enable_customcleanurl();
    $cleanurloptions = get_config($componentname, 'cleanurl_type');
    $cleanurloptions = $cleanurloptions ? explode(",", $cleanurloptions) : [];
    $customcleanurlroutecheck = false;

    // Current admin tree section/category, used below to show the route-check
    // notice only when the user is actually viewing this plugin's settings.
    $section = optional_param('section', '', PARAM_TEXT);
    $category = optional_param('category', '', PARAM_TEXT);

    // Category node: groups all of this plugin's admin pages together under
    // "Local plugins", instead of a single flat settings page.
    $ADMIN->add('localplugins', new admin_category(
        $componentname,
        get_string('pluginname', $componentname)
    ));

    // Main "General settings" page, added into the category above.
    $settings = new admin_settingpage(
        'local_customcleanurl_settings',
        get_string('generalsettings', $componentname)
    );

    // Enable/disable the whole custom clean url feature.
    $name = $componentname . '/enable_customcleanurl';
    $title = get_string('enable_customcleanurl', $componentname);
    $description = '';
    if ($isenablecustomcleanurl) {
        // Only run the htaccess/route check when this plugin's own admin
        // page (settings page or category overview) is being displayed,
        // to avoid the extra check firing on unrelated admin pages.
        if ($section == 'local_customcleanurl_settings' || $category == $componentname) {
            $customcleanurlroutecheck = helper::customcleanurl_routecheck();
            if ($customcleanurlroutecheck) {
                $description .= html_writer::tag(
                    'div',
                    get_string('pass_customcleanurlroutecheck', $componentname),
                    ["class" => "alert alert-info alert-block fade in  alert-dismissible"]
                );
            } else {
                $description .= html_writer::tag(
                    'div',
                    get_string('fail_customcleanurlroutecheck', $componentname),
                    ["class" => "alert alert-danger alert-block fade in  alert-dismissible"]
                );
                $description .= html_writer::tag(
                    'div',
                    html_writer::tag('pre', \local_customcleanurl\local\htaccess::get_default_htaccess_content()),
                    ["class" => "alert alert-info alert-block fade in  alert-dismissible"]
                );
            }
        }
    }
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $settings->add($setting);

    // Enable/disable the url redirect feature.
    $name = $componentname . '/enable_urlredirect';
    $title = get_string('enable_urlredirect', $componentname);
    $description = get_string('enable_urlredirect_desc', $componentname);
    $enableurlredirect = get_config($componentname, 'enable_urlredirect');
    if ($enableurlredirect) {
        $a = new stdClass();
        $a->url = (new moodle_url('/local/customcleanurl/define_urlredirect.php'))->out(false);
        $description .= get_string('enable_urlredirect_descwithlink', $componentname, $a);
    }
    $setting = new admin_setting_configcheckbox($name, $title, $description, 0);
    $settings->add($setting);

    // Remaining settings only make sense once custom clean url is enabled.
    if ($isenablecustomcleanurl) {
        // Which type(s) of clean url are active: course, user, and/or defined custom url.
        $checkboxoptions  = [
            'courseurl' => get_string('course_url', $componentname),
            'userurl' => get_string('user_url', $componentname),
            'defineurl' => get_string('define_custom_url', $componentname),
        ];
        $defaultvalues = [
            'courseurl' => 0,
            'userurl' => 0,
            'defineurl' => 1,
        ];
        $name = $componentname . '/cleanurl_type';
        $title = get_string('clean_url_type', $componentname);
        $description = get_string('cleanurl_options_desc', $componentname);
        if (in_array('defineurl', $cleanurloptions)) {
            $a = new stdClass();
            $a->url = (new moodle_url('/local/customcleanurl/define_custom_url.php'))->out(false);
            $description .= get_string('define_custom_urldesc', $componentname, $a);
        }
        $setting = new admin_setting_configmulticheckbox($name, $title, $description, $defaultvalues, $checkboxoptions);
        $settings->add($setting);

        // Custom content shown on the plugin's 404 error page.
        $name = $componentname . '/error404_content';
        $title = get_string('error404_content', $componentname);
        $description = get_string('error404_content_desc', $componentname);
        $setting = new admin_setting_confightmleditor($name, $title, $description, '');
        $settings->add($setting);
    }

    // Register the general settings page under the category.
    $ADMIN->add($componentname, $settings);

    if ($category != 'local_customcleanurl') {
        // "Define Custom URL" sub-page — only listed when that clean-url type is enabled.
        if ($isenablecustomcleanurl && in_array('defineurl', $cleanurloptions)) {
            $ADMIN->add(
                $componentname,
                new admin_externalpage(
                    'local_customcleanurl_defineurl',
                    get_string('define_custom_url', $componentname),
                    new moodle_url('/local/customcleanurl/define_custom_url.php')
                )
            );
        }

        // "Define URL Redirect" sub-page — only listed when url redirect is enabled.
        $enableurlredirect = get_config($componentname, 'enable_urlredirect');
        if ($enableurlredirect) {
            $ADMIN->add(
                $componentname,
                new admin_externalpage(
                    'local_customcleanurl_urlredirect',
                    get_string('define_urlredirect', $componentname),
                    new moodle_url('/local/customcleanurl/define_urlredirect.php')
                )
            );
        }
    }

}
