@p1
Feature: Delete one history entry
  Users need to remove a single unwanted calculation without losing the rest of
  their history.

  Background:
    Given I am on the calculator page

  Scenario: Delete one saved calculation
    Given I have calculated "3 4 +" with result "7"
    And I have calculated "5 !" with result "120"
    When I delete the history entry for the earlier "3 4 +" calculation
    Then the history should not include "3 4 +"
    And the history should include "5 !" with result "120"

  Scenario: Preserve newest-first ordering after deleting one entry
    Given the following calculations exist from oldest to newest:
      | expression | outcome |
      | 3 4 +      | 7       |
      | 5 !        | 120     |
      | 75 %       | 0.75    |
    When I delete the history entry for the "5 !" calculation
    Then I should see "75 %" before "3 4 +" in history
