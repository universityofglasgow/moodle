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


/// Check for timed out sessions
if (!empty($SESSION->has_timed_out)) {
    $session_has_timed_out = true;
    unset($SESSION->has_timed_out);
} else {
    $session_has_timed_out = false;
}

/// Initialize variables
$errormsg = '';
$errorcode = 0;

/// auth plugins may override these - SSO anyone?
$frm  = false;
$user = false;

if ($session_has_timed_out and !data_submitted()) {
    $errormsg = get_string('sessionerroruser', 'error');
    $errorcode = 4;
}

$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$hascopyright = (empty($PAGE->theme->settings->copyright)) ? false : $PAGE->theme->settings->copyright;
$hasfootnote = (empty($PAGE->theme->settings->footnote)) ? false : $PAGE->theme->settings->footnote;
$hasltiles = (!empty($PAGE->theme->settings->tiles));

$haslogo = (empty($PAGE->theme->settings->logo)) ? false : $PAGE->theme->settings->logo;
//$fluid = (!empty($PAGE->layout_options['fluid']));
$fluid = true;

// Instagram stuff
$instagramdisplay = (empty($PAGE->theme->settings->instagramdisplay)) ? false : $PAGE->theme->settings->instagramdisplay;
if ($instagramdisplay) {
    $images = theme_gu28_instagram_images($PAGE->theme);
} else {
    $images = null;
}

$container = 'container';
if (isset($PAGE->theme->settings->fluidwidth) && ($PAGE->theme->settings->fluidwidth == true)) {
    $container = 'container-fluid';
}
if ($fluid) {
    $container = 'container-fluid';
}

 if (!empty($CFG->loginpasswordautocomplete)) {
    $autocomplete = 'autocomplete="off"';
} else {
    $autocomplete = '';
}

if (!empty($CFG->registerauth) or is_enabled_auth('none') or !empty($CFG->auth_instructions)) {
    $show_instructions = true;
} else {
    $show_instructions = false;
}

if ($show_instructions) {
    $columns = 'twocolumns';
} else {
    $columns = 'onecolumn';
}

$regions = theme_gu28_bootstrap3_grid($hassidepost);
$PAGE->set_popup_notification_allowed(false);
$PAGE->requires->jquery();

if (isloggedin() and !isguestuser()) {
    redirect ($CFG->wwwroot);
}

/// Check if the user has actually submitted login data to us

if ($frm and isset($frm->username)) {                             // Login WITH cookies

    $frm->username = trim(core_text::strtolower($frm->username));

    if (is_enabled_auth('none') ) {
        if ($frm->username !== clean_param($frm->username, PARAM_USERNAME)) {
            $errormsg = get_string('username').': '.get_string("invalidusername");
            $errorcode = 2;
            $user = null;
        }
    }

    if ($user) {
        //user already supplied by aut plugin prelogin hook
    } else if (($frm->username == 'guest') and empty($CFG->guestloginbutton)) {
        $user = false;    /// Can't log in as guest if guest button is disabled
        $frm = false;
    } else {
        if (empty($errormsg)) {
            $user = authenticate_user_login($frm->username, $frm->password);
        }
    }

    // Intercept 'restored' users to provide them with info & reset password
    if (!$user and $frm and is_restored_user($frm->username)) {
        $PAGE->set_title(get_string('restoredaccount'));
        $PAGE->set_heading($site->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('restoredaccount'));
        echo $OUTPUT->box(get_string('restoredaccountinfo'), 'generalbox boxaligncenter');
        require_once('restored_password_form.php'); // Use our "supplanter" login_forgot_password_form. MDL-20846
        $form = new login_forgot_password_form('forgot_password.php', array('username' => $frm->username));
        $form->display();
        echo $OUTPUT->footer();
        die;
    }

    if ($user) {

        // language setup
        if (isguestuser($user)) {
            // no predefined language for guests - use existing session or default site lang
            unset($user->lang);

        } else if (!empty($user->lang)) {
            // unset previous session language - use user preference instead
            unset($SESSION->lang);
        }

        if (empty($user->confirmed)) {       // This account was never confirmed
            $PAGE->set_title(get_string("mustconfirm"));
            $PAGE->set_heading($site->fullname);
            echo $OUTPUT->header();
            echo $OUTPUT->heading(get_string("mustconfirm"));
            echo $OUTPUT->box(get_string("emailconfirmsent", "", $user->email), "generalbox boxaligncenter");
            echo $OUTPUT->footer();
            die;
        }

    /// Let's get them all set up.
        complete_user_login($user);

        // sets the username cookie
        if (!empty($CFG->nolastloggedin)) {
            // do not store last logged in user in cookie
            // auth plugins can temporarily override this from loginpage_hook()
            // do not save $CFG->nolastloggedin in database!

        } else if (empty($CFG->rememberusername) or ($CFG->rememberusername == 2 and empty($frm->rememberusername))) {
            // no permanent cookies, delete old one if exists
            set_moodle_cookie('');

        } else {
            set_moodle_cookie($USER->username);
        }

        $urltogo = core_login_get_return_url();

    /// check if user password has expired
    /// Currently supported only for ldap-authentication module
        $userauth = get_auth_plugin($USER->auth);
        if (!empty($userauth->config->expiration) and $userauth->config->expiration == 1) {
            if ($userauth->can_change_password()) {
                $passwordchangeurl = $userauth->change_password_url();
                if (!$passwordchangeurl) {
                    $passwordchangeurl = $CFG->httpswwwroot.'/login/change_password.php';
                }
            } else {
                $passwordchangeurl = $CFG->httpswwwroot.'/login/change_password.php';
            }
            $days2expire = $userauth->password_expire($USER->username);
            $PAGE->set_title("$site->fullname: $loginsite");
            $PAGE->set_heading("$site->fullname");
            if (intval($days2expire) > 0 && intval($days2expire) < intval($userauth->config->expiration_warning)) {
                echo $OUTPUT->header();
                echo $OUTPUT->confirm(get_string('auth_passwordwillexpire', 'auth', $days2expire), $passwordchangeurl, $urltogo);
                echo $OUTPUT->footer();
                exit;
            } elseif (intval($days2expire) < 0 ) {
                echo $OUTPUT->header();
                echo $OUTPUT->confirm(get_string('auth_passwordisexpired', 'auth'), $passwordchangeurl, $urltogo);
                echo $OUTPUT->footer();
                exit;
            }
        }

        // Discard any errors before the last redirect.
        unset($SESSION->loginerrormsg);

        // test the session actually works by redirecting to self
        $SESSION->wantsurl = $urltogo;
        redirect(new moodle_url(get_login_url(), array('testsession'=>$USER->id)));

    } else {
        if (empty($errormsg)) {
            $errormsg = get_string("invalidlogin");
            $errorcode = 3;
        }
    }
}

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

<section id="main" class="clearfix">
    <div id="page">
        <div id="page-content" class="<?php echo $container; ?>">
            <div id="region-main" class="row">

                <?php if ($images) { ?>
                <div id="login_images" class="container-fluid">
                    <div class="row">
                        <div class="col-md-2 col-md-offset-1">
                            <img class="instafluff" src="<?php echo $images[1]; ?>" /> 
                        </div>
                        <div class="col-md-2">
                            <img class="instafluff" src="<?php echo $images[2]; ?>" /> 
                        </div>
                        <div class="col-md-2">
                            <img class="instafluff" src="<?php echo $images[3]; ?>" /> 
                        </div>
                        <div class="col-md-2">
                            <img class="instafluff" src="<?php echo $images[4]; ?>" /> 
                        </div>
                        <div class="col-md-2">
                            <img class="instafluff" src="<?php echo $images[5]; ?>" /> 
                        </div>
                        <div class="col-md-1"></div>
                    </div>
                </div>
                <?php } ?>

                <div class="loginpanel col-md-4 col-md-offset-1 col-sd-6 col-sd-offset-0">
                    <?php
                    if(isset($_POST['username']) || isset($_POST['password'])){
                        echo get_string("invalidlogin");
                    }else{
                        echo '<h2><i class="fa fa-key"></i> '.get_string("login").'</h2>';
                    }
    
                    if (!empty($errormsg)) {
                        echo html_writer::start_tag('div', array('class' => 'loginerrors'));
                        echo html_writer::link('#', $errormsg, array('id' => 'loginerrormessage', 'class' => 'accesshide'));
                        echo $OUTPUT->error_text($errormsg);
                        echo html_writer::end_tag('div');
                    }
                    ?>
    
                    <form action="<?php echo $CFG->httpswwwroot; ?>/login/index.php" method="post" id="login" <?php echo $autocomplete; ?> >
                        <div class="inputarea">
                            <input type="text" name="username" placeholder="<?php echo get_string('username'); ?>" autocomplete="off"/>
                            <input type="password" name="password" id="password" placeholder="<?php echo get_string('password'); ?>"  value="" <?php echo $autocomplete; ?> />
                            <?php
                            if (!right_to_left()) { ?>
                                <button class="icon-submit fa fa-angle-right"></button>
                                <?php
                            } else { ?>
                                <button class="icon-submit fa fa-angle-left"></button>
                                <?php
                            } ?>
                        </div>
    
                        <a href="forgot_password.php" id="forgotten"><?php echo get_string('passwordforgotten'); ?></a>
                    </form>
    
                    <?php if ($CFG->guestloginbutton and !isguestuser()) {  ?>
                        <div class="subcontent guestsub">
                            <div class="desc">
                                <?php print_string("someallowguest") ?>
                            </div>
    
                            <form action="index.php" method="post" id="guestlogin">
                                <div class="guestform">
                                    <input type="hidden" name="username" value="guest" />
                                    <input type="hidden" name="password" value="guest" />
                                    <input type="submit" value="<?php print_string("loginguest") ?>" />
                                </div>
                            </form>
                        </div>
                    <?php }
                    /// Uncomment the line below if you are using the oAuth plugin
                    // require_once($CFG->dirroot . '/auth/googleoauth2/lib.php'); auth_googleoauth2_display_buttons();
                    ?>
                </div>
    
    
    
                <?php if ($show_instructions) { ?>
                    <div class="signuppanel col-md-6 col-md-offset-1 col-sd-6 col-sd-offset-0">
                        <h2><i class="fa fa-question-circle"></i> <?php print_string("firsttime") ?></h2>
                        <div class="subcontent">
                            <?php if (is_enabled_auth('none')) { // instructions override the rest for security reasons
                                print_string("loginstepsnone");
                            } else if ($CFG->registerauth == 'email') {
    
                                if (!empty($CFG->auth_instructions)) {
                                    echo format_text($CFG->auth_instructions);
                                } else {
                                    print_string("loginsteps", "", "signup.php");
                                } ?>
    
                                <div class="signupform">
                                    <form action="signup.php" method="get" id="signup">
                                        <div><input type="submit" value="<?php print_string("startsignup") ?>" /></div>
                                    </form>
                                </div>
                            <?php } else if (!empty($CFG->registerauth)) {
                                echo format_text($CFG->auth_instructions); ?>
                                <div class="signupform">
                                    <form action="signup.php" method="get" id="signup">
                                        <div><input type="submit" value="<?php print_string("startsignup") ?>" /></div>
                                    </form>
                                </div>
                            <?php } else {
                                echo format_text($CFG->auth_instructions);
                            } ?>
                        </div>
                    </div>
                <?php } ?>
    
                <?php echo "<div style='display: none;'>".$OUTPUT->main_content()."</div>"; ?>
                <?php echo $OUTPUT->standard_end_of_body_html() ?>
    
            </div>
        </div>
    </div>
</section>

<footer id="page-footer" class="hidden-sm hidden-xs">
	<?php require_once(dirname(__FILE__).'/includes/footer.php'); ?>
</footer>

</body>
</html>
