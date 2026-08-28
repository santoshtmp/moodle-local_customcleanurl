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
 * @copyright  2025 https://santoshmagar.com.np/
 * @author     santoshtmp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

use core\exception\moodle_exception;
use core\output\html_writer;
use local_customcleanurl\handler\customcleanurl_handler;

// Get require config file.
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
defined('MOODLE_INTERNAL') || die();

// Get parameter.
$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);
$context = \context_system::instance();

// Access checks and Capability check.
require_login(null, false);
if (!has_capability('moodle/site:config', $context)) {
    throw new moodle_exception('invalidaccess', 'local_customcleanurl');
}
$enableurlredirect = get_config('local_customcleanurl', 'enable_urlredirect');
if (!$enableurlredirect) {
    throw new moodle_exception('featureurlredirectisnotenable', 'local_customcleanurl');
}

// Prepare the page information.
$pagepath = '/local/customcleanurl/define_urlredirect.php';
$pageurl = new moodle_url($pagepath);
$pagetitle = get_string('define_urlredirect', 'local_customcleanurl');

// Setup page information.
$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_pagetype('define_urlredirect');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->navbar->add(get_string('pluginname', 'local_customcleanurl'), '/admin/category.php?category=local_customcleanurl');
$PAGE->navbar->add($pagetitle);
$PAGE->set_blocks_editing_capability('moodle/site:manageblocks');
$PAGE->requires->jquery();

// FORM actions.
$definecustomurlform = new \local_customcleanurl\form\customcleanurl_form(null, ['type' => 'urlredirect']);
if ($definecustomurlform->is_cancelled()) {
    redirect($pageurl);
} else if ($formdata = $definecustomurlform->get_data()) {
    customcleanurl_handler::save_data($formdata, $pageurl, 'urlredirect');
} else {
    if ($action && $id) {
        // Verify sesskey.
        $sesskey = required_param('sesskey', PARAM_ALPHANUM);
        if ($sesskey != sesskey()) {
            redirect($pageurl, get_string('invalidsesskey', 'local_customcleanurl'));
        }
        // For Delete.
        if ($action == 'delete') {
            customcleanurl_handler::delete_data($id, $pageurl);
        }
        // For Edit.
        if ($action == 'edit') {
            customcleanurl_handler::edit_form($definecustomurlform, $id, $pageurl);
        }
    }
}

// Get the data and display.
$contents = '';
$contents .= html_writer::start_tag('div', ['class' => 'add-custom-url-wrapper mt-4 mb-4']);
$contents .= html_writer::tag('h3', get_string('add_new_url_redirect', 'local_customcleanurl'));
$contents .= $definecustomurlform->render();
$contents .= html_writer::end_tag('div');
$contents .= html_writer::start_tag('div', ['class' => 'custom-url-list-wrapper mt-4 mb-4']);
$contents .= html_writer::tag('h3', get_string('list_custom_urlredirect', 'local_customcleanurl'));
$contents .= customcleanurl_handler::get_custom_url_data_table($pagepath, 50, 'urlredirect');
$contents .= html_writer::end_tag('div');

// Output Content.
echo $OUTPUT->header();
echo $contents;
echo $OUTPUT->footer();
