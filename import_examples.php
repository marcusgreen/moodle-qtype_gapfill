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
 * @package    qtype_gapfill
 * @copyright  2015 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/xmlize.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/format/xml/format.php');


admin_externalpage_setup('qtype_gapfill_import');

class gapfill_import_form extends moodleform {

    protected function definition() {
        $mform = $this->_form;
        $mform->addElement('text', 'course', 'Course');
        $mform->setType('course', PARAM_RAW);
        $mform->addHelpButton('course', 'course', 'qtype_gapfill');

        $mform->addElement('submit', 'submitbutton', get_string('import', 'qtype_gapfill'));
    }

    public function validation($fromform, $data) {
        $errors = array();
        $sql = 'select id from {course} where shortname =?';
        global $DB;
        $courseid = $DB->get_records_sql($sql, array($fromform['course']));
        if (count($courseid) == 0) {
            $errors['course'] = get_string('coursenotfound', 'qtype_gapfill');
        }
        if ($errors) {
            return $errors;
        } else {
            return true;
        }
    }

}

$mform = new gapfill_import_form(new moodle_url('/question/type/gapfill/import_examples.php/'));
if ($fromform = $mform->get_data()) {
    $sql = 'Select qcat.id as qcatid, c.id,c.shortname,ctx.id as contextid from {course} as c 
        join {context} as ctx on ctx.instanceid=c.id
        join {question_categories} qcat on qcat.contextid=ctx.id
        and ctx.contextlevel=50 and c.shortname =?';

    $category = $DB->get_records_sql($sql, array($fromform->course));

    $questioncat = array_shift($category);

    if ($questioncat == NULL) {
        print_error(get_string('questioncatnotfound', 'qtype_gapfill') . $fromform->course, '', $PAGE->url);
    }

    $qformat = new qformat_xml();
    $categorycontext = context::instance_by_id($questioncat->contextid);
    $questioncat->context = $categorycontext;
    $file = $CFG->dirroot . '/question/type/gapfill/sample_questions.xml';
    $qformat->setFilename($file);
    $qformat->setCategory($questioncat);
    echo $OUTPUT->header();
    // Do anything before that we need to
    if (!$qformat->importpreprocess()) {
        print_error(get_string('cannotimport', 'qtype_gapfill'), '', $thispageurl->out);
    }

    // Process the uploaded file
    if (!$qformat->importprocess($category)) {
        print_error(get_string('cannotimport', 'qtype_gapfill'), '', $PAGE->url);
    } else {
        echo $OUTPUT->continue_button(new moodle_url('import_examples.php'));
        echo $OUTPUT->footer();
        return;
    }
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
