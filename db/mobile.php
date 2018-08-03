<?php
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
 * Gapfill question type  capability definition
 *
 * @package    qtype_gapfill
 * @copyright  2016 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

global $CFG;
$addons = array(
    "AddonQtypeGapfill" => array(
        "handlers" => array( // Different places where the add-on will display content.
            'gapfill' => array( // Handler unique name (can be anything)
                'displaydata' => array(
                    'title' => 'Gapfill mavg',
                    'icon' => $CFG->wwwroot . '/question/type/gapfill/pix/icon.gif',
                    'class' => 'qgapfill',
                ),
                'delegate' => 'CoreQuestionDelegate', // Delegate (where to display the link to the add-on)
                'method' => 'mobile_get_gapfill', 
                'offlinefunctions' => array(
                    'mobile_get_gapfill' => array(),
                    'mobile_get_gapfill' => array(),
                ), // Function needs caching for offline.
                  'styles' => array(
                    'url' => $CFG->wwwroot . '/question/type/gapfill/styles_app.scss',
                    'version' => '0.2'
                ),
            )
        ),
          'lang' => array(
            array('pluginname', 'Gapfill question')
        )
        
    )
);
