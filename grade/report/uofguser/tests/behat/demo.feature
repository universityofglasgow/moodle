@gradereport @gradereport_uofguser @javascript
Feature: Testing demo in gradereport_uofguser

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category | groupmode |
      | Course 1 | C1        | 0        | 1         |
    And the following "users" exist:
      | username  | firstname | lastname  | email                 | idnumber  |
      | teacher1  | Teacher   | 1         | teacher1@example.com  | t1        |
      | student1  | Student   | 1         | student1@example.com  | s1        |
      | student2  | Student   | 2         | student2@example.com  | s2        |
    And the following "course enrolments" exist:
      | user      | course | role           |
      | teacher1  | C1     | editingteacher |
      | student1  | C1     | student        |
      | student2  | C1     | student        |
    And the following "groups" exist:
      | name          | course | idnumber |
      | Default <span class="multilang" lang="de">Gruppe</span><span class="multilang" lang="en">group</span> | C1     | dg       |
      | Tutor <span class="multilang" lang="de">Gruppe</span><span class="multilang" lang="en">group</span>   | C1     | tg       |
      | Marker <span class="multilang" lang="de">Gruppe</span><span class="multilang" lang="en">group</span>  | C1     | mg       |
    And the following "group members" exist:
      | user     | group |
      | student1 | dg    |
    And the "multilang" filter is "on"
    And the "multilang" filter applies to "content and headings"
    And I am on the "Course 1" "grades > UofG User Report > View" page logged in as "teacher1"

  Scenario: navigate back and forth between different reports
    When I select "Grades" from secondary navigation
    And I navigate to "View > User report" in the course gradebook
    Then I should see "User report"
    And I navigate to "View > UofG User Report" in the course gradebook
    Then I should see "User report"
    And I navigate to "View > Grader report" in the course gradebook
    Then I should see "Grader report"
