<?php

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
 * Mobile output class for qtype_gapfill
 *
 * @package    qtype_gapfill
 * @copyright  2018 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace qtype_gapfill\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Mobile output class for gapfill question type
 *
 * @package    qtype_gapfill
 * @copyright  2018 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {

    /**
     * Returns the gapfill quetion type for the quiz the mobile app.
     * @param  array $args Arguments from tool_mobile_get_content WS
     *
     * @return array       HTML, javascript and otherdata
     */
    public static function mobile_get_gapfill() {
        global $OUTPUT, $USER, $DB, $CFG;
           $template = <<<TEMPLATE
           <section class="list mma-qtype-gapfill-container" ng-if="question.text || question.text === ''">
    <div class="item item-text-wrap">
           <mm-format-text ng-if="question.optionsaftertext" ng-onhold="clearGap($event)" ng-dblclick="clearGap($event)" ng-click="selectAnswer($event)"7 class="mm-content-with-float qtext" component="{{component}}" component-id="{{componentId}}">{{ question.text }}</mm-format-text>
           <p ng-if="!question.readonly && question.isdragdrop" class="mm-info-card-icon gapfill-item-howto"><i class="icon ion-information"></i> {{ 'mm.question.howtodraganddrop' | translate }}</p>
           <p>  <mm-format-text ng-if="question.isdragdrop" ng-onhold="clearGap($event)" ng-click="selectAnswer($event)" watch="true">{{question.answeroptions}} </mm-format-text></p>	
           <mm-format-text ng-if="!question.optionsaftertext" ng-onhold="clearGap($event)" ng-dblclick="clearGap($event)" ng-click="selectAnswer($event)"7 class="mm-content-with-float qtext" component="{{component}}" component-id="{{componentId}}">{{ question.text }}</mm-format-text>
  <mm-format-text watch="true" ng-if="question.feedbackHTML" component="{{component}}" component-id="{{componentId}}">
            {{ question.feedback }}
        </mm-format-text>
    </div>
</section>
TEMPLATE;
         $jsfilepath = $CFG->wwwroot . '/question/type/gapfill/javascript/mobile.js';
         $jscontent=file_get_contents($jsfilepath);

        global $CFG;
        return [
    'templates' => [
       
           'id' => 'main',
           'html' => $template        
     ],
     'javascript' => $jscontent
    ];
    }
 

}
