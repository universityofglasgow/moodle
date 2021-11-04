@qtype @qtype_kprime @qtype_kprime_4
Feature: Step 4

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
      | questioncategory | qtype  | name              | template     |
      | Default for c1   | kprime | KPrime-Question-2 | question_one |
      | Default for c1   | kprime | KPrime-Question-3 | question_one |
      | Default for c1   | kprime | KPrime-Question-4 | question_one |
    And quiz "Quiz 1" contains the following questions:
      | question          | page |
      | KPrime-Question-2 | 1    |
      | KPrime-Question-3 | 2    |
      | KPrime-Question-4 | 3    |

  @javascript
  Scenario: Testcase 12

  # (12) Navigation and label

  # Login as teacher and set Question behavior to "Deferred feedback"
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit settings" in current page administration
    And I click on "Question behaviour" "link"
    And I set the field "How questions behave" to "Deferred feedback"
    And I press "Save and return to course"
    And I log out

  # Login as student and see if everything works
    When I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    Then I should see "Quiz 1"
    And I press "Attempt quiz now"

  # No option selected
    When I click on "quiznavbutton2" "link"
    Then "//a[@id='quiznavbutton1' and @title='Not yet answered']" "xpath_element" should exist

  # Not all options selected
    When I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "quiznavbutton3" "link"
    Then "//a[@id='quiznavbutton2' and @title='Incomplete answer']" "xpath_element" should exist

  #All options selected
    When I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=2]" "css_element"
    And I click on "tr:contains('option text 4') input[value=2]" "css_element"
    And I click on "quiznavbutton1" "link"
    Then "//a[@id='quiznavbutton3' and @title='Answer saved']" "xpath_element" should exist
