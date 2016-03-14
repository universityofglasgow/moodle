@block @block_gismo
Feature: Using an chat activity is viewed in GISMO overviews
	In order to enrol one student in course composed by 
	one chat activity
	As a admin
	I need to have the right data on GISMO overviews 
	after use of chat activity

	@javascript @_switch_window
	Scenario: Add one chat and access GISMO overviews
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
		And I am on homepage
		And I follow "Course 1"
		And I turn editing mode on
		And I add the "Gismo" block
		And I add a "Chat" to section "1" and I fill the form with:
			| Name of this chat room | Chat room |
			| Description | Chat description |
		And I wait until the page is ready
		And I log out
		When I log in as "student1"
		And I am on homepage
		And I follow "Course 1"
		And I follow "Chat room"
		And I follow "Use more accessible interface"
		And I switch to "chat2_1" window
		And I set the field "Send message" to "test message"
		And I press "Submit"
		And I switch to the main window
		And I am on homepage
		And I log out
		Then I log in as "admin"
		And I follow "Course 1"
		And I synchronize gismo data
		And I go to the "Activities > Chats" report
		And I should see "1" on "Activities > Chats over time" report
		And I wait "10" seconds