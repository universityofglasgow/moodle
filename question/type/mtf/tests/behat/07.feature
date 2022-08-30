@qtype @qtype_mtf @qtype_mtf_7
Feature: Step 7

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
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | c1        | Default for c1 |
    And the following "questions" exist:
      | questioncategory | qtype | name             | template     |
      | Default for c1   | mtf   | MTF-Question-001 | question_one |
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I click on "Actions menu" "link"
    And I click on "More..." "link"
    And I click on "Question bank" "link"

  @javascript
  Scenario: Testcase 10, 11
  # Change scoring Method to MTF1/0 and test evaluation.

    When I choose "Edit question" action for "MTF-Question-001" in the question bank
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_mtfonezero" "radio"
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=2]" "css_element"
    And I press "Check"
    Then I should see "Mark 1.00 out of 1.00"
    And I press "Start again"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element"
    And I press "Check"
    Then I should see "Mark 0.00 out of 1.00"

  @javascript
  Scenario: Testcase 10, 11
  # Change scoring Method to Subpoints and test evaluation.

    When I choose "Edit question" action for "MTF-Question-001" in the question bank
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_subpoints" "radio"
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=2]" "css_element"
    And I press "Check"
    Then I should see "Mark 1.00 out of 1.00"
    And I press "Start again"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element"
    And I press "Check"
    Then I should see "Mark 0.50 out of 1.00"
