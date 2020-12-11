@qtype @qtype_mtf @qtype_mtf_step_11
Feature: Step 11

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | c1        | 0        |
    And I log in as "admin"

 @javascript
  Scenario: TESTCASE 11.

  # Install languages
    Given I navigate to "Language > Language packs" in site administration
    When I set the field "Available language packs" to "de_ch"
    And I press "Install selected language pack(s)"

  # Check english version
    And I am on "Course 1" course homepage
    And I navigate to "Question bank" in current page administration
    When I press "Create a new question ..."
    And I click on "item_qtype_mtf" "radio"
    And I press "submitbutton"
    Then "#id_responsetext_1[value='True']" "css_element" should exist
    And "#id_responsetext_2[value='False']" "css_element" should exist
    And I press "id_cancel"

  # Switch to german
    And I click on "English ‎(en)‎" "link"
    And I click on "Deutsch - Schweiz ‎(de_ch)" "link"

  # Check german version
    When I press "Neue Frage erstellen..."
    And I click on "item_qtype_mtf" "radio"
    And I press "submitbutton"
    Then "#id_responsetext_1[value='Wahr']" "css_element" should exist
    And "#id_responsetext_2[value='Falsch']" "css_element" should exist
    And I press "id_cancel"