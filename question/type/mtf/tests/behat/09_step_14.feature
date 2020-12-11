@qtype @qtype_mtf @qtype_mtf_step_14
Feature: Step 14

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | c1     | editingteacher |
    And the following "activities" exist:
      | activity | name   | intro              | course | idnumber |
      | quiz     | Quiz 1 | Quiz 1 for testing | c1     | quiz1    |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | c1        | Default for c1 |
    And the following "questions" exist:
      | questioncategory | qtype | name           | template     |
      | Default for c1   | mtf   | MTF-Question-1 | question_one |
      | Default for c1   | mtf   | MTF-Question-2 | question_one |
      | Default for c1   | mtf   | MTF-Question-3 | question_one |
      | Default for c1   | mtf   | MTF-Question-5 | question_one |
    And quiz "Quiz 1" contains the following questions:
      | question       | page |
      | MTF-Question-1 | 1    |
      | MTF-Question-2 | 2    |
      | MTF-Question-3 | 3    |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit quiz" in current page administration

  @javascript
  Scenario: TESTCASE 14
  # Create a quiz with MTF Questions. Moving, deleting,
  # adding questions in the quiz. Preview quiz.
  # All should work like standard questions.

  # Repaginate
    When I press "Repaginate"
    Then I should see "Repaginate with"
    And I set the field "menuquestionsperpage" to "2"
    When I press "Go"
    And I should see "MTF-Question-1" on quiz page "1"
    And I should see "MTF-Question-2" on quiz page "1"
    And I should see "MTF-Question-3" on quiz page "2"

  # Add a new question to the quiz
    And I click on "li:contains('Page 2') .add-menu-outer" "css_element"
    And I click on ".menu-action-text:contains('a new question')" "css_element"
    And I set the field "item_qtype_mtf" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Multiple True/False question"
    When I set the following fields to these values:
      | id_name              | MTF-Question-4           |
      | id_defaultmark       | 1                        |
      | id_questiontext      | The Questiontext         |
      | id_generalfeedback   | This feedback is general |
      | id_option_0          | questiontext 1           |
      | id_option_1          | questiontext 2           |
      | id_option_2          | questiontext 3           |
      | id_option_3          | questiontext 4           |
      | id_feedback_0        | feedbacktext 1           |
      | id_feedback_1        | feedbacktext 2           |
      | id_feedback_2        | feedbacktext 3           |
      | id_feedback_3        | feedbacktext 4           |
      | id_weightbutton_0_1  | checked                  |
      | id_weightbutton_1_1  | checked                  |
      | id_weightbutton_2_2  | checked                  |
      | id_weightbutton_3_2  | checked                  |
    And I press "id_submitbutton"
    Then I should see "Editing quiz: Quiz 1"
    And I should see "MTF-Question-4"

  # Add a question from the question bank to the quiz
    And I click on "li:contains('Page 2') .add-menu-outer" "css_element"
    And I click on ".menu-action-text:contains('from question bank')" "css_element"
    And I click on "Add to quiz" "link" in the "MTF-Question-5" "table_row"
    Then I should see "Editing quiz: Quiz 1"
    And I should see "MTF-Question-5"

  # Delete a question from a quiz
    When I click on "Delete" "link" in the "MTF-Question-4" "list_item"
    And I click on "Yes" "button" in the ".moodle-dialogue-wrap" "css_element" 
    Then I should see "Editing quiz: Quiz 1"
    And I should not see "MTF-Question-4"

