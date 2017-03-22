
Readme file for the course-quick-access block
=============================================

A plugin for the learning management system moodle to provide individual 
course assortment for its users.

- @package    block_course_quick_access
- @copyright  2012 Thomas Heinz <mail@th1z.net>
- @webseite   http://www.th1z.net/projects/moodle-cqa/
- @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later


Description
-----------

This plugin adresses the navagation problem for users that are enrolled in a 
lot of courses at a moodle site.
It enables the users to configure an individual course-quick-access for 
themselves with simple course to folder assignments.
All user configuration is optional. If a user does not want to configure the
plugin for himself, the behaviour of the default "my courses"-block is mimicked.
Also there a some optional JavaScript functionalities that enhance the course
search and provide a more sophisticated user experience.

For more information visit: http://www.th1z.net/projects/moodle-cqa/


Installation
------------

- Copy the "course_quick_access" folder into the "moodle/blocks" directory
- Visit your moodle site in a browser, logged in as an administrator.
- Go to "Site Administration > Notifications > Continue"
- Add the "course-quick-access"/"Kursschnellzugriff" block to the desired
moodle sites. 
- I would reccommend to configure the block to be displayed on all 
moodle-contextes. 
- Done

Note: During installation two tables ("block_qacategory" & 
"block_qacategory_course") will be created in the moodle database.
These store the users settings and individual course assignments.

Changelog
---------

v2012042308:
- Initial bachelor thesis version