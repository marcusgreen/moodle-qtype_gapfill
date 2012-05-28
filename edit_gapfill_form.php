<?php
/**
 * The editing form code for this question type.
 *
 * @copyright &copy; 2006 YOURNAME
 * @author YOUREMAILADDRESS
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package YOURPACKAGENAME
 *//** */
require_once($CFG->dirroot . '/question/type/edit_question_form.php');

/**
 * QTYPENAME editing form definition.
 * 
 * See http://docs.moodle.org/en/Development:lib/formslib.php for information
 * about the Moodle forms library, which is based on the HTML Quickform PEAR library.
 */
class qtype_gapfill_edit_form extends question_edit_form {

    public $answer;
    public $showanswers;
    

    function definition_inner(&$mform) {
        $mform->addElement('hidden', 'reload', 1);
        $mform->addElement('advcheckbox','showanswers',"Show Answers");
        $mform->addHelpButton('showanswers', 'showanswers', 'qtype_gapfill');
        $mform->removeelement('defaultmark');
    
    }

    function set_data($question) {
          $question->answer = $this->answer;
        $question->showanswers=$this->showanswers;
        parent::set_data($question);
    }

    protected function data_preprocessing($question) {
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

    function validation($data) {
        $errors = array();

        // TODO, do extra validation on the data that came back from the form. E.g.
        // if (/* Some test on $data['customfield']*/) {
        //     $errors['customfield'] = get_string( ... );
        // }


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