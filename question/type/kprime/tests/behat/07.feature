@qtype @qtype_kprime @qtype_kprime_7
Feature: Step 7

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email              |
      | teacher  | T1        | teacher  | teacher@moodle.com |
    And the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | c1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | c1        | Default for c1 |
    And the following "questions" exist:
      | questioncategory | qtype  | name              | template       |
      | Default for c1   | kprime | Kprime Question 3 | question_three |
    Given I log in as "admin"

    And I navigate to "Plugins > Question types > Kprime (ETH)" in site administration
    And I should see "Default values for kprime questions."
    And I set the field "id_s_qtype_kprime_showscoringmethod" to "1"
    And I press "Save changes"

    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration

  @javascript @_switch_window
  Scenario: Testcase 14 - Part 1

  # Change scoring Method to KPrime1/0 and test evaluation.
  # If everything correct -> Max. Points
  # If one or more incorrect -> 0 Points

    When I choose "Edit question" action for "Kprime Question 3" in the question bank
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_kprimeonezero" "radio"
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then I should see "Questiontext for Question 1"
    And I should see "Scoring method: Kprime1/0"
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=2]" "css_element"
    And I click on "tr:contains('option text 4') input[value=2]" "css_element"
    And I press "Check"
    Then I should see "Mark 1.00 out of 1.00"
    And I press "Start again"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=2]" "css_element"
    And I click on "tr:contains('option text 4') input[value=1]" "css_element"
    And I press "Check"
    Then I should see "Mark 0.00 out of 1.00"

  @javascript @_switch_window
  Scenario: Testcase 14 - Part 2

  # Change scoring Method to Subpoints and test evaluation.
  # For each correct answer you should get subpoints.
  # You should also get subpoints if you answer some correctly
  # but dont't fill out all options

    When I choose "Edit question" action for "Kprime Question 3" in the question bank
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_subpoints" "radio"
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then I should see "Questiontext for Question 1"
    And I should see "Scoring method: Subpoints"
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=2]" "css_element"
    And I click on "tr:contains('option text 4') input[value=2]" "css_element"
    And I press "Check"
    Then I should see "Mark 1.00 out of 1.00"
    When I press "Start again"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=1]" "css_element"
    And I click on "tr:contains('option text 4') input[value=2]" "css_element"
    And I press "Check"
    Then I should see "Mark 0.75 out of 1.00"
    When I press "Start again"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=1]" "css_element"
    And I click on "tr:contains('option text 4') input[value=1]" "css_element"
    And I press "Check"
    Then I should see "Mark 0.50 out of 1.00"
    When I press "Start again"
    And I click on "tr:contains('option text 1') input[value=2]" "css_element"
    And I click on "tr:contains('option text 2') input[value=2]" "css_element"
    And I click on "tr:contains('option text 3') input[value=1]" "css_element"
    And I click on "tr:contains('option text 4') input[value=1]" "css_element"
    And I press "Check"
    Then I should see "Mark 0.00 out of 1.00"

  @javascript @_switch_window
  Scenario: Testcase 14 - Part 3

  # Change scoring Method to KPrime and test evaluation.

    When I choose "Edit question" action for "Kprime Question 3" in the question bank
    And I click on "Scoring method" "link"
    And I click on "id_scoringmethod_kprime" "radio"
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then I should see "Questiontext for Question 1"
    And I should see "Scoring method: Kprime"
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=2]" "css_element"
    And I click on "tr:contains('option text 4') input[value=2]" "css_element"
    And I press "Check"
    Then I should see "Mark 1.00 out of 1.00"
    And I press "Start again"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=2]" "css_element"
    And I click on "tr:contains('option text 4') input[value=1]" "css_element"
    And I press "Check"
    Then I should see "Mark 0.50 out of 1.00"
    And I press "Start again"
    And I click on "tr:contains('option text 1') input[value=1]" "css_element"
    And I click on "tr:contains('option text 2') input[value=1]" "css_element"
    And I click on "tr:contains('option text 3') input[value=1]" "css_element"
    And I click on "tr:contains('option text 4') input[value=1]" "css_element"
    And I press "Check"
    Then I should see "Mark 0.00 out of 1.00"

  @javascript @_switch_window
  Scenario: Testcase 14

  # Edit true and false fields.
  # They sould be editable and the values
  # get actualised in the answer options

    When I choose "Edit question" action for "Kprime Question 3" in the question bank
    And I set the field "id_responsetext_1" to "Red Answer"
    And I set the field "id_responsetext_2" to "Blue Answer"
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then I should see "Red Answer"
    And I should see "Blue Answer"
