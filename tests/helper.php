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
 * Contains the helper class for the select missing words question type tests.
 *
 * @package    qtype
 * @copyright  2012 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_gapfill_test_helper extends question_test_helper{
    public function get_test_questions() {
        /* must be implemented or class made abstract */
        return array('catmat');
    }
    public static function make_question($type) {

        question_bank::load_question_definition_classes($type);
        $question = new qtype_gapfill_question();

        test_question_maker::initialise_a_question($question);
        $question->qtype = question_bank::get_qtype('gapfill');

        $question->name = 'Gapfill Test Question';
        $question->questiontext = 'The [cat] sat on the [mat]';
        $question->textfragments=array('The ', ' sat on the ');

        $question->displayanswers = '1';
        $question->casesensitive = '1';
        $question->generalfeedback = 'congratulations on your knowledge of pets and floor covering';

        $question->places[1]='cat';
        $question->places[2]='mat';
        $answer1=new question_answer(43, 'cat', 4, 1, 1);
        $answer2=new question_answer(44, 'mat', 4, 1 , 1);
        $question->answers=array($answer1, $answer2);

        $question->options = new stdClass();

        $question->options->showanswers=false;
        $question->options->delimitchars="[]";
        $question->options->casesensitive=false;

        $question->options->correctfeedback="";
        $question->options->correctfeedbackformat="";
        $question->options->partiallycorrectfeedback="";
        $question->options->partiallycorrectfeedbackformat="";
        $question->options->incorrectfeedback="";
        $question->options->incorrectfeedbackformat="";

        $answers=new stdClass;

        $question->options->answers=array($answer1, $answer2);

        $question->hints = array(
            new question_hint(1, 'This is the first hint.', FORMAT_HTML),
            new question_hint(2, 'This is the second hint.', FORMAT_HTML),
        );
        return $question;

    }
    /**
     * Gets the question data for a shortanswer questionwith just the correct
     * ansewer 'frog', and no other answer matching.
     * @return stdClass
     */
    public function get_gapfill_question_data_catmat() {
        $qdata = new stdClass();
        test_question_maker::initialise_question_data($qdata);

        $qdata->qtype = 'gapfill';
        $qdata->name = 'catmat';
        $qdata->questiontext = 'The [cat] sat on the [mat]';
        $qdata->generalfeedback = 'someanswer';

        $qdata->options = new stdClass();
        $qdata->options->casesensitive = false;

        return $qdata;
    }
}
