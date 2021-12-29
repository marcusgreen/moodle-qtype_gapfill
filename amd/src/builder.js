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
 * @copyright  2020 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
export const init = () => {
  debugger;
  var gaps = document.querySelectorAll('input[id*="_p"].droptarget');
  gaps.forEach(item => {
    item.addEventListener('dblclick', event => {
      item.value = "";
    });
  });

  document.querySelectorAll('span.draggable.answers').forEach(item => {
    item.addEventListener('click', event => {
      var answeroption = event.currentTarget.textContent;
      for (let i = 0; i < gaps.length; i++) {
        if (!gaps[i].value) {
          gaps[i].value = answeroption;
          return;
        }
      }
    });
  });
};