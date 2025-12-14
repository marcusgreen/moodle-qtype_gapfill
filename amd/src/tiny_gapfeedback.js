/**
 * TinyMCE gap feedback functionality for gapfill question type.
 *
 * @module qtype_gapfill/tiny_gapfeedback
 */
import {getTinyMCE} from 'editor_tiny/loader';
import Log from 'core/log';

/**
 * Creates an editable div called gap_select_area and replaces the TinyMCE instance
 */
const createGapSelectArea = () => {
    try {
        // Get the TinyMCE instance for questiontext using the Moodle loader

        const editor = tinyMCE.get('id_questiontext');

        if (!editor) {
            alert('TinyMCE instance for id_questiontext not found');
            return;
        }

        // Get the TinyMCE editor container element using the correct API
        const editorContainer = editor.getContainer ? editor.getContainer() : editor.container;

        if (!editorContainer) {
            alert('TinyMCE instance for id_questiontext not found');
            return;
        }
        // Get dimensions of the TinyMCE instance
        const editorElement = editorContainer.querySelector('.tox-edit-area');
        const editorWidth = editorElement ? editorElement.offsetWidth : editorContainer.offsetWidth;
        const editorHeight = editorElement ? editorElement.offsetHeight : editorContainer.offsetHeight;

        // Hide the TinyMCE instance
        editorContainer.style.display = 'none';

        // Create the gap_select_area div
        const gapSelectArea = document.createElement('div');
        gapSelectArea.id = 'gap_select_area';
        gapSelectArea.className = 'gap_select_area';
        gapSelectArea.contentEditable = 'true';
        gapSelectArea.innerHTML = editor.getContent();

        // Apply dimensions
        gapSelectArea.style.width = editorWidth + 'px';
        gapSelectArea.style.height = editorHeight + 'px';

        // Insert the gap_select_area where the TinyMCE instance was
        editorContainer.parentNode.insertBefore(gapSelectArea, editorContainer.nextSibling);
    } catch (error) {
        Log.error('Error creating gap select area:', error);
    }
};

/**
 * Retrieves and parses JSON from id_itemsettings hidden field
 * @returns {Object} Object with all gap feedback data, handles empty/missing data gracefully
 */
const getItemSettings = () => {
    const settingsElement = document.getElementById('id_itemsettings');
    const parsed = JSON.parse(settingsElement.value);
    return parsed || {};
};

/**
 * Initialize the gap feedback functionality
 */
export const init = async() => {createGapSelectArea
    alert('init');
    // 1. Get the item settings (runs immediately)
    //const itemSettings = getItemSettings();

    // // 2. Wait for the button element to be ready in the DOM
    const button = document.getElementById('id_itemsettings_button')

        button.addEventListener('click', (event) => {
            createGapSelectArea();
        });

};