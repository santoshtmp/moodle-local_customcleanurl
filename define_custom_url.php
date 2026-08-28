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
 * Admin page for defining custom clean urls.
 *
 * Lets a site admin map default Moodle urls to custom clean urls: shows an
 * add/edit form plus a paginated, sortable, and filterable list of existing
 * mappings, and handles add/edit/delete actions for local_customcleanurl.
 *
 * @package    local_customcleanurl
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

use core\exception\moodle_exception;
use core\output\html_writer;
use local_customcleanurl\handler\customcleanurl_handler;
use local_customcleanurl\local\helper;

// Bootstrap Moodle - Get require config file.
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
defined('MOODLE_INTERNAL') || die();

// Get request parameters.
$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);
$context = \context_system::instance();

// Only users with the plugin capability may manage custom urls.
require_login(null, false);
if (!has_capability('local/customcleanurl:managecustomcleanurl', $context)) {
    throw new moodle_exception('invalidaccess', 'local_customcleanurl');
}

// This page is only usable when the "define custom url" clean-url type is
// enabled, on top of the overall custom clean url feature being enabled.
$cleanurloptions = get_config('local_customcleanurl', 'cleanurl_type');
$cleanurloptions = explode(",", $cleanurloptions);
if (!(in_array('defineurl', $cleanurloptions) && helper::is_enable_customcleanurl())) {
    throw new moodle_exception('featureisnotenable', 'local_customcleanurl');
}

// Page setup - Prepare the page information.
$pagepath = '/local/customcleanurl/define_custom_url.php';
$pageurl = new moodle_url($pagepath);
$pagetitle = get_string('define_custom_url', 'local_customcleanurl');

$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_pagetype('define_custom_url');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->navbar->add(get_string('pluginname', 'local_customcleanurl'), '/admin/category.php?category=local_customcleanurl');
$PAGE->navbar->add($pagetitle);
$PAGE->set_blocks_editing_capability('moodle/site:manageblocks');
$PAGE->requires->jquery();

// Build the add/edit form (shared for both add and edit via the moodleform's
// internal state; 'type' => 'defineurl' tells it which clean-url type it's for).
$definecustomurlform = new \local_customcleanurl\form\customcleanurl_form(null, ['type' => 'defineurl']);

if ($definecustomurlform->is_cancelled()) {
    // User cancelled the form: go back to a clean listing page.
    $returnurl = optional_param('returnurl', '', PARAM_URL);
    redirect($returnurl ? $returnurl : $pageurl);
} else if ($formdata = $definecustomurlform->get_data()) {
    // Valid submission: persist the mapping (handles both add and edit internally).
    customcleanurl_handler::save_data($formdata, $pageurl, 'defineurl');
} else {
    if ($action) {
        // Any GET action (edit/delete) must carry a valid sesskey.
        $sesskey = required_param('sesskey', PARAM_ALPHANUM);
        if ($sesskey != sesskey()) {
            redirect($pageurl, get_string('invalidsesskey', 'local_customcleanurl'));
        }
        // Delete an existing mapping.
        if ($action == 'delete' && $id) {
            customcleanurl_handler::delete_data($id, $pageurl);
        }
        // Load an existing mapping into the form for editing.
        if ($action == 'edit' && $id) {
            customcleanurl_handler::edit_form($definecustomurlform, $id, $pageurl);
        }
        // Prepare the form for adding a new mapping.
        if ($action == 'edit' && !$id) {
            customcleanurl_handler::add_form($definecustomurlform);
        }
    }
}

// Build the page content: the add/edit form followed by the list of mappings.
$contents = '';
$contents .= html_writer::start_tag('div', ['class' => 'add-custom-url-wrapper mt-4 mb-4']);
$contents .= html_writer::tag('h3', get_string('add_new_url', 'local_customcleanurl'));
$contents .= $definecustomurlform->render();
$contents .= html_writer::end_tag('div');
$contents .= html_writer::start_tag('div', ['class' => 'custom-url-list-wrapper mt-4 mb-4']);
$contents .= html_writer::tag('h3', get_string('list_custom_url', 'local_customcleanurl'));
$contents .= customcleanurl_handler::get_custom_url_data_table($pagepath, 50, 'defineurl');
$contents .= html_writer::end_tag('div');

// Render the page.
echo $OUTPUT->header();
echo $contents;
echo $OUTPUT->footer();
