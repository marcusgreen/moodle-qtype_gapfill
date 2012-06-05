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
 * gapfill question renderer class.
 *
 * @package    qtype
 * @subpackage gapfill
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for true-false questions.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_gapfill_renderer extends qtype_with_combined_feedback_renderer {

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        $question = $qa->get_question();

        $fields = array();
        $question_text = "";
        //$fragment_count = count($question->textfragments);
        $place_count = count($question->places);


        // $fragment_count = $fragment_count;
        $counter = 0;
        if ($question->showanswers == true) {
            $question_text = $question->get_shuffled_answers() . "<br/>";
        }


        foreach ($question->textfragments as $place => $fragment) {
            if ($place >0) {
                $question_text.=$this->embedded_element($qa, $place, $options);
            }
          $question_text .= $fragment;

        }
        if ($qa->get_state() == question_state::$invalid) {
            $question_text .= html_writer::nonempty_tag('div', $question->get_validation_error(array('answer' => $question_text)), array('class' => 'validationerror'));
        }


        return $question_text;
    }

    function embedded_element(question_attempt $qa, $place, question_display_options $options) {
        $fraction = 0;
        $question = $qa->get_question();
        $fieldname = $question->field($place);
        $currentanswer = $qa->get_last_qt_var($fieldname);
        $answer = $qa->get_last_qt_var('answer');
        $answer = trim($answer);
        $size = "0"; //width of the field to be filled in
        if ($currentanswer == null) {
            if ($answer != null) {
                /* if fill in correct answer is pressed during question preview */
                $answer_parts = explode(' ', $answer);
                /* minus 1 because explode creates array with offset 0, places has offset of 1 */
                $currentanswer = $answer_parts[$place - 1];
            }
        }

        $rightanswer = $question->get_right_choice_for($place);
        $size = strlen($rightanswer);

        //$options->correctness is really about it being ready to mark,
        $feedbackimage = "";
        $inputclass = "";

        if ($options->correctness) {
            $response = $qa->get_last_qt_data();
            if (array_key_exists($fieldname, $response)) {
                $fraction = 0;
                $feedbackimage = $this->feedback_image($fraction);
                /* sets the field background to a different colour if the answer is right */
                $inputclass = $this->feedback_class($fraction);

                if ($response[$fieldname] == $rightanswer) {
                    $fraction = 1;
                    $feedbackimage = $this->feedback_image($fraction);
                    /* sets the field background to a different colour if the answer is wrong */
                    $inputclass = $this->feedback_class($fraction);
                }
            }
        }

        $qprefix = $qa->get_qt_field_name('');
        $inputname = $qprefix . 'p' . $place;
        $inputattributes = array(
            'type' => 'text',
            'name' => $inputname,
            'value' => $currentanswer,
            'id' => $inputname,
            'size' => $size,
            'maxlength' => $size,
            'class' => $inputclass
        );

        return html_writer::empty_tag('input', $inputattributes) . $feedbackimage;
    }

    public function specific_feedback(question_attempt $qa) {
        /*I'm not sure if this is actually doing anything */
        $question = $qa->get_question();
        $response = $qa->get_last_qt_var('answer', '');

    }

    public function correct_response(question_attempt $qa) {
      //  $question = $qa->get_question();
        $answer = $question->get_matching_answer($question->get_correct_response());
        if (!$answer) {
            return '';
        }
        return get_string('correctansweris', 'qtype_gapfill', s($answer->answer));
    }

}
