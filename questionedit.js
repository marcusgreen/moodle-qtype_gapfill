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
 * @package    qtype_gapfill
 * @copyright  2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/* the data is stored in a hidden field */
var feedbackdata = ($("[name='wordfeedbackdata']").val());
var feedback = new Array();
if (feedbackdata > "") {
    var obj = JSON.parse(feedbackdata);
    for (var o in obj) {
        feedback.push(obj[o]);
    }
}
var itemkey = 0;

/**
 * @param {object} item
 * @returns {Array|itemfeedback}
 */
function get_feedback(item) {
    itemfeedback = new Array();
    for (var fb in feedback) {
        if (feedback[fb].word == item.text) {
            if (feedback[fb].offset == item.offset) {
                itemfeedback[0] = feedback[fb];
            }
        }
    }
    return itemfeedback;
}
/**
 * @param {object} item
 * @returns {Array|feedback}
 */
function add_or_update(item) {
    found = false;
    for (var fb in feedback) {
        if (feedback[fb].word == item.text) {
            if (feedback[fb].offset == item.offset) {
                feedback[fb].selected = $("#id_selectededitable").html();
                feedback[fb].notselected = $("#id_notselectededitable").html();
                found = true;
            }
        }
    }
    if (found == false) {
        /* if there is no record for this word add one 
         * a combination of wordtext and offset will be unique*/
        itemkey++;
        var itemfeedback = {
            id: 'id' + itemkey,
            question: $("input[name=id]").val(),
            selected: $("#id_selectededitable").html(),
            notselected: $("#id_notselectededitable").html(),
            word: item.text,
            offset: item.offset
        };
        feedback.push(itemfeedback);
    }
    return feedback;
}


/* a click on the button */
$("#id_itemsettings").on("click", function () {
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
        $("#id_questiontextfeedback").css({
            position: "absolute",
            width: "100%",
            height: "100%",
            top: 0,
            left: 0,
            background: "lightgrey",
            color: "black",
            display: "block"
        }).appendTo(ed).css("position", "relative");
        /* $("id_questiontextfeedback").addClass($(ed).attr('class'));
         $("id_questiontextfeedback").css('line-height','17.5pt');*/

        /* Copy the real html to the feedback editing html */
        $("#id_questiontextfeedback").html($("#id_questiontexteditable").prop("innerHTML"));
        wrapContent($("#id_questiontextfeedback")[0]);
        $("#id_questiontextfeedback").css({height: fbheight, width: fbwidth});
        $("#id_questiontextfeedback").addClass("editor_atto_content");
        $("#id_gapfeedback").attr('value', 'Edit Question Text');
    } else {
        $("#id_questiontexteditable").css({display: "block", backgroundColor: "white"});
        $("#id_questiontexteditable").attr('contenteditable', 'true');
        $("#id_questiontextfeedback").css("display", "none");
        $("#fitem_id_questiontext").find('button').removeAttr("disabled");
        $("#id_feedback_popup").css("display", "none");
        $("#id_gapfeedback").attr('value', 'Add Word Feedback');
    }
});

/*A click on the text */
$("#id_questiontextfeedback").on("click", function (e) {
    if (!$('#id_questiontexteditable').get(0).isContentEditable) {
        delimitchars = $("#id_delimitchars").val();
        var item = get_selected_item(e, delimitchars);
        if (!(isNaN(e.target.id))) {
            itemfeedback = get_feedback(item);
            if (itemfeedback == null || itemfeedback.length == 0) {
                $("#id_selectededitable").html('');
                $("#id_notselectededitable").html('');
            } else {
                $("#id_selectededitable").html(itemfeedback[0].selected);
                $("#id_notselectededitable").html(itemfeedback[0].notselected);
            }
            $("label[for*='id_selected']").text(M.util.get_string("selected", "qtype_wordselect"));
            $("label[for*='id_notselected']").text(M.util.get_string("notselected", "qtype_wordselect"));
            var title = M.util.get_string("additemfeedback", "qtype_wordselect");
            title += ': ' + item.text;
            var $popup = $("#id_feedback_popup");
            $popup.dialog({
                position: {
                    my: 'right',
                    at: 'right',
                    of: "#id_questiontextfeedback"
                },
                height: 500,
                width: "70%",
                modal: true,
                title: title,
                buttons: [
                    {
                        text: "OK",
                        click: function () {
                            feedback = add_or_update(item);
                            var JSONstr = JSON.stringify(feedback);
                            $("[name='wordfeedbackdata']").val(JSONstr);
                            $(this).dialog("close");
                            /*set editable to true as it is checked at the start of click */
                            $("#id_questiontexteditable").attr('contenteditable', 'true');
                            $("#id_wordfeedback").click();
                        }
                    }
                ]
            });
        }
    }
});

function get_new_item() {
    delimitchars = $("#id_delimitchars").val();
    /*l and r for left and right */
    var l = delimitchars.substr(0, 1);
    var r = delimitchars.substr(1, 1);
    var item = {
        text: '',
        offset: null,
        l: l,
        r: r,
        stripdelim: function() {  
           var len = this.text.length;
           var startchar=this.text.indexOf(item.l);
           if(startchar > -1){
               this.text=this.text.substring(startchar+1,len); 
           }
           var endchar =this.text.indexOf(item.r);
           if(endchar > -1){
               this.text=this.text.substring(0,endchar);
           }
           return this.text;
        }
    };
    return item;
}

/**
 * 
 * @param {string} event
 * @param {string} delimitchars
 */
function get_selected_item(event, delimitchars) {

    item=get_new_item();
    /* First get the selected string ignoring
     * if there are delimiters embedded, e.g. if
     * it ends with ]. (the end of a sentence)
     */
    item.text = event.target.innerText;

    var startchar = item.text.substring(0, 1);
    var len = item.text.length;
    var endchar = (item.text.substring(len - 1, len));
    if (startchar === item.l) {
        item.text = item.text.substring(1, len);
    }
    /*if the end of the string has an embedded delimiter,
     * throw away the delimiter and all the string after it
     */
    var end_delim = item.text.indexOf(item.r);
    if (end_delim > 0) {
        item.text = item.text.substring(0, end_delim);
    }
    item.offset = event.target.id;
    return item;
}


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
var wrapContent = (function () {
    var count = 0;
    return function (el) {

        // If element provided, start there, otherwise use the body
        el = el && el.parentNode ? el : document.body;

        // Get all child nodes as a static array
        var node, nodes = toArray(el.childNodes);
        if (el.id == "id_questiontextfeedback" && (count > 0)) {
            count = 0;
        }
        var frag, parent, text;
        var re = /\S+/;
        var sp, span = document.createElement('span');

        // Tag names of elements to skip, there are more to add
        var skip = {'script': '', 'button': '', 'input': '', 'select': '',
            'textarea': '', 'option': ''};

        // For each child node...
        for (var i = 0, iLen = nodes.length; i < iLen; i++) {
            node = nodes[i];
            // If it's an element, call wrapContent
            if (node.nodeType == 1 && !(node.tagName.toLowerCase() in skip)) {
                wrapContent(node);

                // If it's a text node, wrap words
            } else if (node.nodeType == 3) {
                // Match sequences of whitespace and non-whitespace
                text = node.data.match(/\s+|\S+/g);
                if (text) {
                    // Create a fragment, handy suckers these
                    frag = document.createDocumentFragment();
                    for (var j = 0, jLen = text.length; j < jLen; j++) {
                        // If not whitespace, wrap it and append to the fragment
                        if (re.test(text[j])) {
                            sp = span.cloneNode(false);
                            sp.id = count++;
                            /*what does this class do? */
                            sp.className = 'item';
                            
                            item=get_new_item();
                            item.text=text[j];
                            item.offset=sp.id;
                            item.stripdelim();
                            
                            if (get_feedback(item) > '') {
                                sp.className = 'item hasfeedback'
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
