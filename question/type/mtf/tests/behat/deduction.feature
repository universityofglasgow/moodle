@qtype @qtype_mtf
Feature: Deduction for wrong answers

  Background:
    Given the following "users" exist:
      | username |
      | teacher1 |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "question categories" exist:
      | contextlevel | reference | name           |
      | Course       | C1        | Test questions |
    And the following "questions" exist:
      | questioncategory | qtype | name | template     |
      | Test questions   | mtf   | q1   | question_one |
    And I log in as "admin"

  @javascript
  Scenario: Avoid conflicting settings w.r.t. deduction
    When I log in as "admin"
    And I navigate to "Plugins > Question types > Multiple True False (ETH)" in site administration
    # First, set deductions to be the default, without allowing it.
    And I set the following fields to these values:
      | id_s_qtype_mtf_allowdeduction | 0                 |
      | id_s_qtype_mtf_scoringmethod  | subpointdeduction |
    And I press "Save changes"
    Then I should see "Changes saved"
    And the following fields match these values:
      | id_s_qtype_mtf_allowdeduction | 1                 |
      | id_s_qtype_mtf_scoringmethod  | subpointdeduction |
    # Now, disallow deductions but leave it as default method. Should automatically allow it again.
    When I set the following fields to these values:
      | id_s_qtype_mtf_allowdeduction | 0                 |
      | id_s_qtype_mtf_scoringmethod  | subpointdeduction |
    And I press "Save changes"
    Then I should see "Changes saved"
    And the following fields match these values:
      | id_s_qtype_mtf_allowdeduction | 1                 |
      | id_s_qtype_mtf_scoringmethod  | subpointdeduction |
    # Set default method to subpoints and disallow deductions. No objections.
    When I set the following fields to these values:
      | id_s_qtype_mtf_scoringmethod  | subpoints |
      | id_s_qtype_mtf_allowdeduction | 0         |
    And I press "Save changes"
    Then I should see "Changes saved"
    And the following fields match these values:
      | id_s_qtype_mtf_allowdeduction | 0         |
      | id_s_qtype_mtf_scoringmethod  | subpoints |

  @javascript
  Scenario: Test setting and validation of deduction
    # Check that the allowdeduction option is NOT checked by default in the plugin administration
    When I log in as "admin"
    And I navigate to "Plugins > Question types > Multiple True False (ETH)" in site administration
    Then the following fields match these values:
      | id_s_qtype_mtf_allowdeduction |  |
    # The teacher should not be able to set deductions.
    When I am on the "Course 1" "core_question > course question bank" page
    And I press "Create a new question ..."
    And I set the field "item_qtype_mtf" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Multiple True/False question"
    And I follow "Scoring method"
    Then I should not see "Subpoints with deduction"
    # Now activate the deduction
    When I set the following administration settings values:
      | Allow penalty deductions | 1 |
    # The teacher should now be able to set deductions.
    When I am on the "Course 1" "core_question > course question bank" page
    And I press "Create a new question ..."
    And I set the field "item_qtype_mtf" to "1"
    And I press "submitbutton"
    Then I should see "Adding a Multiple True/False question"
    And I follow "Scoring method"
    And I set the following fields to these values:
      | id_name                            | test title |
      | id_scoringmethod_subpointdeduction | 1          |
      | id_deduction                       | 1.5        |
    And I press "submitbutton"
    Then I should see "Deduction must be a float between 0 and 1 (inclusive)"
    And I set the following fields to these values:
      | id_deduction | -0.5 |
    And I press "submitbutton"
    Then I should see "Deduction must be a float between 0 and 1 (inclusive)"
    And I set the following fields to these values:
      | id_deduction | 0.1234 |
    And I press "submitbutton"
    Then I should not see "Deduction must be a float between 0 and 1 (inclusive)"
    And the following fields match these values:
      | id_deduction | 0.1234 |

  @javascript
  Scenario: Error message if trying to save a question with deduction, if this is no longer allowed
    When I log in as "admin"
    And I set the following administration settings values:
      | Allow penalty deductions | 1 |
    And I am on the "Course 1" "core_question > course question bank" page
    And I add a "Multiple True False (ETH)" question filling the form with:
      | id_name                            | test title |
      | id_scoringmethod_subpointdeduction | 1          |
      | id_deduction                       | 0.5        |
      | id_option_0                        | foo        |
      | id_option_1                        | bar        |
    And I set the following administration settings values:
      | Allow penalty deductions | 0 |
    # The teacher should no longer be able to set deductions.
    And I am on the "Course 1" "core_question > course question bank" page
    And I choose "Edit question" action for "test title" in the question bank
    And I press "submitbutton"
    Then I should see "Please set a valid scoring method."

  @javascript
  Scenario: Test deduction and overriding of deduction by admin
    When I log in as "admin"
    And I set the following administration settings values:
      | Allow penalty deductions | 1 |
    And I am on the "Course 1" "core_question > course question bank" page
    And I choose "Edit question" action for "q1" in the question bank
    And I set the following fields to these values:
      | id_scoringmethod_subpointdeduction | 1   |
      | id_deduction                       | 0.5 |
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    And I click on "Preview options" "link"
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element"
    And I press "Check"
    # The deduction should be made
    Then I should see "Mark 0.25 out of 1.00"
    And I switch to the main window
    And I set the following administration settings values:
      | Allow penalty deductions | 0 |
    And I am on the "Course 1" "core_question > course question bank" page
    And I choose "Edit question" action for "q1" in the question bank
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    And I click on "Preview options" "link"
    And I set the field "How questions behave" to "Immediate feedback"
    And I press "Start again with these options"
    And I click on ".qtype_mtf_row:contains('option text 1') input[value=1]" "css_element"
    And I click on ".qtype_mtf_row:contains('option text 2') input[value=1]" "css_element"
    And I press "Check"
    # Now, the deduction should not be made anymore, because the admin has not allowed it
    Then I should see "Mark 0.50 out of 1.00"

  @javascript
  Scenario: Trash icons must not be shown for other scoring methods or when deductions are not allowed
    When I log in as "admin"
    And I set the following administration settings values:
      | Allow penalty deductions | 1 |
    # Deductions are allowed, so the icon should be shown.
    And I am on the "Course 1" "core_question > course question bank" page
    And I choose "Edit question" action for "q1" in the question bank
    And I set the following fields to these values:
      | id_scoringmethod_subpointdeduction | 1   |
      | id_deduction                       | 0.5 |
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then "fa-trash" "qtype_mtf > icon" should exist
    And I switch to the main window
    # Disallow deductions, the trash icon should go away, regardless of the scoring method.
    And I set the following administration settings values:
      | Allow penalty deductions | 0 |
    And I am on the "Course 1" "core_question > course question bank" page
    And I choose "Edit question" action for "q1" in the question bank
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then "fa-trash" "qtype_mtf > icon" should not exist
    And I switch to the main window
    # Turn deductions back on. The icon should reappear, because the method is still with deductions.
    And I set the following administration settings values:
      | Allow penalty deductions | 1 |
    And I am on the "Course 1" "core_question > course question bank" page
    And I choose "Edit question" action for "q1" in the question bank
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then "fa-trash" "qtype_mtf > icon" should exist
    And I switch to the main window
    # Set classic subpoints scoring method. The trash icon should disappear.
    And I set the following fields to these values:
      | id_scoringmethod_subpoints | 1 |
    And I press "id_updatebutton"
    And I click on "Preview" "link"
    And I switch to "questionpreview" window
    Then "fa-trash" "qtype_mtf > icon" should not exist
