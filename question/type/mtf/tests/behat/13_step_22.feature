@qtype @qtype_mtf @qtype_mtf_step_22
Feature: Step 22

  Background:
    Given the following "users" exist:
      | username | firstname   | lastname   | email               |
      | teacher1 | T1Firstname | T1Lasname  | teacher1@moodle.com |
      | student1 | S1Firstname | S1Lastname | student1@moodle.com |
      | student2 | S2Firstname | S2Lastname | student2@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | c1     | editingteacher |
      | student1 | c1     | student        |   
      | student2 | c1     | student        |   
    And the following "activities" exist:
      | activity | name   | intro              | course | idnumber |
      | quiz     | Quiz 1 | Quiz 1 for testing | c1     | quiz1    |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | c1        | Default for c1 |
    And the following "questions" exist:
      | questioncategory | qtype | name           | template       |
      | Default for c1   | mtf   | MTF-Question-2 | question_two   |
    And quiz "Quiz 1" contains the following questions:
      | question       | page |
      | MTF-Question-2 | 1    |


  @javascript @_switch_window
  Scenario: TESTCASE 22.
  # Regrade: After a test was submitted by a student,
  # change the grading options (e.g from MTF1/0 to subpoints).
  # Then go to grades and click on "regrade selected attempts"
  # The regrading should be successful.
  # Check that already manually graded questions won't be affected


  # Set Scoring Method to subpoints
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    And I choose "Edit question" action for "MTF-Question-2" in the question bank
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_subpoints" "radio"
    And I press "id_updatebutton"
    And I log out

  # Solving quiz as student1: 50% correct options
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 3') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 4') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 5') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 6') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 7') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 8') input[value=1]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Solving quiz as student2: 50% correct options
    When I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 3') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 4') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 5') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 6') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 7') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 8') input[value=1]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Login as teacher1 and grade student1 manually
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student1@moodle.com') a:contains('Review attempt')" "css_element"
    And I click on "Make comment or override mark" "link"
    And I switch to "commentquestion" window
    And I set the field "Mark" to "0.86"
    And I press "Save" and switch to main window

  # Set Scoring Method to MTF 1/0
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question MTF-Question-2" "link" in the "MTF-Question-2" "list_item"
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_mtfonezero" "radio"
    And I press "id_submitbutton"

  # Regrade
    And I follow "Quiz 1"
    And I navigate to "Results" in current page administration
    And I click on "#mod-quiz-report-overview-report-selectall-attempts" "css_element"
    And I press "Regrade selected attempts"
    And I press "Continue"

  # Check if grades are correct
    Then ".gradedattempt:contains('student1@moodle.com'):contains('86.00')" "css_element" should exist
    And ".gradedattempt:contains('student2@moodle.com'):contains('0.00')" "css_element" should exist