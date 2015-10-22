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
 * @package    qtype_gapfill
 * @copyright  2013 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox('qtype_gapfill/disableregex',
        get_string('disableregex', 'qtype_gapfill'),
        get_string('disableregexset_text', 'qtype_gapfill'), 0));
    $settings->add(new admin_setting_configcheckbox('qtype_gapfill/fixedgapsize',
        get_string('fixedgapsize', 'qtype_gapfill'),
        get_string('fixedgapsizeset_text', 'qtype_gapfill') , 0));
    $settings->add(new admin_setting_configcheckbox('qtype_gapfill/casesensitive',
        get_string('casesensitive', 'qtype_gapfill'),
        get_string('casesensitive_text', 'qtype_gapfill') , 0));
    $settings->add(new admin_setting_configtextarea('qtype_gapfill/delimitchars',
         get_string('delimitset', 'qtype_gapfill'),
         get_string('delimitset_text', 'qtype_gapfill'),
         "[ ],{ },# #,@ @", PARAM_RAW, 20, 3));
}
