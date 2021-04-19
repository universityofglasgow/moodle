@qtype @qtype_mtf @qtype_mtf_step_5_6
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
      | questioncategory     | qtype          | name             | template            |
      | Default for c1       | mtf            | MTF-Question-001 | question_one        |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  @javascript
  Scenario: TESTCASE 5.
  # Add, change options within a MTF question.
  # Option can be added and changed.

    And I choose "Edit question" action for "MTF-Question-001" in the question bank
    And I press "Blanks for 3 more choices"
    And I set the following fields to these values:
      | id_option_0          | New Questiontext 1                                  |
      | id_option_1          | New Questiontext 2                                  |
      | id_option_2          | questiontext 3                                      |
      | id_option_3          | questiontext 4                                      |
      | id_option_4          | questiontext 5                                      |
      | id_feedback_2        | feedback 3                                          |
      | id_feedback_3        | feedback 4                                          |
      | id_feedback_4        | feedback 5                                          |
      | id_weightbutton_2_2  | checked                                             |
      | id_weightbutton_3_2  | checked                                             |
      | id_weightbutton_4_2  | checked                                             |
    And I press "id_submitbutton"
    Then I should see "MTF-Question-001"
    When I choose "Edit question" action for "MTF-Question-001" in the question bank
    Then I should see "New Questiontext 1"
    And I should see "New Questiontext 2"
    And I should see "questiontext 3"
    And I should see "questiontext 4"
    And I should see "questiontext 5"
    And I should see "feedback 3"
    And I should see "feedback 4"
    And I should see "feedback 5"

  @javascript
  Scenario: TESTCASE 6 - Part 1.
  # Save with empty options
  # Options which were empty, are deleted

    When I choose "Edit question" action for "MTF-Question-001" in the question bank
    And I press "Blanks for 3 more choices"
    Then I should see "Option 3"
    And I should see "Option 4"
    And I should see "Option 5" 
    And I press "id_submitbutton"
    Then I should see "MTF-Question-001"
    When I choose "Edit question" action for "MTF-Question-001" in the question bank
    Then I should see "Option 1"
    And I should see "Option 2"
    But I should not see "Option 3"
    And I should not see "Option 4"
    And I should not see "Option 5"

    @javascript
    Scenario: TESTCASE 6 - Part 2.
    # There must be at least one option to save the MTF question.

    When I choose "Edit question" action for "MTF-Question-001" in the question bank
    And I set the following fields to these values:
      | id_option_0          | |
      | id_option_1          | |
    And I press "id_submitbutton"
    Then I should see "This type of question requires at least 1 option"
