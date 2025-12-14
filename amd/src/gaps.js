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
 * @module qtype_gapfill/gaps
 * @copyright  2025 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * Show gap settings modal for a specific gap
 * @param {string} gapText - The text content of the gap
 */
import ModalFactory from 'core/modal_factory';
import ModalEvents from 'core/modal_events';

const showGapSettingsModal = async(gapText) => {
    const bodyContent = `
        <div class="container-fluid">
            <div class="form-group row mb-3">
                <label for="gapfill-feedback-correct" class="col-md-12 col-form-label font-weight-bold">Feedback
                    for correct.</label>
                <div class="col-md-12">
                    <textarea id="gapfill-feedback-correct" class="form-control" rows="6"></textarea>
                </div>
            </div>
            <div class="form-group row mb-3">
                <label for="gapfill-feedback-incorrect" class="col-md-12 col-form-label font-weight-bold">Feedback
                    for incorrect.</label>
                <div class="col-md-12">
                    <textarea id="gapfill-feedback-incorrect" class="form-control" rows="6"></textarea>
                </div>
            </div>
        </div>
    `;

    // Create and show modal using ModalFactory
    const modal = await ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: `Add Gap settings: ${gapText}`,
        body: bodyContent,
        large: true,
    });

    // Show the modal
    modal.show();


    // After modal is shown, initialize TinyMCE editors for the feedback fields
    modal.getRoot().on(ModalEvents.shown, async() => {
        // Wait a moment for DOM to be ready
        await new Promise(resolve => setTimeout(resolve, 200));

            // Get the TinyMCE instance from the global scope
             /* global tinyMCE */
            // Clean up any existing TinyMCE instances for these elements
            const correctEditor = tinyMCE.get('gapfill-feedback-correct');
            if (correctEditor) {
                correctEditor.remove();
            }

            const incorrectEditor = tinyMCE.get('gapfill-feedback-incorrect');
            if (incorrectEditor) {
                incorrectEditor.remove();
            }

            const settings = getItemSettings();
            const firstKey = Object.keys(settings)[0];
            const correctFeedback = settings[firstKey].correctfeedback;
            const incorrectFeedback = settings[firstKey].incorrectfeedback;


            // Initialize TinyMCE for feedback correct - this creates a new instance
            await tinyMCE.init({
                selector: '#gapfill-feedback-correct',
                menubar: false,
                toolbar: 'undo redo | formatselect | bold italic | bullist numlist | link unlink',
                plugins: 'lists link',
                setup: (ed) => {
                    ed.on('init', () => {
                        ed.setContent(correctFeedback);
                    });
                }
            });

            // Initialize TinyMCE for feedback incorrect - this creates another new instance
            await tinyMCE.init({
                selector: '#gapfill-feedback-incorrect',
                menubar: false,
                toolbar: 'undo redo | formatselect | bold italic | bullist numlist | link unlink',
                plugins: 'lists link',
                setup: (ed) => {
                    ed.on('init', () => {
                        ed.setContent(incorrectFeedback);
                    });
                }
            });

    });

    // Clean up TinyMCE instances when modal is hidden
    modal.getRoot().on(ModalEvents.hidden, () => {
        /* global tinyMCE */
        if (tinyMCE) {
            const correctEditor = tinyMCE.get('gapfill-feedback-correct');
            if (correctEditor) {
                correctEditor.remove();
            }

            const incorrectEditor = tinyMCE.get('gapfill-feedback-incorrect');
            if (incorrectEditor) {
                incorrectEditor.remove();
            }
        }
    });
};


/**
 * Escape special regex characters in a string
 * @param {string} str - String to escape
 * @returns {string} - Escaped string
 */
const escapeRegex = (str) => {
  return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
};

/**
 * Get the value from an input element by its ID
 * @param {string} id - Element ID
 * @returns {string|null} - Element value or null if not found
 */
const getElementValue = (id) => {
  const element = document.getElementById(id);
  return element ? element.value : null;
};

/**
 * Read existing itemsettings from the hidden field
 * @returns {Object} The parsed item settings object
 */
const getItemSettings = () => {
    const itemSettingsField = document.querySelector('#id_itemsettings');
    let existingSettings = {};
    if (itemSettingsField && itemSettingsField.value) {
        try {
            existingSettings = JSON.parse(itemSettingsField.value);
        } catch (e) {
            // If parsing fails, start with empty object
            existingSettings = {};
        }
    }
    return existingSettings;
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
 * Output: "The big <span id=\"id1_0\">[cat]</span> met the small <span id=\"id2_1\">[cat]</span>"
 *
 * Explanation:
 * - First [cat]: id1_0 (position 1, first occurrence of "cat")
 * - Second [cat]: id2_1 (position 2, second occurrence of "cat")
 *
 * @param {string} questionText - Raw question text containing gaps
 * @returns {string} - Processed HTML string with gaps wrapped in spans
 */
const parseQuestionText = (questionText) => {
  const delimitchars = getElementValue('id_delimitchars');

  if (!delimitchars || delimitchars.length !== 2) {
    return questionText;
  }

  const leftDelim = delimitchars.charAt(0);
  const rightDelim = delimitchars.charAt(1);
  let processedText = questionText;
  let gapCounter = 0;
  const gapContentCounts = new Map();

  // Find all occurrences of text within delimiters
  const regex = new RegExp(
    escapeRegex(leftDelim) + '(.*?)' + escapeRegex(rightDelim),
    'g'
  );

  processedText = processedText.replace(regex, (match, gapContent) => {
    gapCounter++;

    // Track occurrence count for this specific gap content
    const currentCount = gapContentCounts.get(gapContent) || 0;
    gapContentCounts.set(gapContent, currentCount + 1);

    // Create ID: position_counter (e.g., id1_0, id2_1)
    const spanId = 'id' + gapCounter + '_' + currentCount;
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
const get_gap = (clickEvent) => {
  // Get the target element from the click event
  let target = clickEvent.target;

  // Check if the target or any of its parents is a gap span
  let gapSpan = target;
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

export {
  parseQuestionText,
  get_gap,
  showGapSettingsModal
};
