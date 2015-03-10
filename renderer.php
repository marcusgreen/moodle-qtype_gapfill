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

/** Gapfill question type with type in gaps, draggable answers or dropdowns */
class qtype_gapfill_renderer extends qtype_with_combined_feedback_renderer {

    public $correctresponses = array();
    public $markedresponses = array();
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
            foreach ($this->allanswers as $potentialanswer) {
                if (!preg_match($question->blankregex, trim($potentialanswer))) {
                    $output .= '<span class= "' . $ddclass . '">' . $potentialanswer . "</span>&nbsp;";
                }
            }
            $output .= "<br/><br/>";
        }
        $markedgaps = $question->get_markedgaps($qa, $options);
        foreach ($question->textfragments as $place => $fragment) {
            if ($place > 0) {
                $output .= $this->embedded_element($qa, $place, $options, $markedgaps, $options);
            }
            /* format the non entry field parts of the question text, this will also
              ensure images get displayed */
            $output .= $question->format_text($fragment, $question->questiontextformat, $qa,
                    'question', 'questiontext', $question->id);
        }
        $output .= "<br/>";

        if ($qa->get_state() == question_state::$invalid) {
            $output .= html_writer::nonempty_tag('div', $question->get_validation_error(array('answer' =>
                                $output)), array('class' => 'validationerror'));
        }
        return $output;
    }

    public function embedded_element(question_attempt $qa, $place, question_display_options $options,
            $markedgaps, question_display_options $options) {
        /* fraction is the mark associated with this field, always 1 or 0 for this question type */
        $question = $qa->get_question();
        $fieldname = $question->field($place);
        $currentanswer = $qa->get_last_qt_var($fieldname);
        $currentanswer = htmlspecialchars_decode($currentanswer);
        $rightanswer = $question->get_right_choice_for($place);
        if ($question->fixedgapsize == 1) {
            /* set all gaps to the size of the  biggest gap
             */
            $size = $question->maxgapsize;
        } else {
            /* otherwise set the size of an individual gap which might
             * be less than the string width if it is in the form
             * "[cat|dog|elephant] the width should be 8 and not 14
             */
            $size = $question->get_size($rightanswer);
        }

        /* $options->correctness is really about it being ready to mark, */
        $aftergapfeedback = "";
        $inputclass = "";
        if (($options->correctness) or ( $options->numpartscorrect)) {
            $gap = $markedgaps['p' . $place];
            $fraction = $gap['fraction'];
            $response = $qa->get_last_qt_data();
            /* fraction is always either 1 or 0 for correct or incorrect response */
            if ($fraction == 1) {
                array_push($this->correctresponses, $response[$fieldname]);
                /* if the gap contains !! or  the response is (a correct) non blank */
                if (!preg_match($question->blankregex, $rightanswer) || ($response[$fieldname] <> '')) {
                    $aftergapfeedback = $this->feedback_image($fraction);
                    /* sets the field background to green or yellow if fraction is 1 */
                    $inputclass = $this->get_input_class($markedgaps, $qa, $fraction, $fieldname);
                }
            } else if ($fraction == 0) {
                /* set background to red and image to cross if fraction is 0 (an incorrect response
                 * was given */
                $aftergapfeedback = $this->feedback_image($fraction);
                if ($options->rightanswer == 1) {
                    /* replace | operator with the word or */
                    $rightanswerdisplay = preg_replace("/\|/", get_string("or", "qtype_gapfill"), $rightanswer);
                    /* replace !! with the 'blank' */
                    $rightanswerdisplay = preg_replace("/\!!/", get_string("blank", "qtype_gapfill"), $rightanswerdisplay);
                    $delim = qtype_gapfill::get_delimit_array($question->delimitchars);
                    $aftergapfeedback .= "<span class='aftergapfeedback' title='".get_string("correctanswer","qtype_gapfill")."'>" . $delim["l"] .
                            $rightanswerdisplay . $delim["r"] . "</span>";
                }
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
            'size' => $size,
            'class' => 'droptarget ' . $inputclass,
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
            $selecthtml = html_writer::select($selectoptions, $inputname, $currentanswer,
                    ' ', $inputattributes) . ' ' . $aftergapfeedback;
            return $selecthtml;
        } else {
            return html_writer::empty_tag('input', $inputattributes) . $aftergapfeedback;
        }
    }

    /**
     *
     * @param array $markedgaps
     * @param question_attempt $qa
     * @param type $fraction either 0 or 1 for correct or incorrect
     * @param type $fieldname p1, p2, p3 etc
     * @return string set the feedback class to green unless noduplicates is set
     * then check if this is a duplicated value and if it is set the background
     * to yellow.
     */
    public function get_input_class(array $markedgaps, question_attempt $qa, $fraction, $fieldname) {
        $response = $qa->get_last_qt_data();
        $question = $qa->get_question();
        $inputclass = $this->feedback_class($fraction);
        foreach ($markedgaps as $gap) {
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
        $arrunique = array_unique($this->correctresponses);
        if (count($arrunique) != count($this->correctresponses)) {
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
