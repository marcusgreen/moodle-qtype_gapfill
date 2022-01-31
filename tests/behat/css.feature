@core @qtype @qtype_gapfill @qtype_gapfill_css @_switch_window
Feature: Test all the basic functionality of this Gapfill question type
    In order to evaluate students responses, As a teacher I need to
  create and preview gapfill questions.

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
    And I am on "Course 1" course homepage
    And I navigate to "Questions" in current page administration

    # Create a new question.
    And I add a "Gapfill" question filling the form with:
        | Question name | Gapfill-001              |
        | Question text | The cat [sat] on the mat |
    When I choose "Preview" action for "Gapfill-001" in the question bank
    And I switch to "questionpreview" window
    # New class so changes in theme is inherited
    Given The element ".droptarget" should have a class with a value of "form-control"
    # The droptargets changed to be more like core elements
    When element "//input[contains(@id,'_p1')]" has a computed style for "borderRadius" of "0px"
    When element "//input[contains(@id,'_p1')]" has a computed style for "padding" of "2px 6px"
    # The draggables
    When element "//span[contains(@id,'pa:_')]" has a computed style for "boxShadow" of "none"
    When element "//span[contains(@id,'pa:_')]" has a computed style for "padding" of "2px 10px"
