<?php
// This file is part of course-quick-access plugin for Moodle
// http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * English language strings for the quick-course-block.
 *
 * @package    block_course_quick_access
 * @copyright  2012 Thomas Heinz <mail@th1z.net>
 * @webseite   http://www.th1z.net/projects/moodle-cqa/
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Course quick access';

// ===== Block ====
$string['course_quick_access:addinstance'] = 'Add instance of course_quick_access';
$string['course_quick_access:myaddinstance'] = 'Add a new course_quick_access block to Dashboard';
$string['usernotenrolledincourses'] = 'Not enrolled in courses';
$string['coursenotcategorized'] = 'Not assigned courses';

// ===== Block Footer =====
$string['allmoodlecourses'] = 'All moodle courses';
$string['allmycourses'] = 'Show all my courses';
$string['configureplugin'] = 'Configure quick access';

// ===== Admin Configuration =====
$string['usejavascript'] = 'Use javascript';
$string['descusejavascript'] = 'Use javascript functionality. TODO: ...';
$string['showsearchsubmitbutton'] = 'Show search-submit button';
$string['descshowsearchsubmitbutton'] = 
	'Displays a submit button below the search field.';
$string['searchsubmitbutton2line'] = 'Submit button on 2. line';
$string['descsearchsubmitbutton2line'] = 'TODO';

// ===== User Configuration =====
$string['configpagetitle'] = 'Course-Quick-Access configuration';
$string['configpageheading'] = 'Course-Quick-Access configuration';
$string['confignavigationtitle'] = 'Configure course-quick-access';

// -- New/Edit Category Form --
$string['createnewcategory'] = 'Create a new category';
$string['createoreditcategory'] = 'Create/Edit category';
$string['nospecialchars'] = 
	'You must enter only letters, numbers, and the following special'.
	 'characters here: "/", "-".';

// -- Delete Category Form --
$string['deletecategoryheader'] = 'Delete category';
$string['deletecategorymessage'] = 
	'Do you really want to delete this category:';
$string['deletecategorymove'] = 'Move child courses to';
$string['deletecategorysubmit'] = 'Delete this category';

// -- Category List Form --
$string['categorylistheadline'] = 'User categories';
$string['nocatcreatedtext'] = 
	'You have not created any categories.' .
	'To customise the course-quick-access plugin click the '. 
	'<b>"Create a new category"</b> link below.';
$string['createmorecattext'] = 
	'To create more categories use the ' .
	'<b>"Create a new category"</b> link below.';
$string['categorylistdesc'] = 
	'Below you find a list of all your created categories.'.
	'You can edit their name, change their visibility, order and '. 
	're-arrange them with the corresponding <b>icons</b>. '.
	'Categories that are hidden or contain no courses '.
	'will not show up in the quick-access list.';
$string['categorynameheadline'] = 'Category Name';
$string['courseamountheadline'] = 'Assigned Courses';
$string['categoryeditheadline'] = 'Edit';
$string['categorymoveheadline'] = 'Move';
$string['cateditlinktext'] = 'edit';
$string['catdeletelinktext'] = 'delete';
$string['cathidelinktext'] = 'hide';
$string['catshowlinktext'] = 'show';
$string['catmoveup'] = 'up';
$string['catmovedown'] = 'down';

// -- Course List Form --
$string['courselistheadline'] = 'User course assignment';
$string['nocourses'] = 'You are currently not enrolled in any courses.';
$string['courselistdesc'] = 
	'All courses you are enroled in are listed below.'.
	'Use the drop-down lists to assign them to your categories. '. 
	'Do not forget to use the <b>"Save changes"</b> button, to save your '.
	'settings.';
$string['courselistcoursehead'] = '<b>Enroled course</b>';
$string['courselistselecthead'] = '<b>Assigned category</b>';
$string['newcategory'] = 'Category name';
$string['categoriyisvisible'] = 'Is visible';

// ===== Javascript =====
$string['hiddencoursesnotloggedin'] = 'Only showing matching search results. Press "Enter" to search over all courses.';
$string['hiddencourses'] = 'Only showing matching search results. Press "Enter" to search over all courses.';
