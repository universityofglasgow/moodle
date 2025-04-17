@block @block_massaction @block_massaction_topcoll
Feature: Check if in format_topcoll block properly disables the currently not active sections (or sections not containing any modules)

  @javascript
  Scenario: Check if checkboxes are created properly for topcoll format
    Given I installed course format "topcoll"
    And the following "courses" exist:
      | fullname        | shortname | numsections | format  |
      | Test course     | TC        | 5           | topcoll |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Mr        | Teacher  | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | TC     | editingteacher |
    And the following "activities" exist:
      | activity | course | idnumber | name           | intro                 | section |
      | page     | TC     | 1        | Test Activity1 | Test page description | 0       |
      | page     | TC     | 2        | Test Activity2 | Test page description | 1       |
      # Label needs to have identical name and intro here for this test to be able to work with moodle 4.3 and before.
      | label    | TC     | 3        | Test Activity3 | Test Activity3        | 2       |
      | page     | TC     | 4        | Test Activity4 | Test page description | 4       |
      | page     | TC     | 5        | Test Activity5 | Test page description | 4       |
    When I log in as "teacher1"
    And I am on "Test course" course homepage with editing mode on
    And I add the "Mass Actions" block
    And I click on "Enable bulk editing" "button"
    And I click on "Open all" "text"
    And I click on "Test Activity1" "checkbox"
    And I click on "Test Activity4" "checkbox"
    Then the field "Test Activity1" matches value "1"
    Then the field "Test Activity2" matches value ""
    Then the field "Test Activity3" matches value ""
    Then the field "Test Activity4" matches value "1"
    Then the field "Test Activity5" matches value ""
    Then the "disabled" attribute of "#block-massaction-control-section-list-select-option-0" "css_element" should not be set
    Then the "disabled" attribute of "#block-massaction-control-section-list-select-option-1" "css_element" should not be set
    Then the "disabled" attribute of "#block-massaction-control-section-list-select-option-2" "css_element" should not be set
    Then the "disabled" attribute of "#block-massaction-control-section-list-select-option-3" "css_element" should be set
    Then the "disabled" attribute of "#block-massaction-control-section-list-select-option-4" "css_element" should not be set

  @javascript
  Scenario: Check if mass actions 'indent' and 'outdent' work
    Given I installed course format "topcoll"
    And the following "courses" exist:
      | fullname    | shortname | numsections | format  |
      | Test course | TC2       | 5           | topcoll |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Mr        | Teacher  | teacher1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | TC2    | editingteacher |
    And the following "activities" exist:
      | activity | course | idnumber | name           | intro                 | section |
      | page     | TC2    | 1        | Test Activity1 | Test page description | 0       |
      | page     | TC2    | 2        | Test Activity2 | Test page description | 1       |
      # Label needs to have identical name and intro here for this test to be able to work with moodle 4.3 and before.
      | label    | TC2    | 3        | Test Activity3 | Test Activity3        | 2       |
      | page     | TC2    | 4        | Test Activity4 | Test page description | 4       |
      | assign   | TC2    | 5        | Test Activity5 | Test page description | 4       |
    When I log in as "teacher1"
    And I am on "Test course" course homepage with editing mode on
    And I add the "Mass Actions" block
    And I click on "Enable bulk editing" "button"
    # Everything is setup now, let's do the real test.
    And I click on "Open all" "text"
    And I click on "Test Activity2" "checkbox"
    And I click on "Test Activity5" "checkbox"
    And I click on "Indent (move right)" "button" in the "Mass Actions" "block"
    Then "#section-1 li.modtype_page div.indent-1" "css_element" should exist
    Then "#section-4 li.modtype_assign div.indent-1" "css_element" should exist
    When I click on "Enable bulk editing" "button"
    And I click on "Test Activity2" "checkbox"
    And I click on "Test Activity5" "checkbox"
    And I click on "Outdent (move left)" "button" in the "Mass Actions" "block"
    Then "#section-1 li.modtype_page div.indent-1" "css_element" should not exist
    Then "#section-4 li.modtype_assign div.indent-1" "css_element" should not exist
