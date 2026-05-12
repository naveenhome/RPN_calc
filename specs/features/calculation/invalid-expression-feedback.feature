Feature: Invalid expression feedback
  Users need clear, human-readable feedback when an expression cannot be
  evaluated so they can correct it without guessing.

  Background:
    Given I am on the calculator page

  Scenario Outline: Show a descriptive error for invalid expressions
    When I enter "<expression>"
    And I submit the expression
    Then I should see the error "<message>"
    And the history should include "<expression>" with error "<message>"

    Examples:
      | expression | message                                           |
      | + 3        | Not enough operands for operator `+`              |
      | 5 0 /      | Division by zero is undefined                     |
      | -3 !       | Factorial is undefined for negative numbers       |
      | 3.5 !      | Factorial requires a non-negative integer         |
      | 3 4 $      | Unknown token `$`                                 |
      | 3 4        | Too many operands - expression is incomplete      |

  Scenario: Require an expression before submit
    When I leave the expression field empty
    And I submit the expression
    Then I should see that the expression is required
    And no new history entry should be created
