Feature: Review calculation history
  Users need an auditable list of previous calculations so they can review and
  reproduce recent work.

  Scenario: Display saved calculations on page load
    Given the following calculations exist from oldest to newest:
      | expression | outcome |
      | 3 4 +      | 7       |
      | 2 10 ^     | 1024    |
    When I open the calculator page
    Then I should see "2 10 ^" before "3 4 +" in history

  Scenario: Add each submitted calculation to the top of history
    Given I am on the calculator page
    And I have calculated "3 4 +" with result "7"
    When I enter "5 !"
    And I submit the expression
    Then the first history entry should be "5 !" with result "120"
    And the history should also include "3 4 +" with result "7"

  Scenario: Keep successful calculations after reload
    Given I am on the calculator page
    And I have calculated "3 4 +" with result "7"
    When I reload the calculator page
    Then the history should include "3 4 +" with result "7"

  Scenario: Keep failed calculations after reload
    Given I am on the calculator page
    And I have submitted "5 0 /" and seen the error "Division by zero is undefined"
    When I reload the calculator page
    Then the history should include "5 0 /" with error "Division by zero is undefined"
