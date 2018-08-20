// (C) Copyright 2015 Martin Dougiamas
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//     http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

/**
 * Component to render a gapfil question.
 */

var that = this;
var result = {
 
    componentInit: function () {
        this.questionRendered = function questionRendered() {
            var draggables = this.componentContainer.querySelectorAll('.draggable');
            for (var i = 0; i < draggables.length; i++) {
                var drag =draggables[i];
                    if (drag.id) {
                        drag.addEventListener('click', () => {
                            event.currentTarget.classList.toggle('picked');
                        })
                    }
                }
        }

        if (!this.question) {
            console.warn('Aborting because of no question received.');
            return that.CoreQuestionHelperProvider.showComponentError(that.onAbort);
        }
        const div = document.createElement('div');
        div.innerHTML = this.question.html;
        // Get question questiontext.
        const questiontext = div.querySelector('.qtext');
  

        // Replace Moodle's correct/incorrect and feedback classes with our own.
        this.CoreQuestionHelperProvider.replaceCorrectnessClasses(div);
        this.CoreQuestionHelperProvider.replaceFeedbackClasses(div);

         // Treat the correct/incorrect icons.
        this.CoreQuestionHelperProvider.treatCorrectnessIcons(div);

        /* from core question */
        //const answerContainer = div.querySelector('.answercontainer');

         // Get answeroptions/draggables.
        const answeroptions = div.querySelector('.answeroptions');

        if (div.querySelector('.readonly') != null) {
            this.question.readonly = true;
        }

        if (div.querySelector('.feedback') != null) {
            this.question.feedback = questionEl.querySelector('.feedback');
            this.question.feedbackHTML = true;
        }
        /* set all droppables to disabled but remove the faded look shown on ios
         * This prevents the keyboard popping up when a droppable is dropped onto
         * a droptarget.
         */
        if (answeroptions !== null) {
            var droptargets = questiontext.querySelectorAll('.droptarget');
            droptargets.forEach((elem) => {
                elem.style.webkitOpacity = 1;
                elem.disabled = "true";
            });

        }

        this.CoreDomUtilsProvider.removeElement(div,'input[name*=sequencecheck]');
        this.CoreDomUtilsProvider.removeElement(div,'.validationerror');

        this.question.text = this.CoreDomUtilsProvider.getContentsOfElement(div, '.qtext');
        this.question.answeroptions = answeroptions.innerHTML;

        if (typeof this.question.text == 'undefined') {
            this.logger.warn('Aborting because of an error parsing question.', this.question.name);
            return this.CoreQuestionHelperProvider.showComponentError(this.onAbort);
        }


    }
    
}
result;




