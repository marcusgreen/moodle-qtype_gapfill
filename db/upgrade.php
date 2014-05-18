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
 * Multi-answer question type upgrade code.
 *
 * @package    qtype
 * @subpackage gapfill
 * @copyright  2912 Marcus Green 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade code for the gapfill question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_gapfill_upgrade($oldversion = 0) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();
    if ($oldversion < 2006082505) {

        /* some fractions may be zero which will confuse the new way of marking */
        $sql = "Update " . $CFG->prefix . "question_answers qa," . $CFG->prefix . "question q set
        qa.fraction='1' where q.id=qa.question and q.qtype='gapfill'";

        $DB->execute($sql);

        $rs = $DB->get_recordset_sql("SELECT wronganswers, question
                                        FROM {question_gapfill}");

        foreach ($rs as $gf) {
            if ($gf->wronganswers == '') {
                continue;
            }
            $wa = explode(",", $gf->wronganswers);
            foreach ($wa as $wronganswer) {
                $answer = new stdClass();
                $answer->question = $gf->question;
                $answer->answer = $wronganswer;
                $answer->feedback = '';
                $answer->fraction = 0;
                $answer->id = $DB->insert_record("question_answers", $answer);
            }
        }

        $DB->change_database_structure("ALTER TABLE " . $CFG->prefix . "question_gapfill drop column wronganswers");
        $DB->change_database_structure("ALTER TABLE " . $CFG->prefix . "question_gapfill drop column shuffledanswers");

        $sql = "ALTER TABLE " . $CFG->prefix . "question_gapfill add column noduplicates tinyint(1)
            default 1 after casesensitive   ";
        $DB->change_database_structure($sql);
        $DB->change_database_structure("ALTER TABLE " . $CFG->prefix . "question_gapfill add column
            'noduplicates' int(1) default 1 NULL ");
        $rs->close();
    }
    if ($oldversion == 2006082507) {
        $sql = "ALTER TABLE " . $CFG->prefix . "question_gapfill add column noduplicates tinyint(1) default 1 af
            ter casesensitive   ";
        $DB->change_database_structure($sql);
    }
    if ($oldversion < 2006082510) {
         // $sql = "ALTER TABLE " . $CFG->prefix . "question_gapfill add column disableregex tinyint(1)
         //   default 0 after noduplicates   ";
        $table = new xmldb_table('question_gapfill');  
        $field = new xmldb_field('disableregex', XMLDB_TYPE_INTEGER,null, null, null,
                                                                    null, null, '1');
        //$DB->change_database_structure($sql);

    }

    // Gapfill savepoint reached.
    upgrade_plugin_savepoint(true, 2006082512, 'qtype', 'gapfill');

    return;
}