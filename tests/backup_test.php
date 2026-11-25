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

namespace qtype_gapfill;

use question_bank;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/course/externallib.php');

/**
 * Tests for the orderinging question type backup and restore logic.
 *
 * @package   qtype_gapfill
 * @copyright 2025 Marcus Green
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later.
 * @covers    \backup_qtype_gapfill_plugin
 * @covers    \restore_qtype_gapfill_plugin
 */
final class backup_test extends \advanced_testcase {
    /**
     * Duplicate quiz with a gapfill question, and check it worked.
     */
    public function test_duplicate_gapfill_question(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $coregenerator = $this->getDataGenerator();
        $questiongenerator = $coregenerator->get_plugin_generator('core_question');

        // Create a course with a page that embeds a question.
        $course = $coregenerator->create_course();
        $quiz = $coregenerator->create_module('quiz', ['course' => $course->id]);
        $quizcontext = \context_module::instance($quiz->cmid);

        $cat = $questiongenerator->create_question_category(['contextid' => $quizcontext->id]);
        $question = $questiongenerator->create_question('gapfill', 'catmat', ['category' => $cat->id, 'shownumcorrect' => null]);

        // Store some counts.
        $numquizzes = count(get_fast_modinfo($course)->instances['quiz']);
        $numgapfillquestions = $DB->count_records('question', ['qtype' => 'gapfill']);

        // Duplicate the page.
        duplicate_module($course, get_fast_modinfo($course)->get_cm($quiz->cmid));

        // Verify the copied quiz exists.
        $this->assertCount($numquizzes + 1, get_fast_modinfo($course)->instances['quiz']);
    }

    /**
     * Restoring onto an existing question maps answers to the existing answer ids.
     *
     * The answer text only matches after line endings are normalised, so this
     * exercises the fallback lookup in process_question_answer().
     */
    public function test_restore_maps_existing_answer_after_cleaning(): void {
        global $CFG, $DB;
        require_once($CFG->dirroot . '/question/type/gapfill/backup/moodle2/restore_qtype_gapfill_plugin.class.php');
        $this->resetAfterTest();

        $questiongenerator = $this->getDataGenerator()->get_plugin_generator('core_question');
        $cat = $questiongenerator->create_question_category();
        $question = $questiongenerator->create_question('gapfill', 'catmat', ['category' => $cat->id]);
        $answer = $DB->get_records('question_answers', ['question' => $question->id], 'id', '*', 0, 1);
        $answer = reset($answer);
        $DB->set_field('question_answers', 'answer', "two\r\nlines", ['id' => $answer->id]);

        $plugin = $this->getMockBuilder(\restore_qtype_gapfill_plugin::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get_old_parentid', 'get_new_parentid', 'get_mappingid', 'set_mapping'])
            ->getMock();
        $plugin->method('get_old_parentid')->willReturn(12345);
        $plugin->method('get_new_parentid')->willReturn($question->id);
        // The question already exists on the site, so it was not created by the restore.
        $plugin->method('get_mappingid')->willReturn(false);

        // The answer id in the backup file differs from the id of the existing answer.
        $backupanswerid = $answer->id + 1000;
        $plugin->expects($this->once())
            ->method('set_mapping')
            ->with('question_answer', $backupanswerid, $answer->id);

        $plugin->process_question_answer([
            'id' => $backupanswerid,
            'answertext' => "two\nlines",
            'fraction' => '1.0000000',
        ]);
    }
}
