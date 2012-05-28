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
 * Gapfill question definition class.
 *
 * @package    qtype
 * @subpackage gapfill
 * @copyright  2012 Marcus Green
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class qtype_gapfill_question extends question_graded_automatically_with_countback {

    public $answer;
	/* display answers as a hint */
    public $showanswers;

    /** @var array of question_answer. */
    public $answers = array();

    public $answerwords = array();

    public function __construct() {
        // parent::__construct(new question_first_matching_answer_grading_strategy($this));
    }

    /**
     * @var array place number => group number of the places in the question
     * text where choices can be put. Places are numbered from 1.
     */
    public $places = array();

    /**
     * @var array of strings, one longer than $places, which is achieved by
     * indexing from 0. The bits of question text that go between the placeholders.
     */
    public $textfragments;

    /** @var array index of the right choice for each stem. */
    public $rightchoices;

    /**
     * @param int $key stem number
     * @return string the question-type variable name.
     */
    public function field($place) {
        return 'p' . $place;
    }

    public function get_expected_data() {
        /* it may make more sense to think of this as get expected data types */
        $data = array();
        foreach ($this->places as $key => $value) {
            $data['p' . $key] = PARAM_RAW_TRIMMED;
        }
        return $data;
    }

/* For displaying a list of correct answers randomly shuffled */
    public function get_shuffled_answers() {
       $random_answers=$this->places;
       shuffle($random_answers);
       /* return a string of answers as a string with gaps */
       return implode(" ",$random_answers);
    }

    /*
     * Value returned will be written to responsesummary field of 
     * the question_attempts table
     */
    public function summarise_response(array $response) {
      $retval="";  
        foreach($response as $key=>$value){
            $retval.=" [".$value."]";            
        }
        return $retval;
    }

   public function is_complete_response(array $response) {
/* checks that none of of the gaps is blanks */
       foreach ($this->answers as $key => $value) {
         $ans=array_shift($response);
          if($ans==""){
               return false;
           }          
         }
       return true;
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
           return 'xyz';
        }
        return get_string('pleaseenterananswer', 'qtype_gapfill');
    }
   public function get_right_choice_for($place) {
       
        return $this->places[$place];
}
  
    
    public function is_same_response(array $prevresponse, array $newresponse) {

        //return question_utils::arrays_same_at_key_missing_is_blank(
        //              $prevresponse, $newresponse, 'answer');
    }

    public function compare_response_with_answer(array $response, question_answer $answer) {

       // var_dump($response);
        //exit();
        //   return self::compare_string_with_wildcard(
        //                 $response['answer'], $answer->answer, !$this->usecase);
    }

//    public static function compare_string_with_wildcard($string, $pattern, $ignorecase) {
//        // Break the string on non-escaped asterisks.
//        $bits = preg_split('/(?<!\\\\)\*/', $pattern);
//        // Escape regexp special characters in the bits.
//        $excapedbits = array();
//        foreach ($bits as $bit) {
//            $excapedbits[] = preg_quote(str_replace('\*', '*', $bit));
//        }
//        // Put it back together to make the regexp.
//        $regexp = '|^' . implode('.*', $excapedbits) . '$|u';
//
//        // Make the match insensitive if requested to.
//        if ($ignorecase) {
//            $regexp .= 'i';
//        }
//
//        return preg_match($regexp, trim($string));
//    }

    public function is_gradable_response(array $response) {
         return   $this->is_complete_response($response);
    }


    public function get_correct_response() {
        $response = array();
        $string = "";

        foreach ($this->places as $answer) {
            $string = $string . " " . $answer;
        }
       $response['answer'] = $string;

        $i = 0;
        foreach ($this->answers as $answer) {
            $this->answerwords[$i] = $answer->answer;
            $i++;
        }
        return $response;
    }

    public function grade_response(array $response) {
/* only runs if is_complete_response has returned true */         
//     var_dump($my_array);
//         exit();

        
        $fraction = 0;
      foreach ($this->answers as $key => $value) {
          $ans=array_shift($response);
          if($ans==$value->answer){
               $fraction++;
           }          
         }
         $fraction=$fraction/$this->defaultmark;
    //  $grade= question_state::graded_state_for_fraction($fraction); 
      //return array($fraction/$this->defaultmark,$grade);
         
      $my_array=array($fraction, question_state::graded_state_for_fraction($fraction));
      return $my_array;
    }
     public function compute_final_grade($responses, $totaltries) {
//        $totalscore = 0;
//        foreach ($this->places as $place => $notused) {
//            $fieldname = $this->field($place);
//
//            $lastwrongindex = -1;
//            $finallyright = false;
//            foreach ($responses as $i => $response) {
//                if (!array_key_exists($fieldname, $response) ||
//                        $response[$fieldname] != $this->get_right_choice_for($place)) {
//                    $lastwrongindex = $i;
//                    $finallyright = false;
//                } else {
//                    $finallyright = true;
//                }
//            }
//
//            if ($finallyright) {
//                $totalscore += max(0, 1 - ($lastwrongindex + 1) * $this->penalty);
//            }
        }


    /**
     * Get an answer that contains the feedback and fraction that should be
     * awarded for this resonse.
     * @param array $response a response.
     * @return question_answer the matching answer.
     */
    public function get_matching_answer(array $response) {
      //var_dump($this->answers);
      //exit();
   }

}
