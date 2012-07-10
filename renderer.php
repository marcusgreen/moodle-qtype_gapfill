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
 * @copyright &copy; 2012 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class qtype_gapfill_renderer extends qtype_with_combined_feedback_renderer {
/** updated file */
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {

        
        $question = $qa->get_question();
        $fields = array();

        $place_count = count($question->places);
        $counter = 0;
        $output='';
        foreach ($question->textfragments as $place => $fragment) {
            if ($place > 0) {
                $output.=$this->embedded_element($qa, $place, $options);
            }
            /* format the non entry field parts of the question text, this will also
              ensure images get displayed */
            $output .= $question->format_text($fragment, $question->questiontextformat, $qa,
                    'question', 'questiontext', $question->id);
        }
        if ($qa->get_state() == question_state::$invalid) {
            $output.= html_writer::nonempty_tag('div', $question->get_validation_error(array('answer' => $output)),
                    array('class' => 'validationerror'));
        }
        return $output;
    }

    public function embedded_element(question_attempt $qa, $place, question_display_options $options) {

        $fraction = 0;
        $question = $qa->get_question();
        $fieldname = $question->field($place);
        $currentanswer = $qa->get_last_qt_var($fieldname);
        $answer = $qa->get_last_qt_var('answer');
        $answer = trim($answer);
        $size = "0"; /* width of the field to be filled in */
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

        /* $options->correctness is really about it being ready to mark,*/
        $feedbackimage = "";
        $inputclass = "";

        if ($options->correctness) {
            $response = $qa->get_last_qt_data();
            if (array_key_exists($fieldname, $response)) {
                $fraction = 0;
                $feedbackimage = $this->feedback_image($fraction);
                /* sets the field background to a different colour if the answer is right */
                $inputclass = $this->feedback_class($fraction);

                if ($question->is_correct_response($response[$fieldname], $rightanswer)) {
                    $fraction = 1;
                    $feedbackimage = $this->feedback_image($fraction);
                    /* sets the field background to a different colour if the answer is wrong */
                    $inputclass = $this->feedback_class($fraction);
                }
            }
        }

        $qprefix = $qa->get_qt_field_name('');
        $inputname = $qprefix . 'p' . $place;
        $style = "";
        $inputattributes = array(
            'type' => "input",
            'name' => $inputname,
            'value' => $currentanswer,
            'id' => $inputname,
            'size' => $size,
            'style' => 'width: ' . $style . 'px;'
        );

        if ($question->showanswers == true) {
            $inputattributes['type'] = "select";
            $inputattributes['size'] = "";

            $selectoptions = $question->places;
            shuffle($selectoptions);
            /* set the key to be the same as the value */
            $selectoptions = array_combine($selectoptions, $selectoptions);

            $selecthtml = html_writer::select($selectoptions, $inputname, $currentanswer, ' ',
                    $inputattributes) . ' ' . $feedbackimage;
            return $selecthtml;
        } else {

            /* When pre */
            if ($options->readonly) {
                $inputattributes['readonly'] = 'readonly';
            }
            $type = "input";
            $inputattributes["type"] = "input";
            $style = $size * 10;
            return html_writer::empty_tag('input', $inputattributes) . $feedbackimage;
        }
    }

    public function specific_feedback(question_attempt $qa) {
        return $this->combined_feedback($qa);
    }

}
