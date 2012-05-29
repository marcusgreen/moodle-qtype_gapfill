<?php

/**
 * The question type class for the gapfill question type.
 *
 * @package  
 * @subpackage questiontypes
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
        return array('question_gapfill', 'showanswers');
    }

/*
 * 
 */
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

    /* called when previewing a question or when displayed in a quiz */
    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_question_answers($question, $questiondata);

        $question->places = array();
        $counter = 1;
        
        foreach ($questiondata->options->answers as $choicedata) {
            $question->places[$counter] = $choicedata->answer;
            $counter++;
        }
                
        //$bits = preg_split('/\[.*?\]/', $question->questiontext, null, PREG_SPLIT_DELIM_CAPTURE);
        $bits = preg_split('/\[.*?\]/', $question->questiontext, null, PREG_SPLIT_NO_EMPTY);
        $question->textfragments = $bits;
    }

    /**
     *
     * @param type $question
     * @param type $form
     * @return type object
     * Sets the default mark as 1* the number of gaps
     * Does not allow setting any other value per space at the moment
     */
    function save_question($question, $form) {
        preg_match_all('/\[(.*?)\]/', $form->questiontext['text'], $bits);
        $form->defaultmark = count($bits[1]);
        return parent::save_question($question, $form);
    }

    /**
     * Save the units and the answers associated with this question.
     * @return boolean to indicate success or failure.
     */
    function save_question_options($question) {
        // Save the extra data to your database tables from the
        // $question object, which has all the post data from editquestion.html
        // 
        $squarebracketsregex = '/.*?\[(.*?)\]/';
        $matches = array();
        preg_match_all($squarebracketsregex, $question->questiontext, $matches);
        $answerwords = $matches[1];

        global $DB;
        $result = new stdClass();
        $context = $question->context;
        // Fetch old answer ids so that we can reuse them
        $oldanswers = $DB->get_records('question_answers', array('question' => $question->id), 'id ASC');

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
            $answer->fraction = '1';
            $DB->update_record('question_answers', $answer);

            $options = $DB->get_record('question_gapfill', array('question' => $question->id));
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
        }

        return true;
    }

//    public function get_possible_responses($questiondata) {
//        $responses = array();
//
//        foreach ($questiondata->options->answers as $aid => $answer) {
//            $responses[$aid] = new question_possible_response($answer->answer,
//                            $answer->fraction);
//        }
//        $responses[null] = question_possible_response::no_response();
//
//        return array($questiondata->id => $responses);
//    }

    public function questionid_column_name() {
        return 'question';
    }

}

