@block @block_course_overview
Feature: Add the course overview (legacy) block on the dashboard and check it's there
  In order to view the course overview (legacy) block on the dashboard
  As a user
  I can add the block to the dashboard page

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email | idnumber |
      | student1 | Student | 1 | student1@example.com | S1 |
      | teacher1 | Teacher | 1 | teacher1@example.com | T1 |
    And the following "categories" exist:
      | name        | category | idnumber |
      | Category 1  | 0        | CAT1     |
      | Category 2  | CAT1     | CAT2     |
    And I log in as "student1"

  Scenario: Add course overview (legacy)  block to page
      When I press "Customise this page"
      And I add the "Course overview (legacy)" block
      And I configure the "Course overview (legacy)" block
      And I set the field "Region" to "content"
      And I press "Save changes"
      Then I should see "Course overview (legacy)" in the "Course overview (legacy)" "block"
      And I should see "There are no courses to show" in the "Course overview (legacy)" "block"


      
