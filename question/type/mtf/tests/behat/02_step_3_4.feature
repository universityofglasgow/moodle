@qtype @qtype_mtf @qtype_mtf_step_3_4
Feature: Step 3 and Step 4

  Background:
    Given the following "users" exist:
      | username | firstname | lastname       | email                |
      | teacher1 | T1        | Teacher1       | teacher1@example.com |
    And the following "courses" exist:
      | fullname | shortname | category       |
      | Course 1 | c1        | 0              |
    And the following "course enrolments" exist:
      | user     | course    | role           |
      | teacher1 | c1        | editingteacher |
    And the following "question categories" exist:
      | contextlevel         | reference      | name                 |
      | Course               | c1             | Default for c1       |
      | Course               | c1             | AnotherCat for c1    |
    And the following "questions" exist:
      | questioncategory     | qtype          | name                 | template        |
      | Default for c1       | mtf            | MTF-Question-001     | question_one    |

    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  Scenario: TESTCASE 3.

    # Edit the question
    When I choose "Edit question" action for "MTF-Question-001" in the question bank
    And I set the following fields to these values:
      | id_name | |
    And I press "id_submitbutton"
    Then I should see "You must supply a value here."
    When I set the following fields to these values:
      | id_name | Edited MTF |
    And I press "id_submitbutton"
    Then I should see "Edited MTF"

    # Duplicate the question
    When I choose "Duplicate" action for "Edited MTF" in the question bank
    And I press "id_submitbutton"
    Then I should see "Edited MTF (copy)"

    # Delete the question
    When I choose "Delete" action for "Edited MTF (copy)" in the question bank
    And I click on "Delete" "button"
    Then I should not see "Edited MTF (copy)"

  @javascript
  Scenario: TESTCASE 4.

    # Move the question to another category
    And I click on "MTF-Question-001" "checkbox" in the "MTF-Question-001" "table_row"
    And I set the field "Question category" to "AnotherCat for c1"
    And I press "Move to >>"
    Then I should see "Question bank"
    And I should see "AnotherCat for c1"
    And I should see "MTF-Question-001"

    And I click on "MTF-Question-001" "checkbox" in the "MTF-Question-001" "table_row"
    And I set the field "Question category" to "AnotherCat for c1"
    And I press "Move to >>"
    Then I should see "Question bank"
    And I should see "AnotherCat for c1"
    And I should see "MTF-Question-001"