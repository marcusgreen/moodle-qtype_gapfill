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
 * Serve question type files
 *
 * @since      3.5
 * @package    qtype_gapfill
 * @copyright  Marcus Green 2019
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir.'/formslib.php';

/**
 * Checks file access for gapfill  questions.
 * @package  qtype_gapfill
 * @category files
 * @param stdClass $course course object
 * @param stdClass $cm course module object
 * @param stdClass $context context object
 * @param string $filearea file area
 * @param array $args extra arguments
 * @param bool $forcedownload whether or not force download
 * @param array $options additional options affecting the file serving
 *
 */
function qtype_gapfill_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    global $CFG;
    require_once($CFG->libdir . '/questionlib.php');
    question_pluginfile($course, $context, 'qtype_gapfill', $filearea, $args, $forcedownload, $options);
}
/**
 *   popup for entering feedback for individual gaps 
 */
class gapfill_feedback_form2 extends moodleform {
    //Add elements to form
    public function definition() {
      
       $mform = $this->_form; 
      $item = json_decode($this->_customdata['item']);
 
        $mform->addElement(
        'editor', 
        'right', 
        get_string('correct','qtype_gapfill'), 
         ['cols' => 50,'rows'=>2],
         ['autosave'=>false])->setValue(['text'=>$item->feedback->correctfeedback]);

         $mform->addElement(
        'editor', 
        'wrong', 
        get_string('incorrect','qtype_gapfill'), 
        ['cols' => 50,'rows'=>2],
        ['autosave'=>false])->setValue(['text'=>$item->feedback->incorrectfeedback]);

        $repeatarray = [];
        $repeatarray[] = $mform->createElement('text','response','Response',['size'=>50]);
        $repeatarray[] = $mform->createElement('editor','feedback','Feedback',['rows'=>2,'cols'=>50]);
        $repeateloptions = [];
        $START_REPETITIONS = 1;
        $this->repeat_elements($repeatarray, $START_REPETITIONS,
            $repeateloptions, 'extended_feedback_repeats', 'add_fields', 1, null, true);
        $this->add_action_buttons();

    }
    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}


function qtype_gapfill_output_fragment_feedbackedit($args) {

    $formdata = [];
     if (!empty($args['jsonformdata'])) {
         $serialiseddata = json_decode($args['jsonformdata']);
         parse_str($serialiseddata, $formdata);
     }
    $mform= new gapfill_feedback_form2(null,$args,'post','',null,true,$formdata);
    
    if($mform->get_data()){
        return;
    }
    return $mform->render();

}