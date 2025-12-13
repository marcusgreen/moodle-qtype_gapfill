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
 * JavaScript code for the gapfill question type.
 *
 * @copyright  2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* The data is stored in a hidden field */
define(['jquery'], function($) {
  return {
    init: function() {
      $('#id_answerdisplay').change(function() {
        var selected = $(this).val();
        if (selected == 'gapfill') {
          $('#id_fixedgapsize').prop('disabled', false);
          $("#id_optionsaftertext").prop('disabled', true).prop('checked', false);
          $('#id_singleuse').prop('disabled', true).prop('checked', false);
          $('#id_disableregex').prop('disabled', false);

        }
        if (selected == 'dragdrop') {
          $('#id_optionsaftertext').prop('disabled', false);
          $('#id_singleuse').prop('disabled', false);
          $('#id_fixedgapsize').prop('disabled', false);
          $('#id_disableregex').prop('disabled', false);
        }
        if (selected == 'dropdown') {
          $('#id_fixedgapsize').prop('disabled', true).prop('checked', false);
          $('#id_optionsaftertext').prop('disabled', true).prop('checked', false);
          $('#id_singleuse').prop('disabled', true).prop('checked', false);
          $('#id_disableregex').prop('disabled', true).prop('checked', false);
        }


      });
    },
  };
});
