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
 */



// Require config.
require_once(dirname(__FILE__) . '/../../config.php');
defined('MOODLE_INTERNAL') || die();

// Get parameter
$id = optional_param('id', 0, PARAM_INT);
$action = optional_param('action', '', PARAM_TEXT);
// Get system context.
$context = \context_system::instance();

// Prepare the page information.
$url = new moodle_url('/local/customcleanurl/define_custom_url.php');
$page_title = 'Define Custom URL';
$PAGE->set_context($context);
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin'); // admin , standard , ...
$PAGE->set_pagetype('define_custom_url');
$PAGE->set_title($page_title);
$PAGE->set_heading($page_title);
// $PAGE->navbar->add($page_title);
$PAGE->set_blocks_editing_capability('moodle/site:manageblocks');
// 
$PAGE->requires->jquery();

// Access checks.
require_login();
if (!has_capability('moodle/site:config', $context)) {
    $contents = "You don't have permission to access this pages";
    $contents .= "<br>";
    $contents .= "<a href='/'> Return Back</a>";
} else {
    /**
     * ========================================================
     *     FORM actions
     * ========================================================
     */
    $define_custom_url_form = new \local_customcleanurl\form\custom_url_form();
    if ($define_custom_url_form->is_cancelled()) {
        redirect($url);
    } else if ($form_data = $define_custom_url_form->get_data()) {
        \local_customcleanurl\form\custom_url_form::data_save($form_data);
    } else {
        if ($action && $id) {
            // verify sesskey
            $sesskey = required_param('sesskey', PARAM_ALPHANUM);
            if ($sesskey != sesskey()) {
                $message = "Your session key is missing or invalid.";
                redirect($url, $message);
            }
            // For Delete
            if ($action == 'delete') {
                \local_customcleanurl\form\custom_url_form::data_delete($id);
            }
            // For Edit
            if ($action == 'edit') {
                \local_customcleanurl\form\custom_url_form::display_edit($define_custom_url_form, $id);
            }
        }
    }
    /**
     * ========================================================
     *     Get the data and display
     * ========================================================
     */
    $contents = '';
    $contents .= '<div> <h3> Add new url<h3></div>';
    $contents .= $define_custom_url_form->render();
    $contents .= '<br>';
    $contents .= '<div> <h3> List of custom url<h3></div>';
    $contents .= \local_customcleanurl\form\custom_url_form::get_custom_url_data_table(50);
}
/**
 * ========================================================
 * -------------------  Output Content  -------------------
 * ========================================================
 */
echo $OUTPUT->header();
echo $contents;
echo $OUTPUT->footer();
