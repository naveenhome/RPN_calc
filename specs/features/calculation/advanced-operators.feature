Feature: Calculate advanced RPN operators
  Engineers and scientists need powers, percentages, factorials, and decimal
  operands for practical technical calculations.

  Background:
    Given I am on the calculator page

  Scenario Outline: Calculate supported advanced expressions
    When I enter "<expression>"
    And I submit the expression
    Then I should see the result "<result>"
    And the history should include "<expression>" with result "<result>"

    Examples:
      | expression | result              |
      | 2 10 ^     | 1024                |
      | 75 %       | 0.75                |
      | 200 %      | 2                   |
      | 0 !        | 1                   |
      | 5 !        | 120                 |
      | 20 !       | 2432902008176640000 |
      | 3.14 2 *   | 6.28                |

  Scenario: Calculate a fractional power
    When I enter "2.0 0.5 ^"
    And I submit the expression
    Then I should see the result approximately "1.4142"
    And the history should include "2.0 0.5 ^" with a result approximately "1.4142"
