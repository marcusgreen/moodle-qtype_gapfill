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
 * @package    qtype
 * @subpackage gapfill
 * @copyright  2016 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* the data is stored in a hidden field */
var feedbackdata = ($("[name='gapfeedbackdata']").val());
var $feedback = JSON.parse(feedbackdata);

function get_feedback($gaptext, gapoffset) {
    retval = null;
    for (var fb in $feedback) {
        if ($feedback[fb].gaptext === $gaptext) {
            if ($feedback[fb].gapoffset == gapoffset) {
                retval = $feedback[fb];
            }
        }
    }
    return retval;
}

function add_or_update($gaptext, gapoffset) {
    found = null;
    for (var fb in $feedback) {
        if ($feedback[fb].gaptext === $gaptext && $feedback[fb].gapoffset == gapoffset) {
            $feedback[fb].incorrect = $("#incorrect").val(),
                    $feedback[fb].correct = $("#correct").val(),
                    found = $feedback[fb];
        }
    }
    if (found === null) {
        /* if there is no record for this gap add one 
         * a combination of gaptext and offset will be unique*/
        $feedback[$gaptext+gapoffset]=  {
            question: $('#id').val(),
            incorrect: $("#incorrect").val(),
            correct: $("#correct").val(),
            gaptext: $gaptext,
            gapoffset: gapoffset
        };
    }
}

$("#fitem_id_questiontext").on("click", function () {
    $the_text = $("#id_questiontexteditable").text();
    delimitchars = $("#id_delimitchars").val();
    /*l and r for left and right */
    l = delimitchars.substr(0, 1);
    r = delimitchars.substr(1, 1);
    rangy.init();
    $sel = rangy.getSelection();
    $qtext = $sel.anchorNode.nodeValue;
    $clickpoint = $sel.focusOffset;
    /* find the character num of the left delimiter*/
    $leftdelim = null;
    for (var x = $clickpoint; x > 0; x--)
    {
        if ($qtext.charAt(x) === l) {
            $leftdelim = x + 1;
            break;
        }
        if ($qtext.charAt(x) === r) {
            break;
        }
    }
    /* find the character num of the right delimiter*/
    $rightdelim = null;
    for (var x = $clickpoint; x < $qtext.length; x++)
    {
        if ($qtext.charAt(x) === l) {
            break;
        }
        if ($qtext.charAt(x) === r) {
            $rightdelim = x;
            break;
        }
    }


    $gaptext = null;
    if ($leftdelim !== null) {
        if ($rightdelim !== null) {
            $gaptext = $the_text.substring($leftdelim, $rightdelim);
            /* Stores where it is in the string, e.g. if it is the only one it will be 0, if there are two it 
             * will be 1 etc etc
             */
            var uptothisgap = $qtext.substr(0, $leftdelim);
            var gapoffset = uptothisgap.split($gaptext).length - 1;
            $fb = get_feedback($gaptext, gapoffset);
            if ($fb !== null) {
                $("#incorrect").val($fb["incorrect"]);
                $("#correct").val($fb["correct"]);
            } else {
                $("#incorrect").val("");
                $("#correct").val("");
            }
            $("#gaptext").val($gaptext);
            $("#gapfeedback-form").dialog({
                height: 350,
                width: 650,
                modal: true,
                buttons: [
                    {
                        text: "OK",
                        click: function () {
                            add_or_update($gaptext, gapoffset);
                            var JSONstr = JSON.stringify($feedback);
                            $("[name='gapfeedbackdata']").val(JSONstr);
                            $(this).dialog("close");
                        }

                    }
                ]
            });
        }
    }
}
);
