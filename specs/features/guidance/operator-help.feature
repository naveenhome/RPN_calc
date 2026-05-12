@p1
Feature: Operator help
  Users need lightweight guidance for supported RPN operators and examples so
  they can calculate correctly without leaving the calculator.

  Background:
    Given I am on the calculator page

  Scenario: View supported operators
    When I open operator help
    Then I should see the supported operators "+", "-", "*", "/", "^", "%", and "!"

  Scenario: View example expressions
    When I open operator help
    Then I should see the example "3 4 +" with result "7"
    And I should see the example "2 10 ^" with result "1024"
    And I should see the example "75 %" with result "0.75"
    And I should see the example "5 !" with result "120"
