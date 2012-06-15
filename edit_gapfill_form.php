<?php
/**
 * The editing form code for this question type.
 * @package    qtype
 * @subpackage gapfill
 * @copyright  2012 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/question/type/edit_question_form.php');

/**
 * gapfill editing form definition.
 * 
 * See http://docs.moodle.org/en/Development:lib/formslib.php for information
 * about the Moodle forms library, which is based on the HTML Quickform PEAR library.
 */
class qtype_gapfill_edit_form extends question_edit_form {

    public $answer;
    public $showanswers;
    public $delimitchars;
    

    function definition_inner($mform) {
        $mform->addElement('hidden', 'reload', 1);
        $mform->removeelement('generalfeedback');
        
        //default mark will be set to 1 * number of fields  
         $mform->removeelement('defaultmark');
        
        //the delimiting characters around fields
        $delimitchars= array("[]"=>"[ ]","{}"=>"{ }","##"=>"##","@@"=>"@ @");
        $mform->addElement('select', 'delimitchars','Delimit Characters', $delimitchars);
        $mform->addHelpButton('delimitchars', 'delimitchars', 'qtype_gapfill');

        //$mform->addElement('advcheckbox','delim',"Show Answers");
        $mform->addElement('advcheckbox','showanswers',"Show Answers");
        $mform->addHelpButton('showanswers', 'showanswers', 'qtype_gapfill');
        
         $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'),
                array('rows' => 10), $this->editoroptions);
        
        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');

        
        
       
        //to add combined feedback (correct, partial and incorrect)
        //$this->add_combined_feedback_fields(true);

        //adds hinting features
        $this->add_interactive_settings();
//         $mform->addElement('select', 'penalty',
//                get_string('penaltyforeachincorrecttry', 'question'), $penaltyoptions);
      
        
    }

    function set_data($question) {
          $question->answer = $this->answer;
        $question->showanswers=$this->showanswers;
        $question->delimitchars=$this->delimitchars;
        parent::set_data($question);
    }

    protected function data_preprocessing($question) {
                $question = $this->data_preprocessing_hints($question);

        $question = parent::data_preprocessing($question);
        if (!empty($question->options)) {
            $question->showanswers = $question->options->showanswers;
        }
        return $question;
    }

    function definition_after_data() {
        parent::definition_after_data();
        global $CFG, $COURSE;
        
        
    }

    function validation($fromform, $data) {
        $errors = array();
        if ($errors) {
            return $errors;
        } else {
            return true;
        }
    }

    function qtype() {
        return 'gapfill';
    }

}
?>