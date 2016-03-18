@block @block_gismo
Feature: Using a quiz activity is viewed in GISMO overviews
	In order to enrol one student in course composed by 
    one quiz activity
	As a admin
	I need to have the right data on GISMO overviews 
	after use of quiz activity

	@javascript @_switch_window
	Scenario: Add one quiz and access GISMO overviews
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
 		And I add a "Quiz" to section "1" and I fill the form with:
			| Name        | Test quiz name        |
			| Description | Test quiz description |
			| Attempts allowed | 1 |
 		And I add a "True/False" question to the "Test quiz name" quiz with:
			| Question name                      | First question                          |
			| Question text                      | Answer the first question               |
			| General feedback                   | Thank you, this is the general feedback |
			| Correct answer                     | False                                   |
			| Feedback for the response 'True'.  | So you think it is true                 |
			| Feedback for the response 'False'. | So you think it is false                |
		And I log out
		When I log in as "student1"
		And I am on homepage
		And I follow "Course 1"
		And I follow "Test quiz name"
		And I press "Attempt quiz now"
		# Moodle 2.7.2
		And I press "Start attempt"
		# Moodle 2.7.1
		# And I press "Yes"
		Then I should see "Question 1"
		And I should see "Answer the first question"
		And I set the field "True" to "1"
		And I press "Next"
		And I should see "Answer saved"
		And I press "Submit all and finish"
		# Moodle 2.7.2
		And I click on "Submit all and finish" "button" in the "Confirmation" "dialogue"
		# Moodle 2.7.1
		# And I press "Yes"
		And I am on homepage
		And I log out
		Then I log in as "admin"
		And I follow "Course 1"
		And I synchronize gismo data
		And I go to the "Activities > Quizzes" report
		And I should see "Grade: 0.00 / 10.00" on "Activities > Quizzes" report
		And I wait "10" seconds