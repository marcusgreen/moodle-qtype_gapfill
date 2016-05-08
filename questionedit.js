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

$("#new-feedback-for").on("click", function () {
    var $somevalue=1;
    $("#gapfeedback-form").append(
            '<label for="name">Response </label> <input id=new-feedback-for type=text class="gfinput" />'+
            '<label for="name">Feedback </label> <input id=new-feedback-for type=text class="gfinput"/>'
            );
});


$("#fitem_id_questiontext").on("click", function () {
    $the_text = $("#id_questiontexteditable").text();
    // alert($the_text);
    rangy.init();
    $sel = rangy.getSelection();
    $qtext = $sel.anchorNode.nodeValue;
    $clickpoint = $sel.focusOffset;
    x = $clickpoint;

    $leftdelim = null;
    for (var x = $clickpoint; x > 0; x--)
    {
        if ($qtext.charAt(x) === "]") {
            break;
        }
        if ($qtext.charAt(x) === "[") {
            $leftdelim = x + 1;
            break;
        }
    }

    $rightdelim = null;
    for (var x = $clickpoint; x < $qtext.length; x++)
    {
        if ($qtext.charAt(x) === "[") {
            break;
        }
        if ($qtext.charAt(x) === "]") {
            $rightdelim = x;
            break;
        }
    }

    $gaptext = null;
    if ($leftdelim != null) {
        if ($rightdelim != null) {
            $gaptext = $the_text.substring($leftdelim, $rightdelim);
            $("#gaptext").val($gaptext);
            $("#correctfeedback").focus();
            $("#gapfeedback-form").dialog({
                height: 500,
                width: 650,
                modal: true,
                buttons: [
                    {
                       text: "OK",
                    }
                ]
            });


        }
    }


});
