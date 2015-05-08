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

$fluid = true;
$container = 'container-fluid';

$knownregionpost = $PAGE->blocks->is_known_region('side-post');

$regions = theme_gu28_bootstrap3_grid();
$PAGE->set_popup_notification_allowed(false);
$PAGE->requires->jquery();

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php require(dirname(__FILE__) . '/includes/navbar.php'); ?>

<header id="moodleheader" class="clearfix">
    <div id="page-navbar" class="container-fluid">
        <nav class="breadcrumb-nav" role="navigation" aria-label="breadcrumb"><?php echo $OUTPUT->navbar(); ?></nav>
        <div class="breadcrumb-button"><?php echo $OUTPUT->page_heading_button(); ?></div>
    </div>

    <div id="course-header">
        <?php echo $OUTPUT->course_header(); ?>
    </div>
</header>

<section id="main" class="clearfix">
    <div id="page" class="<?php echo $container; ?>">
        <header id="page-header" class="clearfix">
            <div id="course-header">
                <?php echo $OUTPUT->course_header(); ?>
            </div>
        </header>
        <div id="page-content" class="row">
            <div id="region-main" class="<?php echo $regions['content']; ?>">
			    <div id="heading"><?php echo $OUTPUT->page_heading(); ?></div>
                    <?php
                    echo $OUTPUT->course_content_header();
        			echo $OUTPUT->main_content();
        			echo $OUTPUT->course_content_footer();
        			?>
                </div>
                <?php
                if ($knownregionpost) {
                    echo $OUTPUT->blocks('side-post', $regions['post']);
                }?>
            </div>
        </div>
    </div>
</section>

<footer id="page-footer">
  <?php require_once(dirname(__FILE__).'/includes/footer.php'); ?>
</footer>

<?php echo $OUTPUT->standard_end_of_body_html() ?>
 <a href="#top" class="back-to-top"><i class="fa fa-angle-up "></i></a>
</body>
</html>
