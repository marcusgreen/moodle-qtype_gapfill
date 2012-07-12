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
    public $showanswers;
    public $delimitchars;


    protected function definition_inner($mform) {
        $mform->addElement('hidden', 'reload', 1);
        $mform->removeelement('generalfeedback');

        // Default mark will be set to 1 * number of fields.
        $mform->removeelement('defaultmark');

        // The delimiting characters around fields.
        $delimitchars = array("[]" => "[ ]", "{}" => "{ }", "##" => "##", "@@" => "@ @");
        $mform->addElement('select', 'delimitchars', get_string('delimitchars', 'qtype_gapfill'), $delimitchars);
        $mform->addHelpButton('delimitchars', 'delimitchars', 'qtype_gapfill');

        $mform->addElement('advcheckbox', 'showanswers', get_string('showanswers', 'qtype_gapfill'));
        $mform->addHelpButton('showanswers', 'showanswers', 'qtype_gapfill');

        $mform->addElement('advcheckbox', 'casesensitive', get_string('casesensitive', 'qtype_gapfill'));

        $mform->addHelpButton('casesensitive', 'casesensitive', 'qtype_gapfill');

           $mform->addElement('text', 'wronganswers', get_string('wronganswers', 'qtype_gapfill'),
                array('size' => 70));
        $mform->addHelpButton('wronganswers', 'wronganswers', 'qtype_gapfill');

        $mform->setType('wronganswers', PARAM_TEXT);
        $mform->addElement('editor', 'generalfeedback', get_string('generalfeedback', 'question'), array('rows' => 10),
                $this->editoroptions);

        $mform->setType('generalfeedback', PARAM_RAW);
        $mform->addHelpButton('generalfeedback', 'generalfeedback', 'question');

        // To add combined feedback (correct, partial and incorrect).
        $this->add_combined_feedback_fields(true);

        // Adds hinting features.
        $this->add_interactive_settings();
    }

    public function set_data($question) {
        $question->answer = $this->answer;
        $question->showanswers = $this->showanswers;
        $question->delimitchars = $this->delimitchars;

        parent::set_data($question);
    }

    protected function data_preprocessing($question) {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_combined_feedback($question);
        $question = $this->data_preprocessing_hints($question);

        if (!empty($question->options)) {
            $question->showanswers = $question->options->showanswers;
        }
        return $question;
    }

    public function validation($fromform, $data) {
        $errors = array();
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