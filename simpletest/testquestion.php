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
 * Unit tests for the gapfill question definition class.
 *
 * @package    qtype
 * @subpackage gapfill
 * @copyright  2012 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/gapfill/simpletest/helper.php');

require_once($CFG->dirroot . '/question/type/questionbase.php');

require_once($CFG->dirroot . '/question/type/gapfill/question.php');

require_once($CFG->dirroot . '/question/engine/simpletest/helpers.php');

/**
 * Unit tests for the short answer question definition class.
 *
 * @copyright  2008 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_gapfill_question_test extends UnitTestCase {

    public function test_gapfill_qtype() {
        //notice arse arse arse
        $question = qtype_gapfill_test_helper::make_a_gapfill_question();
        $this->assertEqual($question->field('1'), 'p1');
        $expected_data = array('p0' => 'raw_trimmed', 'p1' => 'raw_trimmed');
        $this->assertEqual($question->get_expected_data(), $expected_data);
        $this->assertEqual(is_string($question->get_Shuffled_Answers()), true);


        $response = array('p0' => 'cat', 'p1' => 'dog');
        list($fraction, $state) = $question->grade_response($response);

        //with two fields, if you have one wrong the score (fraction)
        //will be .5. Fraction is always a a fractional part of one.
        $this->assertEqual($fraction, .5);

        //grade_response(array $response) {
    }

    public function test_is_complete_response() {
        $question = qtype_gapfill_test_helper::make_a_gapfill_question();

        $response = array('p1' => 'cat', 'p2' => 'mat');

        $this->assertTrue($question->is_complete_response($response));

        $response = array('p1' => 'cat');
        $this->assertFalse($question->is_complete_response($response));

        $this->assertFalse($question->is_complete_response(array()));
    }

    public function test_is_correct_response() {
        $question = qtype_gapfill_test_helper::make_a_gapfill_question();
        $question->casesensitive = 0;
        $answergiven = 'CAT';
        $rightanswer = 'cat';
        $this->assertTrue($question->is_correct_response($answergiven, $rightanswer));

        $question->casesensitive = 1;
        $this->assertFalse($question->is_correct_response($answergiven, $rightanswer));

        $answergiven = 'dog';
        $this->assertFalse($question->is_correct_response($answergiven, $rightanswer));
    }

    public function test_get_right_choice_for_place() {
        $question = qtype_gapfill_test_helper::make_a_gapfill_question();
        $this->assertEqual($question->get_right_choice_for(0), 'cat');
        $this->assertNotEqual($question->get_right_choice_for(1), 'cat');
    }

    public function test_is_same_response() {
        $question = qtype_gapfill_test_helper::make_a_gapfill_question();
        $prevresponse = array();
        $newresponse = array('p1' => 'cat', 'p2' => 'mat');
        $this->assertFalse($question->is_same_response($prevresponse, $newresponse));
        $prevresponse = array('p1' => 'cat', 'p2' => 'mat');
        $newresponse = array('p1' => 'cat', 'p2' => 'mat');
        $this->assertTrue($question->is_same_response($prevresponse, $newresponse));
    }

}
