@core @qtype @qtype_gapfill @_switch_window
Feature: Test all the basic functionality of this question type
  In order to evaluate students responses, As a teacher I need to
  create and preview wordselect (Select correct words) questions.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |

  @javascript
  Scenario: Create, edit then preview a gapfill question.
    Given I log in as "teacher1"
    And I follow "Course 1"
    And I navigate to "Question bank" node in "Course administration"

    # Create a new question.
    And I add a "Gapfill" question filling the form with:
      | Question name             | Gapfill-001                   |
      | Question text             | The cat [sat] on the [mat]    |
      | General feedback          | This is general feedback      |
      | Hint 1                    | First hint                    |
      | Hint 2                    | Second hint                   |
    Then I should see "Gapfill-001"

    # Preview it.
    When I click on "Preview" "link" in the "Gapfill-001" "table_row"
    And I switch to "questionpreview" window

    #################################################
    #Interactive with multiple tries
    #################################################
    And I set the following fields to these values:
      | How questions behave | Interactive with multiple tries |
      | Marked out of        | 2                               |
      | Marks                | Show mark and max               |
      | Specific feedback    | Shown |
      | Right answer         | Shown |
    And I press "Start again with these options"

    #Enter both correct responses 
    And I type "sat" into gap "1" in the gapfill question
    And I type "mat" into gap "2" in the gapfill question

    And I press "Check"      
    And I should see "Your answer is correct."
    And I should see "Mark 2.00 out of 2.00"

    #Enter one incorrect option on the first attempt
    #and all/both correct options on the second attempt
    ################################################
    #first attempt
    And I press "Start again with these options"
    And I type "sat" into gap "1" in the gapfill question
    And I type "xxx" into gap "2" in the gapfill question

    And I press "Check"      
    And I should see "Your answer is partially correct."

    ################################################
    #second attempt
    And I press "Try again"
    And I type "sat" into gap "1" in the gapfill question
    And I type "mat" into gap "2" in the gapfill question
    
    And I press "Check"      
    And I should see "Your answer is correct."
    And I should see "Mark 1.67 out of 2.00"
    And I wait "2" seconds
    

    ##################################################
    # Deferred Feedback behaviour
     And I set the following fields to these values:
      | How questions behave | Deferred feedback |
      | Marked out of        | 2                               |
      | Marks                | Show mark and max               |
      | Specific feedback    | Shown |
      | Right answer         | Shown |
    
    And I press "Start again with these options" 
    And I type "sat" into gap "1" in the gapfill question
    And I type "mat" into gap "2" in the gapfill question

    And I press "Submit and finish"      
    And I should see "Your answer is correct."
    And I should see "Mark 2.00 out of 2.00"
    And I wait "5" seconds

    And I press "Start again with these options" 
    And I type "sat" into gap "1" in the gapfill question
    And I type "xxx" into gap "2" in the gapfill question

    And I press "Submit and finish"      
    And I should see "Your answer is partially correct."
    And I should see "Mark 1.00 out of 2.00"
    And I wait "5" seconds
    
    And I press "Start again with these options" 
    And I type "xxx" into gap "1" in the gapfill question
    And I type "yyy" into gap "2" in the gapfill question
  
    And I press "Submit and finish"      
    And I should see "Your answer is incorrect."
    And I should see "Mark 0.00 out of 2.00"
    And I wait "5" seconds
