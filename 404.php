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

use local_customcleanurl\local\helper;

// phpcs:ignore moodle.Files.RequireLogin.Missing -- This endpoint is intentionally public.
// Get require config file.
require_once(dirname(__FILE__) . '/../../config.php');

// Prepare the page information.
global $OUTPUT, $PAGE;
$pagepath = '/local/customcleanurl/404.php';
$redirectstatus = (isset($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS'] === '403') ? "403" : http_response_code();
if ($redirectstatus === '403') {
    $pagetitle = get_string('forbiddenpage', 'local_customcleanurl');
} else {
    $pagetitle = get_string('pagenotfound', 'local_customcleanurl');
}
$context = \context_system::instance();
$pageurl = new moodle_url($pagepath);
$strcssclass = $redirectstatus . '-page';

// ... setup page information.
$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($pagetitle);
$PAGE->set_heading($pagetitle);
$PAGE->set_pagetype('error-404');
$PAGE->navbar->add($pagetitle);
$PAGE->requires->jquery();
$PAGE->add_body_class($strcssclass);

// ... template context
$context = [];
$isenablecustomcleanurl = helper::is_enable_customcleanurl();
if ($isenablecustomcleanurl) {
    $context['error404content'] = get_config('local_customcleanurl', 'error404_content');
}
// ... output content
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_customcleanurl/404', $context);
echo $OUTPUT->footer();
