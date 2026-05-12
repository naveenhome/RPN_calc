@p1
Feature: Copy the latest result
  Users need to copy results into other tools without selecting text manually.

  Background:
    Given I am on the calculator page

  Scenario: Copy a successful calculation result
    Given I have calculated "3 4 +" with result "7"
    When I copy the latest result
    Then the clipboard should contain "7"

  Scenario: Copy is unavailable when no result exists
    Given I have not calculated an expression yet
    Then I should not be able to copy a result
