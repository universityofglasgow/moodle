<?php

    require_once("$CFG->dirroot/enrol/locallib.php");
    
    $hillheadnotificationtype = get_config('theme_hillhead', 'hillhead_notification_type');
$hillheadNotificationText =  get_config('theme_hillhead', 'hillhead_notification');

if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($hillheadNotificationText), $_SESSION['SESSION']->hillhead_notifications)) {
    switch($hillheadnotificationtype) {
        case 'alert-danger':
            $notiftext = '<div class="alert alert-danger"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($hillheadNotificationText).'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-warning"></i><span>'.$hillheadNotificationText.'</span></div>';
            break;
        case 'alert-warning':
            $notiftext = '<div class="alert alert-warning"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($hillheadNotificationText).'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-warning"></i><span>'.$hillheadNotificationText.'</span></div>';
            break;
        case 'alert-success':
            $notiftext = '<div class="alert alert-success"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($hillheadNotificationText).'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-info-circle"></i><span>'.$hillheadNotificationText.'</span></div>';
            break;
        case 'alert-info':
            $notiftext = '<div class="alert alert-info"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($hillheadNotificationText).'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-info-circle"></i><span>'.$hillheadNotificationText.'</span></div>';
            break;
        default:
            $notiftext = '';
    }
} else {
    $notiftext = '';
}

$hillheadoldbrowseralerts = get_config('theme_hillhead', 'hillhead_old_browser_alerts');

if($hillheadoldbrowseralerts == 'enabled') {
    $userAgentFlags = Array(
        'windows-xp'			=>'/(Windows NT 5.1)|(Windows XP)/',
		'firefox-1-51'          => '/Firefox\/([0-9]|[1-4][0-9]|5[0-1])\b/',
		'safari-1-7'			=> '/AppleWebKit\/([0-9][0-9]|[0-5][0-9][0-9]|600)\b/',
		'ie-5-10'               =>  '/MSIE ([5-9]|10)\b/'
	);
	
	$friendlyNames = Array(
    	'windows-xp'			=> 'Windows XP',
		'firefox-1-51'			=> 'an old version of Firefox',
		'safari-1-7'            => 'an old version of Safari',
		'ie-5-10'               => 'an old version of Internet Explorer'
    );
    
	$flags = Array();
		
	foreach ($userAgentFlags as $flag=>$regex) {
		if(preg_match($regex, $_SERVER['HTTP_USER_AGENT'])) {
			$flags[$flag] = $flag;
		}
	}
	
	foreach($flags as $flag) {
    	$oldBrowserText = '<strong>You\'re using '.$friendlyNames[$flag].'</strong>.&ensp;';
    	switch($flag) {
        	case 'windows-xp':
        	    $oldBrowserText .= 'Windows XP is obsolete and hasn\'t received security updates since 2014.';
        	    break;
            default:
                $oldBrowserText .= 'This is an old browser and isn\'t supported by Moodle. Some things might be broken or might not look right.';
                //$oldBrowserText .= 'Debug Information: '.$_SERVER['HTTP_USER_AGENT'].print_r($flags, true);
                break;
    	}
    	
    	$notiftext .= '<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i><span>'.$oldBrowserText.'</span></div>';
	}
	
	
}

$hillheadsmartalerts = get_config('theme_hillhead', 'hillhead_smart_alerts');

if((substr($PAGE->pagetype, 0, 11) == 'course-view') && ($hillheadsmartalerts == 'enabled')) {

    $courseDetails = $PAGE->course;
    
    $automaticEnrolmentsDisabled = false;
    
    $context = context_course::instance($courseDetails->id);
    
    
    
    $canEditCourse = has_capability('moodle/course:visibility', $context);
    
    if((!empty($courseDetails->id)) && $courseDetails->id != 1) {
        if($courseDetails->visible=='0') {
            if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'courseinvisible'), $_SESSION['SESSION']->hillhead_notifications) && $canEditCourse) {
                $notiftext .= '<div class="alert alert-info"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'courseinvisible').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-eye-slash"></i><span><strong>This course is currently hidden.</strong> You can see it, but students can\'t. You can unhide this course <a class="alert-link" href="edit.php?id='.$courseDetails->id.'">on the settings page</a>.</span></div>';
                $automaticEnrolmentsDisabled = true;
                $automaticEnrolmentsReason = 'this course has been hidden from students.';
            }
        }
            
        $canConfigureEnrolments = has_capability('enrol/gudatabase:config', $context);
        
        $studentyUsers = count_role_users(5, $PAGE->context);
                
        if($studentyUsers === 0) {
            if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'coursenostudents'), $_SESSION['SESSION']->hillhead_notifications) && $canConfigureEnrolments) {
                $notiftext .= '<div class="alert alert-warning"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'coursenostudents').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-users"></i><span><strong>There are no students on this course.</strong> <a class="alert-link" href="https://www.gla.ac.uk/myglasgow/moodle/universityofglasgowmoodleguides/enrollingstudentsonmoodlecourses/" target="_blank">How do I add students to my course?</a></span></div>';
            }
        }
        
        $canEditCourse = has_capability('moodle/course:update', $context);
        
        if($canEditCourse) {
        
            if(!empty($courseDetails->enddate) && ($courseDetails->enddate) < time()) {
                if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'courseenddate'), $_SESSION['SESSION']->hillhead_notifications) && $canEditCourse) {
                    $notiftext .= '<div class="alert alert-info"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'courseenddate').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-clock-o"></i><span><strong>This course\'s end date is in the past.</strong> MyCampus enrolments are frozen, and won\'t be updated. If you\'re still using this course, you can change the end date <a class="alert-link" href="edit.php?id='.$courseDetails->id.'">on the settings page</a>.</span></div>';
                    $automaticEnrolmentsDisabled = true;
                    $automaticEnrolmentsReason = 'the course\'s end date is in the past. Any old MyCampus enrolments have been frozen and won\'t be removed.';
                }
            }
            
            if(!empty($courseDetails->startdate) && ($courseDetails->startdate) > time()) {
                if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'courseenddate'), $_SESSION['SESSION']->hillhead_notifications) && $canEditCourse) {
                    $notiftext .= '<div class="alert alert-info"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'courseenddate').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-clock-o"></i><span><strong>This course\'s start date is in the future.</strong> If you\'re using this course right now, you can change the start date <a class="alert-link" href="edit.php?id='.$courseDetails->id.'">on the settings page</a>.</span></div>';
                    $automaticEnrolmentsDisabled = true;
                    $automaticEnrolmentsReason = 'the course\'s start date is in the future.';
                }
            }
            
            if(empty($courseDetails->enddate)) {
                if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'courseenddate'), $_SESSION['SESSION']->hillhead_notifications) && $canEditCourse) {
                    $notiftext .= '<div class="alert alert-danger"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'courseenddate').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-clock-o"></i><span><strong>This course doesn\'t have an end date.</strong> Automatic enrolments won\'t work unless you add one <a class="alert-link" href="edit.php?id='.$courseDetails->id.'">on the settings page</a>. This is to protect old courses from accidental changes.</span></div>';
                    $automaticEnrolmentsDisabled = true;
                    $automaticEnrolmentsReason = 'the course doesn\'t have an end date.';
                }
            }
            
            $enman = new \course_enrolment_manager($PAGE, $courseDetails);
            $enrolmentInstances = $enman->get_enrolment_instances();
        
            $usesMyCampus = false;
            $usesSelfEnrolment = false;
            foreach($enrolmentInstances as $enrolmentInstance) {
                switch($enrolmentInstance->enrol) {
                    case 'gudatabase':
                        if ($enrolmentInstance->status==0) {
                            $usesMyCampus = true;
                        }
                        break;
                    case 'self':
                        if ($enrolmentInstance->status==0) {
                            if(empty($enrolmentInstance->password)) {
                                $usesSelfEnrolment = true;
                            }
                        }
                        break;
                }
            }
            
            if ($usesMyCampus == false) {
                $automaticEnrolmentsDisabled = true;
                $automaticEnrolmentsReason = 'the plugin has been disabled.';
            }
    
            
            if ($automaticEnrolmentsDisabled) {
                if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'nomycampus'), $_SESSION['SESSION']->hillhead_notifications)) {
                    $notiftext .= '<div class="alert alert-danger"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'nomycampus').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-refresh"></i><span><strong>MyCampus enrolments are disabled for this course.</strong> This is because '.$automaticEnrolmentsReason.' <a class="alert-link" href="'.$CFG->wwwroot.'/report/guenrol/index.php?id='.$courseDetails->id.'">More Information</a></span></div>';
                }
            }
            
            if ($usesSelfEnrolment) {
                if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'selfenabled'), $_SESSION['SESSION']->hillhead_notifications)) {
                    $notiftext .= '<div class="alert alert-warning"><a class="close" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'selfenabled').'" aria-label="Close"><span aria-hidden="true">&times;</span></a><i class="fa fa-user-plus"></i><span><strong>Self-enrolment is enabled for this course, with no enrolment key.</strong> This means anybody can add themselves to this course. <a class="alert-link" href="'.$CFG->wwwroot.'/enrol/instances.php?id='.$courseDetails->id.'">Manage Enrolments</a></span></div>';
                }
            }
        }
    }
    
}
    
?>