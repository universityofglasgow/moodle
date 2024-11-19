# moodle-blocks_newgu_spdetails
Student MyGrades
___

# Purpose
___
This plugin is intended to display both current and historical courses that a student is, or has been enrolled in (currently is for the current academic year).

The initial view presents charts showing an 'at a glance' summary of assessments that have been submitted, graded or are still outstanding. There is also a chart displaying assessments that are due within the next 24 hours, week or month. Both charts provides links through to a view showing these assessments.

The main view displays a list of courses and their top level sub categories, which the student can navigate into, in order to either view any assessments, or further grade categories - depending on how the course has been structured. From here, the student can follow the links to the submission page - if applicable - for the relevant activity.

# Installation
___
* Either clone or checkout the files to [/your/moodle/]blocks/newgu_spdetails
* Visit Site admin => Notifications, follow the upgrade instructions which will install the files in the usual Moodle way.
* Add the following link to the "Custom menu items" in Site Administration -> Appearance -> Advanced theme settings:
* MyGrades (BETA)|http://[your moodle address]/blocks/newgu_spdetails/index.php

# Use
___
* To use, simply begin by logging in to your Moodle environment as a user with the Student Role. The link added above should now appear in the top navigation - clicking this link will take you to the Student MyGrades page.
* From here, you can access assessments via the charts, or by following the links to the various grade categories for each course. The course name links directly to the course. Activities link directly to the submission pages for something that is still be submitted or is overdue. Feedback links to the feedback section of an activity - if it has been provided after grading.

# Uninstall
___
* Access the Plugins overview page: Site Administration > Plugins > Plugins overview
* Look for each plugin and click on "Uninstall". Follow the on screen instructions.
* Once the process has completed, remove the `block_newgu_spdetails` directory, followed by `local_gustaffview` from your source code directory:
   * [yourmoodledir]/blocks/newgu_spdetails
   * [yourmoodledir]/local/gustaffview
* this will prevent the plugins from reinstalling themselves.