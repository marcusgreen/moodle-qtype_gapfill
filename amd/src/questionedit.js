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
 * @copyright  2025 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Module for setting up the quesiton editing page
 *
 * @module     qtype_gapfill/questionedit
 */
import Log from 'core/log';

/**
 *  Initialize the question edit functionality.
 *  @param {string} preferredEditor any
 *  @method init
 *  @returns void
 */
export const init = (preferredEditor) => {
  document.getElementById('id_answerdisplay').addEventListener('change', function() {
    // Use const instead of var.
    const selected = this.value;

    // ... (The change event listener logic remains the same)
    if (selected == 'gapfill') {
      document.getElementById('id_fixedgapsize').disabled = false;
      document.getElementById("id_optionsaftertext").disabled = true;
      document.getElementById("id_optionsaftertext").checked = false;
      document.getElementById('id_singleuse').disabled = true;
      document.getElementById('id_singleuse').checked = false;
      document.getElementById('id_disableregex').disabled = false;

    }
    if (selected == 'dragdrop') {
      document.getElementById('id_optionsaftertext').disabled = false;
      document.getElementById('id_singleuse').disabled = false;
      document.getElementById('id_fixedgapsize').disabled = false;
      document.getElementById('id_disableregex').disabled = false;
    }
    if (selected == 'dropdown') {
      document.getElementById('id_fixedgapsize').disabled = true;
      document.getElementById('id_fixedgapsize').checked = false;
      document.getElementById('id_optionsaftertext').disabled = true;
      document.getElementById('id_optionsaftertext').checked = false;
      document.getElementById('id_singleuse').disabled = true;
      document.getElementById('id_singleuse').checked = false;
      document.getElementById('id_disableregex').disabled = true;
      document.getElementById('id_disableregex').checked = false;
    }

  });

    if (preferredEditor === 'atto') {
      import('qtype_gapfill/atto_gapfeedback').then(module => {
        return module.init();
      }).catch(error => { // Use arrow function
        Log.error('qtype_gapfill: Error loading atto_gapfeedback module');
        Log.debug(error);
        throw error;
      });
    } else if (preferredEditor === 'tiny') { // Use strict comparison ===
      import('qtype_gapfill/tiny_gapfeedback').then(module => {
        return module.init();
      }).catch(error => { // Use arrow function
        Log.error('qtype_gapfill: Error loading tiny_gapfeedback module');
        Log.debug(error);
        throw error;
      });
    } else {
        // Optional: Add a log warning if no editor is found.
       // Log.warn('qtype_gapfill: Could not reliably detect active editor.');
       // Or hide the button in the event someone has plain text editing enabled.
    }
};