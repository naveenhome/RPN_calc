@p1
Feature: Input confidence
  Users need lightweight feedback before submitting so they can spot obvious
  expression problems early.

  Background:
    Given I am on the calculator page

  Scenario: Indicate when an expression appears ready to evaluate
    When I enter "3 4 +"
    Then I should see the input status "Ready to calculate"

  Scenario: Indicate when an expression appears incomplete
    When I enter "3 4"
    Then I should see the input status "Expression may be incomplete"

  Scenario: Allow full validation feedback after pre-submit guidance
    When I enter "3 4 $"
    And I submit the expression
    Then I should see the error "Unknown token `$`"
