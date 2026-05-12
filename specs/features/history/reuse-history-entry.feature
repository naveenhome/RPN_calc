@p1
Feature: Reuse a history entry
  Users need to recall previous expressions so they can rerun or modify a
  calculation without retyping it from scratch.

  Background:
    Given I am on the calculator page

  Scenario: Populate the input from a history entry
    Given I have calculated "3 4 +" with result "7"
    When I choose the history entry "3 4 +"
    Then the expression field should contain "3 4 +"

  Scenario: Edit and resubmit a recalled expression
    Given I have calculated "3 4 +" with result "7"
    When I choose the history entry "3 4 +"
    And I change the expression to "3 4 + 2 *"
    And I submit the expression
    Then I should see the result "14"
    And the first history entry should be "3 4 + 2 *" with result "14"
