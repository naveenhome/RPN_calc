Feature: Calculate basic RPN expressions
  Engineers need to evaluate simple space-delimited RPN expressions quickly so
  they can verify calculations without managing parentheses.

  Background:
    Given I am on the calculator page

  Scenario Outline: Calculate a simple binary expression
    When I enter "<expression>"
    And I submit the expression
    Then I should see the result "<result>"
    And the history should include "<expression>" with result "<result>"

    Examples:
      | expression | result |
      | 3 4 +      | 7      |
      | 10 4 -     | 6      |
      | 3 4 *      | 12     |
      | 8 2 /      | 4      |
      | 3.5 2 +    | 5.5    |

  Scenario: Calculate a chained expression
    When I enter "3 4 + 2 *"
    And I submit the expression
    Then I should see the result "14"
    And the history should include "3 4 + 2 *" with result "14"

  Scenario: Submit an expression with the Enter key
    When I enter "9 3 /"
    And I press Enter in the expression field
    Then I should see the result "3"
    And the history should include "9 3 /" with result "3"
