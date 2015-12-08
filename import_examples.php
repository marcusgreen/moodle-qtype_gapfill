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

    public $course = null;
    public $questioncategory = null;
    public $doimport = false;

    protected function definition() {
        $mform = $this->_form;
        $mform->addElement('text', 'courseshortname', 'Course Shortname');
        $mform->setType('courseshortname', PARAM_RAW);
        $mform->addHelpButton('courseshortname', 'courseshortname', 'qtype_gapfill');
        $mform->addElement('submit', 'submitbutton', get_string('import', 'qtype_gapfill'));
    }

    public function get_data() {
        $fromform= parent::get_submitted_data();
        if($fromform){
        global $DB;
        $sql = 'Select qcat.id qcatid, c.id,c.shortname,ctx.id as contextid from {course} c
        join {context} ctx on ctx.instanceid=c.id
        join {question_categories} qcat on qcat.contextid=ctx.id
        and ctx.contextlevel=50 and c.shortname =?';
            $category = $DB->get_records_sql($sql, array($fromform->courseshortname));
            $this->questioncategory = array_shift($category);
            $sql = 'select id from {course} where shortname =?';
            $this->course = $DB->get_records_sql($sql, array($fromform->courseshortname));
        }
        parent::get_data();
    }

    public function validation($fromform, $data) {
        $errors = array();
        if (count($this->course) == 0) {
            $errors['courseshortname'] = get_string('coursenotfound', 'qtype_gapfill');
        } else {
            if (count($this->questioncategory) == 0) {
                $course = array_shift($this->course);
                $url = new moodle_url('/question/edit.php?courseid=' . $course->id);
                $erstring = 'Question category not found, click <a href=' . $url . '>here</a> to initialise';
                $errors['courseshortname'] = $erstring;
            }
        }

        if ($errors) {
            return $errors;
        } else {
            $this->doimport = true;
            return true;
        }
    }

}

$mform = new gapfill_import_form(new moodle_url('/question/type/gapfill/import_examples.php/'));
$mform->get_data();
if ($mform->doimport === true) {
    $qformat = new qformat_xml();
    $categorycontext = context::instance_by_id($questioncat->contextid);
    $questioncat->context = $categorycontext;
    $file = $CFG->dirroot . '/question/type/gapfill/sample_questions.xml';
    $qformat->setFilename($file);
    $qformat->setCategory($questioncat);
    echo $OUTPUT->header();
    // Do anything before that we need to.
    if (!$qformat->importpreprocess()) {
        print_error(get_string('cannotimport', 'qtype_gapfill'), '', $thispageurl->out);
    }

    // Process the uploaded file.
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
