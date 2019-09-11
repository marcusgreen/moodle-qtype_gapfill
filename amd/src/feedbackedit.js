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
 * JavaScript code for the wordselect question type.
 *
 * @package    qtype
 * @subpackage gapfill
 * @copyright  2019 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery',"core/fragment", "qtype_gapfill/dialog_info"], function($,Fragment,DialogInfo) {

    return {
        init: function(contextid) {
                 /* make repeat elements work with fragments
                https://tracker.moodle.org/browse/MDL-63685
                */
               var modalCreateFeedback = new DialogInfo('', '', null, false, false, contextid);
               var loadFormFragment = function(repeat,item) {
                   debugger;
                   /* item gets passed in via LoadFragment so this should not be necessary */
                   modalCreateFeedback.setItem(item);
                   var params = { 
                       'repeat': repeat,
                       'item': JSON.stringify(item)
                    };
                
                   Fragment.loadFragment("qtype_gapfill", "feedbackedit", contextid, params).then(function(html, js) {
                       modalCreateFeedback.show('Create feedback',
                           '<div id="createFeedback">' +
                           html + '</div>',
                           '<div id="creatFeedbackDialogFooter"></div>', true);
                       runJS(js);

                   });
               };
               var runJS = function(source) {
                if (source.trim() !== '') {
                    var newscript = $('<script>').attr('type', 'text/javascript').html(source);
                    $('head').append(newscript);
                }
            };
            $('body').on('click', '#item_feedback #id_extended_feedback', function(e) {
                e.preventDefault();
                loadFormFragment(true);
            });
            $("#id_itemsettings_button").on("click", function(e) {
                e.preventDefault();

                setupcanvas(loadFormFragment);
            });
        }
    };


function get_settingsdata(){
/* The data is stored in a hidden field */
var settingsdata = ($("[name='itemsettings']").val());

var settings = [];
if (settingsdata > "") {
    var obj = JSON.parse(settingsdata);
    for (var o in obj) {
        settings.push(obj[o]);
    }
    return settings;
}else{
    return [];
}
}
/**
 *
 * @param {string} text
 * @param {string} delimitchars
 */
function Item(text, delimitchars,event) {
    this.event = event;
    this.questionid = $("input[name=id]").val();
    this.gaptext = text;
    this.delimitchars = delimitchars;
    /* The l and r is for left and right */
    this.l = delimitchars.substr(0, 1);
    this.r = delimitchars.substr(1, 1);
    this.len = this.gaptext.length;
    this.startchar = this.gaptext.substring(0, 1);
    /* For checking if the end char is the right delimiter */
    this.endchar = this.gaptext.substring(this.len - 1, this.len);
    this.gaptextNodelim = '';
    this.feedback = {};
    this.instance = 0;
    this.settings = get_settingsdata();
    for (var set in this.settings) {
    if (set.indexOf('feedback') !== -1) {
        this.feedback[set] = this.settings[set];
        //         settings[set]['feedback'].correctfeedback = $("#id_correcteditable")[0].innerHTML;
        //         settings[set]['feedback'].incorrectfeedback = $("#id_incorrecteditable")[0].innerHTML;
        //         found = true;
           }
     }

  //  this.feedback.correct = $("#id_correcteditable").html();
  //  this.feedback.incorrect = $("#id_incorrecteditable").html();
  Item.prototype.striptags = function(gaptext) {
        /* This is not a perfect way of stripping html but it may be good enough */
        if (gaptext === undefined) {
            return "";
        }
        var regex = /(<([^>]+)>)/ig;
        return gaptext.replace(regex, "");
    };
    this.stripdelim = function() {
        if (this.startchar === this.l) {
            this.gaptextNodelim = this.gaptext.substring(1, this.len);
        }
        if (this.endchar === this.r) {
            var len = this.gaptextNodelim.length;
            this.gaptextNodelim = this.gaptextNodelim.substring(0, len - 1);
        }
        return this.gaptextNodelim;
    };

    var itemsettings = [];
    // Get the settings for the gap that was clicked on.
    Item.prototype.getItemSettings = function(target) {
        var itemid = target.id;
        var underscore = itemid.indexOf("_");
        /* The instance, normally 0 but incremented if a gap has the same text as another
         * instance is not currently used*/
        this.instance = itemid.substr(underscore + 1);
        var settings =get_settingsdata()
   
        for (var set in this.settings) {
            text = this.stripdelim();
            if (this.settings[set] === text) {
                itemsettings = this.settings[set];
            }
        }
        if (itemsettings === null || itemsettings.length === 0) {
                this.feedback.correctfeedback='';
                this.feedback.incorrectfeedback='';

        }

        return itemsettings;
    };
    this.updateJson = function(e) {
        var found = false;
        var id = e.id;
        debugger;
        for (var set in this.settings) {
            if (this.settings[set].gaptext === this.stripdelim()) {
                // settings[set]['feedback'].correctfeedback = $("#id_correcteditable")[0].innerHTML;
                // settings[set]['feedback'].incorrectfeedback = $("#id_incorrecteditable")[0].innerHTML;
                 found = true;
            }
        }
        if (found === false) {
            /* If there is no record for this word add one */
            var itemsettings = {
                itemid: id,
                questionid: $("input[name=id]").val(),
                correctfeedback: $("#id_correcteditable").html(),
                incorrectfeedback: $("#id_incorrecteditable").html(),
                gaptext: this.stripdelim()
            };
           this.settings.push(itemsettings);
        }
        return JSON.stringify(this.settings);
    };
}

    function setupcanvas(loadFormFragment){
        if ($('#id_questiontexteditable').get(0).isContentEditable) {
            $("#id_questiontexteditable").attr('contenteditable', 'false');
            $("#fitem_id_questiontext").find('button').attr("disabled", 'true');
            var settingformheight = $("#id_questiontexteditable").css("height");
            var settingformwidth = $("#id_questiontexteditable").css("width");
            $("#id_questiontexteditable").css("display", 'none');
            $('#id_itemsettings_canvas').css('padding-top','6px');
            $('#id_itemsettings_canvas').css('padding-bottom','6px');
            $('#id_itemsettings_canvas').css('padding-right','12px');
            $('#id_itemsettings_canvas').css('padding-left','12px');

            var ed = $("#id_questiontexteditable").closest(".editor_atto_content_wrap");
            $("#id_itemsettings_canvas").appendTo(ed).css("position", "relative");
            $("#id_itemsettings_canvas").css({
                "display": "block",
                "background": "lightgrey"
            });
    
            /* Copy the real html to the feedback editing html */
            $("#id_itemsettings_canvas").html($("#id_questiontexteditable").prop("innerHTML"));
            $("#id_itemsettings_canvas").css({height: settingformheight, width: settingformwidth});
            $("#id_itemsettings_canvas").css({height: "100%", width: "100%"});
            $("#id_itemsettings_button").html(M.util.get_string("editquestiontext", "qtype_gapfill"));
            /* Setting the height by hand gets around a quirk of MSIE */
            $('#id_itemsettings_canvas').height($("#id_questiontexteditable").height());
            /* Disable the buttons on questiontext but not on the feedback form */
            /* wrapContent should be the last on this block as it sometimes falls over with an error */
             wrapContent($("#id_itemsettings_canvas")[0]);
        } else{
            $("#id_questiontexteditable").css({display: "block", backgroundColor: "white"});
            $("#id_questiontexteditable").attr('contenteditable', 'true');
            $("#id_itemsettings_canvas").css("display", "none");
            $("#fitem_id_questiontext").find('button').removeAttr("disabled");
            $("#id_settings_popup").css("display", "none");
            $("#id_itemsettings_button").html(M.util.get_string("additemsettings", "qtype_gapfill"));
            $('[class^=atto_]').removeAttr("disabled");
        }
        $("#id_itemsettings_canvas").on("click", function(e) {
            canvasClick(e,loadFormFragment);
        });

    }

/* A click on the text */
function canvasClick(e,loadFormFragment){
    /*
     * Questiontext needs to be edditable and the target must start
     * with id followed by one or more digits and an underscore
     * */
    if (!$('#id_questiontexteditable').get(0).isContentEditable && (e.target.id.match(/^id[0-9]+_/))) {
        var delimitchars = $("#id_delimitchars").val();
        debugger;
        var item = new Item(e.target.innerHTML, delimitchars,e);
        var itemsettings = item.getItemSettings(e.target);
        if (itemsettings === null || itemsettings.length === 0) {
            
        //     $("#id_correcteditable").html('');
        //     $("#id_incorrecteditable").html('');
        // } else {
        //     $("#id_correcteditable").html(itemsettings.correctfeedback);
        //     $("#id_incorrecteditable").html(itemsettings.incorrectfeedback);
        }
        $("label[for*='id_correct']").text(M.util.get_string("correct", "qtype_gapfill"));
        $("label[for*='id_incorrect']").text(M.util.get_string("incorrect", "qtype_gapfill"));
        $('#id_itemsettings_popup .atto_image_button').attr("disabled", 'true');
        $('#id_itemsettings_popup .atto_media_button').attr("disabled", 'true');
        $('#id_itemsettings_popup .atto_managefiles_button').attr("disabled", 'true');
        var title = M.util.get_string("additemsettings", "qtype_gapfill");
        /* The html jquery call will turn any encoded entities such as &gt; to html, i.e. > */
        title += ': ' + $("<div/>").html(item.stripdelim()).text();
        debugger;
        loadFormFragment(true,item);

    }
};
/**
 * Convert an object to an array
 * @param {object} obj
 * @return {array}
 */
function toArray(obj) {
    var arr = [];
    for (var i = 0, iLen = obj.length; i < iLen; i++) {
        arr.push(obj[i]);
    }
    return arr;
}
// Wrap the words of an element and child elements in a span.
// Recurs over child elements, add an ID and class to the wrapping span.
// Does not affect elements with no content, or those to be excluded.
function wrapContent (el) {
        var count = 0;
        gaps = [];
        // If element provided, start there, otherwise use the body.
        el = el && el.parentNode ? el : document.body;
        // Get all child nodes as a static array.
        var node,
        nodes = toArray(el.childNodes);
        if (el.id === "id_questiontextfeedback" && (count > 0)) {
            count = 0;
        }
        var frag, text;
        var delimitchars = $("#id_delimitchars").val();
        var l = delimitchars.substring(0, 1);
        var r = delimitchars.substring(1, 2);
        var regex = new RegExp("(\\" + l + ".*?\\" + r + ")", "g");
        var sp,
        span = document.createElement('span');
        // Tag names of elements to skip, there are more to add.
        var skip = {'script': '', 'button': '', 'input': '', 'select': '',
            'textarea': '', 'option': ''};
        // For each child node...
        for (var i = 0, iLen = nodes.length; i < iLen; i++) {
            node = nodes[i];
            // If it's an element, call wrapContent.
            if (node.nodeType === 1 && !(node.tagName.toLowerCase() in skip)) {
                wrapContent(node);
                // If it's a text node, wrap words.
            } else if (node.nodeType === 3) {
                var textsplit = new RegExp("(\\" + l + ".*?\\" + r + ")", "g");
                text = node.data.split(textsplit);
                if (text) {
                    // Create a fragment, handy suckers these.
                    frag = document.createDocumentFragment();
                    for (var j = 0, jLen = text.length; j < jLen; j++) {
                        // If not whitespace, wrap it and append to the fragment.
                        if (regex.test(text[j])) {
                            sp = span.cloneNode(false);
                            count++;
                            sp.className = 'item';
                            var item = new Item(text[j], $("#id_delimitchars").val(),el);
                            if (item.gaptext > '') {
                                var instance = 0;
                                for (var k = 0; k < gaps.length; ++k) {
                                    if (gaps[k] === item.text) {
                                        instance++;
                                    }
                                }
                                item.id = 'id' + count + '_' + instance;
                                sp.id = item.id;
                                var is = item.getItemSettings(item);
                                if (item.striptags(is.correctfeedback) > "") {
                                    sp.className = 'hascorrect';
                                }
                                if (item.striptags(is.incorrectfeedback) > "") {
                                    sp.className = sp.className + " " + 'hasnocorrect';
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
                }
                // Replace the original node with the fragment.
                node.parentNode.replaceChild(frag, node);
            }
        }
    };
    
/**
 *
 * @param {array} source
 * @return {array} product
 */
function copyStyles(source) {
    debugger;
    let css = document.defaultView.getComputedStyle($("#id_questiontexteditable")[0]);

    // The map to return with requested styles and values as KVP.
    var product = {};
    // The style object from the DOM element we need to iterate through.
    var style;
    // Recycle the name of the style attribute.
    var name;
    // Prevent from empty selector.
    if (source.length) {
        // Otherwise, we need to get everything.
        var dom = source.get(0);
        if (window.getComputedStyle) {
            // Convenience methods to turn css case ('background-image') to camel ('backgroundImage').
            var pattern = /\-([a-z])/g;
            var uc = function(a, b) {
                return b.toUpperCase();
            };
            var camelize = function(string) {
                return string.replace(pattern, uc);
            };
            // Make sure we're getting a good reference.
            if ((style = window.getComputedStyle(dom, null))) {
                var camel, value;
                for (var i = 0, l = style.length; i < l; i++) {
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
                for (name in style) {
                    if (typeof style[name] != 'function') {
                        product[name] = style[name];
                    }
                }
            }
            debugger;
            return product;
        }
    }
    return false;
}
});
