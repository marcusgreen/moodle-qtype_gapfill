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

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        global $PAGE;

        $question = $qa->get_question();

        if ($question->answerdisplay == "dragdrop") {

            $PAGE->requires->js('/question/type/gapfill/jquery/jquery-1.4.2.js');
            $PAGE->requires->js('/question/type/gapfill/jquery/ui/jquery.ui.core.min.js');
            $PAGE->requires->js('/question/type/gapfill/jquery/ui/jquery.ui.widget.min.js');
            $PAGE->requires->js('/question/type/gapfill/jquery/ui/jquery.ui.mouse.min.js');
            $PAGE->requires->js('/question/type/gapfill/jquery/ui/jquery.ui.draggable.min.js');
            $PAGE->requires->js('/question/type/gapfill/jquery/ui/jquery.ui.droppable.min.js');
            $PAGE->requires->js('/question/type/gapfill/dragdrop.js');
        }
        $fields = array();

        $answers=$qa->get_step(0)->get_qt_var('_allanswers');
        $place_count = count($question->places);
        $counter = 0;
        $output = '';
        if ($question->answerdisplay == "dragdrop") {
            $ddclass = "draggable answers";
            $answers = $this->get_answers('dragdrop', $answers);
            foreach ($answers as $key => $value) {
                $output.= '<span class="' . $ddclass . '">' . $value . "</span>&nbsp";
            }
            $output.="</br></br>";
        }

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

        /* fraction is the mark associated with this field, always 1 or 0 for this question type */
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

        /* $options->correctness is really about it being ready to mark, */
        $feedbackimage = "";
        $inputclass = "";
                
         if (($options->correctness) or ($options->numpartscorrect) ) {
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
            'type' => "text",
            'name' => $inputname,
            'value' => $currentanswer,
            'id' => $inputname,
            'size' => $size,
       
            'class' => 'droppable ' . $inputclass,
        );
        /* When previewing after a quiz is complete */
        if ($options->readonly) {
            $readonly = array('disabled' => 'true');
           $inputattributes = array_merge($inputattributes, $readonly);
        }

        if ($question->answerdisplay == "dropdown") {
            $inputattributes['type'] = "select";
            $inputattributes['size'] = "";
            $inputattributes['class']=$inputclass;
             $answers=$qa->get_step(0)->get_qt_var('_allanswers');
             $selectoptions = $this->get_answers('dropdown', $answers);
             $selecthtml = html_writer::select($selectoptions, $inputname, $currentanswer, ' ',
                     $inputattributes) . ' ' . $feedbackimage;
            return $selecthtml;
        } else {
            return html_writer::empty_tag('input', $inputattributes) . $feedbackimage;
        }
    }

    public function specific_feedback(question_attempt $qa) {
        return $this->combined_feedback($qa);
    }

    public function get_answers($answerdisplay, $answers) {
        // Turn string into an array.
        $answers=explode(",", $answers);
        if ($answerdisplay == 'dragdrop') {
                return $answers;
        }
        if ($answerdisplay == 'dropdown') {
            // Make the key and value the same in the array.
            $answers = array_combine($answers, $answers);
            return $answers;
        }
    }
    /* overriding base class method purely to return a string yougotnrightcount
     * instead of default yougotnright
     */
    protected function num_parts_correct(question_attempt $qa) {
        $a = new stdClass();
        list($a->num, $a->outof) = $qa->get_question()->get_num_parts_right(
                $qa->get_last_qt_data());
        if (is_null($a->outof)) {
            return '';
        } else {
           return get_string('yougotnrightcount', 'qtype_gapfill', $a);
        }
    }

}
