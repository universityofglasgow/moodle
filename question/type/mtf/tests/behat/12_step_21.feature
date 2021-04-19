@qtype @qtype_mtf @qtype_mtf_step_21
Feature: Step 21

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


  @javascript
  Scenario: TESTCASE 21.
  # Click on "manual grading" and then on "also questions that have been
  # graded automatically".
  # Important: 
  # Solve the quiz as several students so that you have severeal results
  # to grade.
  # Expected result.
  # Questions should be displayed and you shoule be able to grade them 
  # manually

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