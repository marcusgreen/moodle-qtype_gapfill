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
 * JavaScript code for parsing gapfill question text.
 *
 * @copyright  2025 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define([], function() {
  /**
   * Escape special regex characters in a string
   * @param {string} str - String to escape
   * @returns {string} - Escaped string
   */
  var escapeRegex = function(str) {
    return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  };

  /**
   * Get the value from an input element by its ID
   * @param {string} id - Element ID
   * @returns {string|null} - Element value or null if not found
   */
  var getElementValue = function(id) {
    var element = document.getElementById(id);
    return element ? element.value : null;
  };

  /**
   * Parse question text and wrap gaps in spans with unique IDs
   *
   * The function generates IDs based on two factors:
   * 1. Position in text (first number): id1, id2, id3, etc.
   * 2. Occurrence count of each unique gap content (second number): _0, _1, _2, etc.
   *
   * Example:
   * Input:  "The big [cat] met the small [cat]"
   * Output: "The big <span id="id1_0">[cat]</span> met the small <span id="id2_1">[cat]</span>"
   *
   * Explanation:
   * - First [cat]: id1_0 (position 1, first occurrence of "cat")
   * - Second [cat]: id2_1 (position 2, second occurrence of "cat")
   *
   * @param {string} questionText - Raw question text containing gaps
   * @returns {string} - Processed HTML string with gaps wrapped in spans
   */
  var parseQuestionText = function(questionText) {
    var delimitchars = getElementValue('id_delimitchars');

    if (!delimitchars || delimitchars.length !== 2) {
      return questionText;
    }

    var leftDelim = delimitchars.charAt(0);
    var rightDelim = delimitchars.charAt(1);
    var processedText = questionText;
    var gapCounter = 0;
    var gapContentCounts = new Map();

    // Find all occurrences of text within delimiters
    var regex = new RegExp(
      escapeRegex(leftDelim) + '(.*?)' + escapeRegex(rightDelim),
      'g'
    );

    processedText = processedText.replace(regex, function(match, gapContent) {
      gapCounter++;

      // Track occurrence count for this specific gap content
      var currentCount = gapContentCounts.get(gapContent) || 0;
      gapContentCounts.set(gapContent, currentCount + 1);

      // Create ID: position_counter (e.g., id1_0, id2_1)
      var spanId = 'id' + gapCounter + '_' + currentCount;
      return '<span id="' + spanId + '">' + match + '</span>';
    });

    return processedText;
  };

  /**
   * Determines if click was within a gap span and extracts gap information
   *
   * @param {Event} clickEvent - The click event to analyze
   * @returns {Object|null} - Object with gapId and gapText, or null if not a gap click
   */
  var get_gap = function(clickEvent) {
    // Get the target element from the click event
    var target = clickEvent.target;

    // Check if the target or any of its parents is a gap span
    var gapSpan = target;
    while (gapSpan && gapSpan.tagName !== 'SPAN') {
      gapSpan = gapSpan.parentNode;
    }

    // If we found a span, check if it has an id attribute starting with 'id'
    if (gapSpan && gapSpan.id && gapSpan.id.startsWith('id')) {
      return {
        gapId: gapSpan.id,
        gapText: gapSpan.textContent || gapSpan.innerText
      };
    }

    // Not a gap click
    return null;
  };

  return {
    parseQuestionText: parseQuestionText,
    get_gap: get_gap
  };
});