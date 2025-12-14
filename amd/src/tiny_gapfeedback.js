/**
 * TinyMCE gap feedback functionality for gapfill question type.
 *
 * @module qtype_gapfill/tiny_gapfeedback
 */
/**
 * Creates an editable div called gap_select_area and replaces the TinyMCE instance
 */
const createGapSelectArea = () => {
    /* global tinyMCE */
    const editor = tinyMCE.get('id_questiontext');
    const container = editor.getContainer();
    const tox = container.querySelector('.tox-edit-area');
    // Check if gap_select_area already exists (toggle back to TinyMCE)
    const existingGapSelectArea = document.getElementById('gap_select_area');
    if (existingGapSelectArea) {
        // Toggle back: remove gap_select_area and show TinyMCE
        existingGapSelectArea.remove();
        container.style.display = 'block';
        return;
    }
    // Get the actual editor dimensions from the edit area
    const editorElement = tox.querySelector('.tox-edit-area');
    const editorWidth = editorElement ? editorElement.offsetWidth : container.offsetWidth;
    const editorHeight = editorElement ? editorElement.offsetHeight : container.offsetHeight;
    // Create the gap_select_area div
    const gapSelectArea = document.createElement('div');
    gapSelectArea.id = 'gap_select_area';
    gapSelectArea.className = 'select_area';
    gapSelectArea.contentEditable = 'true';
    gapSelectArea.innerHTML = editor.getContent();
    // Apply dimensions
    gapSelectArea.style.width = editorWidth + 'px';
    gapSelectArea.style.height = editorHeight + 'px';
    // Insert the gap_select_area where the TinyMCE instance was
    container.parentNode.insertBefore(gapSelectArea, container.nextSibling);
    container.style.display = 'none';
};
/**
 * Retrieves and parses JSON from id_itemsettings hidden field
 * @returns {Object} Object with all gap feedback data, handles empty/missing data gracefully
 */
const getItemSettings = () => {
    const settingsElement = document.getElementById('id_itemsettings');
    if (!settingsElement || !settingsElement.value) {
        return {};
    }
    try {
        const parsed = JSON.parse(settingsElement.value);
        return parsed || {};
    } catch (e) {
        console.error('Error parsing item settings:', e);
        return {};
    }
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