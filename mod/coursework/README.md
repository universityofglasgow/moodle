Coursework Activity
======================================================================
Copyright University of London.

Any queries, please email coursework@london.ac.uk

The Coursework Activity has been written to provide a way to receive coursework and have it marked without teachers knowing which student the work belongs to. Multiple markers are also supported where a final grade can be agreed based on several options. It does not replace the standard assignment activity and is intended to work alongside it.
Special thanks to Royal Veterinary College and @aspark21, Plymouth University, London School of Tropical Medicine and University of London International Programmes for funding parts of the development and documentation. 

Current Coursework plugin includes following features as well as features added in Releases below:

•	Integration with Turnitin plagiarism tool

•	Use of Moodle’s core grading methods

•	Blind Marking (Instructors do not know who they are marking)

•	Blind Feedback (Students do not know who marked their submission)

•	Bulk download of submissions

•	Bulk upload of annotated files

•	Bulk submission of grades through a grading worksheet

•	Support for up to 3 markers

•	Control over who sees feedback and grades at each stage

•	Automatic marker allocation rules

•	Group submissions

•	Backup & Restore Support

•	Duplication Support

•	Import of markers allocation from CSV



Releases 
=================

**November 2019** - Features funded by UoLIA

* Moodle 3.5 & Moodle 3.6 compatibility
* small bug fixes
* GDPR added

New features:

* Moodle groups access restrictions- the markers will have access to submissions belonging to Moodle groups they are allocated to. If 'Assessor from Moodle course group assigned to Stage 1' option from 'Marking workflow->Assessor allocation strategy' is chosen, the first marking stage will be automatically allocated to a tutor who is the part of the group 

* Moodle Groups can be filtered on the grading page when enabled in 'Common module settings', 'Group mode'  NOTE: When group filtering on the coursework marking page is applied, ALL available marks will be released (as if the filter wasn't applied), not only those visible on the page.

* Rubric support - Coursework now supports offline Rubic grading ('Export grading sheet', 'Upload grading worksheet'). Rubric marks will also appear in 'Export final grades' download. 

* Plagiarism Identification - Markers will be able to set a flag (mark) for students identified for plagiarism. This can be enabled in Coursework settings 'Submissions->Enable Plagiarism flagging'.  The plagiarism marking has 4 flags: 
    - Under Investigation - this will prevent release of grades and feedbacks
    - Release (no action taken)
    - Cleared: Release Results
    - Not Cleared: Withhold Results -  this will prevent release of grades and feedbacks



**March 2018**

* general bug fixes
* bug fix to respect Moodle "Restrict Access" for individual students and groups
* bug fix to encode % sign properly for feedback files which was causing "Bad Request" server error
* added new scheduled tasks to process enrolment/unenrolment allocations when user is enrolled/unenrolled from the course. <br> 
  This is to make sure the allocation takes place in the background preventing pages freeze. This is set by default to run every 1 hour,<br> 
  but if you require it more often, change settings in the scheduled tasks 

New features: 

* moderation agreement for single marked coursework where moderator can agree/disagree with assessor mark
* new 'Save as draft' button for assessors feedbacks
* pagination on the Allocation page
* pin/unpin all assessors on the Allocation page
* global default for view per page

**August 2017**

* compatibility with Moodle 3.3
* compatibility with new core Course Overview - only for courseworks with the fixed deadlines using 'Initial marking deadline' and/or 'Agreed grade marking deadline'  (courseworks with individual deadlines or no deadlines will not be displayed in neither student or teacher views as they have relative marking deadlines)


New features:

* initial marking deadline (date that initial grading should be completed by)
* agreed grade marking deadline (date agreed grading should be completed by)
* deadline defaults in Coursework global settings 

**June 2017**

* compatibility with Moodle 3.2
* general bug fixes
* PHP7 compatibility
* new coursework icon

**May 2017**

* compatibility with Moodle 3.1
* general bug fixes
* local_ulcc_framework merged into coursework plugin 
* Export final grade sheet - allocated assessor field added

New features:  

* receipting for all submissions with the global setting to switch the receipting only for finalised submissions
* auto-populate agreed feedback with comments from initial marking (new coursework setting)
* coursework with personal deadlines for individual students or groups
* coursework with no deadlines


**September 2016**

* added compatibility with TII plugin - plagiarism_turnitin v2016091401
* removed deprecated add_into_editor function to make it compatible with Moodle 3.1
* changed Events handlers to use new Events 2 API (observers) - compatibility with Moodle 3.1


**January 2016**

New features:

* delay agreed grade giving initial markers chance to edit their grades
* import of markers allocation from CSV
* addallocatedagreedgrade capability that allows a user to add agreed grade for submissions they marked in initial stage


**November 2015**

New features:

* manual and automatic sampling (range and total rules)
* auto agreement of agreed grade within percentage distance
* download and upload of grading sheet
* final grades download
* bulk download and upload of annotated files
* coursework backup & restore
* coursework duplication
* coursework notification message for released feedback


Plagiarism
==========
Coursework works with Turnitin plagiarism plugin https://github.com/turnitin/moodle-plagiarism_turnitin and the lowest recommended version is v2017022201


Release & Support
=================

This is currently a beta preview release and includes untested code. 
This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details: 
http://www.gnu.org/copyleft/gpl.html


Documentation
=============

The documentation can be found in https://docs.moodle.org/31/en/Coursework_module

Installation
============
Place the plugin under Moodle root/mod folder. 
This version of Coursework does not require local/ulcc_framework

Reliability
===========

University of London tests its developments on its own systems that run on a standard LAMP stack. Any issues resulting from running on other infrastructure is not supported. 
Bug Reports:
Please report bugs using the GitHub issues tab. When reporting a bug please outline the exact steps you took that resulted in the bug so it is easier to identify and potentially fix. 

Defining roles
===============

Coursework allows institutions to define their permissions unique to their institution. If you wish for help setting these up please contact moodle@rvc.ac.uk 

Configuration
=============

Be aware that you will need to prevent teachers from viewing logs in your courses/site in order for the anonymity to be effective.

Running Behat tests
=================

Follow the steps to install both PHPUnit and Behat on your Moodle instance:

http://docs.moodle.org/dev/PHPUnit#Installation_of_PHPUnit_via_Composer
http://docs.moodle.org/dev/Acceptance_testing#Installation

PHPUnit

* In PHPStorm, go to settings --> PHP --> PHPUnit
* Make a new configuration if there's not one there already
* Choose 'use custom loader'
* Set the path to the loader to be "/path/to/your/docroot/vendor/autoload.php"
* Set a keyboard shortcut for the Main menu/Navigate/Test action (Settings --> keymap)
* Go to the /tests folder, open the generator_test.php file, place the cursor inside the class and press the shortcut.

Behat

* In PHPStorm, go to Settings --> PHP --> Behat
* Make a new configuration if there's not one there already
* Set the path to the Behat directory to be "/path/to/your/docroot/vendor/behat/behat"
* Set the deafult configuration file to be "/path/to/your/behat_moodledata/behat/behat.yml"
* Go to the /tests/behat folder, open the factory.feature file, place the cursor inside one of the scenarios and press the shortcut.


