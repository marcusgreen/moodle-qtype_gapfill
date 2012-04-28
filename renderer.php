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
 * gapfill question renderer class.
 *
 * @package    qtype
 * @subpackage gapfill
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * Generates the output for true-false questions.
 *
 * @copyright  2009 The Open University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_gapfill_renderer extends qtype_with_combined_feedback_renderer {
//qtype_elements_embedded_in_question_text_renderer
//qtype_renderer    
    
       public function formulation_and_controls(question_attempt $qa,
            question_display_options $options) {
        $question = $qa->get_question();
        
        $answer = $qa->get_last_qt_var('answer');
         $fieldname = $question->field($place);

        //$options->correctness is really about it being ready to mark,
        if ($options->correctness) {
            $response = $qa->get_last_qt_data();
            if (array_key_exists($fieldname, $response)) {
                    echo("1");
            }
          }
       $answer=trim($answer);
       if($answer !=null){
       $answer_parts=explode(' ',$answer);  
       }else{
           $answer_parts=array();
       }
       
$fields=array();
$qt="";
$fragment_count=count($question->textfragments);
$fragment_count=$fragment_count-1;
$counter=0;

foreach ($question->textfragments as $place => $fragment) {
$qt.=$fragment;
//$options="";
    if($place<$fragment_count){
    
        $qt.=$this->embedded_element($qa, $place,$answer_parts);
    }    
    $counter++;
}

return $qt;      
}


 function embedded_element(question_attempt $qa, $place,$answer_parts) {
$value="";
$qprefix=$qa->get_qt_field_name('');

$inputfield='<input type="text" name="'.$qprefix.'q'.$place.'"id='.$qprefix.'q'.$place ;

if(sizeof($answer_parts)>0){
    $value=' value="'.$answer_parts[$place].'"';
}

$inputfield.=$value.' />'.$this->feedback_image(0);
 
return $inputfield;
}
    public function specific_feedback(question_attempt $qa) {
        $question = $qa->get_question();
        $response = $qa->get_last_qt_var('answer', '');

        if ($response) {
            return $question->format_text($question->truefeedback, $question->truefeedbackformat,
                    $qa, 'question', 'answerfeedback', $question->trueanswerid);
        } else if ($response !== '') {
            return $question->format_text($question->falsefeedback, $question->falsefeedbackformat,
                    $qa, 'question', 'answerfeedback', $question->falseanswerid);
        }
    }

    public function correct_response(question_attempt $qa) {
        //var_dump($qa);
        //exit();
      $question = $qa->get_question();
       $answer = $question->get_matching_answer($question->get_correct_response());
        if (!$answer) {
            return '';
        }

        return get_string('correctansweris', 'qtype_gapfill', s($answer->answer));
    }

   
     
}
