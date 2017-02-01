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

    /* data used by export_to_xml (among other things possibly */

    public function extra_question_fields() {
        return array('question_gapfill', 'answerdisplay', 'delimitchars', 'casesensitive',
            'noduplicates', 'disableregex', 'fixedgapsize');
    }

    /**
     * Utility method used by {@link qtype_renderer::head_code()}
     * It looks for any of the files script.js or script.php that
     * exist in the plugin folder and ensures they get included.
     * It also includes the jquery files required for this plugin
     */
    public function find_standard_scripts() {
        global $CFG, $PAGE;

        // Include "script.js" and/or "script.php" in the normal way.
        parent::find_standard_scripts();

        $version = '';
        $minversion = '1.11.0'; // Moodle 2.7.
        $search = '/jquery-([0-9.]+)(\.min)?\.js$/';

        // ...make sure jQuery version is high enough.
        // (required if Quiz is in a popup window)
        // Moodle 2.5 has jQuery 1.9.1
        // Moodle 2.6 has jQuery 1.10.2
        // Moodle 2.7 has jQuery 1.11.0
        // Moodle 2.8 has jQuery 1.11.1
        // Moodle 2.9 has jQuery 1.11.1.
        if (method_exists($PAGE->requires, 'jquery')) {
            // Moodle >= 2.5.
            if ($version == '') {
                include($CFG->dirroot . '/lib/jquery/plugins.php');
                if (isset($plugins['jquery']['files'][0])) {
                    if (preg_match($search, $plugins['jquery']['files'][0], $matches)) {
                        $version = $matches[1];
                    }
                }
            }
            if ($version == '') {
                $filename = $CFG->dirroot . '/lib/jquery/jquery*.js';
                foreach (glob($filename) as $filename) {
                    if (preg_match($search, $filename, $matches)) {
                        $version = $matches[1];
                        break;
                    }
                }
            }
            if (version_compare($version, $minversion) < 0) {
                $version = '';
            }
        }

        // ...include jquery files.
        if ($version) {
            // Moodle >= 2.7.
            $PAGE->requires->jquery();
            $PAGE->requires->jquery_plugin('ui');
            $PAGE->requires->jquery_plugin('ui.touch-punch', 'qtype_gapfill');
        } else {
            // Moodle <= 2.6.
            $jquery = '/question/type/' . $this->name() . '/jquery';
            $PAGE->requires->js($jquery . '/jquery-1.9.1.min.js', true);
            $PAGE->requires->js($jquery . '/jquery-ui-1.11.4.min.js', true);
            $PAGE->requires->js($jquery . '/jquery-ui.touch-punch.js', true);
        }
    }

    /**
     *
     * @global type moodle_database $DB
     * @param type $question
     */
    public function get_question_options($question) {
        global $DB;
        $question->options = $DB->get_record('question_gapfill', array('question' => $question->id), '*', MUST_EXIST);
        parent::get_question_options($question);
    }

    /* called when previewing or at runtime in a quiz */

    protected function initialise_question_answers(question_definition $question, $questiondata, $forceplaintextanswers = true) {
        $question->answers = array();
        if (empty($questiondata->options->answers)) {
            return;
        }

        foreach ($questiondata->options->answers as $a) {
            if (strstr($a->fraction, '1') == false) {
                /* if this is a wronganswer/distractor strip any
                 * backslashes, this allows escaped backslashes to
                 * be used i.e. \, and not displayed in the draggable
                 * area
                 */
                $a->answer = stripslashes($a->answer);
            }
            if (!in_array($a->answer, $question->allanswers, true)) {
                array_push($question->allanswers, $a->answer);
            }
            /* answer in this context means correct answers, i.e. where
             * fraction contains a 1 */
            if (strpos($a->fraction, '1') !== false) {
                $question->answers[$a->id] = new question_answer($a->id, $a->answer, $a->fraction,
                        $a->feedback, $a->feedbackformat);
                $question->gapcount++;
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
        $question->maxgapsize = 0;
        foreach ($questiondata->options->answers as $choicedata) {
            /* find the width of the biggest gap */
            $len = $question->get_size($choicedata->answer);
            if ($len > $question->maxgapsize) {
                $question->maxgapsize = $len;
            }

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

        $nongapregex = '/\\' . $l . '.*?\\' . $r . '/';
        $nongaptext = preg_split($nongapregex, $question->questiontext, null, PREG_SPLIT_DELIM_CAPTURE);
        $i = 0;
        while (!empty($nongaptext)) {
            $question->textfragments[$i] = array_shift($nongaptext);
            $i++;
        }
    }

    /**
     * @param type $question The current question
     * @param type $form The question editing form data
     * @return type object
     * Sets the default mark as 1* the number of gaps
     * Does not allow setting any other value per space/field at the moment
     */
    public function save_question($question, $form) {
        $gaps = $this->get_gaps($form->delimitchars, $form->questiontext['text']);
        /* count the number of gaps
         * this is used to set the maximum
         * value for the whole question. Value for
         * each gap can be only 0 or 1
         */
        $ua = array_unique($gaps);
        $form->defaultmark = count($gaps);
        return parent::save_question($question, $form);
    }

    /* chop the delimit string into a two element array
     * this might be better done on initialisation
     */

    public static function get_delimit_array($delimitchars) {
        $delimitarray = array();
        $delimitarray["l"] = substr($delimitchars, 0, 1);
        $delimitarray["r"] = substr($delimitchars, 1, 1);
        return $delimitarray;
    }

    /* it really does need to be static */

    public static function get_gaps($delimitchars, $questiontext) {
        /* l for left delimiter r for right delimiter
         * defaults to []
         * e.g. l=[ and r=] where question is
         * The [cat] sat on the [mat]
         */
        $delim = self::get_delimit_array($delimitchars);
        $fieldregex = '/.*?\\' . $delim["l"] . '(.*?)\\' . $delim["r"] . '/';
        $matches = array();
        preg_match_all($fieldregex, $questiontext, $matches);
        return $matches[1];
    }

    /**
     * Save the units and the answers associated with this question.
     * @return boolean to indicate success or failure.
     * @global moodle_database $DB;
     */
    public function save_question_options($question) {
        /* Save the extra data to your database tables from the
          $question object, which has all the post data from editquestion.html */
        $gaps = $this->get_gaps($question->delimitchars, $question->questiontext);
        /* answerwords are the text within gaps */
        $answerfields = $this->get_answer_fields($gaps, $question);
        global $DB;

        $context = $question->context;
        // Fetch old answer ids so that we can reuse them.
        $this->update_question_answers($question, $answerfields);

        $options = $DB->get_record('question_gapfill', array('question' => $question->id));
        $this->update_question_gapfill($question, $options, $context);
        $this->save_hints($question, true);
        return true;
    }

    /* runs from question editing form */

    public function update_question_gapfill($question, $options, $context) {
        global $DB;
        $options = $DB->get_record('question_gapfill', array('question' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->question = $question->id;
            $options->correctfeedback = '';
            $options->partiallycorrectfeedback = '';
            $options->incorrectfeedback = '';
            $options->answerdisplay = '';
            $options->delimitchars = '';
            $options->casesensitive = '';
            $options->noduplicates = '';
            $options->disableregex = '';
            $options->id = $DB->insert_record('question_gapfill', $options);
        }
        $options->delimitchars = $question->delimitchars;
        $options->answerdisplay = $question->answerdisplay;
        $options->casesensitive = $question->casesensitive;
        $options->noduplicates = $question->noduplicates;
        $options->disableregex = $question->disableregex;
        $options->fixedgapsize = $question->fixedgapsize;
        $options = $this->save_combined_feedback_helper($options, $question, $context, true);
        $DB->update_record('question_gapfill', $options);
    }

    /**
     *
     * @global moodle_database $DB
     * @param type $question
     * @param array $answerfields
     */
    public function update_question_answers($question, array $answerfields) {
        global $DB;
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
                $answer->fraction = $field['fraction'];
                $answer->id = $DB->insert_record('question_answers', $answer);
            }
        }
        // Delete old answer records.
        foreach ($oldanswers as $oa) {
            $DB->delete_records('question_answers', array('id' => $oa->id));
        }
    }

    /**
     * Set up all the answer fields with respective fraction (mark values)
     * This is used to update the question_answers table. Answerwords has
     * been pulled from within the delimitchars e.g. the cat within [cat]
     * Wronganswers (distractors) has been pulled from a comma delimited edit
     * form field
     *
     * @param array $answerwords
     * @param type $question
     * @return type array
     */
    public function get_answer_fields(array $answerwords, $question) {
        /* this code runs both on saving from a form and from importing and needs
         * improving as it mixes pulling information from the question object which
         * comes from the import and from $question->wronganswers field which
         * comes from the question_editing form.
         */
        $answerfields = array();
        /* this next block runs when importing from xml */
        if (property_exists($question, 'answer')) {
            foreach ($question->answer as $key => $value) {
                if ($question->fraction[$key] == 0) {
                    $answerfields[$key]['value'] = $question->answer[$key];
                    $answerfields[$key]['fraction'] = 0;
                } else {
                    $answerfields[$key]['value'] = $question->answer[$key];
                    $answerfields[$key]['fraction'] = 1;
                }
            }
        }

        /* the rest of this function runs when saving from edit form */
        if (!property_exists($question, 'answer')) {
            foreach ($answerwords as $key => $value) {
                $answerfields[$key]['value'] = $value;
                $answerfields[$key]['fraction'] = 1;
            }
        }
        if (property_exists($question, 'wronganswers')) {
            if ($question->wronganswers['text'] != '') {
                /* split by commas and trim white space */
                $wronganswers = array_map('trim', explode(',', $question->wronganswers['text']));
                $regex = '/(.*?[^\\\\](\\\\\\\\)*?),/';
                $wronganswers = preg_split($regex, $question->wronganswers['text'], -1,
                        PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                $wronganswerfields = array();
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
        global $CFG;
        $pluginmanager = core_plugin_manager::instance();
        $gapfillinfo = $pluginmanager->get_plugin_info('qtype_gapfill');
        $output = parent::export_to_xml($question, $format);
        $output .= '    <delimitchars>' . $question->options->delimitchars .
                "</delimitchars>\n";
        $output .= '    <answerdisplay>' . $question->options->answerdisplay .
                "</answerdisplay>\n";
        $output .= '    <casesensitive>' . $question->options->casesensitive .
                "</casesensitive>\n";
        $output .= '    <noduplicates>' . $question->options->noduplicates .
                "</noduplicates>\n";
        $output .= '    <disableregex>' . $question->options->disableregex .
                "</disableregex>\n";
        $output .= '    <fixedgapsize>' . $question->options->fixedgapsize .
                "</fixedgapsize>\n";
        $output .= '    <!-- Gapfill release:'
                . $gapfillinfo->release . ' version:' . $gapfillinfo->versiondisk . ' Moodle version:'
                . $CFG->version . ' release:' . $CFG->release
                . " -->\n";
        $output .= $format->write_combined_feedback($question->options, $question->id, $question->contextid);
        return $output;
    }

}
