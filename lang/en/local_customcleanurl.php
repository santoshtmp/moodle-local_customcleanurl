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
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

// phpcs:ignoreFile moodle.Files.LangFilesOrdering.IncorrectOrder
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Custom Clean URL';
$string['pluginname_desc'] = 'Local plugin to customize moodle page url';
$string['configtitle'] = 'Custom Clean URL Settings';
$string['privacy:metadata'] = 'local customcleanurl plugin does not store any data itself.';
$string['invalidaccess'] = 'Invalid access.';
$string['featureisnotenable'] = "Custom Clean URL is not enable.";
$string['featureurlredirectisnotenable'] = "URL redirect is not enable.";
$string['invalidsesskey'] = "Invalid sesskey";
$string['add_new_url'] = "Add new url";
$string['list_custom_url'] = "List of custom url";
$string['forbiddenpage'] = "Forbidden Page";
$string['pagenotfound'] = "Page Not Found";
$string['errorpage404'] = "404 error page";
$string['cachedef_clean_url'] = 'Clean URL Cache';
$string['cachedef_unclean_url'] = 'Default moodle URL cache';
$string['invalidcustomparam'] = 'Parameter "{$a->param}" is restricted as this parameter is alrady present in original url "{$a->responsepath}"';
$string['delete_data_heading'] = 'Delete URL';
$string['delete_conform_text'] = 'Are you sure you want to delete "{$a->customcleanurl}"?';

$string['enable_customcleanurl'] = 'Enable Customcleanurl';
$string['pass_customcleanurlroutecheck'] = 'Customcleanurl route integrated successfully.';
$string['fail_customcleanurlroutecheck'] = 'Customcleanurl route integration failed. Check your .htaccess file and update it according to the README or by adding the rules provided below.';
$string['course_url'] = 'Course URL';
$string['user_url'] = 'User URL';
$string['define_custom_url'] = 'Define Custom URL';
$string['cleanurl_options_desc'] = 'Selected options url will only be modified.
This will change the default moodle url to clean url.
<br> Example:
<br>
<ol>
    <li>Course URL
        <ol>
            <li>your_domain/course/view.php?id={ID} => your_domain/course/{course_short_name}</li>
            <li>your_domain/course/edit.php?id={ID} => your_domain/course/edit/{course_short_name}</li>
            <li>your_domain/course/index.php => your_domain/course</li>
            <li>your_domain/course/index.php?categoryid={ID} => your_domain/course/category/{ID}/{category_name}</li>
        </ol>
    </li>
    <li>User URL
        <ol>
            <li>your_domain/user/profile.php?id={ID} => your_domain/user/profile/{username}</li>
        </ol>
    </li>
    <li>Define Custom URL :: you can re-write particular custom moodle url.</li>
</ol>
';

$string['generalsettings'] = 'General settings';
$string['define_urlredirect'] = 'Define URL Redirect';
$string['enable_urlredirect'] = 'Enable url redirect';
$string['enable_urlredirect_desc'] = 'URLs are redirected to the next destination URL.';
$string['enable_urlredirect_descwithlink'] = ' <br> Now you can define url redirect for the existing moodle url at <a href="{$a->url}" target="_blank" class="btn btn-primary">HERE - URL Redirect</a>.';
$string['list_custom_urlredirect'] = 'List of custom url redirect';
$string['add_new_url_redirect'] = 'Add new url redirect';
$string['destination_url'] = 'Destination URL';
$string['clean_url_type'] = 'Custom Clean URL Type';
$string['define_custom_urldesc'] = 'Now you can define custom url for the existing moodle url at <a href="{$a->url}" target="_blank" class="btn btn-primary">HERE - Define Custom URL</a>';
$string['error404_content'] = '404 Page Content';
$string['error404_content_desc'] = 'Content which is shown in 404 error Page.';

$string['sn'] = 'S.N.';
$string['default_url'] = 'Default Moodle URL';
$string['default_url_help'] = 'Default original moodle url. <br> It should be .php url and must start with your_domain or / .<br> Example: your_domain/course/view.php?id=7 <br> <strong>All the moodle url may not support the clean url.</strong>';
$string['custom_url'] = "Custom URL";
$string['redirect_url'] = "Redirect URL";
$string['custom_url_help'] = 'Clean custom url. <br> It should not match moodle default url, which is without .php file and moodle dir url.<br> Example: your_domain/course/math';
$string['error_default_url_adminurl'] = 'Admin url is not accepted.';
$string['error_default_url'] = 'Default Moodle URL "{$a->default_url}" alrady taken.';
$string['error_default_url_alrady_clean'] = 'Default Moodle URL "{$a->default_url}" is alrady clean.';
$string['error_default_url_path'] = 'Provided path "{$a->default_url}" must start with forward slash "/".';
$string['error_default_url_not_originalurl'] = 'Provided URL "{$a->default_url}" is not the default original URL.';
$string['error_custom_url_is_default'] = 'Provided URL "{$a->custom_url}" is the default original moodle URL.';
$string['error_custom_url_path'] = 'Provided path "{$a->custom_url}" must start with forward slash "/".';
$string['error_custom_exist'] = 'Provided URL "{$a->custom_url}" alrady taken.';
$string['error_custom_url_not_originalurl'] = 'Provided URL "{$a->custom_url}" is not the default original URL.';
$string['error_customurlparam'] = 'Custom Clean url cannot have parameter.';
$string['error_url_too_long'] = 'URL is too long.';

$string['edit_custom_url_title'] = 'Edit custom url';
$string['data_saved'] = 'Custom Clean URL data is sucesfully saved.';
$string['data_updated'] = 'Custom Clean URL data is sucesfully updated.';
$string['data_saved_error'] = 'Data error on save.';
$string['something_went_wrong'] = 'Something went wrong.';
$string['data_delete'] = 'Custom Clean URL data successfully deleted.';
$string['data_delete_missing'] = 'Custom Clean URL delete data is missing.';
$string['data_edit_missing'] = 'Custom Clean URL edit data is missing.';

$string['customcleanurl:managecustomcleanurl'] = 'Manage custom clean URLs';
$string['customcleanurl:manageurlredirect'] = 'Manage URL redirects';