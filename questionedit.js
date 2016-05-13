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


var feedback_text = '[{"gaptext":"cat","index":"0","correct":"that is correct","incorrect":"wrong answer","response":"bat","feedback":"no not bat"},\n\
{"gaptext":"mat","index":"0","correct":"that is correct","incorrect":"wrong answer","response":"rug","feedback":"no not rug"}]';


$feedback = JSON.parse(feedback_text);

function get_feedback($gaptext,$index){
        for(index=0;index< $feedback.length;index++){
        if($feedback[index]["gaptext"]==$gaptext){
            alert($feeedback[index]["correct"]);
        }
      
    }
    
}

$("#new-response").on("click", function () {

    var $feedbackcount = 1;
    $("#gapfeedback-form").append(
            '<label for="name">Response </label> \n\
            <input id=response[' + $feedbackcount + '] name=response_' + $feedbackcount + ' type=text class="gfinput" />' +
            '<label for="name">Feedback </label> \n\
            <input id=feedback[' + $feedbackcount + '] name=response_' + $feedbackcount + ' type=text class="gfinput"/>'
            );
    $("#new-feedback-for").focus();
});

$("#fitem_id_questiontext").on("click", function () {
    $the_text = $("#id_questiontexteditable").text();
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
            x=get_feedback($gaptext,0);
            $("#gaptext").val($gaptext);
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
