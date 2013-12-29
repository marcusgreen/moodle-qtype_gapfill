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

    public $correct_responses = array();
    public $marked_responses = array();
    public $allanswers = array();

    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        global $PAGE;

        $question = $qa->get_question();

        if ($question->answerdisplay == "dragdrop") {
            $PAGE->requires->js('/question/type/gapfill/jquery/jquery-1.9.1.min.js');
            $PAGE->requires->js('/question/type/gapfill/jquery/jquery-ui-1.10.3.custom.min.js');
            $PAGE->requires->js('/question/type/gapfill/jquery/jquery.ui.touch-punch.min.js');
            $PAGE->requires->js('/question/type/gapfill/dragdrop.js');
        }

        $seranswers = $qa->get_step(0)->get_qt_var('_allanswers');
        $this->allanswers = unserialize($seranswers);
        $output = '';
        if ($question->answerdisplay == "dragdrop") {
            $ddclass = " draggable answers ";
            foreach ($this->allanswers as $value) {
                $output.= '<span class="' . $ddclass . '">' . $value . "</span>&nbsp;";
            }
            $output.="<br/><br/>";
        }
        $marked_gaps = $question->get_marked_gaps($qa, $options);
        foreach ($question->textfragments as $place => $fragment) {
            if ($place > 0) {
                $output.=$this->embedded_element($qa, $place, $options, $marked_gaps);
            }
            /* format the non entry field parts of the question text, this will also
              ensure images get displayed */
            $output .= $question->format_text($fragment, $question->questiontextformat,
                    $qa, 'question', 'questiontext', $question->id);
        }

        $output.="<br/>";

        if ($qa->get_state() == question_state::$invalid) {
            $output.= html_writer::nonempty_tag('div', $question->get_validation_error(array('answer' =>
                                $output)), array('class' => 'validationerror'));
        }
        return $output;
    }

    public function embedded_element(question_attempt $qa, $place, question_display_options $options, $marked_gaps) {

        /* fraction is the mark associated with this field, always 1 or 0 for this question type */
        $question = $qa->get_question();
        $fieldname = $question->field($place);
        $currentanswer = $qa->get_last_qt_var($fieldname);
        $currentanswer = htmlspecialchars_decode($currentanswer);

        $rightanswer = $question->get_right_choice_for($place);

        $size = strlen(htmlspecialchars_decode($rightanswer));

        /* $options->correctness is really about it being ready to mark, */
        $feedbackimage = "";
        $inputclass = "";
        if (($options->correctness) or ($options->numpartscorrect)) {
            $gap = $marked_gaps['p' . $place];
            $fraction = $gap['fraction'];
            $response = $qa->get_last_qt_data();
            if ($fraction == 1) {
                array_push($this->correct_responses, $response[$fieldname]);
                $feedbackimage = $this->feedback_image($fraction);
                /* sets the field background to green or yellow if fraction is 1 */
                $inputclass = $this->get_input_class($marked_gaps, $qa, $fraction, $fieldname);
            } else {
                /* set background to red and image to cross if fraction is 0  */
                $feedbackimage = $this->feedback_image($fraction);
                $inputclass = $this->feedback_class($fraction);
            }
        }

        $qprefix = $qa->get_qt_field_name('');
        $inputname = $qprefix . 'p' . $place;

        $inputattributes = array(
            'type' => "text",
            'name' => $inputname,
            'value' => $currentanswer,
            'id' => $inputname,
            'size' => $size + 1,
            'class' => 'droppable ' . $inputclass
                /* 'style'=> 'width:'.(($size*10)).'px' */
        );

        /* When previewing after a quiz is complete */
        if ($options->readonly) {
            $readonly = array('disabled' => 'true');
            $inputattributes = array_merge($inputattributes, $readonly);
        }

        if ($question->answerdisplay == "dropdown") {
            $inputattributes['type'] = "select";
            $inputattributes['size'] = "";
            $inputattributes['class'] = $inputclass;
            /* blank out the style put in previously */
            $inputattributes['style'] = '';
            $selectoptions = $this->get_dropdown_list();
            $selecthtml = html_writer::select($selectoptions, $inputname, $currentanswer, ' ',
                    $inputattributes) . ' ' . $feedbackimage;
            return $selecthtml;
        } else {
            return html_writer::empty_tag('input', $inputattributes) . $feedbackimage;
        }
    }

    /**
     * 
     * @param array $marked_gaps
     * @param question_attempt $qa
     * @param type $fraction either 0 or 1 for correct or incorrect
     * @param type $fieldname p1, p2, p3 etc
     * @return string set the feedback class to green unless noduplicates is set
     * then check if this is a duplicated value and if it is set the background
     * to yellow.
     */
    public function get_input_class(array $marked_gaps, question_attempt $qa, $fraction, $fieldname) {
        $response = $qa->get_last_qt_data();
        $question = $qa->get_question();
        $inputclass = $this->feedback_class($fraction);
        foreach ($marked_gaps as $gap) {
            if ($response[$fieldname] == $gap['value']) {
                if ($gap['duplicate'] == 'true') {
                    if ($question->noduplicates == 1) {
                        $inputclass = ' correctduplicate';
                    }
                }
            }
        }
        return $inputclass;
    }

    public function specific_feedback(question_attempt $qa) {
        return $this->combined_feedback($qa) . $this->get_duplicate_feedback($qa);
    }

    /**
     * @param type questionattemtp $qa
     * @return type string
     * if noduplicates is set check if any responses 
     * are duplicate values
     */
    public function get_duplicate_feedback(question_attempt $qa) {
        $question = $qa->get_question();
        if ($question->noduplicates == 0) {
            return;
        }
        $arr_unique = array_unique($this->correct_responses);
        if (count($arr_unique) != count($this->correct_responses)) {
            return get_string('duplicatepartialcredit', 'qtype_gapfill');
        }
    }

    /* used to populate values that appear in dropdowns */

    public function get_dropdown_list() {
        /* convert things like &gt; to > etc */
        foreach ($this->allanswers as $key => $value) {
            $this->allanswers[$key] = htmlspecialchars_decode($value);
        }
        // Make the key and value the same in the array.
        $selectoptions = array_combine($this->allanswers, $this->allanswers);
        return $selectoptions;
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
