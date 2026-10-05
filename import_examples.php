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
 * Import sample Gapfill questions from xml file.
 *
 * This does the same as the standard xml import but easier
 * @package    qtype_gapfill
 * @copyright  2015 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/xmlize.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/format/xml/format.php');

use core_question\local\bank\question_bank_helper;

admin_externalpage_setup('qtype_gapfill_import');

/**
 *  This does the same as the standard xml import but easier
 *
 * @copyright Marcus Green 2017
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Form for importing example questions
 * @package qtype_gapfill
 */
class gapfill_import_form extends moodleform {
    /**
     *
     * @var stdClass
     */
    public $course;
    /**
     * mini form for entering the import details
     */
    protected function definition() {
        $mform = $this->_form;
        $mform->addElement('course', 'courseid', get_string('course'));
        $mform->addRule('courseid', null, 'required', null, 'client');
        $mform->addElement('submit', 'submitbutton', get_string('import'));
    }

    /**
     * Get the default question category of the course to import into,
     * creating it (and on Moodle 5.0+ the course question bank) if needed.
     *
     * @param stdClass $course
     * @return stdClass question category with ->context set
     */
    public function get_question_category(stdClass $course): stdClass {
        if (method_exists(question_bank_helper::class, 'get_default_open_instance_system_type')) {
            // Moodle 5.0+: questions live in the course's mod_qbank system bank.
            $cm = question_bank_helper::get_default_open_instance_system_type($course, true);
            $context = context_module::instance($cm->id);
            $category = question_get_default_category($context->id, true);
        } else {
            // Moodle 4.5: questions live in the course context.
            $context = context_course::instance($course->id);
            $category = question_make_default_categories([$context]);
        }
        $category->context = $context;
        return $category;
    }

    /**
     * Check that the course exists.
     *
     * @param array $fromform
     * @param array $data
     * @return array
     */
    public function validation($fromform, $data) {
        global $DB;
        $errors = [];
        $this->course = $DB->get_record('course', ['id' => $fromform['courseid']]);
        if (!$this->course) {
            $errors['courseid'] = get_string('coursenotfound', 'qtype_gapfill');
        }
        return $errors;
    }
}

$mform = new gapfill_import_form(new moodle_url('/question/type/gapfill/import_examples.php/'));
if ($fromform = $mform->get_data()) {
    $category = $mform->get_question_category($mform->course);

    $qformat = new qformat_xml();
    $file = $CFG->dirroot . '/question/type/gapfill/examples/' . current_language() . '/gapfill_examples.xml';
    $qformat->setFilename($file);

    $qformat->setCategory($category);
    echo $OUTPUT->header();
    // Do anything before that we need to.
    if (!$qformat->importpreprocess()) {
        throw new \moodle_exception(get_string('cannotimport', ''), '', $PAGE->url);
    }
    // Process the uploaded file.
    if (!$qformat->importprocess($category)) {
        throw new \moodle_exception(get_string('cannotimport', ''), '', $PAGE->url);
    } else {
        /* after the import offer a link to go to the course and view the questions */
        if ($category->context->contextlevel == CONTEXT_MODULE) {
            $visitquestions = new moodle_url('/question/edit.php', ['cmid' => $category->context->instanceid]);
        } else {
            $visitquestions = new moodle_url('/question/edit.php', ['courseid' => $mform->course->id]);
        }
        echo $OUTPUT->notification(get_string('visitquestions', 'qtype_gapfill', $visitquestions->out()), 'notifysuccess');
        echo $OUTPUT->continue_button(new moodle_url('import_examples.php'));
        echo $OUTPUT->footer();
        return;
    }
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
