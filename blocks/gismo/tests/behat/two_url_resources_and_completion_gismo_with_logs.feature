@block @block_gismo
Feature: Using two url type resources is viewed in GISMO completion overviews
	In order to enrol one student in course composed by 
    two url type resources
	As a admin
	I need to have the right data on GISMO completion overviews 
	after use of two url type resources

	@javascript
	Scenario: Add two url type resources with completion and access GISMO overviews
		Given the following "courses" exist:
			| fullname | shortname | category |
			| Course 1 | C1 | 0 |
		And the following "users" exist:
			| username | firstname | lastname | email |
			| student1 | Student | 1 | student1@asd.com |
		And the following "course enrolments" exist:
			| user | course | role |
			| student1 | C1 | student |
		And I log in as "admin"
		And I set the following administration settings values:
			| Enable completion tracking | 1 |
			| Enable conditional access | 1 |
		And I am on homepage
		And I follow "Course 1"
		And I turn editing mode on
		And I follow "Edit settings"
		# Moodle 2.7.2
		And I set the following fields to these values:
		# Moodle 2.7.1
		# And I fill the moodle form with:
			| Enable completion tracking | Yes |
		And I press "Save changes"
		And I add the "Gismo" block
		And I add a "URL" to section "1" and I fill the form with:
			| Name | univ-lemans |
			| Description | Test URL description |
			| External URL | http://www.univ-lemans.fr |
			| Completion tracking | Show activity as complete when conditions are met |
			| Student must view this activity to complete it | 1 |
		And I add a "URL" to section "1" and I fill the form with:
			| Name | openStreetMap |
			| Description | Test URL description |
			| External URL | http://www.openstreetmap.org |
			| Completion tracking | Show activity as complete when conditions are met |
			| Student must view this activity to complete it | 1 |
		And I log out
		When I log in as "student1"
		And I am on homepage
		And I follow "Course 1"
		And I follow "univ-lemans"
		And I follow "openStreetMap"
		And I log out
		Then I log in as "admin"
		And I follow "Course 1"
		And I synchronize gismo data
		And I should see "Completed" on "Completion > Resources" report
		And I wait "10" seconds