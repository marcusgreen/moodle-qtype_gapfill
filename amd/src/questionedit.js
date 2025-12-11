// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.
/**
 * JavaScript code for the gapfill question type.
 *
 * @copyright  2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/* globals tinyMCE */
/* The data is stored in a hidden field */
define([
    'qtype_gapfill/Item',
    'core/modal_save_cancel',  // Updated import - use specific modal type
    'core/modal_events'
], function(Item, ModalSaveCancel, ModalEvents) {
  return {
    init: function() {
      /**
       * Helper function to set element properties by ID
       * @param {string} id - Element ID
       * @param {object} properties - Object containing property-value pairs to set
       */
      const setElementProps = (id, properties) => {
        let element = document.getElementById(id);
        if (element) {
          Object.keys(properties).forEach(prop => {
            element[prop] = properties[prop];
          });
        }
      };
      /**
       * Check which editor is active on the page
       * @return {string|null} Returns 'tinymce', 'atto', or null if neither is active
       */
      const getActiveEditor = () => {
        // Check if TinyMCE is active
        if (document.querySelector('.tox-tinymce')) {
          return 'tinymce';
        }
        // Check if Atto is active
        let attoIsLive = document.querySelectorAll('.editor_atto').length;
        if (attoIsLive > 0) {
          return 'atto';
        }
        return null;
      };
      /**
       * Handle TinyMCE editor specific item settings functionality
       */
      const handleTinyItemSettings = () => {
        // Access TinyMCE through the global tinyMCE object
        if (typeof tinyMCE === 'undefined') {
          console.error('TinyMCE global object not available');
          return;
        }

        // Find the TinyMCE editor instance for questiontext
        // Try common TinyMCE editor IDs used in Moodle
        let possibleIds = ['id_questiontext_editor', 'id_questiontexteditable', 'id_questiontext'];
        let editor = null;
        for (let id of possibleIds) {
          editor = tinyMCE.get(id);
          if (editor) {
            break;
          }
        }
        // If still not found, try to find any TinyMCE editor in the questiontext area
        if (!editor && tinyMCE.editors && tinyMCE.editors.length > 0) {
          // Look for an editor whose container is within the questiontext area
          for (let ed of tinyMCE.editors) {
            let container = ed.getContainer();
            if (container && container.closest('#fitem_id_questiontext')) {
              editor = ed;
              break;
            }
          }
        }
        if (!editor) {
          console.error('TinyMCE editor not found. Available editors:', tinyMCE.editors);
          console.error('Tried IDs:', possibleIds);
          return;
        }
        let canvas = document.getElementById('id_itemsettings_canvas');
        let isCanvasVisible = canvas.style.display === 'block';
        if (!isCanvasVisible) {
          // Switch to canvas mode (disable editing)
          // Get the content from TinyMCE
          let editorContent = editor.getContent();
          // Get the editor container dimensions
          let editorContainer = editor.getContainer();
          let settingformheight = window.getComputedStyle(editorContainer).height;
          let settingformwidth = window.getComputedStyle(editorContainer).width;
          // Disable TinyMCE editor
          editor.mode.set('readonly');
          // Disable all toolbar buttons
          let toolbarButtons = editorContainer.querySelectorAll('button');
          toolbarButtons.forEach(button => {
            button.setAttribute('disabled', 'true');
          });
          // Hide the editor container
          editorContainer.style.display = 'none';
          // Setup the canvas
          canvas.style.position = 'relative';
          canvas.style.display = 'block';
          canvas.style.background = 'lightgrey';
          canvas.style.padding = '10px';
          canvas.style.border = '1px solid #ccc';
          canvas.style.minHeight = settingformheight;
          canvas.style.width = settingformwidth;
          canvas.innerHTML = editorContent;
          // Insert canvas after the editor container
          editorContainer.parentNode.insertBefore(canvas, editorContainer.nextSibling);
          // Update button text
          document.getElementById('id_itemsettings_button').innerHTML =
            M.util.get_string('editquestiontext', 'qtype_gapfill');
          // Wrap content should be last as it sometimes falls over with an error
          wrapContent(canvas);
        } else {
          // Switch back to edit mode (enable editing)
          // Show the editor container
          let editorContainer = editor.getContainer();
          editorContainer.style.display = 'block';
          // Enable TinyMCE editor
          editor.mode.set('design');
          // Enable all toolbar buttons
          let toolbarButtons = editorContainer.querySelectorAll('button');
          toolbarButtons.forEach(button => {
            button.removeAttribute('disabled');
          });
          // Hide the canvas
          canvas.style.display = 'none';
          // Hide settings popup if it exists
          let settingsPopup = document.getElementById('id_settings_popup');
          if (settingsPopup) {
            settingsPopup.style.display = 'none';
          }
          // Update button text
          document.getElementById('id_itemsettings_button').innerHTML =
            M.util.get_string('additemsettings', 'qtype_gapfill');
        }
      };
      /**
       * Handle Atto editor specific item settings functionality
       */
      const handleAttoItemSettings = () => {
        let questionTextEditable = document.getElementById('id_questiontexteditable');
        if (questionTextEditable.isContentEditable) {
          questionTextEditable.setAttribute('contenteditable', 'false');
          // Disable all buttons in fitem_id_questiontext
          let buttons = document.getElementById('fitem_id_questiontext').querySelectorAll('button');
          buttons.forEach(button => {
            button.setAttribute('disabled', 'true');
          });
          let settingformheight = window.getComputedStyle(questionTextEditable).height;
          let settingformwidth = window.getComputedStyle(questionTextEditable).width;
          questionTextEditable.style.display = 'none';
          /* Copy the styles from attos editable area so the canvas looks the same (except gray) */
          let canvas = document.getElementById('id_itemsettings_canvas');
          let styles = copyStyles(questionTextEditable);
          Object.assign(canvas.style, styles);
          let ed = questionTextEditable.closest('.editor_atto_content_wrap');
          ed.appendChild(canvas);
          canvas.style.position = 'relative';
          canvas.style.display = 'block';
          canvas.style.background = 'lightgrey';
          /* Copy the real html to the feedback editing html */
          canvas.innerHTML = questionTextEditable.innerHTML;
          canvas.style.height = settingformheight;
          canvas.style.width = settingformwidth;
          canvas.style.height = '100%';
          canvas.style.width = '100%';
          document.getElementById('id_itemsettings_button').innerHTML =
            M.util.get_string('editquestiontext', 'qtype_gapfill');
          /* Setting the height by hand gets around a quirk of MSIE */
          canvas.style.height = window.getComputedStyle(questionTextEditable).height;
          /* Disable the buttons on questiontext but not on the feedback form */
          /* wrapContent should be the last on this block as it sometimes falls over with an error */
          wrapContent(canvas);
        } else {
          questionTextEditable.style.display = 'block';
          questionTextEditable.style.backgroundColor = 'white';
          questionTextEditable.setAttribute('contenteditable', 'true');
          document.getElementById('id_itemsettings_canvas').style.display = 'none';
          // Enable all buttons in fitem_id_questiontext
          let buttons = document.getElementById('fitem_id_questiontext').querySelectorAll('button');
          buttons.forEach(button => {
            button.removeAttribute('disabled');
          });
          document.getElementById('id_settings_popup').style.display = 'none';
          document.getElementById('id_itemsettings_button').innerHTML =
            M.util.get_string('additemsettings', 'qtype_gapfill');
          // Enable all elements with class starting with atto_
          let attoElements = document.querySelectorAll('[class^="atto_"]');
          attoElements.forEach(element => {
            element.removeAttribute('disabled');
          });
        }
      };
      document.getElementById('id_answerdisplay').addEventListener('change', function() {
        let selected = this.value;
        if (selected == 'gapfill') {
          setElementProps('id_fixedgapsize', { disabled: false });
          setElementProps('id_optionsaftertext', { disabled: true, checked: false });
          setElementProps('id_singleuse', { disabled: true, checked: false });
          setElementProps('id_disableregex', { disabled: false });
        }
        if (selected == 'dragdrop') {
          setElementProps('id_optionsaftertext', { disabled: false });
          setElementProps('id_singleuse', { disabled: false });
          setElementProps('id_fixedgapsize', { disabled: false });
          setElementProps('id_disableregex', { disabled: false });
        }
        if (selected == 'dropdown') {
          setElementProps('id_fixedgapsize', { disabled: true, checked: false });
          setElementProps('id_optionsaftertext', { disabled: true, checked: false });
          setElementProps('id_singleuse', { disabled: true, checked: false });
          setElementProps('id_disableregex', { disabled: true, checked: false });
        }
      });
      /* A click on the itemsettings button */
      document.getElementById('id_itemsettings_button').addEventListener('click', function() {
        let activeEditor = getActiveEditor();
        /* Show error if no supported editor is active. It might be because the page has not finished loading
         * or because plain text elements are being used or (perhaps less likely as time goes on)
         * the HTMLarea editor is being used. It might be possible to work with those other editors
         * but limiting to supported editors keeps things straightforward and maintainable.
         */
        if (!activeEditor) {
          let errorElement = document.getElementById('id_error_itemsettings_button');
          errorElement.style.display = 'inline';
          errorElement.style.color = 'red';
          errorElement.innerHTML = M.util.get_string(
            'itemsettingserror',
            'qtype_gapfill'
          );
          return;
        }
        // Disable editor-specific buttons based on active editor
        if (activeEditor === 'atto') {
          let htmlButtons = document.querySelectorAll('#questiontext .atto_html_button');
          htmlButtons.forEach(button => {
            button.setAttribute('disabled', 'true');
          });
          // Invoke the Atto-specific function
          handleAttoItemSettings();
        } else if (activeEditor === 'tinymce') {
            handleTinyItemSettings();
        }
      });
      /* A click on the text */
      document.getElementById('id_itemsettings_canvas').addEventListener('click', function(e) {
        /*
         * Questiontext needs to be editable and the target must start
         * with id followed by one or more digits and an underscore
         *
         * For TinyMCE, id_questiontexteditable doesn't exist, so we check if it exists first.
         * If it doesn't exist (TinyMCE), we proceed. If it does exist (Atto), we check if it's NOT editable.
         * */
        let questionTextEditable = document.getElementById('id_questiontexteditable');
        let canProceed = !questionTextEditable || !questionTextEditable.isContentEditable;

        if (
          canProceed &&
          e.target.id.match(/^id[0-9]+_/)
        ) {
          let delimitchars = document.getElementById('id_delimitchars').value;
          let item = new Item(e.target.innerHTML, delimitchars);
          // Var item = new Item(e.target.innerHTML, delimitchars);
          let itemsettings = item.getItemSettings(e.target);
          if (itemsettings === null || itemsettings.length === 0) {
            document.getElementById('id_correct').innerHTML = '';
            document.getElementById('id_incorrect').innerHTML = '';
          } else {
            document.getElementById('id_correct').innerHTML = itemsettings.correctfeedback;
            document.getElementById('id_incorrect').innerHTML = itemsettings.incorrectfeedback;
          }
          // Set label texts
          let correctLabels = document.querySelectorAll("label[for*='id_correct']");
          correctLabels.forEach(label => {
            label.textContent = M.util.get_string('correct', 'qtype_gapfill');
          });
          let incorrectLabels = document.querySelectorAll("label[for*='id_incorrect']");
          incorrectLabels.forEach(label => {
            label.textContent = M.util.get_string('incorrect', 'qtype_gapfill');
          });
          // Disable specific atto buttons
          let imageButtons = document.querySelectorAll('#id_itemsettings_popup .atto_image_button');
          imageButtons.forEach(button => {
            button.setAttribute('disabled', 'true');
          });
          let mediaButtons = document.querySelectorAll('#id_itemsettings_popup .atto_media_button');
          mediaButtons.forEach(button => {
            button.setAttribute('disabled', 'true');
          });
          let manageFilesButtons = document.querySelectorAll('#id_itemsettings_popup .atto_managefiles_button');
          manageFilesButtons.forEach(button => {
            button.setAttribute('disabled', 'true');
          });
          let title = M.util.get_string('additemsettings', 'qtype_gapfill');
          /* The html jquery call will turn any encoded entities such as &gt; to html, i.e. > */
          let tempDiv = document.createElement('div');
          tempDiv.innerHTML = item.stripdelim();
          title += ': ' + tempDiv.textContent;
          openItemSettingsDialog(item, e, title);
        }
      });
      const openItemSettingsDialog = (item, e, title) => {
        // Get the content from the popup element
        let popupContent = document.getElementById('id_itemsettings_popup');

        // Updated approach: Use ModalSaveCancel.create() directly instead of ModalFactory
        ModalSaveCancel.create({
          title: title,
          body: popupContent.innerHTML,
          large: true,
          removeOnClose: true
        }).then(function(modal) {
          // Show the modal
          modal.show();

          // Make the modal wider by adjusting the max-width
          const modalRoot = modal.getRoot()[0];
          const modalDialog = modalRoot.querySelector('.modal-dialog');
          if (modalDialog) {
            modalDialog.style.maxWidth = '90%';
          }

          // Get the modal root element to manipulate content
          // After the modal is shown, we need to copy content from modal back to original elements
          modal.getRoot().on(ModalEvents.shown, function() {
            // Find the editable elements in the modal
            let modalCorrect = modalRoot.querySelector('#id_correcteditable');
            let modalIncorrect = modalRoot.querySelector('#id_incorrecteditable');
            // Get the original elements
            let originalCorrect = document.getElementById('id_correcteditable');
            let originalIncorrect = document.getElementById('id_incorrecteditable');
            // Copy content from original to modal
            if (modalCorrect && originalCorrect) {
              modalCorrect.innerHTML = originalCorrect.innerHTML;
            }
            if (modalIncorrect && originalIncorrect) {
              modalIncorrect.innerHTML = originalIncorrect.innerHTML;
            }
          });

          // Handle the save event
          modal.getRoot().on(ModalEvents.save, function() {
            // Copy content from modal back to original elements before updating JSON
            let modalCorrect = modalRoot.querySelector('#id_correcteditable');
            let modalIncorrect = modalRoot.querySelector('#id_incorrecteditable');
            let originalCorrect = document.getElementById('id_correcteditable');
            let originalIncorrect = document.getElementById('id_incorrecteditable');
            if (modalCorrect && originalCorrect) {
              originalCorrect.innerHTML = modalCorrect.innerHTML;
            }
            if (modalIncorrect && originalIncorrect) {
              originalIncorrect.innerHTML = modalIncorrect.innerHTML;
            }
            let JSONstr = item.updateJson(e);
            // Enable all atto elements
            let attoElements = document.querySelectorAll('[class^="atto_"]');
            attoElements.forEach(element => {
              element.removeAttribute('disabled');
            });
            document.querySelector("[name='itemsettings']").value = JSONstr;
            /* Set editable to true as it is checked at the start of click */
            let questionTextEditable = document.getElementById('id_questiontexteditable');
            if (questionTextEditable) {
              questionTextEditable.setAttribute('contenteditable', 'true');
            }
            document.getElementById('id_itemsettings_button').click();
            modal.destroy();
          });

          // Handle the cancel event
          modal.getRoot().on(ModalEvents.cancel, function() {
            // Enable all atto elements
            let attoElements = document.querySelectorAll('[class^="atto_"]');
            attoElements.forEach(element => {
              element.removeAttribute('disabled');
            });
            modal.destroy();
          });

          return modal;
        }).catch(function(error) {
          // Handle any errors in creating the modal
          console.error('Error creating modal:', error);
        });
      };
      /**
       * Convert an object to an array
       * @param {object} obj
       * @return {array}
       */
      const toArray = obj => {
        let arr = [];
        for (let i = 0, iLen = obj.length; i < iLen; i++) {
          arr.push(obj[i]);
        }
        return arr;
      };
      // Wrap the words of an element and child elements in a span.
      // Recurs over child elements, add an ID and class to the wrapping span.
      // Does not affect elements with no content, or those to be excluded.
      const wrapContent = (function() {
        return function(el) {
          let count = 0;
          let gaps = [];
          // If element provided, start there, otherwise use the body.
          el = el && el.parentNode ? el : document.body;
          // Get all child nodes as a static array.
          let node,
            nodes = toArray(el.childNodes);
          if (el.id === 'id_questiontextfeedback' && count > 0) {
            count = 0;
          }
          let frag, text;
          let delimitchars = document.getElementById('id_delimitchars').value;
          let l = delimitchars.substring(0, 1);
          let r = delimitchars.substring(1, 2);
          let regex = new RegExp('(\\' + l + '.*?\\' + r + ')', 'g');
          let sp,
            span = document.createElement('span');
          // Tag names of elements to skip, there are more to add.
          const skip = {
            script: '',
            button: '',
            input: '',
            select: '',
            textarea: '',
            option: '',
          };
          // For each child node...
          for (let i = 0, iLen = nodes.length; i < iLen; i++) {
            node = nodes[i];
            // If it's an element, call wrapContent.
            if (node.nodeType === 1 && !(node.tagName.toLowerCase() in skip)) {
              wrapContent(node);
              // If it's a text node, wrap words.
            } else if (node.nodeType === 3) {
              let textsplit = new RegExp('(\\' + l + '.*?\\' + r + ')', 'g');
              text = node.data.split(textsplit);
              if (text) {
                // Create a fragment, handy suckers these.
                frag = document.createDocumentFragment();
                for (let j = 0, jLen = text.length; j < jLen; j++) {
                  // If not whitespace, wrap it and append to the fragment.
                  doGap(text, span, j);
                }
              }
              // Replace the original node with the fragment.
              node.parentNode.replaceChild(frag, node);
            }
          }
          /**
           * Process each gap
           *
           * @param {*} text
           * @param {*} span
           * @param {*} j
           */
          function doGap(text, span, j) {
            gaps = [];
            if (regex.test(text[j])) {
              sp = span.cloneNode(false);
              count++;
              sp.className = 'item';
              let item = new Item(text[j], document.getElementById('id_delimitchars').value);
              if (item.gaptext > '') {
                let instance = 0;
                for (let k = 0; k < gaps.length; ++k) {
                  if (gaps[k] === item.text) {
                    instance++;
                  }
                }
                item.id = 'id' + count + '_' + instance;
                sp.id = item.id;
                let is = item.getItemSettings(item);
                if (item.striptags(is.correctfeedback) > '') {
                  sp.className = 'hascorrect';
                }
                if (item.striptags(is.incorrectfeedback) > '') {
                  sp.className = sp.className + ' ' + 'hasnocorrect';
                }
                gaps.push(item.gaptext);
              }
              sp.appendChild(document.createTextNode(text[j]));
              frag.appendChild(sp);
              // Otherwise, just append it to the fragment.
            } else {
              frag.appendChild(document.createTextNode(text[j]));
            }
          }
        };
      })();
      /**
       *
       * @param {array} source
       * @return {array} product
       */
      const copyStyles = source => {
        // The map to return with requested styles and values as KVP.
        let product = {};
        // The style object from the DOM element we need to iterate through.
        let style;
        // Recycle the name of the style attribute.
        let name;
        // Prevent from empty selector.
        if (source.length) {
          // Otherwise, we need to get everything.
          let dom = source.get(0);
          if (window.getComputedStyle) {
            // Convenience methods to turn css case ('background-image') to camel ('backgroundImage').
            const pattern = /-([a-z])/g;
            const uc = (a, b) => b.toUpperCase();
            const camelize = string => string.replace(pattern, uc);
            // Make sure we're getting a good reference.
            if ((style = window.getComputedStyle(dom, null))) {
              let camel, value;
              for (let i = 0, l = style.length; i < l; i++) {
                name = style[i];
                camel = camelize(name);
                value = style.getPropertyValue(name);
                product[camel] = value;
              }
            } else if ((style = dom.currentStyle)) {
              for (name in style) {
                product[name] = style[name];
              }
            } else if ((style = dom.style)) {
              product = getStyle(style, product, name);
            }
            return product;
          }
        }
        return false;
      };
      /**
       * TODO check if this function is needed
       * @param {string} style
       * @param {object} product
       * @param {string} name
       * @returns {string}
       */
      function getStyle(style, product, name) {
        for (name in style) {
          if (typeof style[name] != 'function') {
            product[name] = style[name];
          }
        }
        return product;
      }
    },
  };
});