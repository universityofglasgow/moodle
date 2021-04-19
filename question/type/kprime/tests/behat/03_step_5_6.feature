@qtype @qtype_kprime @qtype_kprime_step_5_6
Feature: Step 5 and Step 6

  Background:
    Given the following "users" exist:
      | username             | firstname      | lastname         | email               |
      | teacher1             | T1             | Teacher1         | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname             | shortname      | category         |
      | Course 1             | c1             | 0                |
    And the following "course enrolments" exist:
      | user                 | course         | role             |
      | teacher1             | c1             | editingteacher   |
    And the following "question categories" exist:
      | contextlevel         | reference      | name             |
      | Course               | c1             | Default for c1   |
    And the following "questions" exist:
      | questioncategory     | qtype          | name                | template            |
      | Default for c1       | kprime         | KPrime-Question-001 | question_one        |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  @javascript
  Scenario: TESTCASE 5.
  # Change options within a KPrime question.
  # Option can be changed.

    When I choose "Edit question" action for "KPrime-Question-001" in the question bank
    And I set the following fields to these values:
      | id_option_1          | New Questiontext 1 |
      | id_option_2          | New Questiontext 2 |
      | id_option_3          | questiontext 3     |
      | id_option_4          | questiontext 4     |
      | id_feedback_1        | New Feedbacktext 1 |
      | id_feedback_2        | feedback 2         |
      | id_feedback_3        | feedback 3         |
      | id_feedback_4        | feedback 4         |
      | id_weightbutton_1_2  | checked            |
      | id_weightbutton_2_2  | checked            |
      | id_weightbutton_3_1  | checked            |
      | id_weightbutton_4_1  | checked            |
    And I press "id_submitbutton"
    Then I should see "KPrime-Question-001"
    When I choose "Edit question" action for "KPrime-Question-001" in the question bank
    Then I should see "New Questiontext 1"
    And I should see "New Questiontext 2"
    And I should see "questiontext 3"
    And I should see "questiontext 4"
    And I should see "New Feedbacktext 1"
    And I should see "feedback 2"
    And I should see "feedback 3"
    And I should see "feedback 4"

  @javascript
  Scenario: TESTCASE 6.
  # Save with empty options
  # All 4 options must be filled

    When I choose "Edit question" action for "KPrime-Question-001" in the question bank
    And I set the following fields to these values:
      | id_option_1 | |
    And I press "id_submitbutton"
    And I set the following fields to these values:
      | id_option_1 | New Optiontext 1 |
    And I press "id_submitbutton"
    Then I should see "KPrime-Question-001"