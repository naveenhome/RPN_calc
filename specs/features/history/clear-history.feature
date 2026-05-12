Feature: Clear calculation history
  Users need to remove all saved calculations when they want to start from a
  clean workspace.

  Background:
    Given I am on the calculator page

  Scenario: Clear all saved history
    Given I have calculated "3 4 +" with result "7"
    And I have calculated "5 !" with result "120"
    When I clear calculation history
    Then the history should be empty

  Scenario: Continue calculating after clearing history
    Given I have calculated "3 4 +" with result "7"
    And I have cleared calculation history
    When I enter "75 %"
    And I submit the expression
    Then I should see the result "0.75"
    And the history should include "75 %" with result "0.75"
