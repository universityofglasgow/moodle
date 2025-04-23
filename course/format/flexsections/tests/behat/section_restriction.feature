@format @format_flexsections @javascript
Feature: Testing section restrictions in format_flexsections

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email                |
      | student1 | Sam       | Student  | student1@example.com |
      | student2 | Mary      | Student  | student2@example.com |
      | teacher1 | Terry     | Teacher  | teacher1@example.com |
    And the following config values are set as admin:
      | maxsectiondepth | 4 | format_flexsections |
    And the following "courses" exist:
      | fullname | shortname | format       | numsections | enablecompletion |
      | Course 1 | C1        | flexsections | 3           | 1                |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity | course | idnumber | intro | name          | section | completion |
      | assign   | C1     | a1       | x     | First module  | 1       | 1          |
      | forum    | C1     | a2       | x     | Second module | 2       |            |
    And I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    And I open section "2" edit menu
    And I click on "Move" "link" in the "#section-2 .action-menu" "css_element"
    And I click on "As a subsection of 'Topic 1'" "link" in the "Move section" "dialogue"
    And I open section "3" edit menu
    And I click on "Add subsection" "link" in the "li#section-3" "css_element"

  Scenario: Restricting section in flexsections format displays this section as not available
    And I edit the section "1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button" in the "root" "core_availability > Availability Button Area"
    And I click on "Date" "button" in the "Add restriction..." "dialogue"
    And I set the field "year" in the "1" "availability_date > Date Restriction" to "2023"
    And I set the field "Direction" in the "1" "availability_date > Date Restriction" to "until"
    And I press "Save changes"
    And I click on "Open course index" "button"
    And I should see "Topic 1" in the "courseindex-content" "region"
    And I should see "First module" in the "courseindex-content" "region"
    And I should see "Topic 2" in the "courseindex-content" "region"
    And I should see "Second module" in the "courseindex-content" "region"
    And I should see "Topic 3" in the "courseindex-content" "region"
    And I should see "Topic 4" in the "courseindex-content" "region"
    And I log out
    And I change window size to "large"
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I should see "Topic 1" in the "courseindex-content" "region"
    And I should not see "First module" in the "courseindex-content" "region"
    And I should not see "Topic 2" in the "courseindex-content" "region"
    And I should not see "Second module" in the "courseindex-content" "region"
    And I should see "Topic 3" in the "courseindex-content" "region"
    And I should see "Topic 4" in the "courseindex-content" "region"
    And I should see "Topic 1" in the "region-main" "region"
    And I should not see "First module" in the "region-main" "region"
    And I should not see "Topic 2" in the "region-main" "region"
    And I should not see "Second module" in the "region-main" "region"
    And I should see "Topic 3" in the "region-main" "region"
    And I should see "Topic 4" in the "region-main" "region"

  Scenario: Restricting and hiding section in flexsections format hides this section from students
    And I edit the section "1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button" in the "root" "core_availability > Availability Button Area"
    And I click on "Date" "button" in the "Add restriction..." "dialogue"
    And I set the field "year" in the "1" "availability_date > Date Restriction" to "2023"
    And I set the field "Direction" in the "1" "availability_date > Date Restriction" to "until"
    And I click on ".availability-item .availability-eye img" "css_element"
    And I press "Save changes"
    And I click on "Open course index" "button"
    And I should see "Topic 1" in the "courseindex-content" "region"
    And I should see "First module" in the "courseindex-content" "region"
    And I should see "Topic 2" in the "courseindex-content" "region"
    And I should see "Second module" in the "courseindex-content" "region"
    And I should see "Topic 3" in the "courseindex-content" "region"
    And I should see "Topic 4" in the "courseindex-content" "region"
    And I log out
    And I change window size to "large"
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I should not see "Topic 1" in the "courseindex-content" "region"
    And I should not see "First module" in the "courseindex-content" "region"
    And I should not see "Topic 2" in the "courseindex-content" "region"
    And I should not see "Second module" in the "courseindex-content" "region"
    And I should see "Topic 3" in the "courseindex-content" "region"
    And I should see "Topic 4" in the "courseindex-content" "region"
    And I should not see "Topic 1" in the "region-main" "region"
    And I should not see "First module" in the "region-main" "region"
    And I should not see "Topic 2" in the "region-main" "region"
    And I should not see "Second module" in the "region-main" "region"
    And I should see "Topic 3" in the "region-main" "region"
    And I should see "Topic 4" in the "region-main" "region"

  Scenario: Restricting section in flexsections format displays this section as not available when subsection also has restriction
    # Make first section restricted with an availability message
    And I edit the section "1"
    And I expand all fieldsets
    And I click on "Add restriction..." "button" in the "root" "core_availability > Availability Button Area"
    And I click on "Date" "button" in the "Add restriction..." "dialogue"
    And I set the field "year" in the "1" "availability_date > Date Restriction" to "2023"
    And I set the field "Direction" in the "1" "availability_date > Date Restriction" to "until"
    And I press "Save changes"
    # Add a restriction to the subsection too with an availability message
    And I edit the section "2"
    And I expand all fieldsets
    And I click on "Add restriction..." "button" in the "root" "core_availability > Availability Button Area"
    And I click on "Date" "button" in the "Add restriction..." "dialogue"
    And I set the field "year" in the "1" "availability_date > Date Restriction" to "2023"
    And I set the field "Direction" in the "1" "availability_date > Date Restriction" to "until"
    And I press "Save changes"
    # Teacher can see everything
    And I click on "Open course index" "button"
    And I should see "Topic 1" in the "courseindex-content" "region"
    And I should see "First module" in the "courseindex-content" "region"
    And I should see "Topic 2" in the "courseindex-content" "region"
    And I should see "Second module" in the "courseindex-content" "region"
    And I should see "Topic 3" in the "courseindex-content" "region"
    And I should see "Topic 4" in the "courseindex-content" "region"
    And I log out
    And I change window size to "large"
    # Student can not see the restricted section and its subsections
    And I log in as "student1"
    And I am on "Course 1" course homepage
    And I should see "Topic 1" in the "courseindex-content" "region"
    And I should not see "First module" in the "courseindex-content" "region"
    And I should not see "Topic 2" in the "courseindex-content" "region"
    And I should not see "Second module" in the "courseindex-content" "region"
    And I should see "Topic 3" in the "courseindex-content" "region"
    And I should see "Topic 4" in the "courseindex-content" "region"
    And I should see "Topic 1" in the "region-main" "region"
    And I should not see "First module" in the "region-main" "region"
    And I should not see "Topic 2" in the "region-main" "region"
    And I should not see "Second module" in the "region-main" "region"
    And I should see "Topic 3" in the "region-main" "region"
    And I should see "Topic 4" in the "region-main" "region"
