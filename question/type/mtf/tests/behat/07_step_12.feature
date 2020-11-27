@qtype @qtype_mtf @qtype_mtf_step_12
Feature: Step 12

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
      | questioncategory     | qtype          | name             | template            |
      | Default for c1       | mtf            | MTF-Question-001 | question_one        |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  @javascript
  Scenario: TESTCASE 12
  # Change which options are true and which are false.
  # There should never be a state where neither true or
  # false are selected

    When I choose "Edit question" action for "MTF-Question-001" in the question bank
    And I click on "id_weightbutton_1_1" "radio"
    And I press "id_updatebutton"
    Then "#id_weightbutton_1_1[checked]" "css_element" should exist
    And "#id_weightbutton_1_2:not([checked])" "css_element" should exist
    When I click on "id_weightbutton_1_2" "radio"
    And I press "id_updatebutton"
    Then "#id_weightbutton_1_1:not([checked])" "css_element" should exist
    And "#id_weightbutton_1_2[checked]" "css_element" should exist
    When I click on "id_weightbutton_1_1" "radio"
    And I click on "id_weightbutton_1_1" "radio"
    And I press "id_updatebutton"
    Then "#id_weightbutton_1_1[checked]" "css_element" should exist
    And "#id_weightbutton_1_2:not([checked])" "css_element" should exist