@local @local_xp @javascript
Feature: Completion rules can be applied sitewide
  In order to award points for completing activities, sections and courses
  As a manager
  I need to configure the sitewide and scoped rules

  Background:
    Given the following "courses" exist:
      | fullname  | shortname | enablecompletion |
      | Course 1  | c1        | 1                |
      | Course 2  | c2        | 1                |
    And the following "users" exist:
      | username | firstname | lastname |
      | s1       | Student   | One      |
      | s2       | Student   | Two      |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | s1       | c1     | student        |
      | s2       | c1     | student        |
      | s1       | c2     | student        |
      | s2       | c2     | student        |
    And the following config values are set as admin:
      | name              | value |
      | block_xp_context  | 10    |
    And the following "blocks" exist:
      | blockname | contextlevel | reference |
      | xp        | System       | -         |
    And the following "activities" exist:
      | activity | course | section | name   | intro       | content        | completion | completionview |
      | page     | c1     | 1       | Page 1 | Page 1 desc | Page 1 content | 1          | 1              |
      | page     | c1     | 2       | Page 2 | Page 2 desc | Page 2 content | 1          | 1              |
      | page     | c1     | 2       | Page 3 | Page 3 desc | Page 3 content | 1          | 1              |
      | page     | c2     | 1       | Page 1 | Page 1 desc | Page 1 content | 1          | 1              |
      | page     | c2     | 2       | Page 2 | Page 2 desc | Page 2 content | 1          | 1              |
      | page     | c2     | 2       | Page 3 | Page 3 desc | Page 3 content | 1          | 1              |
    And I am on the "sys" "block_xp > rules" page logged in as "admin"
    And I delete all XP event rules

  Scenario: Points can be awarded for completing any activity
    And I am on the "sys" "block_xp > completionrules" page logged in as "admin"
    And I press "Add"
    And I click on "Any activity" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "2"
    And I press tab
    And I press "Save"

    And I press "Change to course"
    And I set the field "Search and select a course" to "Course 1"
    When I click on "Select" "button" in the "Select course" "dialogue"
    And I press "Add"
    And I click on "Any activity" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "4"
    And I press tab
    And I press "Save"

    And I follow "Report" in the XP nav
    And I should see "The report is empty"

    When I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"

    And I am on the "sys" "block_xp > report" page logged in as "admin"
    Then the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 4     |

    And I am on the "c2" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"

    And I am on the "sys" "block_xp > report" page logged in as "admin"
    Then the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 6     |

  Scenario: Points can be awarded for completing specific activities
    Given I am on the "sys" "block_xp > completionrules" page logged in as "admin"
    And I press "Change to course"
    And I set the field "Search and select a course" to "Course 1"
    When I click on "Select" "button" in the "Select course" "dialogue"
    And I press "Add"
    And I click on "Specific" "button" in the "Add a condition" "dialogue"
    And I click on "Page 2" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "3"
    And I press tab
    And I press "Save"
    And I follow "Report" in the XP nav
    And I should see "The report is empty"

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"

    And I am on the "sys" "block_xp > report" page logged in as "admin"
    And I should see "The report is empty"

    And I am on the "c1" "course" page logged in as "s1"
    When I click on "Page 2" "link" in the "region-main" "region"

    And I am on the "sys" "block_xp > report" page logged in as "admin"
    Then the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 3     |

  Scenario: Points can be awarded for completing with specific names
    Given I am on the "sys" "block_xp > completionrules" page logged in as "admin"
    And I press "Add"
    And I click on "Activity name" "button" in the "Add a condition" "dialogue"
    And I set the field "Comparison method" to "contains"
    And I set the field "Activity name" to "Page"
    And I set the field "Points to award" to "2"
    And I press tab
    And I press "Save"
    And I press "Change to course"
    And I set the field "Search and select a course" to "Course 1"
    And I click on "Select" "button" in the "Select course" "dialogue"

    And I press "Add"
    And I click on "Activity name" "button" in the "Add a condition" "dialogue"
    And I set the field "Comparison method" to "contains"
    And I set the field "Activity name" to "Page"
    And I set the field "Points to award" to "8"
    And I press tab
    And I press "Save"

    And I press "Add"
    And I click on "Activity name" "button" in the "Add a condition" "dialogue"
    And I set the field "Comparison method" to "is equal to"
    And I set the field "Activity name" to "Page 2"
    And I set the field "Points to award" to "32"
    And I press tab
    And I press "Save"

    And I follow "Report" in the XP nav
    And I should see "The report is empty"

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"
    And I follow the breadcrumb "Course 1"
    And I click on "Page 2" "link" in the "region-main" "region"

    And I am on the "c2" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"
    And I follow the breadcrumb "Course 2"
    And I click on "Page 2" "link" in the "region-main" "region"

    And I am on the "sys" "block_xp > report" page logged in as "admin"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 44    |

  Scenario: Points can be awarded for completing any sections
    Given I am on the "sys" "block_xp > completionrules" page logged in as "admin"
    And I press "Section"
    And I press "Add"
    And I click on "Any section" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "1"
    And I press tab
    And I press "Save"

    And I press "Change to course"
    And I set the field "Search and select a course" to "Course 1"
    And I click on "Select" "button" in the "Select course" "dialogue"

    And I press "Section"
    And I press "Add"
    And I click on "Any section" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "7"
    And I press tab
    And I press "Save"

    And I follow "Report" in the XP nav
    And I should see "The report is empty"

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 2" "link" in the "region-main" "region"
    And I am on the "sys" "block_xp > report" page logged in as "admin"
    And I should see "The report is empty"

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 3" "link" in the "region-main" "region"
    And I am on the "sys" "block_xp > report" page logged in as "admin"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 7     |

    And I am on the "c2" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"
    And I am on the "sys" "block_xp > report" page logged in as "admin"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 8     |

  Scenario: Points can be awarded for completing specific sections
    Given I am on the "c1" "course" page logged in as "admin"
    And I turn editing mode on
    And I edit the section "1"
    And I click on "Custom" "checkbox" if it exists
    And I set the field "name" to "Topic 1"
    And I press "Save changes"
    And I am on "c1" course homepage
    And I edit the section "2"
    And I click on "Custom" "checkbox" if it exists
    And I set the field "name" to "Topic 2"
    And I press "Save changes"
    And I am on the "c2" "course" page logged in as "admin"
    And I turn editing mode on
    And I edit the section "1"
    And I click on "Custom" "checkbox" if it exists
    And I set the field "name" to "Topic 1"
    And I press "Save changes"
    And I am on "c2" course homepage
    And I edit the section "2"
    And I click on "Custom" "checkbox" if it exists
    And I set the field "name" to "Topic 2"
    And I press "Save changes"

    And I am on the "sys" "block_xp > completionrules" page logged in as "admin"
    And I press "Change to course"
    And I set the field "Search and select a course" to "Course 1"
    And I click on "Select" "button" in the "Select course" "dialogue"

    And I press "Section"
    And I press "Add"
    And I click on "Specific section" "button" in the "Add a condition" "dialogue"
    And I press "Topic 2"
    And I set the field "Points to award" to "8"
    And I press tab
    And I press "Save"

    And I follow "Report" in the XP nav
    And I should see "The report is empty"

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 2" "link" in the "region-main" "region"
    And I am on the "sys" "block_xp > report" page logged in as "admin"
    And I should see "The report is empty"

    And I am on the "c1" "course" page logged in as "s1"
    When I click on "Page 3" "link" in the "region-main" "region"
    And I am on the "sys" "block_xp > report" page logged in as "admin"
    Then the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 8     |

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"
    And I am on the "sys" "block_xp > report" page logged in as "admin"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 8     |

  Scenario: Points can be awarded for completing the course
    Given I am on the "c1" "course" page logged in as "admin"
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the following fields to these values:
      | Page 1 | 1 |
    And I press "Save changes"
    And I am on the "c2" "course" page logged in as "admin"
    And I navigate to "Course completion" in current page administration
    And I expand all fieldsets
    And I set the following fields to these values:
      | Page 1 | 1 |
    And I press "Save changes"

    And I am on the "sys" "block_xp > completionrules" page logged in as "admin"
    And I click on "Course" "button" in the "region-main" "region"
    And I press "Add"
    And I click on "Any course" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "1"
    And I press tab
    And I press "Save"

    And I press "Change to course"
    And I set the field "Search and select a course" to "Course 1"
    And I click on "Select" "button" in the "Select course" "dialogue"
    And I click on "Course" "button" in the "region-main" "region"
    And I press "Add"
    And I click on "This course" "button" in the "Add a condition" "dialogue"
    And I set the field "Points to award" to "10"
    And I press tab
    And I press "Save"

    And I am on the "sys" "block_xp > report" page logged in as "admin"
    And I should see "The report is empty"

    And I am on the "c1" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"
    And I trigger cron
    And I am on the "sys" "block_xp > report" page logged in as "admin"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 10     |

    And I am on the "c2" "course" page logged in as "s1"
    And I click on "Page 1" "link" in the "region-main" "region"
    And I trigger cron
    And I am on the "sys" "block_xp > report" page logged in as "admin"
    And the following should exist in the "table" table:
      | First name  | Total |
      | Student One | 11    |
