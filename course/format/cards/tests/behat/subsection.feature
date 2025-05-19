@format @format_cards @format_cards_subsection
Feature: Subsection support
  In order to use courses using the cards format
  As a user
  I can view and navigate through subsections

  Background:
    Given I enable "subsection" "mod" plugin
    And the following config values are set as admin:
      | subsectionsascards | 1 | format_cards |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | Teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format | category | numsections | initsections |
      | Course 1 | C1        | cards  | 0        | 3           | 1            |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity   | name                | course | idnumber | section | visible |
      | subsection | Subsection1         | C1     | sub1     | 1       | 1       |
      | subsection | Subsection2         | C1     | sub2     | 1       | 1       |
      | data       | Database            | C1     | data1    | 1       | 1       |
      | page       | Page in section 1   | C1     | page1    | 1       | 1       |
      | subsection | Subsection3         | C1     | sub3     | 1       | 1       |
      | subsection | Subsection4         | C1     | sub4     | 1       | 0       |
      | subsection | Subsection5         | C1     | sub5     | 1       | 1       |
      | page       | Page in Subsection1 | C1     | page11   | 4       | 1       |
      | page       | Page in Subsection2 | C1     | page21   | 5       | 1       |
      | page       | Page in Subsection3 | C1     | page31   | 6       | 1       |
      | page       | Page in Subsection4 | C1     | page41   | 7       | 1       |
      | page       | Page in Subsection5 | C1     | page51   | 8       | 1       |

  @javascript @moodle_405_and_after
  Scenario: A user can view and navigate to a subsection displayed on a course page
    Given the following config values are set as admin:
      | subsectionsascards | 1 | format_cards |
    And I log in as "student1"
    When I am on "Course 1" course homepage
    Then I should not see "Subsection1" in the "region-main" "region"
    When I am on the "Course 1 > Section 1" "format_cards > section" page
    Then I should see "Subsection1" in the "region-main" "region"
    When I click on "Subsection1" "link" in the "Subsection1" "activity"
    Then I should see "Page in Subsection1" in the "region-main" "region"

  @javascript @moodle_405_and_after
  Scenario: A user can view and navigate to a subsection displayed on a course page, in cards mode
    Given I log in as "student1"
    When I am on "Course 1" course homepage
    Then I should not see "Subsection1" in the "region-main" "region"
    When I am on the "Course 1 > Section 1" "format_cards > section" page
    Then I should see "Subsection1" in the "region-main" "region"
    When I click on "Subsection1" "format_cards > card"
    Then I should see "Page in Subsection1" in the "region-main" "region"

  @javascript @moodle_405_and_after
  Scenario: The section navigation region for a subsection contains a button to go to the parent section
    Given the following config values are set as admin:
      | sectionnavigation | 4 | format_cards |
    And I log in as "student1"
    And I am on the "Course 1 > Section 1" "format_cards > section" page
    Then "[data-action=parentsection]" "css_element" should not exist in the "sectionnavigation-top" "region"
    And "[data-action=parentsection]" "css_element" should not exist in the "sectionnavigation-bottom" "region"
    When I click on "Subsection1" "link" in the "Subsection1" "activity"
    Then "Go to section Section 1" "icon" should exist in the "sectionnavigation-top" "region"
    And "Go to section Section 1" "icon" should exist in the "sectionnavigation-bottom" "region"
    When I click on "Go to section Section 1" "icon" in the "sectionnavigation-top" "region"
    And I click on "Subsection1" "link" in the "Subsection1" "activity"
    And I click on "Go to section Section 1" "icon" in the "sectionnavigation-bottom" "region"
    Then "Subsection1" "activity" should exist

  @javascript @moodle_405_and_after
  Scenario Outline: I can navigate between subsections
    Given the following config values are set as admin:
      | sectionnavigation  | 4 | format_cards |
    And I log in as "<user>"
    When I am on the "Course 1 > Section 1" "format_cards > section" page
    Then "Subsection4" "activity" <should or not> be visible

    # Make sure we can click the next section button in the top navigation
    When I click on "Subsection3" "format_cards > card"
    Then ".prevsection [data-action=previoussection]" "css_element" should not exist
    And I click on "Go to section <subsection after subsection3>" "link" in the "sectionnavigation-top" "region"
    Then "Page in <subsection after subsection3>" "activity" should exist in the "region-main" "region"

    # And that we can click the next section button in the bottom navigation
    When I am on the "Course 1 > Section 1" "format_cards > section" page
    And I click on "Subsection3" "format_cards > card"
    And I click on "Go to section <subsection after subsection3>" "link" in the "sectionnavigation-bottom" "region"
    Then "Page in <subsection after subsection3>" "activity" should exist in the "region-main" "region"

    # Next check that we can click the previous section button in the top navigation
    When I am on the "Course 1 > Section 1" "format_cards > section" page
    And I click on "Subsection5" "format_cards > card"
    Then ".nextsection [data-action=nextsection]" "css_element" should not exist
    And I click on "Go to section <subsection before subsection5>" "link" in the "sectionnavigation-top" "region"
    Then "Page in <subsection before subsection5>" "activity" should exist in the "region-main" "region"

    # And the previous section button in the bottom navigation
    When I am on the "Course 1 > Section 1" "format_cards > section" page
    And I click on "Subsection5" "format_cards > card"
    And I click on "Go to section <subsection before subsection5>" "link" in the "sectionnavigation-bottom" "region"
    Then "Page in <subsection before subsection5>" "activity" should exist in the "region-main" "region"

    # The last section in the course should not include a "next section" button to take us to the first subsection
    When I am on the "Course 1 > Section 3" "format_cards > section" page
    Then ".nextsection [data-action=nextsection]" "css_element" should not exist

    # The first subsection should not include a "previous section" button that takes us to the last top-level section
    When I am on the "Course 1 > Section 1" "format_cards > section" page
    And I click on "Subsection1" "format_cards > card"

    Examples:
      | user     | should or not | subsection after subsection3 | subsection before subsection5 |
      | teacher1 | should        | Subsection4                  | Subsection4                   |
      | student1 | should not    | Subsection5                  | Subsection3                   |

  @moodle_405_and_after
  Scenario Outline: I can choose for subsections to be rendered as cards instead
    Given the following config values are set as admin:
      | subsectionsascards | <adminvalue> | format_cards |
    And I am on the "Course 1" "course editing" page logged in as "admin"
    And I expand all fieldsets
    And I set the field "Subsections" to "<coursevalue>"
    And I click on "Save and display" "button"
    When I am on the "Course 1 > Section 1" "format_cards > section" page
    Then "Subsection1" "format_cards > card" <should or not> exist

    Examples:
      | adminvalue | coursevalue                            | should or not |
      | 1          | Default (Show as cards)                | should        |
      | 1          | Show as cards                          | should        |
      | 1          | Show as collapsible sections           | should not    |
      | 2          | Default (Show as collapsible sections) | should not    |
      | 2          | Show as cards                          | should        |
      | 2          | Show as collapsible sections           | should not    |

  @javascript @moodle_405_and_after
  Scenario: I can create a new subsection
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage with editing mode on
    When I click on "Add content" "button" in the "General" "section"
    And I click on "Subsection" "link" in the ".dropdown-menu.show" "css_element"
    Then I should see "New subsection" in the "General" "section"

  @javascript @moodle_405_and_after
  Scenario Outline: Subsections in the general section zero are rendered properly
    Given the following "activities" exist:
      | activity   | name                | course | idnumber | section | visible |
      | subsection | Subsection0         | C1     | sub0     | 0       | 1       |
      | page       | Page in Subsection0 | C1     | page01   | 9       | 1       |
    And the following config values are set as admin:
      | subsectionsascards | <adminvalue> | format_cards |
    And I am on the "Course 1" "course editing" page logged in as "admin"
    And I expand all fieldsets
    And I set the field "Subsections" to "<coursevalue>"
    And I click on "Save and display" "button"
    And I am on "Course 1" course homepage
    Then I <should or not> see "Page in Subsection0" in the "region-main" "region"

    Examples:
      | adminvalue | coursevalue                            | should or not |
      | 1          | Default (Show as cards)                | should not    |
      | 1          | Show as cards                          | should not    |
      | 1          | Show as collapsible sections           | should        |
      | 2          | Default (Show as collapsible sections) | should        |
      | 2          | Show as cards                          | should not    |
      | 2          | Show as collapsible sections           | should        |
