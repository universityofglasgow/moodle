<?php
// This file is part of Moodle - http://moodle.org/
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
 * The Elegance theme is built upon  Bootstrapbase 3 (non-core).
 *
 * @package    theme
 * @subpackage theme_elegance
 * @author     Julian (@moodleman) Ridden
 * @author     Based on code originally written by G J Bernard, Mary Evans, Bas Brands, Stuart Lamour and David Scotson.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$hasfacebook    = (empty($PAGE->theme->settings->facebook)) ? false : $PAGE->theme->settings->facebook;
$hastwitter     = (empty($PAGE->theme->settings->twitter)) ? false : $PAGE->theme->settings->twitter;
$hasinstagram   = (empty($PAGE->theme->settings->instagram)) ? false : $PAGE->theme->settings->instagram;
$haswebsite     = (empty($PAGE->theme->settings->website)) ? false : $PAGE->theme->settings->website;

// If any of the above social networks are true, sets this to true.
$hassocialnetworks = ($hasfacebook || $hastwitter || $hasinstagram) ? true : false;

?>
<div class="container">
	<div class="row">
		<div class="col-sm-6">
			<h3 class="glasgow">University <em>of</em> Glasgow</h3>
			<p class="address">Glasgow, G12 8QQ, Scotland</p>
			<p class="phone">Tel +44 (0) 141 330 2000</p>
			<p class="charity">The University of Glasgow is a registered Scottish charity: Registration Number SC004401</p>
		</div>
		<div class="col-sm-3 footer-links">
			<h4>Site Links</h4>
			<ul>
				<li><a href="<?php echo $CFG->wwwroot;?>">Moodle Home</a></li>
				<li><?php echo str_replace('Moodle Docs for this page', 'Get help with this page', strip_tags($OUTPUT->page_doc_link(), '<a>')); ?></li>
			</ul>
		</div>
		<div class="col-sm-3 footer-links">
			<h4>Current Students</h4>
			<ul>
				<li><a href="http://www.gla.ac.uk/students/">Information for students</a></li>
				<li><a href="http://www.gla.ac.uk/students/myglasgow/">MyGlasgow students</a></li>
			</ul>
			
			<h4>Staff</h4>
			<ul>
				<li><a href="http://www.gla.ac.uk/myglasgow/staff/">MyGlasgow staff</a></li>
			</ul>
		</div>
		
		<div id="course-footer">
			<?php echo $OUTPUT->course_footer(); ?>
		</div>
	</div>
</div>