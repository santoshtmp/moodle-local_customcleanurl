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


require_once(dirname(__FILE__) . '/../../../config.php');

global $OUTPUT, $PAGE;
$url = '/local/customcleanurl/locallib/404.php';
$redirect_status = ($_SERVER['REDIRECT_STATUS'] === '403') ? "403" : http_response_code();
if ($redirect_status === '403') {
    $page_title =    "Forbidden Page";
} else {
    $page_title =    "Page Not Found";
}


// Set PAGE variables.
$context = \context_system::instance();
$PAGE->set_context($context);
$url = new moodle_url($url);
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($page_title);
// $PAGE->set_heading($page_title);
$PAGE->set_pagetype('error-404');

$PAGE->navbar->add($page_title);
$PAGE->requires->jquery();

// Adds a CSS class to the body tag 
$strcssclass = $redirect_status . '-page';
$PAGE->add_body_class($strcssclass);

// output content
// page header
echo $OUTPUT->header();

echo "
404 error page
";

echo $OUTPUT->footer();
