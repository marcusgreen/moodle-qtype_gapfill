@qtype @qtype_gapfill @qtype_gapfill_dragdrop
Feature: Drag and drop answer options into gapfill gaps
  In order to answer a drag and drop gapfill question
  As a student I need to drag options into gaps and move them between gaps

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher  | Mark      | Allright | teacher@example.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    And I am on the "Course 1" "core_question > course question bank" page logged in as teacher
    And I add a "item_qtype_gapfill" question filling the form with:
      | Question name          | Gapfill-drag               |
      | Question text          | The [cat] sat on the [mat] |
      | Single use draggables  | 1                          |

  @javascript
  Scenario: Drag options into gaps, move and swap between gaps, then clear
    When I am on the "Gapfill-drag" "core_question > preview" page logged in as teacher
    And I drag "mat" into gap "1" in the gapfill question
    Then the field with xpath "//input[contains(@id, '_p1')]" matches value "mat"
    And "//span[contains(@class, 'draggable') and contains(@class, 'hide') and text()='mat']" "xpath_element" should exist
    # Move to an empty gap, the first gap is emptied and the option stays hidden.
    And I drag gap "1" into gap "2" in the gapfill question
    And the field with xpath "//input[contains(@id, '_p1')]" matches value ""
    And the field with xpath "//input[contains(@id, '_p2')]" matches value "mat"
    And "//span[contains(@class, 'draggable') and contains(@class, 'hide') and text()='mat']" "xpath_element" should exist
    # Move to a filled gap, the two gaps swap.
    And I drag "cat" into gap "1" in the gapfill question
    And I drag gap "2" into gap "1" in the gapfill question
    And the field with xpath "//input[contains(@id, '_p1')]" matches value "mat"
    And the field with xpath "//input[contains(@id, '_p2')]" matches value "cat"
    # Double click clears a gap and returns its option to the list.
    And I double click gap "1" in the gapfill question
    And the field with xpath "//input[contains(@id, '_p1')]" matches value ""
    And "//span[contains(@class, 'draggable') and contains(@class, 'hide') and text()='mat']" "xpath_element" should not exist
    And I drag gap "2" into gap "1" in the gapfill question
    And I drag "mat" into gap "2" in the gapfill question
    And I press "Submit and finish"
    And I should see "Your answer is correct."
