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
 * The question type class for the gapfill question type.
 *
 * @package    qtype
 * @subpackage gapfill
 * @copyright &copy; 2012 Marcus Green
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');

/**
 * The gapfill question class
 * Load from database, and initialise class
 * A "fill in the gaps" cloze style question type
 */
class qtype_gapfill extends question_type {

    public function extra_question_fields() {
        return array('question_gapfill', 'answerdisplay', 'delimitchars', 'casesensitive', 'noduplicates');
    }

    /* populates fields such as combined feedback in the editing form */

    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('question_gapfill', array('question' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    protected function initialise_question_answers(question_definition $question, $questiondata, $forceplaintextanswers = true) {

        $question->answers = array();
        if (empty($questiondata->options->answers)) {
            return;
        }
        foreach ($questiondata->options->answers as $a) {
            array_push($question->allanswers, $a->answer);
            /* answer in this context means correct answers, i.e. where
             * fraction contains a 1 */
            if (strpos($a->fraction, '1') !== false) {
                $question->answers[$a->id] = new question_answer($a->id, $a->answer,
                                $a->fraction, $a->feedback, $a->feedbackformat);
                if (!$forceplaintextanswers) {
                    $question->answers[$a->id]->answerformat = $a->answerformat;
                }
            }
        }
    }

    /*
     *  Called when previewing a question or when displayed in a quiz
     *  (not from within the editing form)
     */

    protected function initialise_question_instance(question_definition $question, $questiondata) {

        parent::initialise_question_instance($question, $questiondata);
        $this->initialise_question_answers($question, $questiondata);
        $this->initialise_combined_feedback($question, $questiondata);

        $question->places = array();
        $counter = 1;

        foreach ($questiondata->options->answers as $choicedata) {

            /* fraction contains a 1 */
            if (strpos($choicedata->fraction, '1') !== false) {
                $question->places[$counter] = $choicedata->answer;
                $counter++;
            }
        }
        /* Will put empty places '' where there is no text content.
         * l for left delimiter r for right delimiter
         */

        $l = substr($question->delimitchars, 0, 1);
        $r = substr($question->delimitchars, 1, 1);

        $nonfieldregex = '/\\' . $l . '.*?\\' . $r . '/';
        $bits = preg_split($nonfieldregex, $question->questiontext, null, PREG_SPLIT_DELIM_CAPTURE);
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
     * @param type $form The question editing form data
     * @return type object
     * Sets the default mark as 1* the number of gaps
     * Does not allow setting any other value per space/field at the moment
     */
    public function save_question($question, $form) {
        /*
          l for left delimiter r for right delimiter
          this should be refactored into a separate method
          for use also in initialsing the question
         */
        $l = substr($form->delimitchars, 0, 1);
        $r = substr($form->delimitchars, 1, 1);

        $fieldregex = '/\\' . $l . '(.*?)\\' . $r . '/';
        preg_match_all($fieldregex, $form->questiontext['text'], $bits);

        /* count the number of fields */
        $form->defaultmark = count($bits[1]);
        return parent::save_question($question, $form);
    }

    /**
     * Save the units and the answers associated with this question.
     * @return boolean to indicate success or failure.
     * 
     */
    public function save_question_options($question) {
        /* Save the extra data to your database tables from the
          $question object, which has all the post data from editquestion.html */

        /* left and right delimiters pulled in from the
         * delimitchars field in the question_gapfill table
         */
        $l = substr($question->delimitchars, 0, 1);
        $r = substr($question->delimitchars, 1, 1);

        $fieldregex = '/.*?\\' . $l . '(.*?)\\' . $r . '/';
        $matches = array();
        preg_match_all($fieldregex, $question->questiontext, $matches);

        /* just the field contents */
        $answerwords = $matches[1];

        $answerfields = $this->get_answer_fields($answerwords, $question);
        global $DB;

        $context = $question->context;
        // Fetch old answer ids so that we can reuse them.
        $oldanswers = $DB->get_records('question_answers', array('question' => $question->id), 'id ASC');

        // Insert all the new answers.
        foreach ($answerfields as $field) {
            // Save the true answer - update an existing answer if possible.
            if ($answer = array_shift($oldanswers)) {
                $answer->question = $question->id;
                $answer->answer = $field['value'];
                $answer->feedback = '';
                $answer->fraction = $field['fraction'];
                $DB->update_record('question_answers', $answer);
            } else {
                // Insert a blank record.
                $answer = new stdClass();
                $answer->question = $question->id;
                $answer->answer = $field['value'];
                $answer->feedback = '';
                $answer->correctfeedback = '';
                $answer->partiallycorrectfeedback = '';
                $answer->incorrectfeedback = '';
                $answer->wronganswers = '';
                $answer->fraction = $field['fraction'];
                $answer->id = $DB->insert_record('question_answers', $answer);
            }
        }
        // Delete old answer records.
        foreach ($oldanswers as $oa) {
            $DB->delete_records('question_answers', array('id' => $oa->id));
        }

        $options = $DB->get_record('question_gapfill', array('question' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->question = $question->id;
            $options->wronganswers = '';
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            $options->answerdisplay = '';
            $options->delimitchars = '';
            $options->casesensitive = '';
            $options->noduplicates='';
            $options->id = $DB->insert_record('question_gapfill', $options);
        }
        $options->delimitchars = $question->delimitchars;
        $options->answerdisplay = $question->answerdisplay;
        $options->casesensitive = $question->casesensitive;

        $options->noduplicates = $question->noduplicates;
        $options = $this->save_combined_feedback_helper($options, $question, $context, true);

        $DB->update_record('question_gapfill', $options);

        $this->save_hints($question);

        return true;
    }

    /**
     * Set up all the answer fields with respective fraction (mark values)
     * This is used to update the question_answers table. Answerwords has
     * been pulled from within the delimitchars e.g. the cat within [cat]
     * Wronganswers has been pulled from a comma delimited edit form field
     * 
     * @param array $answerwords
     * @param type $question
     * @return type array
     */
    public function get_answer_fields(array $answerwords, $question) {

        foreach ($answerwords as $key => $value) {
            $answerfields[$key]['value'] = $value;
            $answerfields[$key]['fraction'] = 1;
        }

        if (property_exists($question, 'wronganswers')) {
            if ($question->wronganswers != '') {
                /* remove any trailing commas */
                $question->wronganswers = rtrim($question->wronganswers, ',');
                $wronganswers = explode(",", $question->wronganswers);

                foreach ($wronganswers as $key => $word) {
                    $wronganswerfields[$key]['value'] = $word;
                    $wronganswerfields[$key]['fraction'] = 0;
                }
                $answerfields = array_merge($answerfields, $wronganswerfields);
            }
        }
        return $answerfields;
    }

    protected function make_hint($hint) {
        return question_hint_with_parts::load_from_record($hint);
    }

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        /* Thanks to Jean-Michel Vedrine for pointing out the need for this and delete_files function */
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_combined_feedback($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_combined_feedback($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    public function questionid_column_name() {
        return 'question';
    }

    public function import_from_xml($data, $question, qformat_xml $format, $extra = null) {
        if (!isset($data['@']['type']) || $data['@']['type'] != 'gapfill') {
            return false;
        }
        $question = parent::import_from_xml($data, $question, $format, null);
        $format->import_combined_feedback($question, $data, true);
        $format->import_hints($question, $data, true, false, $format->get_format($question->questiontextformat));
        return $question;
    }

    public function export_to_xml($question, qformat_xml $format, $extra = null) {
        $output = parent::export_to_xml($question, $format);
        $output .= '    <delimitchars>' . $question->options->delimitchars .
                "</delimitchars>\n";
        $output .= '    <answerdisplay>' . $question->options->answerdisplay .
                "</answerdisplay>\n";
        $output .= '    <casesensitive>' . $question->options->casesensitive .
                "</casesensitive>\n";
        $output .= '    <noduplicates>' . $question->options->casesensitive .
                "</noduplicates>\n";
        $output .= $format->write_combined_feedback($question->options, $question->id, $question->contextid);
        return $output;
    }

}