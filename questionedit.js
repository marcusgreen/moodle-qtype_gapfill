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
 * @package    qtype_gapfill
 * @copyright  2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/* the data is stored in a hidden field */
var settingsdata = ($("[name='itemsettingsdata']").val());


var settings = new Array();
var gaps = new Array();
if (settingsdata > "") {
    var obj = JSON.parse(settingsdata);
    for (var o in obj) {
        settings.push(obj[o]);
    }
}
function Item(text, delimitchars) {
    this.questionid = $("input[name=id]").val(),
    this.text = text;
    this.delimitchars = delimitchars;
    /*l and r for left and right */
    this.l = delimitchars.substr(0, 1);
    this.r = delimitchars.substr(1, 1);
    this.len = this.text.length;
    this.startchar = this.text.substring(0, 1);
    /*for checking if the end char is the right delimiter */
    this.endchar = this.text.substring(this.len - 1, this.len);
    this.text_nodelim = '';
    this.feedback = {};
    this.feedback.correct = $("#id_corecteditable").html(),
    this.feedback.notcorrect = $("#id_notcorrecteditable").html();
    this.get_text_nodelim = function () {
                if (this.startchar === this.l) {
                    this.text_nodelim = this.text.substring(1, this.len);
                }
                if (this.endchar === this.r) {
                    len = this.text_nodelim.length;
                    this.text_nodelim = this.text_nodelim.substring(0, len - 1);
                }
                return this.text_nodelim;
            }
    itemsettings = new Array();
    this.get_itemsettings = function (target) {
        var id = target.id;
        /*The instance, normally 0 but incremented if a gap has the ame text as another*/
        instance=id.substr(id.indexOf("_")+1);
        this.get_text_nodelim();
        for (var set in settings) {
            var set_instance = settings[set].id.substr(id.indexOf("_")+1);
            if (settings[set].text === this.text) {
                if (set_instance === instance) {
                    itemsettings = settings[set];
                }
            }
        }
        return itemsettings;
    };
    this.update_json = function (e) {
        found = false;
        var id = e.target.id;
        for (var set in settings) {
            if (settings[set].text === this.text) {
                if ((settings[set].id.substr(id.indexOf("_")+1) === this.instance)) {
                    settings[set].correct = $("#id_correcteditable")[0].innerHTML;
                    settings[set].notcorrect = $("#id_notcorrecteditable")[0].innerHTML;
                    found = true;
                }
            }
        }
        if (found === false) {
            /* if there is no record for this word add one 
             * a combination of text and offset will be unique*/
            var itemfeedback = {
                id: id,
                questionid: $("input[name=id]").val(),
                correct: $("#id_correcteditable").html(),
                notcorrect: $("#id_notcorrecteditable").html(),
                text: this.text
            };
            settings.push(itemfeedback);
        }
        return JSON.stringify(settings);
    };
}


/* a click on the button */
$("#id_itemsettings_button").on("click", function () {
    var atto_islive = ($(".editor_atto")).length;
    /* show error if Atto is not loaded. It might be because the page has not finished loading
     * or because plain text elements are being used or (perhaps less likely as time goes on)
     * the HTMLarea editor is being used. It might be possible to work with those other editors
     * but limiting to Atto keeps things straightforward and maintainable.
     */
    if (atto_islive < 1) {
        $("#id_error_itemsettings").css({'display': 'inline', 'color': 'red'});
        $("#id_error_itemsettings")[0].innerHTML = M.util.get_string("itemsettingserror", "qtype_gapfill");
        return;
    }
    if ($('#id_questiontexteditable').get(0).isContentEditable) {
        $("#id_questiontexteditable").attr('contenteditable', 'false');
        $("#fitem_id_questiontext").find('button').attr("disabled", 'true');
        var fbheight = $("#id_questiontexteditable").css("height");
        var fbwidth = $("#id_questiontexteditable").css("width");
        $("#id_questiontexteditable").css("display", 'none');
        var ed = $("#id_questiontexteditable").closest(".editor_atto_content_wrap");
        $("#id_itemsettings_canvas").css({
            position: "absolute",
            width: "100%",
            height: "100%",
            top: 0,
            left: 0,
            background: "lightgrey",
            color: "black",
            display: "block"
        }).appendTo(ed).css("position", "relative");

        /* Copy the real html to the feedback editing html */
        $("#id_itemsettings_canvas").html($("#id_questiontexteditable").prop("innerHTML"));
        wrapContent($("#id_itemsettings_canvas")[0]);
        $("#id_itemsettings_canvas").css({height: fbheight, width: fbwidth});
        $("#id_itemsettings_canvas").addClass("editor_atto_content");
        $("#id_itemsettings_button").html('Edit Question Text');
    } else {
        $("#id_questiontexteditable").css({display: "block", backgroundColor: "white"});
        $("#id_questiontexteditable").attr('contenteditable', 'true');
        $("#id_itemsettings_canvas").css("display", "none");
        $("#fitem_id_questiontext").find('button').removeAttr("disabled");
        $("#id_feedback_popup").css("display", "none");
        $("#id_itemsettings_button").html('Add Gap Settings');
    }
});

/*A click on the text */
$("#id_itemsettings_canvas").on("click", function (e) {
    if (!$('#id_questiontexteditable').get(0).isContentEditable) {
        delimitchars = $("#id_delimitchars").val();
        var item = new Item(e.target.innerHTML,delimitchars);
        if ((e.target.id.substr(0,2)==='id')) {
            itemsettings = item.get_itemsettings(e.target);
            if (itemsettings === null || itemsettings.length === 0) {
                $("#id_correcteditable").html('');
                $("#id_notcorrecteditable").html('');
            } else {
                $("#id_correcteditable").html(itemsettings.correct);
                $("#id_notcorrecteditable").html(itemsettings.notcorrect);
            }
            $("label[for*='id_correct']").text(M.util.get_string("correct", "qtype_gapfill"));
            $("label[for*='id_notcorrect']").text(M.util.get_string("notcorrect", "qtype_gapfill"));
            var title = M.util.get_string("additemsettings", "qtype_gapfill");
            title += ': ' + item.get_text_nodelim();
            var $popup = $("#id_itemsettings_popup");
            $popup.dialog({
                position: {
                    my: 'right',
                    at: 'right',
                    of: "#id_itemsettings_canvas"
                },
                height: 500,
                width: "70%",
                modal: true,
                title: title,
                buttons: [
                    {
                        text: "OK",
                        click: function () {
                            var JSONstr = item.update_json(e);
                            $("[name='itemsettingsdata']").val(JSONstr);
                            $(this).dialog("close");
                            /*set editable to true as it is checked at the start of click */
                            $("#id_questiontexteditable").attr('contenteditable', 'true');
                            $("#id_itemsettings_button").click();
                        }
                    }
                ]
            });
        }
    }
});



function toArray(obj) {
    var arr = [];
    for (var i = 0, iLen = obj.length; i < iLen; i++) {
        arr.push(obj[i]);
    }
    return arr;
}


// Wrap the words of an element and child elements in a span
// Recurs over child elements, add an ID and class to the wrapping span
// Does not affect elements with no content, or those to be excluded
var wrapContent = (function (delimitchars) {

    return function (el) {
        var count = 0;
        gaps = [];
        // If element provided, start there, otherwise use the body
        el = el && el.parentNode ? el : document.body;

        // Get all child nodes as a static array
        var node, nodes = toArray(el.childNodes);
        if (el.id === "id_questiontextfeedback" && (count > 0)) {
            count = 0;
        }
        var frag, parent, text;
        var regex = /.*?\[(.*?)\]/;
        var sp, span = document.createElement('span');

        // Tag names of elements to skip, there are more to add
        var skip = {'script': '', 'button': '', 'input': '', 'select': '',
            'textarea': '', 'option': ''};

        // For each child node...
        for (var i = 0, iLen = nodes.length; i < iLen; i++) {
            node = nodes[i];
            // If it's an element, call wrapContent
            if (node.nodeType === 1 && !(node.tagName.toLowerCase() in skip)) {
                wrapContent(node);

                // If it's a text node, wrap words
            } else if (node.nodeType === 3) {
                // Match sequences of whitespace and non-whitespace
                // text = node.data.match(/\s+|\S+/g);
                text = node.data.match(/(\s+)|([A-z]+)|(\.)/g);

                if (text) {
                    // Create a fragment, handy suckers these
                    frag = document.createDocumentFragment();
                    for (var j = 0, jLen = text.length; j < jLen; j++) {
                        // If not whitespace, wrap it and append to the fragment
                        if (regex.test(text[j])) {
                            sp = span.cloneNode(false);
                            count++;
                            sp.className = 'item';
                            item = new Item(text[j],$("#id_delimitchars").val());                            
                            if (item.text > '') {
                                var instance=0;
                                for(var i=0; i < gaps.length; ++i){
                                        if(gaps[i] === item.text){
                                            instance++;
                                        }
                                }
                                item.id='id'+count +'_'+ instance;
                                sp.id=item.id;
                               var is = item.get_itemsettings(item);                               
                                if ((is.correct >"") || (is.notcorrect >"")){
                                        sp.className = 'item hasfeedback';
                                 }
                                 gaps.push(item.text);
                            }
                            sp.appendChild(document.createTextNode(text[j]));
                            frag.appendChild(sp);

                            // Otherwise, just append it to the fragment
                        } else {
                            frag.appendChild(document.createTextNode(text[j]));
                        }
                    }
                }
                // Replace the original node with the fragment
                node.parentNode.replaceChild(frag, node);
            }
        }
    };
}());
