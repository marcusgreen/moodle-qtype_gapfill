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
 * Multiple choice question type upgrade code.
 *
 * @package    qtype
 * @subpackage multichoice
 * @copyright  1999 onwards Martin Dougiamas {@link http://moodle.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Upgrade code for the gapfill question type.
 * @param int $oldversion the version we are upgrading from.
 */
function xmldb_qtype_gapfill_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

 //don't run this code without looking more closely
 return;

        // Define table question_gapselect to be created
        $table = new xmldb_table('question_gapfill');

        // Adding fields to table gapfill
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
       
        // Adding keys to table question_gapselect
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('questionid', XMLDB_KEY_FOREIGN, array('questionid'), 'question', array('id'));
		
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

    return true;
}
