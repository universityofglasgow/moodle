# moodle-blocks_newgu_spdetails
DASH - Student Dashboard
___

# Purpose
___
This next of iteration the Student Dashboard (as part of the GCAT project) is intended to display both the current and historical progress of a students journey through the duration of their course.

The initial view on the Dashboard presents an 'at a glance' summary of assignments that have been submitted or need submitting.

The main view displays more detailed information of the work that has been completed, or is outstanding for each of the courses that the student is enrolled on to.

There is also a Staff view of the 'dashboard', which allows only Staff that are enrolled on a particular course to view the students progress also. This is accessed through the Course navigation 'More' menu - aptly named 'Student Dashoard - Staff View'.

# Installation
___
* Either clone or checkout the files to [/your/moodle/]blocks/newgu_spdetails
* Visit Site admin => Notifications, follow the upgrade instructions which will install the files in the usual Moodle way.

# Use
___
* To use, begin by visiting the Dashboard as a user with the Student role, and adding the block "Your assessment details (New)" to the main page. 
* The Student view is then accessed by the "Click here to view your assessment details" button which should appear after the Course Overview block.
* From the assessment details page, you can access assignments, sort by various criteria and view past courses.
* The Status column also allows the student to go directly to the assignment submission page if necessary.

# Uninstall
___
* Remove the `block_newgu_spdetails` and `local_gustaffview` plugins from the Moodle folder:
   * [yourmoodledir]/block/newgu_spdetails
   * [yourmoodledir]/local/gustaffview
* Access the plugin uninstall page: Site Administration > Plugins > Plugins overview
* Look for the removed plugins and click on uninstall for each plugin. 