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
 * Dragging uses core/dragdrop (mouse and touch events, the same as
 * qtype_ddwtos) rather than HTML5 drag and drop, so it works on touch
 * devices and the browser never drags text out of a gap.
 *
 * As an alternative to dragging, an option or filled gap can be picked
 * with a click, tap, Enter or Space, then placed by clicking a gap or
 * pressing Enter in it. Each change is announced to screen readers.
 *
 * @copyright  2020 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import $ from 'jquery';
import dragDrop from 'core/dragdrop';
import {getString} from 'core/str';
import Notification from 'core/notification';

/**
 * Set up drag and drop for one question.
 *
 * @param {Boolean} singleuse hide options once they have been placed in a gap
 * @param {String} containerid id of the outer div of this question
 */
export const init = (singleuse, containerid) => {
  // Scope to this question so multiple questions on a page do not interfere.
  var root = (containerid && document.getElementById(containerid)) || document;
  var draggables = root.querySelectorAll('span.draggable');
  var gaps = root.querySelectorAll('input.droptarget');
  var options = root.querySelector('.answeroptions');
  var status = root.querySelector('.gapfill-status');
  // The option or filled gap picked to be placed, if any.
  var picked = null;
  // The gap filled by the first click of a double click, so the double click does not clear it.
  var placedbyclick = null;

  draggables.forEach(function(e) {
    e.addEventListener('mousedown', dragStart);
    e.addEventListener('touchstart', dragStart);
    e.addEventListener('keydown', function(ev) {
      if (ev.key === 'Enter' || ev.key === ' ') {
        ev.preventDefault();
        pick(this);
      } else if (ev.key === 'Escape') {
        unpick();
      }
    });
  });
  gaps.forEach(function(e) {
    // A filled gap can be dragged to another gap, an empty one can be typed in.
    e.addEventListener('mousedown', dragStart);
    e.addEventListener('touchstart', dragStart);
    e.addEventListener('dblclick', function() {
      if (placedbyclick === this) {
        placedbyclick = null;
        return;
      }
      clearGap(this);
    });
    e.addEventListener('keydown', function(ev) {
      if (ev.key === 'Enter') {
        // Also stops Enter submitting the quiz page.
        ev.preventDefault();
        gapActivated(this);
      } else if (ev.key === 'Escape') {
        unpick();
      }
    });
    e.addEventListener('input', function() {
      typedIn(this);
    });
    // Stop the browser dragging selected text out of the gap (issue #123).
    e.addEventListener('dragstart', function(ev) {
      ev.preventDefault();
    });
  });

  /**
   * Start dragging an option, or the text in a filled gap.
   *
   * @param {Event} e mousedown or touchstart
   */
  function dragStart(e) {
    var source = e.currentTarget;
    if (e.type === 'mousedown' && e.detail === 1) {
      // A new click, not the second half of a double click.
      placedbyclick = null;
    }
    if (e.type === 'mousedown' && e.detail > 1) {
      // The second click of a double click, leave it to the dblclick handler
      // rather than picking or placing again.
      e.preventDefault();
      return;
    }
    if (source.tagName === 'INPUT' && !source.value) {
      if (picked) {
        // Place without the browser focusing the gap, so the on screen keyboard
        // does not open on touch. With a mouse, focus the gap that now has the text.
        e.preventDefault();
        gapActivated(source);
        if (e.type === 'mousedown') {
          source.focus();
        }
      }
      return;
    }
    var info = dragDrop.prepare(e);
    if (!info.start) {
      return;
    }
    var bymouse = (e.type === 'mousedown');
    // Drag a copy of the gap or option so it keeps its border and size.
    var proxy = $(source.cloneNode(true))
      .removeAttr('id name')
      .removeClass('dragover')
      .addClass('gapfill-dragproxy')
      .attr({'aria-hidden': 'true', tabindex: '-1'})
      .css('width', source.offsetWidth + 'px')
      .appendTo(document.body)
      .offset($(source).offset());
    Object.keys(source.dataset).forEach(function(key) {
      delete proxy[0].dataset[key];
    });
    if (source.tagName === 'INPUT') {
      proxy.val(source.value);
    }
    var over = null;

    dragDrop.start(e, proxy, function(x, y) {
      var target = targetAt(x, y);
      if (target !== over) {
        if (over) {
          over.classList.remove('dragover');
        }
        if (target) {
          target.classList.add('dragover');
        }
        over = target;
      }
    }, function(x, y) {
      if (over) {
        over.classList.remove('dragover');
      }
      proxy.remove();
      if (Math.abs(x - info.x) + Math.abs(y - info.y) < 5) {
        clicked(source, bymouse);
      } else {
        unpick();
        var target = targetAt(x, y);
        dropOn(source, target);
        if (bymouse && target && target !== options && target !== source) {
          // Focus follows the text, as it does for a click or the keyboard.
          target.focus();
        }
      }
    });
  }

  /**
   * The gap, or the list of options, under a page position.
   *
   * @param {Number} x page x
   * @param {Number} y page y
   * @return {HTMLElement|null}
   */
  function targetAt(x, y) {
    var el = document.elementFromPoint(x - window.scrollX, y - window.scrollY);
    if (!el || !root.contains(el)) {
      return null;
    }
    if (el.matches('input.droptarget')) {
      return el;
    }
    return (options && options.contains(el)) ? options : null;
  }

  /**
   * Finish a drag of an option or gap onto a gap or the list of options.
   *
   * @param {HTMLElement} source the option or gap dragged
   * @param {HTMLElement|null} target where it was dropped
   */
  function dropOn(source, target) {
    if (source.tagName !== 'INPUT') {
      if (target && target !== options) {
        placeInGap(target, source);
      }
    } else if (target === options) {
      clearGap(source);
    } else if (target && target !== source) {
      moveBetweenGaps(source, target);
    }
  }

  /**
   * An option or filled gap was clicked or tapped rather than dragged.
   *
   * @param {HTMLElement} source
   * @param {Boolean} bymouse focus a clicked gap, so focus follows the text and the gap
   *     can be typed in, not done for touch as it would open the on screen keyboard
   */
  function clicked(source, bymouse) {
    if (source.tagName !== 'INPUT') {
      pick(source);
      return;
    }
    gapActivated(source);
    if (bymouse) {
      source.focus();
    }
  }

  /**
   * A gap was clicked or Enter pressed in it. Place the picked option or gap
   * text here, otherwise pick this gap's text so it can be moved.
   *
   * @param {HTMLElement} gap
   */
  function gapActivated(gap) {
    if (picked) {
      var from = picked;
      unpick();
      if (from.tagName !== 'INPUT') {
        placeInGap(gap, from);
      } else if (from !== gap) {
        moveBetweenGaps(from, gap);
      }
      placedbyclick = gap;
    } else if (gap.value) {
      pick(gap);
    }
  }

  /**
   * Pick an option or filled gap, picking it again unpicks it.
   * Only one can be picked at a time.
   *
   * @param {HTMLElement} el
   */
  function pick(el) {
    var waspicked = (picked === el);
    unpick();
    if (waspicked) {
      return;
    }
    picked = el;
    el.classList.add('picked');
    if (el.tagName === 'INPUT') {
      announce('gapselected', gapNumber(el));
    } else {
      el.setAttribute('aria-pressed', 'true');
      announce('optionselected', optionText(el));
    }
  }

  /**
   * Clear any picked option or gap.
   */
  function unpick() {
    if (picked) {
      picked.classList.remove('picked');
      if (picked.tagName !== 'INPUT') {
        picked.setAttribute('aria-pressed', 'false');
      }
    }
    picked = null;
  }

  /**
   * The gap was edited by typing. If it no longer holds the option that was
   * placed in it, return that option to the list.
   *
   * @param {HTMLElement} gap
   */
  function typedIn(gap) {
    if (picked === gap) {
      unpick();
    }
    var option = findOption(gap.dataset.sourceid);
    if (gap.dataset.sourceid && (!option || optionText(option) !== gap.value)) {
      if (singleuse && option) {
        option.classList.remove('hide');
      }
      delete gap.dataset.sourceid;
    }
  }

  /**
   * Put the text of an option into a gap. Any option previously in the
   * gap is returned to the list when singleuse is on.
   *
   * @param {HTMLElement} gap
   * @param {HTMLElement} option
   */
  function placeInGap(gap, option) {
    if (singleuse) {
      showOption(gap);
      option.classList.add('hide');
    }
    gap.value = optionText(option);
    gap.dataset.sourceid = option.id;
    changed(gap);
    announce('optionplaced', {option: optionText(option), gap: gapNumber(gap)});
  }

  /**
   * Move the contents of one gap to another. If the target is already
   * filled the two gaps swap, so no option is lost or returned to the list.
   *
   * @param {HTMLElement} src
   * @param {HTMLElement} target
   */
  function moveBetweenGaps(src, target) {
    announce(target.value ? 'gapsswapped' : 'gapmoved', {from: gapNumber(src), to: gapNumber(target)});
    var value = target.value;
    var sourceid = target.dataset.sourceid;
    target.value = src.value;
    setSourceId(target, src.dataset.sourceid);
    src.value = value;
    setSourceId(src, sourceid);
    changed(src);
    changed(target);
  }

  /**
   * Record which option filled a gap, or forget it.
   *
   * @param {HTMLElement} gap
   * @param {String|undefined} sourceid
   */
  function setSourceId(gap, sourceid) {
    if (sourceid) {
      gap.dataset.sourceid = sourceid;
    } else {
      delete gap.dataset.sourceid;
    }
  }

  /**
   * Empty a gap, returning its option to the list when singleuse is on.
   *
   * @param {HTMLElement} gap
   */
  function clearGap(gap) {
    if (singleuse) {
      showOption(gap);
    }
    if (picked === gap) {
      unpick();
    }
    gap.value = '';
    delete gap.dataset.sourceid;
    changed(gap);
    announce('gapcleared', gapNumber(gap));
  }

  /**
   * The number of a gap, from the end of its id, e.g. q12:1_p3 is gap 3.
   *
   * @param {HTMLElement} gap
   * @return {String}
   */
  function gapNumber(gap) {
    var match = gap.id.match(/p(\d+)$/);
    return match ? match[1] : '';
  }

  /**
   * Tell screen reader users what just happened.
   *
   * @param {String} key lang string identifier
   * @param {*} a lang string parameter
   */
  function announce(key, a) {
    if (status) {
      getString(key, 'qtype_gapfill', a).then(function(text) {
        status.textContent = text;
        return text;
      }).catch(Notification.exception);
    }
  }

  /**
   * Fire a change event so quiz autosave and the unsaved changes
   * warning notice a value set by script.
   *
   * @param {HTMLElement} gap
   */
  function changed(gap) {
    gap.dispatchEvent(new Event('change', {bubbles: true}));
  }

  /**
   * Return the option that filled this gap to the list of options.
   * Use the id recorded on drop, fall back to matching the text for
   * gaps that were filled before the page loaded.
   *
   * @param {HTMLElement} gap
   */
  function showOption(gap) {
    var option = findOption(gap.dataset.sourceid);
    if (!option) {
      var value = gap.value.trim();
      option = Array.from(draggables).find(function(e) {
        return e.classList.contains('hide') && optionText(e) === value;
      });
    }
    if (option) {
      option.classList.remove('hide');
    }
  }

  /**
   * The text of an option. Uses textContent not innerText, innerText is empty
   * for an option hidden with visibility: hidden once it has been placed.
   *
   * @param {HTMLElement} option
   * @return {String}
   */
  function optionText(option) {
    return option.textContent.trim();
  }

  /**
   * Find one of this question's options by id. Search this question only,
   * ids may repeat across questions on a page.
   *
   * @param {String|undefined} id
   * @return {HTMLElement|undefined}
   */
  function findOption(id) {
    return id ? Array.from(draggables).find(function(e) {
      return e.id === id;
    }) : undefined;
  }
};
