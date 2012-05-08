<?php
/**
 * The question type class for the QTYPENAME question type.
 *
 * @copyright &copy; 2006 YOURNAME
 * @author YOUREMAILADDRESS
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package questionbank
 * @subpackage questiontypes
 *//** */

/**
 * The gapfill question class
 *
 * TODO give an overview of how the class works here.
 */
class qtype_gapfill extends question_type {
      
      protected function initialise_question_answers(question_definition $question,
            $questiondata, $forceplaintextanswers = true) {
        $question->answers = array();
        if (empty($questiondata->options->answers)) {
            return;
        }
        foreach ($questiondata->options->answers as $a) {
            $question->answers[$a->id] = new question_answer($a->id, $a->answer,
                    $a->fraction, $a->feedback, $a->feedbackformat);
            if (!$forceplaintextanswers) {
                $question->answers[$a->id]->answerformat = $a->answerformat;
            }
            
        }
        
    }
/* called when previewing a question or when displayed in a quiz */    

protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
         $this->initialise_question_answers($question, $questiondata);

         $question->places = array();       
         $counter=0;
         foreach ($questiondata->options->answers as $choicedata) {
         $question->places[$counter]=$choicedata->answer;   
         $counter++;
         }
         $bits = preg_split('/\[.*?\]/', $question->questiontext,
                null, PREG_SPLIT_DELIM_CAPTURE);
  $question->textfragments = $bits;
 

    }    
 
     
 
    /**
     * Save the units and the answers associated with this question.
     * @return boolean to indicate success or failure.
     */
    function save_question_options($question) {
        // TODO code to save the extra data to your database tables from the
        // $question object, which has all the post data from editquestion.html
       $squarebracketsregex='/.*?\[(.*?)\]/';
       $matches=array();
       preg_match_all($squarebracketsregex, $question->questiontext, $matches);
       $answerwords=$matches[1];
     
     global $DB;
        $result = new stdClass();
        $context = $question->context;
         // Fetch old answer ids so that we can reuse them
        $oldanswers = $DB->get_records('question_answers',
                    array('question' => $question->id), 'id ASC');
     
         // Insert all the new answers
         foreach ($answerwords as $key => $words) {
                 // Save the true answer - update an existing answer if possible.
                $answer = array_shift($oldanswers);
                
                if (!$answer) {
                    //Insert a blank record
                    $answer = new stdClass();
                    $answer->question = $question->id;
                    $answer->answer = '';
                    $answer->feedback = '';
                    $answer->id = $DB->insert_record('question_answers', $answer);
            }
       
                $answer->question = $question->id;
                $answer->answer = $words;
                $answer->feedback = '';
                $answer->fraction='1';
            $DB->update_record('question_answers', $answer);
         
          $options = $DB->get_record('question_gapfill', array('question' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->question = $question->id;
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            
           
           $options->id = $DB->insert_record('question_gapfill', $options);
        }
        
        }
        
        //$answer->answer=$newanswer[0];
        //$DB->update_record('question_answers', $answer);
        return true;
    }

   
   /*
    function print_question_formulation_and_controls(&$question, &$state, $cmoptions, $options) {
        global $CFG;

        $readonly = empty($options->readonly) ? '' : 'disabled="disabled"';

        // Print formulation
        $questiontext = $this->format_text($question->questiontext,
                $question->questiontextformat, $cmoptions);
        $image = get_question_image($question, $cmoptions->course);
    
        // TODO prepare any other data necessary. For instance
        $feedback = '';
        if ($options->feedback) {
    
        }
    
        include("$CFG->dirroot/question/type/gapfill/display.html");
    }
    * 
    */

 public function get_possible_responses($questiondata) {
        $responses = array();

        foreach ($questiondata->options->answers as $aid => $answer) {
            $responses[$aid] = new question_possible_response($answer->answer,
                    $answer->fraction);
        }
        $responses[null] = question_possible_response::no_response();

        return array($questiondata->id => $responses);
    }
     protected function questionid_column_name() {
        return 'question';
    }
}


