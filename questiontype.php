<?php

/**
 * The question type class for the gapfill question type.
 *
 * @package    qtype
 * @subpackage gapfill
 * @copyright &copy; 2012 Marcus Green
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License

 */

/**
 * The gapfill question class
 * Load from database, and initialise class
 * A "fill in the gaps" cloze style question type
 */
class qtype_gapfill extends question_type {

    public function extra_question_fields() {
        return array('question_gapfill', 'showanswers','delimitchars');
    }

    protected function initialise_question_answers(question_definition $question, $questiondata, $forceplaintextanswers = true) {
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

    /*
     *  Called when previewing a question or when displayed in a quiz 
     */
    protected function initialise_question_instance(question_definition $question, $questiondata) {
       
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_question_answers($question, $questiondata);

        $question->places = array();
        $counter = 1;

        foreach ($questiondata->options->answers as $choicedata) {
            $question->places[$counter] = $choicedata->answer;
            $counter++;
        }

        //$bits = preg_split('/\[.*?\]/', $question->questiontext, null, PREG_SPLIT_DELIM_CAPTURE
        // will put empty places '' where there is no text content
        $l=substr($question->delimitchars,0,1);
        $r=substr($question->delimitchars,1,1);
       
        $left="[";
        $right="]";
        $nonfieldregex= '/\\'.$l.'.*?\\'.$r.'/';
        $bits = preg_split($nonfieldregex, $question->questiontext, null, PREG_SPLIT_DELIM_CAPTURE);
        //$bits = preg_split('/\[.*?\]/', $question->questiontext, null, PREG_SPLIT_DELIM_CAPTURE);
        $question->textfragments[0] = array_shift($bits);
        $i = 1;
        while (!empty($bits)) {
            $question->textfragments[$i] = array_shift($bits);
            $i += 1;
        }
    }

    /**
     *
     * @param type $question The current question
     * @param type $form The question editing form
     * @return type object
     * Sets the default mark as 1* the number of gaps
     * Does not allow setting any other value per space at the moment
     */
    function save_question($question, $form) {
      
   
        $l=substr($form->delimitchars,0,1);
        $r=substr($form->delimitchars,1,1);
               
        $left="[";
        $right="]";
        //preg_match_all('/\[(.*?)\]/', $form->questiontext['text'], $bits);
        $fieldregex = '/\\'.$l.'(.*?)\\'.$r.'/';
        preg_match_all($fieldregex, $form->questiontext['text'], $bits);

        $form->defaultmark = count($bits[1]);
        return parent::save_question($question, $form);
    }

    /**
     * Save the units and the answers associated with this question.
     * @return boolean to indicate success or failure.
     * 
     */
    function save_question_options($question) {
        // Save the extra data to your database tables from the
        // $question object, which has all the post data from editquestion.html
        // 
        $l=substr($question->delimitchars,0,1);
        $r=substr($question->delimitchars,1,1);
        
        $left='[';
        $right=']';
        
        $fieldregex = '/.*?\\'.$l.'(.*?)\\'.$r.'/';
        $matches = array();
        preg_match_all($fieldregex, $question->questiontext, $matches);
        $answerwords = $matches[1];

        global $DB;
        $result = new stdClass();
        $context = $question->context;
        // Fetch old answer ids so that we can reuse them
        $oldanswers = $DB->get_records('question_answers', array('question' => $question->id), 'id ASC');

        // Insert all the new answers
        foreach ($answerwords as $key => $word) {
            // Save the true answer - update an existing answer if possible.
            //  $answer = array_shift($oldanswers);

            if ($answer = array_shift($oldanswers)) {
                $answer->question = $question->id;
                $answer->answer = $word;
                $answer->feedback = '';
                $answer->fraction = '1';
                $DB->update_record('question_answers', $answer);
            } else {
                //Insert a blank record
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = $word;
                $answer->feedback = '';
                $answer->id = $DB->insert_record('question_answers', $answer);
            }
            
        }
        // Delete old answer records
            foreach ($oldanswers as $oa) {
                $DB->delete_records('question_answers', array('id' => $oa->id));
            }

        $options = $DB->get_record('question_gapfill', array('question' => $question->id));
         $options->delimitchars=$question->delimitchars;
        
        $options->showanswers = $question->showanswers;

        if (!$options) {
            $options = new stdClass();
            $options->question = $question->id;
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';

            $options->id = $DB->insert_record('question_gapfill', $options);
        } else {
            $parentresult = parent::save_question_options($question);
            $options->id = $question->id;
            $DB->update_record('question_gapfill', $options);
        }
        $this->save_hints($question);
        return true;
    }

    public function questionid_column_name() {
        return 'question';
    }

}

