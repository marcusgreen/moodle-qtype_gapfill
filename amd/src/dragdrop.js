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
 * @copyright  2017 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'jqueryui', 'qtype_gapfill/jquery.ui.touch-punch-improved'], function($) {
    return {
        init: function() {
          // var dropvals = [];
          // $(".droptarget").each(function(el) {
          //   dropvals.push($(this).val());
          // });
          // $(".draggable").each(function(el){
          //   if(dropvals.includes($(this).text())) {
          //     document.getElementById(this.id ).style.visibility = 'hidden';
          //   }
          // });
          $(".droptarget").on('dblclick', function() {
            dragShow(this);
            $(this).val("");

          })

          // function dragHide(that){
          //   var draggables = $(".draggable");
          //   var targetVal = $(that).val();
          //   for(i=0;i < draggables.length;i++){
          //    sourceVal = draggables[i].textContent;
          //    if(sourceVal == targetVal){
          //      $(draggables[i]).addClass("hide");
          //    }
          //   }
          // }
          function dragShow(that){
            var draggables = $(".draggable");
            var targetVal = $(that).val();
            for(i=0;i < draggables.length;i++){
             sourceVal = draggables[i].textContent;
             if(sourceVal == targetVal){
               $(draggables[i]).removeClass("hide");
             }
            }
          }

          $(".droptarget").on('keydown drop', function() {
            dragShow(this);
          });


          // function dragvisibility(that){
          //   debugger;

          //   var draggables = $(".draggable");
          //   var targetVal = $(that).val();
          //   for(i=0;i < draggables.length;i++){
          //    sourceVal = draggables[i].textContent;
          //    if(sourceVal == targetVal){
          //      $(draggables[i]).addClass("hide");
          //      $(that).val("");
          //    }
          //   }
          // }


            $(".draggable").draggable({
                revert: false,
                helper: 'clone',
                cursor: 'pointer',
                scroll: 'false',
            });

            $(".droptarget").droppable({
                hoverClass: 'active',
                drop: function(event, ui) {
                    if ($(ui.draggable).hasClass('readonly')) {
                        return;
                    }
                    this.value = $(ui.draggable).text();
                    $(ui.draggable).addClass("hide");
                }
            });
        }
    };
});