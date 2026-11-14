@mod @mod_quiz @qtype @qtype_gapfill @javascript @qtype_gapfill_review_unvisited
Feature: Review shows right answers for gapfill questions on unvisited pages
  In order to learn from a quiz I did not complete
  As a student
  I need to see the right answers for every question in the review,
  including those on pages I never visited
  See https://github.com/marcusgreen/moodle-qtype_gapfill/issues/118

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam1      | Student1 | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype   | name            | questiontext                     |
      | Test questions   | gapfill | First question  | The [cat] sat on the [mat]       |
      | Test questions   | gapfill | Second question | The [cow] jumped over the [moon] |
    And the following "activity" exists:
      | activity           | quiz                  |
      | course             | C1                    |
      | name               | Gapfill multipage     |
      | idnumber           | quiz1                 |
      | preferredbehaviour | deferredfeedback      |
    And quiz "Gapfill multipage" contains the following questions:
      | question        | page |
      | First question  | 1    |
      | Second question | 2    |

  Scenario: Finish an attempt without visiting page 2 and review it
    Given I am on the "Gapfill multipage" "mod_quiz > View" page logged in as "student1"
    And I press "Attempt quiz"
    And I should see "Question 1"
    # Finish straight from page 1; page 2 is never visited.
    And I follow "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Submit all your answers and finish?" "dialogue"
    Then I should see "[cat]" in the "sat on the" "question"
    And I should see "[mat]" in the "sat on the" "question"
    And I should see "[cow]" in the "jumped over the" "question"
    And I should see "[moon]" in the "jumped over the" "question"
