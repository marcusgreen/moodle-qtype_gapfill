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
 * JavaScript code for handling the gap settings modal.
 *
 * @module qtype_gapfill/modal
 * @copyright  2025 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import ModalSaveCancel from 'core/modal_save_cancel';
import ModalEvents from 'core/modal_events';
import Log from 'core/log';
import Templates from 'core/templates';
import {getInstanceForElementId, setupForTarget} from 'editor_tiny/editor';
import {getContextId, getCurrentLanguage, getMoodleLang} from 'editor_tiny/options';

// Import functions from gaps.js that are needed
import {
    updateJson,
    getItemSettings,
    stripdelim,
    updateGapClasses,
    updateGapClassesFromSettings
} from 'qtype_gapfill/gaps';

/**
 * Build editor_tiny options for the feedback editors.
 *
 * Settings that core normally supplies from PHP are copied from the question text editor.
 * Only the TinyMCE lists and link plugins are loaded: the feedback is stored as JSON in the
 * itemsettings field, so file and media plugins could not save their files.
 *
 * @returns {Object} options for editor_tiny/editor setupForTarget
 */
const getEditorOptions = () => {
    const source = getInstanceForElementId('id_questiontext');
    if (!source) {
        return {plugins: {lists: {}, link: {}}};
    }
    return {
        css: source.options.get('content_css')[0],
        context: getContextId(source),
        currentLanguage: getCurrentLanguage(source),
        language: getMoodleLang(source),
        branding: source.options.get('branding'),
        // eslint-disable-next-line camelcase
        extended_valid_elements: source.options.get('extended_valid_elements'),
        filepicker: {},
        draftitemid: 0,
        placeholderSelectors: [],
        plugins: {lists: {}, link: {}},
    };
};

/**
 * Show gap settings modal for a specific gap
 * @param {Object} gapInfo - Object containing gap information
 * @param {string} gapInfo.gapId - Unique identifier for the gap
 * @param {string} gapInfo.gapText - The text content of the gap
 */
const showGapSettingsModal = async(gapInfo) => {
    // Get language strings
    const correctLabel = M.util.get_string('correct', 'qtype_gapfill');
    const incorrectLabel = M.util.get_string('incorrect', 'qtype_gapfill');

    // Get delimiter characters and strip them from gap text for cleaner display
    const delimitcharsElement = document.getElementById('id_delimitchars');
    const delimitchars = delimitcharsElement ? delimitcharsElement.value : '[]';
    const cleanGapText = stripdelim(gapInfo.gapText, delimitchars);
    const titleString = M.util.get_string('additemsettings', 'qtype_gapfill') + ': ' + cleanGapText;

    // Render the Mustache template with language strings
    const templateContext = {
        correct: correctLabel,
        incorrect: incorrectLabel
    };
    const bodyContent = await Templates.render('qtype_gapfill/gapfeedback_modal', templateContext);

    // Create modal using ModalFactory
    const modal = await ModalSaveCancel.create({
        title: titleString,
        body: bodyContent,
        large: true,
    });

    let correctEditorInstance = null;
    let incorrectEditorInstance = null;

    // Show the modal
    modal.show();

    // Update gap classes based on existing feedback when modal loads
    updateGapClassesFromSettings(gapInfo);

    // After modal is shown, initialize TinyMCE editors for the feedback fields
    modal.getRoot().on(ModalEvents.shown, async() => {
        const feedback = getItemSettings(gapInfo);
        // Look inside this modal: the ids are fixed, so a document lookup could find another copy.
        const root = modal.getRoot()[0];
        const correctField = root.querySelector('#gapfill-feedback-correct');
        const incorrectField = root.querySelector('#gapfill-feedback-incorrect');
        correctField.value = (feedback && feedback.correctFeedback) || '';
        incorrectField.value = (feedback && feedback.incorrectFeedback) || '';

        // Create the editors through core editor_tiny so they get its configuration (licence key,
        // language, content CSS, modal handling) rather than calling tinyMCE.init() directly.
        try {
            const options = getEditorOptions();
            [correctEditorInstance, incorrectEditorInstance] = await Promise.all([
                setupForTarget(correctField, options),
                setupForTarget(incorrectField, options),
            ]);
        } catch (error) {
            Log.debug('Failed to initialize TinyMCE:', error);
        }

        // Set up save event handler after TinyMCE is ready
        modal.getRoot().on(ModalEvents.save, (e) => {
            e.preventDefault();

            const correctFeedback = correctEditorInstance ? correctEditorInstance.getContent() : '';
            const incorrectFeedback = incorrectEditorInstance ? incorrectEditorInstance.getContent() : '';

            // Update the JSON data
            const JSONstr = updateJson(gapInfo, correctFeedback, incorrectFeedback);

            // Save to the hidden field
            const itemSettingsField = document.querySelector("[name='itemsettings']");
            if (itemSettingsField) {
                itemSettingsField.value = JSONstr;
            }

            // Update gap classes based on the new feedback
            updateGapClasses(gapInfo, correctFeedback, incorrectFeedback);

            // Close the modal
            modal.hide();
        });

    });

    // Clean up TinyMCE instances when modal is hidden
    modal.getRoot().on(ModalEvents.hidden, () => {
        [correctEditorInstance, incorrectEditorInstance].forEach((editor) => {
            if (editor) {
                editor.remove();
            }
        });
        correctEditorInstance = null;
        incorrectEditorInstance = null;
        // A new modal is created each time, so remove this one rather than leaving it hidden in the page.
        modal.destroy();
    });
};

export {
    showGapSettingsModal
};