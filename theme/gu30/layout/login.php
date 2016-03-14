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
$container = 'container';

$knownregionpost = $PAGE->blocks->is_known_region('side-post');

$regions = theme_gu30_bootstrap3_grid();
$PAGE->set_popup_notification_allowed(false);
$PAGE->requires->jquery();

$loginslogan = theme_gu30_login_title($PAGE->theme);
$logindescription = theme_gu30_login_description($PAGE->theme);

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link href='https://fonts.googleapis.com/css?family=Yantramanav:400,100,300,500,700,900' rel='stylesheet' type='text/css'>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php require(dirname(__FILE__) . '/includes/navbar.php'); ?>

<header id="moodleheader" class="clearfix">

    <div id="course-header">
        <?php echo $OUTPUT->course_header(); ?>
    </div>
</header>
<section class="login-spacer hidden-xs">
</section>
<section id="main" class="clearfix">
    <div id="page" class="<?php echo $container; ?>">
	    
	    <div class="row">
		    <div class="col-sm-8 login-text">
			    <h2 class="slogan"><?php echo $loginslogan; ?></h2>
			    <div class="hidden-xs">
				    <?php echo $logindescription; ?>
			    </div>
				    
		    </div>
		    <div class="col-sm-4">
			    <form class="form-horizontal" method="post">
				    <div class="control-group">
					    <label class="hide" for="username">Username</label>
					    <input type="text" class="form-control" name="username" placeholder="Username" />
				    </div>
				    <div class="control-group">
					    <label class="hide" for="password">Password</label>
					    <input type="password" class="form-control" name="password" placeholder="Password" />
				    </div>
				    <div class="control-group">
					    <button type="submit" class="btn btn-primary btn-block">
					    	<i class="fa fa-lock"></i> Log in<span class="hidden-xs hidden-sm"> with your GUID</span>
					    </button>
				    </div>
				    <p class="login-help"><a href="https://password.gla.ac.uk">I've Forgotten My Password</a><span class="separator">&bull;</span><a href="http://www.gla.ac.uk/services/it/guid/#tabs=1">What Is My GUID?</a></p>
			    </form>
		    </div>
	    </div>
	    
        <header id="page-header" class="clearfix">
            <div id="course-header">
                <?php echo $OUTPUT->course_header(); ?>
            </div>
        </header>
    
        <?php 
            echo $OUTPUT->main_cuntent(); 
        ?>

    </div>
</section>

<?php echo $OUTPUT->standard_end_of_body_html() ?>

 <a href="#top" class="back-to-top"><i class="fa fa-angle-up "></i></a>
</body>
</html>
