@qtype @qtype_mtf @qtype_mtf_6
Feature: Step 6

  Background:
    Given the following "users" exist:
      | username | firstname    | lastname   | email               |
      | teacher1 | T1Firstname  | T1Lasname  | teacher1@moodle.com |
      | student1 | S1_SP_100    | S1Lastname | student1@moodle.com |
      | student2 | S2_SP_050    | S2Lastname | student2@moodle.com |
      | student3 | S3_SP_000    | S3Lastname | student3@moodle.com |
      | student4 | S4_MTF10_100 | S4Lastname | student4@moodle.com |
      | student5 | S5_MTF10_000 | S5Lastname | student5@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | c1     | editingteacher |
      | student1 | c1     | student        |
      | student2 | c1     | student        |
      | student3 | c1     | student        |
      | student4 | c1     | student        |
      | student5 | c1     | student        |
    And the following "activities" exist:
      | activity | name   | intro              | course | idnumber |
      | quiz     | Quiz 1 | Quiz 1 for testing | c1     | quiz1    |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | c1        | Default for c1 |
    And the following "questions" exist:
      | questioncategory | qtype | name           | template     |
      | Default for c1   | mtf   | MTF-Question-2 | question_two |
    And quiz "Quiz 1" contains the following questions:
      | question       | page |
      | MTF-Question-2 | 1    |

  @javascript
  Scenario: Testcase 19
  # Check wether scoringmethod info is displayed
    When I log in as "admin"
    When I navigate to "Plugins > Question types > Multiple True False (ETH)" in site administration
    And I should see "Default values for Multiple True/False questions."
    And I set the following fields to these values:
      | id_s_qtype_mtf_showscoringmethod | checked |
    And I press "Save changes"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    Then "[id^='scoringmethodinfo_q'][label='Scoring method: <b>Subpoints</b>']" "css_element" should exist

  @javascript
  Scenario: Testcase 19
  # Check feedback
  # Solving quiz as student1: 50% correct options (SUBPOINTS are activated)
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
    Then I should see "feedback to option 1"
    And I should see "feedback to option 2"
    And I should see "feedback to option 3"
    And I should see "feedback to option 4"
    And I should see "feedback to option 5"
    And I should see "feedback to option 6"
    And I should see "feedback to option 7"
    And I should see "feedback to option 8"
    And I should see "option text 1: True"
    And I should see "option text 2: True"
    And I should see "option text 3: True"
    And I should see "option text 4: True"
    And I should see "option text 5: False"
    And I should see "option text 6: False"
    And I should see "option text 7: False"
    And I should see "option text 8: False"
    And I log out

  @javascript
  Scenario: Testcase 11, 19
  # Check feedback
  # Solving quiz as student1: 50% correct options (SUBPOINTS are activated). Some options are not answered at all.
    When I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 3') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 4') input[value=1]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then I should see "feedback to option 1"
    And I should see "feedback to option 2"
    And I should see "feedback to option 3"
    And I should see "feedback to option 4"
    And I should not see "feedback to option 5"
    And I should not see "feedback to option 6"
    And I should not see "feedback to option 7"
    And I should not see "feedback to option 8"
    And I should see "option text 1: True"
    And I should see "option text 2: True"
    And I should see "option text 3: True"
    And I should see "option text 4: True"
    And I should see "option text 5: False"
    And I should see "option text 6: False"
    And I should see "option text 7: False"
    And I should see "option text 8: False"
    And I log out

  @javascript
  Scenario: Testcase 20
  # Check if answers are stored correctly
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
    And I click on ".qtype_mtf_row:contains('option text 7') input[value=2]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 8') input[value=2]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 3') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 4') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 5') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 6') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 7') input[value=2]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 8') input[value=2]" "css_element" should exist
    And I log out

  @javascript
  Scenario: Testcase 10, 11, 19
  # Check grade: Verify that all possible mappings from
  # responses (correct, partially correct, incorrect) to
  # points function as specified for the different scoring
  # methods
  # The correct number of points is awarded, as specified
  # Also check the correctness icons on the result page

  # Set Scoring Method to subpoints
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question MTF-Question-2" "link" in the "MTF-Question-2" "list_item"
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_subpoints" "radio"
    And I press "id_updatebutton"
    And I log out

  # Solving quiz as student1: 100% correct options (SUBPOINTS are activated)
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
    And I click on ".qtype_mtf_row:contains('option text 7') input[value=2]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 8') input[value=2]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 1')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 2')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 3')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 4')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 5')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 6')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 7')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 8')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And I log out

  # Solving quiz as student2: 50% correct options (SUBPOINTS are activated)
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
    Then "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 1')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 2')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 3')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 4')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 5')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 6')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 7')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 8')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And I log out

  # Solving quiz as student3: 0% correct options (SUBPOINTS are activated)
    When I log in as "student3"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=2]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=2]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 3') input[value=2]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 4') input[value=2]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 5') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 6') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 7') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 8') input[value=1]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 1')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 2')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 3')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 4')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 5')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 6')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 7')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 8')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And I log out

  # Check results for Subpoints
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student1@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Correct')" "css_element" should exist
    And ".grade:contains('Mark 1.00 out of 1.00')" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 3') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 4') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 5') input[value=2]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 6') input[value=2]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 7') input[value=2]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 8') input[value=2]" "css_element" should exist
    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student2@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Partially correct')" "css_element" should exist
    And ".grade:contains('Mark 0.50 out of 1.00')" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 3') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 4') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 5') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 6') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 7') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 8') input[value=1]" "css_element" should exist
    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student3@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Incorrect')" "css_element" should exist
    And ".grade:contains('Mark 0.00 out of 1.00')" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 1') input[value=2]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 2') input[value=2]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 3') input[value=2]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 4') input[value=2]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 5') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 6') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 7') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 8') input[value=1]" "css_element" should exist

  # Set Scoring Method to MTF 1/0
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Edit quiz" in current page administration
    And I click on "Edit question MTF-Question-2" "link" in the "MTF-Question-2" "list_item"
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_mtfonezero" "radio"
    And I press "id_submitbutton"
    And I log out

  # Solving quiz as student4: 100% correct options (MTF1/0 is activated)
    When I log in as "student4"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I press "Attempt quiz now"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 3') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 4') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 5') input[value=2]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 6') input[value=2]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 7') input[value=2]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 8') input[value=2]" "css_element"
    And I press "Finish attempt ..."
    And I press "Submit all and finish"
    And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
    Then "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 1')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 2')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 3')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 4')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 5')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 6')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 7')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 8')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And I log out

  # Solving quiz as student5: 0% correct options (MTF1/0 is activated)
    When I log in as "student5"
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
    Then "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 1')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 2')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 3')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 4')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Correct']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 5')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 6')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 7')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And "//div[starts-with(@id,'question') and substring(@id, string-length(@id)-1)='-1']//tr[contains(.,'option text 8')]//td[contains(@class, 'mtfcorrectness')]//i[@title='Incorrect']" "xpath_element" should exist
    And I log out

  # Check results for MTF1/0
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student4@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Correct')" "css_element" should exist
    And ".grade:contains('Mark 1.00 out of 1.00')" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 3') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 4') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 5') input[value=2]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 6') input[value=2]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 7') input[value=2]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 8') input[value=2]" "css_element" should exist
    And I follow "Quiz 1"
    And I navigate to "Responses" in current page administration
    And I click on "tr:contains('student5@moodle.com') a:contains('Review attempt')" "css_element"
    Then ".state:contains('Incorrect')" "css_element" should exist
    And ".grade:contains('Mark 0.00 out of 1.00')" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 3') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 4') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 5') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 6') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 7') input[value=1]" "css_element" should exist
    And ".qtype_mtf_row:contains('option text 8') input[value=1]" "css_element" should exist
