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

namespace local_customcleanurl\form;

use moodle_url;
use stdClass;

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
/**
 * define custom url form
 *
 * @package    local_customcleanurl
 * @copyright  2025 santoshtmp <https://santoshmagar.com.np/>
 * @author     santoshtmp
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class customcleanurl_form extends \moodleform {
    /**
     * Define form.
     */
    public function definition() {
        $mform = $this->_form;
        // Get custom data.
        $type = $this->_customdata['type'] ?? 'defineurl';

        // Type defineurl.
        if ($type == 'defineurl') {
            // Moodle default url.
            $mform->addElement('text', 'default_url', get_string('default_url', 'local_customcleanurl'), ['size' => 70]);
            $mform->addRule('default_url', null, 'required', null, 'client');
            $mform->addHelpButton('default_url', 'default_url', 'local_customcleanurl');
            $mform->setType('default_url', PARAM_TEXT);

            // Custom url.
            $mform->addElement('text', 'custom_url', get_string('custom_url', 'local_customcleanurl'), ['size' => 70]);
            $mform->addRule('custom_url', null, 'required', null, 'client');
            $mform->addHelpButton('custom_url', 'custom_url', 'local_customcleanurl');
            $mform->setType('custom_url', PARAM_TEXT);
        }

        // Type urlredirect.
        if ($type == 'urlredirect') {
            // Moodle url.
            $mform->addElement('text', 'default_url', get_string('default_url', 'local_customcleanurl'), ['size' => 70]);
            $mform->addRule('default_url', null, 'required', null, 'client');
            $mform->setType('default_url', PARAM_TEXT);

            // Redirect url.
            $mform->addElement('text', 'custom_url', get_string('destination_url', 'local_customcleanurl'), ['size' => 70]);
            $mform->addRule('custom_url', null, 'required', null, 'client');
            $mform->setType('custom_url', PARAM_TEXT);
        }
        // Action btn.
        $this->add_action_buttons();

        // Hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_TEXT);
        $mform->setDefault('action', '');

        $mform->addElement('hidden', 'type');
        $mform->setType('type', PARAM_TEXT);
        $mform->setDefault('type', $type);

        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_URL);
        $mform->setDefault('returnurl', '');
    }

    /**
     * Custom validation for the form.
     *
     * @param array $data Submitted form data.
     * @param array $files Uploaded files (not used here).
     * @return array Array of errors, empty if no errors.
     */
    public function validation($data, $files) {
        global $CFG, $DB;
        $dbtable = 'local_customcleanurl';

        $errors = parent::validation($data, $files);
        $a = new stdClass();
        $a->default_url = trim($data['default_url']);
        $a->custom_url = trim($data['custom_url']);

        // ... validation for type defineurl
        if ($data['type'] == 'defineurl') {
            if (!isset($CFG->subdirpath)) {
                $CFG->subdirpath = (new \moodle_url($CFG->wwwroot))->get_path(false);
            }

            // Add field validation check for duplicate default_url.
            if ($data['default_url']) {
                $mooodleurl = new moodle_url(trim($data['default_url']));
                $filepath = $mooodleurl->get_path(false);
                if (!empty($CFG->subdirpath)) {
                    if (strpos($filepath, $CFG->subdirpath) === 0) {
                        $filepath = substr($filepath, strlen($CFG->subdirpath));
                    }
                }
                if (str_starts_with($filepath, '/admin')) {
                    $errors['default_url'] = get_string('error_default_url_adminurl', 'local_customcleanurl');
                }
                $moodlefile = $CFG->dirroot .  $filepath;
                if (strpos($mooodleurl->get_path(false), '/') != 0) {
                    $errors['default_url'] = get_string('error_default_url_path', 'local_customcleanurl', $a);
                } else if ($mooodleurl->get_host() != (new moodle_url($CFG->wwwroot))->get_host()) {
                    $errors['default_url'] = get_string('error_default_url_not_originalurl', 'local_customcleanurl', $a);
                } else if (is_file($moodlefile)) {
                    $defaulturl = str_replace($CFG->wwwroot, '', trim($data['default_url']));
                    $existing = $DB->get_record($dbtable, [
                        'default_url' => $defaulturl,
                        'cleanurl_type' => 'defineurl',
                    ]);
                    if ($existing) {
                        if (!$data['id'] || $existing->id != $data['id']) {
                            $errors['default_url'] = get_string('error_default_url', 'local_customcleanurl', $a);
                        }
                    }
                } else if (is_dir($moodlefile)) {
                    $errors['default_url'] = get_string('error_default_url_alrady_clean', 'local_customcleanurl', $a);
                } else {
                    $errors['default_url'] = get_string('error_default_url_not_originalurl', 'local_customcleanurl', $a);
                }
            }

            // Add field validation check for duplicate custom_url.
            if ($data['custom_url']) {
                $cleanurl = new moodle_url(trim($data['custom_url']));
                $filepath = $cleanurl->get_path(false);
                if ($cleanurl->params()) {
                    $errors['custom_url'] = get_string('error_customurlparam', 'local_customcleanurl', $a);
                }
                if (!empty($CFG->subdirpath)) {
                    if (strpos($filepath, $CFG->subdirpath) === 0) {
                        $filepath = substr($filepath, strlen($CFG->subdirpath));
                    }
                }
                $cleanurlfile = $CFG->dirroot . $filepath;
                if (strpos($cleanurl->get_path(false), '/') != 0) {
                    $errors['custom_url'] = get_string('error_custom_url_path', 'local_customcleanurl', $a);
                } else if ($cleanurl->get_host() != (new moodle_url($CFG->wwwroot))->get_host()) {
                    $errors['custom_url'] = get_string('error_custom_url_not_originalurl', 'local_customcleanurl', $a);
                } else if (is_file($cleanurlfile)) {
                    $errors['custom_url'] = get_string('error_custom_url_is_default', 'local_customcleanurl', $a);
                } else if (is_dir($cleanurlfile)) {
                    $errors['custom_url'] = get_string('error_default_url_alrady_clean', 'local_customcleanurl', $a);
                } else {
                    $customurl = str_replace($CFG->wwwroot, '', trim($data['custom_url']));
                    if (strlen($customurl) >= 225) {
                        $errors['custom_url'] = get_string('error_url_too_long', 'local_customcleanurl');
                    } else {
                        $existing = $DB->get_record($dbtable, [
                            'custom_url' => $customurl,
                            'cleanurl_type' => 'defineurl',
                        ]);
                        if ($existing) {
                            if (!$data['id'] || $existing->id != $data['id']) {
                                $errors['custom_url'] = get_string('error_custom_exist', 'local_customcleanurl', $a);
                            }
                        }
                    }
                }
            }
        }

        // ... validation for type urlredirect
        if ($data['type'] == 'urlredirect') {
            if ($data['default_url']) {
                $mooodleurl = new moodle_url(trim($data['default_url']));
                if (strpos($mooodleurl->get_path(false), '/') != 0) {
                    $errors['default_url'] = get_string('error_default_url_path', 'local_customcleanurl', $a);
                } else if ($mooodleurl->get_host() != (new moodle_url($CFG->wwwroot))->get_host()) {
                    $errors['default_url'] = get_string('error_default_url_not_originalurl', 'local_customcleanurl', $a);
                } else {
                    $defaulturl = str_replace($CFG->wwwroot, '', trim($data['default_url']));
                    $existing = $DB->get_record($dbtable, [
                        'default_url' => $defaulturl,
                        'cleanurl_type' => 'urlredirect',
                    ]);
                    if ($existing) {
                        if (!$data['id'] || $existing->id != $data['id']) {
                            $errors['default_url'] = get_string('error_default_url', 'local_customcleanurl', $a);
                        }
                    }
                }
            }

            if ($data['custom_url']) {
                $data['custom_url'] = rtrim($data['custom_url'], '/');
                $cleanurl = new moodle_url(trim($data['custom_url']));
                if (strpos($cleanurl->get_path(false), '/') != 0) {
                    $errors['custom_url'] = get_string('error_custom_url_path', 'local_customcleanurl', $a);
                }
            }
        }

        return $errors;
    }
}
