@qtype @qtype_gapfill @qtype_gapfill_clicktoplace
Feature: Click to place answer options in a gapfill question
  In order to answer without drag and drop
  As a student I need to click an option then click a gap

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
      | Question name          | Gapfill-click              |
      | Question text          | The [cat] sat on the [mat] |
      | Single use draggables  | 1                          |

  @javascript
  Scenario: Click an option then a gap to fill it, swap and clear it
    When I am on the "Gapfill-click" "core_question > preview" page logged in as teacher
    And I click "cat" then gap "1" in the gapfill question
    Then the field with xpath "//input[contains(@id, '_p1')]" matches value "cat"
    And "//span[contains(@class, 'draggable') and contains(@class, 'hide') and text()='cat']" "xpath_element" should exist
    # Placing a second option in a filled gap swaps it, cat returns to the list.
    And I click "mat" then gap "1" in the gapfill question
    And the field with xpath "//input[contains(@id, '_p1')]" matches value "mat"
    And "//span[contains(@class, 'draggable') and contains(@class, 'hide') and text()='cat']" "xpath_element" should not exist
    # Double click clears the gap.
    And I double click gap "1" in the gapfill question
    And the field with xpath "//input[contains(@id, '_p1')]" matches value ""
    And I click "cat" then gap "1" in the gapfill question
    And I click "mat" then gap "2" in the gapfill question
    And I press "Submit and finish"
    And I should see "Your answer is correct."

  @javascript
  Scenario: Move the text in one gap to another gap, swapping if it is filled
    When I am on the "Gapfill-click" "core_question > preview" page logged in as teacher
    And I click "mat" then gap "1" in the gapfill question
    # Move to an empty gap, the first gap is emptied.
    And I click gap "1" then gap "2" in the gapfill question
    Then the field with xpath "//input[contains(@id, '_p1')]" matches value ""
    And the field with xpath "//input[contains(@id, '_p2')]" matches value "mat"
    # The option stays hidden because it is still in a gap.
    And "//span[contains(@class, 'draggable') and contains(@class, 'hide') and text()='mat']" "xpath_element" should exist
    # Move to a filled gap, the two gaps swap.
    And I click "cat" then gap "1" in the gapfill question
    And I click gap "2" then gap "1" in the gapfill question
    And the field with xpath "//input[contains(@id, '_p1')]" matches value "mat"
    And the field with xpath "//input[contains(@id, '_p2')]" matches value "cat"
    And I click gap "1" then gap "2" in the gapfill question
    # Clearing a moved gap returns its option to the list.
    And I double click gap "2" in the gapfill question
    And "//span[contains(@class, 'draggable') and contains(@class, 'hide') and text()='mat']" "xpath_element" should not exist
    And I click "mat" then gap "2" in the gapfill question
    And I press "Submit and finish"
    And I should see "Your answer is correct."

  @javascript
  Scenario: Place and move options with the keyboard only
    When I am on the "Gapfill-click" "core_question > preview" page logged in as teacher
    And I focus option "mat" in the gapfill question
    And I press the enter key
    And I focus gap "1" in the gapfill question
    And I press the enter key
    Then the field with xpath "//input[contains(@id, '_p1')]" matches value "mat"
    And the focused element is "//input[contains(@id, '_p1')]" "xpath_element"
    # Enter on a filled gap picks its text, Enter on another gap moves it there.
    And I press the enter key
    And I focus gap "2" in the gapfill question
    And I press the enter key
    And the field with xpath "//input[contains(@id, '_p1')]" matches value ""
    And the field with xpath "//input[contains(@id, '_p2')]" matches value "mat"
    # Escape cancels a pick.
    And I focus option "cat" in the gapfill question
    And I press the enter key
    And I press the escape key
    And I focus gap "1" in the gapfill question
    And I press the enter key
    And the field with xpath "//input[contains(@id, '_p1')]" matches value ""
    And I focus option "cat" in the gapfill question
    And I press the space key
    And I focus gap "1" in the gapfill question
    And I press the enter key
    And I press "Submit and finish"
    And I should see "Your answer is correct."
