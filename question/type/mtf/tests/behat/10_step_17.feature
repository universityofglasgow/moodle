@qtype @qtype_mtf @qtype_mtf_step_17
Feature: Step 17

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email               |
      | teacher1 | T1        | Teacher1 | teacher1@moodle.com |
      | student2 | Student   | Tneduts  | student2@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | c1     | editingteacher |
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
      | Default for c1   | mtf   | MTF-Question-3 | question_two   |
      | Default for c1   | mtf   | MTF-Question-4 | question_two   |
    And quiz "Quiz 1" contains the following questions:
      | question       | page |
      | MTF-Question-2 | 1    |
      | MTF-Question-3 | 2    |
      | MTF-Question-4 | 3    |
 

  @javascript
  Scenario: TESTCASE 17.
  # Solve quiz as a student. 
  # 1. Navigate to next question without responding to any option.
  # -> Label "Not answered yet" in white
  # 2. Navigate to next question with responding to only one option.
  # -> Label "incomplete answer" in yellow
  # 3. Navigate to next question having responded to all options.
  # -> Label "Answer saved" in green


  # See if the Review is shown if enabled

    Given I log in as "teacher1"

  # Login as admin and set Question behavior to "Deferred feedback"
    When I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit settings" in current page administration
    And I click on "Question behaviour" "link"
    And I set the field "How questions behave" to "Deferred feedback"
    And I press "Save and return to course"
    And I log out

  # Login as student and see if everything works
    And I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    Then I should see "Quiz 1"
    When I press "Attempt quiz now"
  
  # No option selected
    When I click on "quiznavbutton2" "link"
    Then "#quiznavbutton1[title='Not yet answered']" "css_element" should exist

  # Not all options selected
    When I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 3') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 4') input[value=1]" "css_element"
    And I click on "quiznavbutton3" "link"
    Then "#quiznavbutton2[title='Incomplete answer']" "css_element" should exist

  #All options selected
    When I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 3') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 4') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 5') input[value=2]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 6') input[value=2]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 7') input[value=2]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 8') input[value=2]" "css_element"
    And I click on "quiznavbutton1" "link"
    Then "#quiznavbutton3[title='Answer saved']" "css_element" should exist