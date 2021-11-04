@qtype @qtype_mtf @qtype_mtf_5
Feature: Step 5

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
      | questioncategory | qtype | name           | template     |
      | Default for c1   | mtf   | MTF Question 2 | question_two |
    And quiz "Quiz 1" contains the following questions:
      | question       | page |
      | MTF Question 2 | 1    |

  @javascript
  Scenario: Testcase 22
  # Check manual grading override

  # Solving quiz as student1: 75% correct options
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 3') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 4') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 5') input[value=2]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 6') input[value=2]" "css_element"
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

  # Regrade
  # Login as teacher1 and grade manually
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Manual grading" in current page administration
    Then I should see "Nothing to display"
    When I click on "Also show questions that have been graded automatically" "link"
    And I click on "grade all" "link"
    Then I should see "Attempt number 1 for S1Firstname S1Lastname"
    And I should see "Attempt number 1 for S2Firstname S2Lastname"
    And "input[value='0.75']" "css_element" should exist
    And "input[value='0.5']" "css_element" should exist
    And I set the field with xpath "//*[@value='0.75']" to "0.66"
    And I set the field with xpath "//*[@value='0.5']" to "0.33"
    And I press "Save and go to next page"

  # Check regraded attempts
    When I click on "nav a:contains('Quiz 1')" "css_element"
    And I navigate to "Results" in current page administration
    Then "tr[class='gradedattempt']:contains('66.00')" "css_element" should exist
    And "tr[class='gradedattempt']:contains('33.00')" "css_element" should exist

  @javascript @_switch_window
  Scenario: Testcase 22, 23
  # Change scoringmethod after test has been submitted
  # Check grades. Manual applied grades should not be overwritten

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

  # Set Scoring Method to MTF1/0
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question MTF Question 2" "link" in the "MTF Question 2" "list_item"
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

  @javascript @_switch_window
  Scenario: Testcase 22
  # Change correct answer after test has been submitted.
  # Regrade the test and check the results

  # Solving quiz as student1: 100% (Post: 50%) correct options
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

  # Solving quiz as student2: 50% (Post 100%) correct options
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

  # Changing the correct answer from 1 1 0 0 to 1 1 1 1
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question MTF Question 2" "link" in the "MTF Question 2" "list_item"
    And I set the following fields to these values:
      | id_weightbutton_0_1 | checked |
      | id_weightbutton_1_1 | checked |
      | id_weightbutton_2_1 | checked |
      | id_weightbutton_3_1 | checked |
    And I press "id_submitbutton"

  # Regrade
    And I follow "Quiz 1"
    And I navigate to "Results" in current page administration
    And I click on "#mod-quiz-report-overview-report-selectall-attempts" "css_element"
    And I press "Regrade selected attempts"
    And I press "Continue"

  # Check if grades are correct
    Then ".gradedattempt:contains('student1@moodle.com'):contains('50.00')" "css_element" should exist
    And ".gradedattempt:contains('student2@moodle.com'):contains('0.00')" "css_element" should exist
