@qtype @qtype_kprime @qtype_kprime_6
Feature: Step 6

  Background:
    Given the following "users" exist:
      | username | firstname    | lastname   | email               |
      | teacher  | T1Firstname  | T1Lasname  | teacher@moodle.com |
      | student1 | S1_SP_100    | S1Lastname | student1@moodle.com |
      | student2 | S2_SP_050    | S2Lastname | student2@moodle.com |
      | student3 | S3_SP_000    | S3Lastname | student3@moodle.com |
      | student4 | S4_KPR10_100 | S4Lastname | student4@moodle.com |
      | student5 | S5_KPR10_000 | S5Lastname | student5@moodle.com |
      | student6 | S6_KPRIM_100 | S6Lastname | student6@moodle.com |
      | student7 | S7_KPRIM_050 | S7Lastname | student7@moodle.com |
      | student8 | S8_KPRIM_000 | S8Lastname | student8@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher  | c1     | editingteacher |
      | student1 | c1     | student        |
      | student2 | c1     | student        |
      | student3 | c1     | student        |
      | student4 | c1     | student        |
      | student5 | c1     | student        |
      | student6 | c1     | student        |
      | student7 | c1     | student        |
      | student8 | c1     | student        |
    And the following "activities" exist:
      | activity | name   | intro              | course |
      | quiz     | Quiz 1 | Quiz 1 for testing | c1     |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | c1        | Default for c1 |
    And the following "questions" exist:
      | questioncategory | qtype  | name              | template       |
      | Default for c1   | kprime | Kprime Question 4 | question_four  |
    And quiz "Quiz 1" contains the following questions:
      | question          | page |
      | Kprime Question 4 | 1    |

  @javascript @qtype_kprime_scenario_2
  Scenario: Testcase 2
  # Test if the Scoring Method information is correctly displayed within quiz attempts

  # The scoring method information should not be disabled by default
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz"
    Then I should not see "Scoring method: Subpoints"
    And I log out

  # Log in as admin and configure the Scoring method to be displayed
    When I log in as "admin"
    And I navigate to "Plugins > Question types > Kprime (ETH)" in site administration
    And I should see "Default values for kprime questions."
    And I set the field "id_s_qtype_kprime_showscoringmethod" to "1"
    Then I press "Save changes"
    And I log out

  # The scoring method information should be enabled now
    When I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz"
    Then I should see "Scoring method: Subpoints"
    And I log out

  # Set scoring method to Kprime 1/0
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Questions" in current page administration
    And I click on "Edit question Kprime Question 4" "link" in the "Kprime Question 4" "list_item"
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_kprimeonezero" "radio"
    Then I press "id_updatebutton"
    And I log out

  # The scoring method information should be displayed now as Kprime 1/0
    When I log in as "student3"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz"
    Then I should see "Scoring method: Kprime1/0"
    And I log out

  # Set scoring method to Kprime
    When I log in as "admin"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Questions" in current page administration
    And I click on "Edit question Kprime Question 4" "link" in the "Kprime Question 4" "list_item"
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_kprime" "radio"
    Then I press "id_updatebutton"
    And I log out

  # The scoring method information should be displayed now as Kprime
    When I log in as "student4"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz"
    Then I should see "Scoring method: Kprime"
    And I log out

  @javascript
  Scenario: Testcase 15
  # Check grades: Verify that all possible mappings from
  # responses (correct, partially correct, incorrect) to
  # points function as specified for the different scoring
  # methods
  # The correct number of points is awarded, as specified

  # Set Scoring Method to subpoints
    Given I log in as "teacher"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Questions" in current page administration
    And I click on "Edit question Kprime Question 4" "link" in the "Kprime Question 4" "list_item"
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_subpoints" "radio"
    And I press "id_updatebutton"
    And I log out

  # Solving quiz as student1: 100% correct options (SUBPOINTS are activated)
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=2]" "css_element"
    And I click on "tr:contains('option text 4') input[value=2]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Solving quiz as student2: 50% correct options (SUBPOINTS are activated)
    When I log in as "student2"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=1]" "css_element"
    And I click on "tr:contains('option text 4') input[value=1]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Solving quiz as student3: 0% correct options (SUBPOINTS are activated)
    When I log in as "student3"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz"
    And I click on "tr:contains('option text 1') input[value=2]" "css_element"
    And I click on "tr:contains('option text 2') input[value=2]" "css_element"
    And I click on "tr:contains('option text 3') input[value=1]" "css_element"
    And I click on "tr:contains('option text 4') input[value=1]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Check results for Subpoints
    When I log in as "teacher"
    And I am on "Course 1" course homepage
    And I am on the "Quiz 1" "quiz activity" page
    And I navigate to "Results" in current page administration
    And I click on "tr:contains('student1@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Correct')" "css_element" should exist
    And ".grade:contains('Mark 1.00 out of 1.00')" "css_element" should exist
    And "tr:contains('option text 1') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 2') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 3') input[value='2'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 4') input[value='2'][checked='checked']" "css_element" should exist
    And I navigate to "Results" in current page administration
    And I click on "tr:contains('student2@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Partially correct')" "css_element" should exist
    And ".grade:contains('Mark 0.50 out of 1.00')" "css_element" should exist
    And "tr:contains('option text 1') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 2') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 3') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 4') input[value='1'][checked='checked']" "css_element" should exist
    And I navigate to "Results" in current page administration
    And I click on "tr:contains('student3@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Incorrect')" "css_element" should exist
    And ".grade:contains('Mark 0.00 out of 1.00')" "css_element" should exist
    And "tr:contains('option text 1') input[value='2'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 2') input[value='2'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 3') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 4') input[value='1'][checked='checked']" "css_element" should exist

  # Set Scoring Method to Kprime 1/0
    And I am on "Course 1" course homepage
    And I am on the "Quiz 1" "quiz activity" page
    And I navigate to "Questions" in current page administration
    And I click on "Edit question Kprime Question 4" "link" in the "Kprime Question 4" "list_item"
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_kprimeonezero" "radio"
    And I press "id_submitbutton"
    And I log out

  # Solving quiz as student4: 100% correct options (KPrime1/0 is activated)
    When I log in as "student4"
    And I am on "Course 1" course homepage
    And I am on the "Quiz 1" "quiz activity" page
    And I press "Attempt quiz"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=2]" "css_element"
    And I click on "tr:contains('option text 4') input[value=2]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Solving quiz as student5: 0% correct options (KPrime1/0 is activated)
    When I log in as "student5"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=1]" "css_element"
    And I click on "tr:contains('option text 4') input[value=1]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Check results for Kprime1/0
    When I log in as "teacher"
    And I am on "Course 1" course homepage
    And I am on the "Quiz 1" "quiz activity" page
    And I navigate to "Results" in current page administration
    And I click on "tr:contains('student4@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Correct')" "css_element" should exist
    And ".grade:contains('Mark 1.00 out of 1.00')" "css_element" should exist
    And "tr:contains('option text 1') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 2') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 3') input[value='2'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 4') input[value='2'][checked='checked']" "css_element" should exist
    And I navigate to "Results" in current page administration
    And I click on "tr:contains('student5@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Incorrect')" "css_element" should exist
    And ".grade:contains('Mark 0.00 out of 1.00')" "css_element" should exist
    And "tr:contains('option text 1') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 2') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 3') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 4') input[value='1'][checked='checked']" "css_element" should exist

  # Set Scoring Method to Kprime
    And I am on "Course 1" course homepage
    And I am on the "Quiz 1" "quiz activity" page
    And I navigate to "Questions" in current page administration
    And I click on "Edit question Kprime Question 4" "link" in the "Kprime Question 4" "list_item"
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_kprime" "radio"
    And I press "id_submitbutton"
    And I log out

  # Solving quiz as student6: 100% correct options (KPrime is activated)
    When I log in as "student6"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=2]" "css_element"
    And I click on "tr:contains('option text 4') input[value=2]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Solving quiz as student7: 1 false option -> 50% (Kprime is activated)
    When I log in as "student7"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=2]" "css_element"
    And I click on "tr:contains('option text 4') input[value=1]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

  # Solving quiz as student8: 2 false option -> 0% (KPrime is activated)
    When I log in as "student8"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=1]" "css_element"
    And I click on "tr:contains('option text 4') input[value=1]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    And I log out

# Check results for Kprime
    When I log in as "teacher"
    And I am on "Course 1" course homepage
    And I am on the "Quiz 1" "quiz activity" page
    And I navigate to "Results" in current page administration
    And I click on "tr:contains('student6@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Correct')" "css_element" should exist
    And ".grade:contains('Mark 1.00 out of 1.00')" "css_element" should exist
    And "tr:contains('option text 1') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 2') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 3') input[value='2'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 4') input[value='2'][checked='checked']" "css_element" should exist
    And I navigate to "Results" in current page administration
    And I click on "tr:contains('student7@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Partially correct')" "css_element" should exist
    And ".grade:contains('Mark 0.50 out of 1.00')" "css_element" should exist
    And "tr:contains('option text 1') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 2') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 3') input[value='2'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 4') input[value='1'][checked='checked']" "css_element" should exist
    And I navigate to "Results" in current page administration
    And I click on "tr:contains('student8@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Incorrect')" "css_element" should exist
    And ".grade:contains('Mark 0.00 out of 1.00')" "css_element" should exist
    And "tr:contains('option text 1') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 2') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 3') input[value='1'][checked='checked']" "css_element" should exist
    And "tr:contains('option text 4') input[value='1'][checked='checked']" "css_element" should exist
