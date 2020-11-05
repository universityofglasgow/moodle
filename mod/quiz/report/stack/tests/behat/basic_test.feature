@quiz @quiz_stack
Feature: Verify that there are no obvious errors when accessing the report
  In order to evaluate the effectiveness of STACK questions
  As an teacher
  I need to view the STACK response analysis report.

  Background:
    Given I set up STACK using the PHPUnit configuration
    And the following "courses" exist:
      | fullname | shortname | format |
      | Course 1 | C1        | topics |
    And the following "users" exist:
      | username | firstname |
      | teacher  | Teacher   |
      | student  | Student   |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
      | student | C1     | student        |

    And I log in as "teacher"
    And I follow "Course 1"
    And I turn editing mode on
    And I add a "Quiz" to section "1" and I fill the form with:
      | Name                 | Test STACK quiz                               |
      | Description          | For testing the STACK response anaysis report |
      | How questions behave | Adaptive mode                                 |
    And I add a "STACK" question to the "Test STACK quiz" quiz with:
      | Question name      | Test STACK question                                                           |
      | Question variables | p : (x-1)^3;                                                                  |
      | Question text      | Differentiate @p@ with respect to \\(x\\). [[input:ans1]] [[validation:ans1]] |
      | Model answer       | diff(p,x)                                                                     |
      | SAns               | ans1                                                                          |
      | TAns               | diff(p,x)                                                                     |
    And I log out

    And I log in as "student"
    And I follow "Course 1"
    And I follow "Test STACK quiz"
    And I press "Attempt quiz now"
    Then I should see "Question 1"
    And I should see "with respect to"
    And I set the field "q1:1_ans1" to "3*(x-1)^2"
    And I wait "2" seconds
    And I press "Check"

    And I press "Next"
    And I should see "Answer saved"
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I should see "10.00 out of 10.00 (100%)"
    And I log out

    And I log in as "teacher"
    And I follow "Course 1"
    And I follow "Test STACK quiz"

  @javascript
  Scenario: Access the STACK response analysis report
    When I navigate to "STACK response analysis" node in "Quiz administration > Results"
    Then I should see "Test STACK quiz"
    And I should see "STACK questions in this quiz"
    When I follow "Test STACK question"
    Then I should see "p : (x-1)^3;"
    And I should see "Differentiate @p@ with respect to \\(x\\). [[input:ans1]] [[validation:ans1]]"
    And I should see "prt1-1-T" in the "ans1: 3*(x-1)^2 [score]" "table_row"
    And I should see "ans1:[3*(x-1)^2]$"
    And I should see "variants:[\"Differentiate @p@ with respect to \\(x\\). [[input:ans1]] [[validation:ans1]]\"]$"
    And I should see "inputs:[ans1]$"
