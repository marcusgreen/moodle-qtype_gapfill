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
require_once($CFG->libdir.'/formslib.php');
$courseid = optional_param('courseid', '', PARAM_INT);
$category = optional_param('category', '', PARAM_INT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

$PAGE->set_context(context_course::instance($courseid));
$course = get_course($courseid);
$PAGE->set_heading($course->fullname);
$PAGE->set_url(new moodle_url('/question/type/gapfill/import_examples.php'));



$PAGE->set_context(context_course::instance($courseid));
$PAGE->navbar->add(get_string('course'), new moodle_url('/course/view.php', ['id'=>$courseid]));
$PAGE->navbar->add(get_string('questionbank', 'qtype_gapfill'), new moodle_url('/question/edit.php', ['courseid' =>$courseid])) ;


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
        global $DB;
        $mform = $this->_form;

        $courseid = optional_param('courseid', '', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $coursecontext = \context_course::instance($courseid);
        $category = $DB->get_record('question_categories', ['contextid' => $coursecontext->id, 'name'=>'Gapfill sample questions']);
        $mform->addElement('html', '<div id="description">'.get_string('description', 'qtype_gapfill').'</div>');

        if ($category) {
          $mform->addElement('html', '<div id="description">'. get_string('importwarning', 'qtype_gapfill').'</div>');
        }

        $mform->addElement('submit', 'submitbutton', get_string('import'));

    }

    /**
     * Todo add validation if required.
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

    $qformat = new \qformat_xml();
    $filename = 'gapfill_examples.xml';
    $importfile = $CFG->dirroot . '/question/type/gapfill/examples/'.current_language().'/'.$filename;

    $coursecontext = \context_course::instance($fromform->courseid);

    $topcategory = question_get_top_category($coursecontext->id, true);
    $course = $DB->get_record('course', array('id'=>$fromform->courseid), '*', MUST_EXIST);
    $contexts = $DB->get_records('context');

    $qformat->setContexts([$coursecontext]);

    $qformat->setCategory($topcategory);
    $qformat->setCourse($course);
    $qformat->setFilename($importfile);
    $qformat->setRealfilename($importfile);
    $qformat->setMatchgrades('nearest');
    $qformat->setStoponerror(true);
    $qformat->setCatfromfile(true);


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
      //  echo $OUTPUT->header();
        $visitquestions = new moodle_url('/question/edit.php?courseid',['courseid'=>$courseid]);
        echo $OUTPUT->notification(get_string('visitquestions', 'qtype_gapfill', $visitquestions->out()), 'notifysuccess');
        echo $OUTPUT->continue_button(new moodle_url('import_examples.php'));
        echo $OUTPUT->footer();
        return;
    }
}




echo $OUTPUT->header();
$mform->display();
echo $OUTPUT->footer();
