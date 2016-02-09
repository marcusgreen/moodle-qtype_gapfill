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

$("#id_gapfeedback").click(function (event) {
    if ($(".qtx").css("display") !== "none") {
      //  $(".static_qtx").css("visibility", "visible");
      //  $(".qtx").css("visibility", "hidden");
        $(".mavg").height($(".qtx").height());
       // var offset= $(".qtx").offset();
        
        $(".static_qtx").css("display", "block");
        $(".qtx").css("display", "none");
        $("#id_gapfeedback").attr('value', 'Edit Question');
       // alert($("#id_questiontext").val());
    } else {
        //$(".static_qtx").css("visibility", "hidden");
        //$(".qtx").css("visibility", "visible");
        $(".static_qtx").css("display", "none");
        $(".qtx").css("display", "block");
        $("#id_gapfeedback").attr('value', 'Add Gap Feedback');
    }
});
