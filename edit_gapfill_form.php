x<?php
/**
 * The editing form code for this question type.
 *
 * @copyright &copy; 2006 YOURNAME
 * @author YOUREMAILADDRESS
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package YOURPACKAGENAME
 *//** */

require_once($CFG->dirroot.'/question/type/edit_question_form.php');

/**
 * QTYPENAME editing form definition.
 * 
 * See http://docs.moodle.org/en/Development:lib/formslib.php for information
 * about the Moodle forms library, which is based on the HTML Quickform PEAR library.
 */
class qtype_gapfill_edit_form extends question_edit_form {
  
public $answer;
    function definition_inner(&$mform) {
        // TODO, add any form fields you need.
        // $mform->addElement( ... );
   
        $mform->addElement('hidden', 'reload', 1);

        $mform->addElement('submit','next','Next');

        $mform->registerNoSubmitButton('next');
        $mform->addElement('submit','previous','Previous');

        $mform->registerNoSubmitButton('previous');

//        $mform->addElement('hidden','answer');

 
         if (optional_param('next', false, PARAM_BOOL)) {
       
            //$mform->removeElement('questiontext');
           // $mform->removeElement('generalfeedback');
            //$mform->freeze('name');
            //$mform->freeze('category');
            //$mform->removeElement('defaultmark');
         }
       // if ($mform->no_submit_button_pressed()) {
         //  // $mform->removeElement('questiontext');
        //}
        
    }

    function set_data($question) {
        
       $question->answer=$this->answer;  
        parent::set_data($question);
        
    }
    
        
    
       protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        //$question = $this->data_preprocessing_answers($question, true);
   
 
//  $question->answer='99 baloon'; works
        //$question->answer=$question->answer;
       // var_dump($question);
    //exit();
    
        //$question->answers[0]="fecketyfeck";  

        /* $question = $this->data_preprocessing_answers($question, true);
        $question = $this->data_preprocessing_combined_feedback($question, true);
        $question = $this->data_preprocessing_hints($question, true, true);

        if (!empty($question->options)) {
            $question->single = $question->options->single;
            $question->shuffleanswers = $question->options->shuffleanswers;
            $question->answernumbering = $question->options->answernumbering;
        }
*/
        return $question;
    }
    
//    protected function data_preprocessing_answers($question) {
  ///      $question = parent::data_preprocessing_answers($question);
       
/*if (empty($question->options->answers)) {
            return $question;
        }

        $key = 0;
        foreach ($question->options->answers as $answer) {
            // See comment in the parent method about this hack.
            unset($this->_form->_defaultValues["tolerancetype[$key]"]);
            unset($this->_form->_defaultValues["correctanswerlength[$key]"]);
            unset($this->_form->_defaultValues["correctanswerformat[$key]"]);

            $question->tolerancetype[$key]       = $answer->tolerancetype;
            $question->correctanswerlength[$key] = $answer->correctanswerlength;
            $question->correctanswerformat[$key] = $answer->correctanswerformat;
            $key++;
        }
*/
     //   return $question;
   // }

    
    function definition_after_data() {
              
     parent::definition_after_data();
      
      global $CFG, $COURSE;
      //$mform =& $this->_form;
      //$mform->removeElement('tags');
      //$mform =& $this->_form;
      //$element=  & $mform->getElement('questiontext');
      //$contents=$mform->exportValue('questiontext');
      //$questiontext=$contents['text'];
          // $matches = array();
        //$squarebracketsregex = '/\[[^]]*?\]/';
        //$squarebracketsregex ='/\\[(.*)\]/';
 //       $squarebracketsregex='/.*?\[(.*?)\]/';
  //     preg_match_all($squarebracketsregex, $questiontext, $matches);
   //    $this->question->answer=$matches[0];
    //   $this->question->answer='88 baloon';
       
      
     
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