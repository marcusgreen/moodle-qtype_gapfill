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


admin_externalpage_setup('qtype_gapfill_import');

/**
 *  This does the same as the standard xml import but easier
 *
 * @copyright Marcus Green 2020
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * Form for importing example questions
 */
class gapfill_import_form extends moodleform {
    /**
     *
     * @var number
     */
    public $questioncategory;
    /**
     *
     * @var number
     */
    public $course;
    /**
     * mini form for entering the import details
     */
    protected function definition() {
        global $PAGE;
        $mform = $this->_form;


        $origin = optional_param('origin', '', PARAM_TEXT);
        $courseid = optional_param('courseid', '', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);
        if ($origin !=='editform') {
          $mform->addElement('text', 'courseshortname', get_string('course'));
          $mform->setType('courseshortname', PARAM_RAW);
        }
        $mform->addElement('html', '<div id="description">'.get_string('description', 'qtype_gapfill').'</div>');
        $mform->addElement('submit', 'submitbutton', get_string('import'));
    }

    /**
     * Check that the course exists and that it has a top level question category
     * If if does not have the category prompt the user to visit the course which
     * will create the category. TODO improve this bit.
     *
     * @param array $fromform
     * @param array $data
     * @return boolean
     */
    public function validation($fromform, $data) {
        $errors = [];
        if ($errors) {
            return $errors;
        } else {
            return true;
        }
    }

}
$mform = new gapfill_import_form(new moodle_url('/question/type/gapfill/import_examples.php/'));
if ($fromform = $mform->get_data()) {
    $context= context_course::instance($fromform->courseid);
    //$topcategory = question_get_top_category($context->id, true);

    $qformat = new \qformat_xml();
    $filename = 'gapfill_examples.xml';
    $importfile = $CFG->dirroot . '/question/type/gapfill/examples/'.current_language().'/'.$filename;

    $coursecontext = \context_course::instance($fromform->courseid);
    $category = $DB->get_record('question_categories', ['contextid' => $coursecontext->id]);
    $course = $DB->get_record('course', array('id'=>$fromform->courseid), '*', MUST_EXIST);
    $contexts = $DB->get_records('context');

    $qformat->setContexts([$coursecontext]);

    $qformat->setCategory($category);
    $qformat->setCourse($course);
    $qformat->setFilename($importfile);
    $qformat->setRealfilename($importfile);
    $qformat->setMatchgrades('nearest');
    $qformat->setStoponerror(true);
    $qformat->setCatfromfile(true);

    // $contexts = $DB->get_records('context');
    // $realfilename = $filename;
    // $qformat->setContexts($contexts);
    // global $DB;
    // $course= $DB->get_record('course', ['id'=>$fromform->courseid]);
    // $qformat->setCourse($course);
    // $qformat->setFilename($importfile);
    // $qformat->setRealfilename($realfilename);
    // $qformat->setMatchgrades('error');
    // $qformat->setCatfromfile(1);
    // $qformat->setContextfromfile(1);
    // $qformat->setStoponerror(1);
    // $qformat->setCattofile(1);
    // $qformat->setContexttofile(1);
    // $qformat->set_display_progress(true);



    echo $OUTPUT->header();
    // Do anything before that we need to.
    if (!$qformat->importpreprocess()) {
        print_error('cannotimport', 'qtype_gapfill', $PAGE->out);
    }
    // Process the uploaded file.
    if (!$qformat->importprocess()) {
        print_error(get_string('cannotimport', ''), '', $PAGE->url);
    } else {
        /* after the import offer a link to go to the course and view the questions */
        $visitquestions = new moodle_url('/question/edit.php?courseid=' . $fromform->courseid);
        echo $OUTPUT->notification(get_string('visitquestions', 'qtype_gapfill', $visitquestions->out()), 'notifysuccess');
        echo $OUTPUT->continue_button(new moodle_url('import_examples.php'));
        echo $OUTPUT->footer();
        return;
    }
}

echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
