/**
 * TinyMCE gap feedback functionality for gapfill question type.
 *
 * @module qtype_gapfill/tiny_gapfeedback
 */
import * as Log from 'core/log';

// Import functions from gaps.js using ES6 import
import { parseQuestionText, get_gap, showGapSettingsModal } from 'qtype_gapfill/gaps';
/**
 * Retrieves and parses item settings, returning the specific feedback
 * (correctfeedback and incorrectfeedback) for a given itemid.
 *
 * @param {object} gapInfo - Object containing the search criteria.
 * @param {string} gapInfo.itemid - The unique ID of the item to find (e.g., "id2_0").
 * @returns {?{correctfeedback: string, incorrectfeedback: string}} Feedback object, or null on error/not found.
 */
const getItemSettings = (gapInfo) => {
    const settingsElement = document.getElementById('id_itemsettings');
    if (!settingsElement || !settingsElement.value) {
        return null;
    }

    let parsedSettings = {};

    parsedSettings = JSON.parse(settingsElement.value) || {};

    const searchId = gapInfo && gapInfo.gapId;
    if (!searchId) {
        return null;
    }

    const innerObjects = Object.values(parsedSettings);

    const foundObject = innerObjects.find(
        item => item.itemid === searchId
    );

    if (foundObject) {
        return {
            correctFeedback: foundObject.correctfeedback,
            incorrectFeedback: foundObject.incorrectfeedback
        };
    } else {
        return null;
    }
};


/**
 * Handle click events on the select area
 * @param {Event} event - The click event
 * @param {HTMLElement} selectArea - The select area element
 */
const handleSelectAreaClick = (event) => {

        // Use get_gap to check if the click was within a gap
        const gapInfo = get_gap(event);

        if (gapInfo) {
            showGapSettingsModal(gapInfo);
        }
};


/**
 * Creates an editable div called select_area and replaces the TinyMCE instance
 */
const createGapSelectArea = () => {
    /* global tinyMCE */
    const editor = tinyMCE.get('id_questiontext');
    const container = editor.getContainer();
    const tox = container.querySelector('.tox-edit-area');
    // Check if select_area already exists (toggle back to TinyMCE)
    const existingSelectArea = document.getElementById('select_area');
    if (existingSelectArea) {
        // Toggle back: remove select_area and show TinyMCE
        existingSelectArea.remove();
        container.style.display = 'block';
        return;
    }
    // Get the actual editor dimensions from the edit area
    const editorElement = tox.querySelector('.tox-edit-area');
    const editorWidth = editorElement ? editorElement.offsetWidth : container.offsetWidth;
    const editorHeight = editorElement ? editorElement.offsetHeight : container.offsetHeight;
    // Get the text content from TinyMCE
    const questionText = editor.getContent();
    // Process the text with parseQuestionText to wrap gaps in spans
    const processedText = parseQuestionText(questionText);
    // Create the select_area div
    const selectArea = document.createElement('div');
    selectArea.id = 'select_area';
    selectArea.className = 'select_area';
    selectArea.contentEditable = 'true';
    selectArea.innerHTML = processedText;
    // Apply dimensions
    selectArea.style.width = editorWidth + 'px';
    selectArea.style.height = editorHeight + 'px';
    // Insert the select_area where the TinyMCE instance was
    container.parentNode.insertBefore(selectArea, container.nextSibling);
    container.style.display = 'none';

    // Attach event handler to the select area
    selectArea.addEventListener('click', (event) => handleSelectAreaClick(event));
};


/**
 * Initialize the gap feedback functionality
 */
export const init = async() => {
    // Wait for the button element to be ready in the DOM
    const button = document.getElementById('id_itemsettings_button');
    if (button) {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            createGapSelectArea();
        });
    }
};
