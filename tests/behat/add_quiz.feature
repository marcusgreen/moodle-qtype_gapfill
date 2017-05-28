@mod @mod_quiz

Feature: Add a quiz
  In order to evaluate students
  As a teacher
  I need to create a quiz with gapfill questions

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Terry1    | Teacher1 | teacher1@example.com |
      | student1 | Sam1      | Student1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1 | 0 |
    And the following "course enrolments" exist:
      | user | course | role |
      | teacher1 | C1 | editingteacher |
      | student1 | C1 | student |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I add a "Quiz" to section "1" and I fill the form with:
      | Name        | Test quiz name        |
      | Description | Test quiz description |


   And I add a "Gapfill" question to the "Test quiz name" quiz with:
      | Question name                      | First question                         |
      | Question text                      | The [cat] sat on the mat               |
      | General feedback                   | General feedback cat mat|

 And I add a "Gapfill" question to the "Test quiz name" quiz with:
      | Question name                      | Second question                         |
      | Question text                      | The [cow] jumped over the [moon]        |
      | General feedback                   | General feedback cow moon|

    And I press "Repaginate"
    And I press "Go"
    And I log out
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Test quiz name"
    And I press "Attempt quiz now"
    Then I should see "Question 1"
    And I type "cat" into gap "1" in the gapfill question
    And I press "Next page"
    Then I should see "Question 2"
    And I type "cow" into gap "1" in the gapfill question  
    And I type "moon" into gap "2" in the gapfill question  
    And I press "Finish attempt ..."
    And I press "Submit all and finish"

  @javascript
  Scenario: Add and configure small quiz and perform an attempt as a student with Javascript enabled
    Then I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I follow "Finish review"
    And I log out

