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

namespace local_customcleanurl\form;

use flexible_table;
use moodle_url;
use stdClass;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

require_once($CFG->libdir . '/formslib.php');

class custom_url_form extends \moodleform
{
    // define form
    public function definition()
    {
        global $CFG;
        $mform = $this->_form;

        $mform->addElement('text', 'default_url', 'Default Moodle URL ', ['size' => 70]);
        $mform->addRule('default_url', 'Default original moodle url', 'required', null, 'client');
        $mform->addHelpButton('default_url', 'default_url', 'local_customcleanurl');
        $mform->setType('default_url', PARAM_TEXT);

        $mform->addElement('text', 'custom_url', 'New Custom URL', ['size' => 70]);
        $mform->addRule('custom_url', 'New custom clean url', 'required', null, 'client');
        $mform->addHelpButton('custom_url', 'custom_url', 'local_customcleanurl');
        $mform->setType('custom_url', PARAM_TEXT);

        $this->add_action_buttons();

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_TEXT);
        $mform->setDefault('action', '');
    }

    // form validation
    function validation($data, $files)
    {
        global $CFG, $DB;
        $db_table = 'local_customcleanurl';

        $errors = parent::validation($data, $files);

        // Add field validation check for duplicate default_url.
        if ($data['default_url']) {
            $mooodle_url = new moodle_url(trim($data['default_url']));
            $moodle_file = $CFG->dirroot . $mooodle_url->get_path(false);
            if (is_file($moodle_file)) {
                if ($existing = $DB->get_record($db_table, array('default_url' => trim($data['default_url'])))) {
                    if (!$data['id'] || $existing->id != $data['id']) {
                        $errors['default_url'] = 'Default Moodle URL "' . trim($data['default_url']) . '" is alrady taken';
                    }
                }
            } else if (is_dir($moodle_file)) {
                // 
                $errors['default_url'] = 'Default Moodle URL "' . trim($data['default_url']) . '" is alrady clean';
            } else if (strpos($mooodle_url->get_path(false), '/') != 0) {
                $errors['default_url'] = 'Provided path "' . trim($data['default_url']) . '" must start with /';
            } else {
                $errors['default_url'] = 'Provided URL "' . trim($data['default_url']) . '" is not the default original URL';
            }
        }

        // Add field validation check for duplicate custom_url.
        if ($data['custom_url']) {
            $clean_url = new moodle_url(trim($data['custom_url']));
            $clean_url_file = $CFG->dirroot . $clean_url->get_path(false);
            // is_dir($clean_url_file)
            if (is_file($clean_url_file)) {
                $errors['custom_url'] = 'Provided URL "' . trim($data['custom_url']) . '" is the default original moodle URL';
            } else if (strpos($clean_url->get_path(false), '/') != 0) {
                $errors['custom_url'] = 'Provided path "' . trim($data['custom_url']) . '" must start with /';
            } else {
                if ($existing = $DB->get_record($db_table, array('custom_url' => trim(rtrim($data['custom_url'], '/'))))) {
                    if (!$data['id'] || $existing->id != $data['id']) {
                        $errors['custom_url'] = 'New Custom Clean URL "' . trim($data['custom_url']) . '" is alrady taken';
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * get custom_url_form post save
     * @param int $per_page
     */
    public static function get_custom_url_data_table(int $per_page = 12)
    {
        global $CFG, $DB;
        $output_data = '';
        $url = new moodle_url('/local/customcleanurl/define_custom_url.php');
        $db_table = 'local_customcleanurl';
        // 
        require_once($CFG->libdir . '/tablelib.php');
        $table = new \flexible_table('moodle-clean-custom-url-data');
        $tablecolumns = ['id', 'default_url', 'custom_url', 'action'];
        $tableheaders = ['S.N', 'Default Moodle URLs', 'Custom URLs', 'Action'];
        $table->define_columns($tablecolumns);
        $table->define_headers($tableheaders);
        $table->define_baseurl($url);
        $table->sortable(true);
        $table->set_attribute('id', 'moodle-clean-custom-url-data');
        $table->set_attribute('class', 'moodle-clean-custom-url');
        $table->set_control_variables(array(
            TABLE_VAR_SORT    => 'ssort',
            TABLE_VAR_IFIRST  => 'sifirst',
            TABLE_VAR_ILAST   => 'silast',
            TABLE_VAR_PAGE    => 'spage'
        ));
        $table->no_sorting('action');
        $table->no_sorting('id');
        $table->setup();
        $table->pagesize($per_page, $DB->count_records($db_table, []));
        $limitfrom = $table->get_page_start();
        $limitnum = $table->get_page_size();
        if (isset($_GET['ssort']) && $table->get_sql_sort()) {
            $sort = $table->get_sql_sort();
        } else {
            $sort = 'id DESC';
        }
        // 
        $data_records = $DB->get_records($db_table, [], $sort, $fields = '*', $limitfrom = $limitfrom, $limitnum = $limitnum);
        ob_start();
        if ($data_records) {
            $i = $limitfrom + 1;
            foreach ($data_records as $record) {
                $edit_link = $url->out() . '?action=edit&id=' . $record->id . '&sesskey=' . sesskey();
                $delete_link = $url->out() . '?action=delete&id=' . $record->id . '&sesskey=' . sesskey();
                $row = array();
                $row[] =  $i; //$record->id;
                $row[] = $record->default_url;
                $row[] = $record->custom_url;
                $row[] = '
                <a href="' . $edit_link . '" class="btn btn-primary">Edit</a> 
                <a href="' . $delete_link . '" class="btn btn-secondary">Delete</a> 
                ';
                $table->add_data($row);
                $i =  $i + 1;
            }
        }
        $table->finish_output();
        $output_data = ob_get_contents();
        ob_end_clean();
        // 
        return $output_data;
    }

    /**
     * save custom_url_form data
     * @param object $data
     */
    public static function data_save($data)
    {
        global $DB, $CFG;
        $url = new moodle_url('/local/customcleanurl/define_custom_url.php');
        // Form was submitted and validated, process the data
        $message = "Error on submit";
        $db_table = 'local_customcleanurl';
        if ($data->id && ($data->action == 'edit')) {
            $data_exists = $DB->record_exists($db_table, ['id' =>  $data->id]);
            if ($data_exists) {
                $data->timemodified = time();
                $updated =  $DB->update_record($db_table, $data);
                if ($updated) {
                    $message = "Data is sucesfully updated.";
                }
            }
        } else {
            $data->timecreated = time();
            $data->timemodified = time();
            $saved = $DB->insert_record($db_table, $data);
            if ($saved) {
                $message = "Data is sucesfully saved.";
            }
        }

        redirect($url, $message);
    }

    /**
     * delete custom_url_form data
     * @param int $id
     */
    public static function data_delete($id)
    {
        global $DB, $USER;
        $url = new moodle_url('/local/customcleanurl/define_custom_url.php');
        $db_table = 'local_customcleanurl';

        $data = $DB->get_record($db_table, ['id' => $id]);
        if ($data) {
            $delete =  $DB->delete_records($db_table, ['id' => $data->id]);
            if ($delete) {
                $message = "DAta successfully deleted.";
            } else {
                $message = "Error on delete";
            }
        } else {
            $message = "Data is missing";
        }

        redirect($url, $message);
    }


    /**
     * edit form data
     */
    public static function display_edit($mform, $id)
    {
        $db_table = 'local_customcleanurl';

        global $DB, $PAGE, $OUTPUT, $CFG;
        $url = new moodle_url('/local/edzsocial/index.php');
        $data = $DB->get_record($db_table, ['id' => $id]);
        if ($data) {
            $PAGE->navbar->add('Edit url');
            $entry = new stdClass();
            $entry->id = $id;
            $entry->default_url = $data->default_url;
            $entry->custom_url = $data->custom_url;
            $entry->action = 'edit';
            // output content
            echo $OUTPUT->header();
            echo '<div> <h3> Edit url <h3></div>';
            $mform->set_data($entry);
            $mform->display();
            echo $OUTPUT->footer();
            die;
        } else {
            $message = "Data is missing";
        }
        redirect($url, $message);
    }
}
