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
define(['jquery'], function($) {

    return {
        init: function() {
            $("#id_itemsettings_button").on("click", function() {
                setupcanvas();
            });
        }
    };
    function setupcanvas(){
        debugger;
        if ($('#id_questiontexteditable').get(0).isContentEditable) {
            $("#id_questiontexteditable").attr('contenteditable', 'false');
            $("#fitem_id_questiontext").find('button').attr("disabled", 'true');
            var settingformheight = $("#id_questiontexteditable").css("height");
            var settingformwidth = $("#id_questiontexteditable").css("width");
            $("#id_questiontexteditable").css("display", 'none');
            /* Copy the styles from attos editable area so the canvas looks the same (except gray) */
            $('#id_itemsettings_canvas').css(copyStyles($("#id_questiontexteditable")));
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
           // wrapContent($("#id_itemsettings_canvas")[0]);
        } else{
            $("#id_questiontexteditable").css({display: "block", backgroundColor: "white"});
            $("#id_questiontexteditable").attr('contenteditable', 'true');
            $("#id_itemsettings_canvas").css("display", "none");
            $("#fitem_id_questiontext").find('button').removeAttr("disabled");
            $("#id_settings_popup").css("display", "none");
            $("#id_itemsettings_button").html(M.util.get_string("additemsettings", "qtype_gapfill"));
            $('[class^=atto_]').removeAttr("disabled");
        }
        
    }
/**
 *
 * @param {array} source
 * @return {array} product
 */
function copyStyles(source) {
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
            return product;
        }
    }
    return false;
}
});