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
 * The editing form code for this question type.
 * @package    qtype
 * @subpackage gapfill
 * @copyright  2012 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/question/type/edit_question_form.php');

defined('MOODLE_INTERNAL') || die();

/**
 * gapfill editing form definition.
 * 
 * See http://docs.moodle.org/en/Development:lib/formslib.php for information
 * about the Moodle forms library, which is based on the HTML Quickform PEAR library.
 */
class qtype_gapfill_edit_form extends question_edit_form {

    public $answer;
    public $answerdisplay;
    public $delimitchars;

    protected function definition_inner($mform) {
        $mform->addElement('hidden', 'reload', 1);
        $mform->setType('reload', PARAM_RAW);
        $mform->removeelement('generalfeedback');

        // Default mark will be set to 1 * number of fields.
        $mform->removeelement('defaultmark');

        $mform->addElement('editor', 'wronganswers', get_string('wronganswers', 'qtype_gapfill'), array('size' => 70, 'rows' => 1),
                $this->editoroptions);
        $mform->addHelpButton('wronganswers', 'wronganswers', 'qtype_gapfill');

        /* Only allow plain text in for the comma delimited set of wrong answer values
         * wrong answers really should be a set of zero marked ordinary answers in the answers
         * table.
         */
        $mform->setType('wronganswers', PARAM_TEXT);

        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question')
                , array('rows' => 10), $this->editoroptions);

        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');
        $mform->addElement('header', 'feedbackheader', get_string('moreoptions', 'qtype_gapfill'));

        // The delimiting characters around fields.

        $config = get_config('qtype_gapfill');
        /* turn  config->delimitchars into an array) */
        $delimitchars = explode(",", $config->delimitchars);
        /* copies the values into the keys */
        $delimitchars = array_combine($delimitchars, $delimitchars);
        /* strip any spaces from keys. This is about backward compatibility with old code
         * and avoiding having to expand the size of the delimitchar column from its current
         * 2. The value in the drop down looks better with a gap between the delimitchars, but
         * a gap in the key will break the insert into the question_gapfill table
         */
        foreach ($delimitchars as $key => $value) {
            $key2 = str_replace(' ', '', $key);
            $delimitchars2[$key2] = $value;
        }
        $mform->addElement('select', 'delimitchars', get_string('delimitchars', 'qtype_gapfill'), $delimitchars2);
        $mform->addHelpButton('delimitchars', 'delimitchars', 'qtype_gapfill');

        $answerdisplaytypes = array("dragdrop" => get_string('displaydragdrop', 'qtype_gapfill'),
            "gapfill" => get_string('displaygapfill', 'qtype_gapfill'),
            "dropdown" => get_string('displaydropdown', 'qtype_gapfill'));

        $mform->addElement('select', 'answerdisplay', get_string('answerdisplay', 'qtype_gapfill'), $answerdisplaytypes);
        $mform->addHelpButton('answerdisplay', 'answerdisplay', 'qtype_gapfill');

        /* use plain string matching instead of regular expressions */
        $mform->addElement('advcheckbox', 'disableregex', get_string('disableregex', 'qtype_gapfill'));
        $mform->addHelpButton('disableregex', 'disableregex', 'qtype_gapfill');
        $mform->setDefault('disableregex', $config->disableregex);

        /* sets all gaps to the size of the largest gap, avoids giving clues to the correct answer */
        $mform->addElement('advcheckbox', 'fixedgapsize', get_string('fixedgapsize', 'qtype_gapfill'));
        $config = get_config('qtype_gapfill');
        $mform->setDefault('disableregex', $config->fixedgapsize);
        $mform->addHelpButton('fixedgapsize', 'fixedgapsize', 'qtype_gapfill');

        /* Discards duplicates before processing answers, useful for tables with gaps like [cat|dog][cat|dog] */
        $mform->addElement('advcheckbox', 'noduplicates', get_string('noduplicates', 'qtype_gapfill'));
        $mform->addHelpButton('noduplicates', 'noduplicates', 'qtype_gapfill');
        $mform->setAdvanced('noduplicates');

        $mform->addElement('advcheckbox', 'casesensitive', get_string('casesensitive', 'qtype_gapfill'));
        $mform->addHelpButton('casesensitive', 'casesensitive', 'qtype_gapfill');
        $mform->setAdvanced('casesensitive');

        // To add combined feedback (correct, partial and incorrect).
        $this->add_combined_feedback_fields(true);

        // Adds hinting features.
        $this->add_interactive_settings(true, true);
    }

    public function set_data($question) {
        /* accessing the form in this way is probably not correct style */
        $wronganswers = $this->get_wrong_answers($question);
        $this->_form->getElement('wronganswers')->setValue(array('text' => $wronganswers));
        parent::set_data($question);
    }

    /**
     * Pull out a comma delimited string with the 
     * wrong answers (distractors) in it from question->options->answers
     * @param type $question
     * @return type string
     */
    public function get_wrong_answers($question) {
        $wronganswers = "";
        if (property_exists($question, 'options')) {
            foreach ($question->options->answers as $a) {
                /* if it doesn't contain a 1 it must be zero and so be a wrong answer */
                if (!(strpos($a->fraction, '1') !== false)) {
                    $wronganswers .= $a->answer . ",";
                }
            }
        }
        return $wronganswers = rtrim($wronganswers, ',');
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_combined_feedback($question);
        /* populates the hints and adds clearincorrect and and shownumcorrect (true,true) */
        $question = $this->data_preprocessing_hints($question, true, true);

        if (!empty($question->options)) {
            $question->answerdisplay = $question->options->answerdisplay;
        }
        return $question;
    }

    public function validation($fromform, $data) {
        $errors = array();
        /* don't save the form if there are no fields defined */
        $gaps = qtype_gapfill::get_gaps($fromform['delimitchars'], $fromform['questiontext']['text']);
        if (count($gaps) == 0) {
            $errors['questiontext'] = get_string('questionsmissing', 'qtype_gapfill');
        }
        if ($errors) {
            return $errors;
        } else {
            return true;
        }
    }

    public function qtype() {
        return 'gapfill';
    }

}
