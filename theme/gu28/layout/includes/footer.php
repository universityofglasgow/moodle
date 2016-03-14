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
		<div id="course-footer">
			<?php echo $OUTPUT->course_footer(); ?>
		</div>
	</div>

	<div class="row">
		<div class="col-lg-6">
      <?php echo $OUTPUT->home_link(); ?>

		</div>

		<div class="col-lg-6 pull-right">
			<?php echo $OUTPUT->login_info();
			if ($hassocialnetworks) {
				echo '<ul class="socials unstyled">';
					if ($hastwitter) {
						echo '<a href="'.$hastwitter.'" class="socialicon twitter">';
						echo '<i class="fa fa-twitter fa-inverse"></i>';
							echo '<span class="sr-only">'.get_string('socialnetworksicondescriptiontwitter','theme_gu28').'</span>';
						echo '</a>';
					}

					if ($hasfacebook) {
						echo '<a href="'.$hasfacebook.'" class="socialicon facebook">';
						echo '<i class="fa fa-facebook fa-inverse"></i>';
							echo '<span class="sr-only">'.get_string('socialnetworksicondescriptionfacebook','theme_gu28').'</span>';
						echo '</a>';
					}

					if ($hasinstagram) {
						echo '<a href="'.$hasinstagram.'" class="socialicon instagram">';
						echo '<i class="fa fa-instagram fa-inverse"></i>';
							echo '<span class="sr-only">'.get_string('socialnetworksicondescriptioninstagram','theme_gu28').'</span>';
						echo '</a>';
					}
				echo '</ul>';
			} ?>
		</div>
	</div>

	<div class="row">
    <p class="helplink"><?php echo $OUTPUT->page_doc_link(); ?></p>
		<?php echo $OUTPUT->standard_footer_html(); ?>
	</div>

</div>
