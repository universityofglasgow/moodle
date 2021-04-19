<?php

    require_once("$CFG->dirroot/enrol/locallib.php");
    
    $notiftext = '';
    
    $hillheaddowntimedatetime = get_config('theme_hillhead', 'hillhead_downtime_datetime');
    $hillheaddowntimelength = get_config('theme_hillhead', 'hillhead_downtime_length');
    
    if(!empty($hillheaddowntimedatetime)) {
        $countdown = strtotime($hillheaddowntimedatetime);
        $finishtime = $countdown + ($hillheaddowntimelength * 60);
        if($countdown !== false) {
            $timetildowntime = $countdown - time();
            if($timetildowntime > 604800) {
                // Downtime is over a week away. Do nothing.
            } else if ($timetildowntime > 172800) {
                // Downtime is between 2 and 7 days away.
                $notiftext .= '<div class="alert alert-info d-flex align-items-center"><i class="fa fa-info-circle d-flex-item"></i><span class="d-flex-item"><strong>Moodle is being upgraded</strong> on '.date('l jS F', $countdown).' between '.date('H:i', $countdown).' and '.date('H:i', $finishtime).'. The site may be unavailable or slower than usual during the upgrade.</span></div>';
            } else if ($timetildowntime > 10800) {
                // Downtime is between 3 hours and 2 days away.
                $notiftext .= '<div class="alert alert-warning d-flex align-items-center"><i class="fa fa-exclamation-circle d-flex-item"></i><span class="d-flex-item"><strong>Moodle is being upgraded</strong> on '.date('l jS F', $countdown).' between '.date('H:i', $countdown).' and '.date('H:i', $finishtime).'. The site may be unavailable or slower than usual during the upgrade.</span></div>';
            } else if ($timetildowntime > 900) {
                // Downtime is between 30 minutes and 3 hours away
                $notiftext .= '<div class="alert alert-danger d-flex align-items-center"><i class="fa fa-exclamation-triangle d-flex-item"></i><span class="d-flex-item"><strong>Moodle is being upgraded</strong> on '.date('l jS F', $countdown).' between '.date('H:i', $countdown).' and '.date('H:i', $finishtime).'. The site may be unavailable or slower than usual during the upgrade.</span></div>';
            } else if ($timetildowntime > -7200) {
                // Downtime is less than 30 minutes away
                $notiftext .= '<div class="alert alert-danger alert-pulse d-flex align-items-center"><i class="fa fa-exclamation-triangle d-flex-item"></i><span class="d-flex-item"><strong>Moodle is being upgraded</strong> on '.date('l jS F', $countdown).' between '.date('H:i', $countdown).' and '.date('H:i', $finishtime).'. The site may be unavailable or slower than usual during the upgrade. <strong>Please save your work!</strong></span></div>';
            }
        }
    }
    

$hillheadoldbrowseralerts = get_config('theme_hillhead', 'hillhead_old_browser_alerts');

if($hillheadoldbrowseralerts == 'enabled') {
    $userAgentFlags = Array(
        'windows-xp'			=>'/(Windows NT 5.1)|(Windows XP)/',
		'firefox-1-51'          => '/Firefox\/([0-9]|[1-4][0-9]|5[0-1])\b/',
		'safari-1-7'			=> '/(?=.*?AppleWebKit\/([0-9][0-9]|[0-5][0-9][0-9]|600)\b)(?!.*?Chrome\/).*/',
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
    	
    	$notiftext .= '<div class="alert alert-danger d-flex align-items-center"><i class="fa fa-exclamation-triangle d-flex-item"></i><span class="d-flex-item">'.$oldBrowserText.'</span></div>';
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
                $notiftext .= '<div class="alert alert-info d-flex align-items-center"></a><i class="fa fa-eye-slash d-flex-item"></i><span class="d-flex-item"><strong>This course is currently hidden.</strong> You can see it, but students can\'t. You can unhide this course <a class="alert-link" href="edit.php?id='.$courseDetails->id.'">on the settings page</a>.</span><a class="close d-flex-item ml-auto" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'courseinvisible').'" aria-label="Close"><span aria-hidden="true">&times;</span></a></div>';
                $automaticEnrolmentsDisabled = true;
                $automaticEnrolmentsReason = 'this course has been hidden from students.';
            }
        }
            
        $canConfigureEnrolments = has_capability('enrol/gudatabase:config', $context);
        
        $studentyUsers = count_role_users(5, $PAGE->context);
                
        if($studentyUsers === 0) {
            if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'coursenostudents'), $_SESSION['SESSION']->hillhead_notifications) && $canConfigureEnrolments) {
                $notiftext .= '<div class="alert alert-warning d-flex align-items-center"></a><i class="fa fa-users d-flex-item"></i><span class="d-flex-item"><strong>There are no students on this course.</strong> <a class="alert-link" href="'.$CFG->wwwroot.'/enrol/instances.php?id='.$courseDetails->id.'">Manage Enrolments</a></span><a class="close d-flex-item ml-auto" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'coursenostudents').'" aria-label="Close"><span aria-hidden="true">&times;</span></a></div>';
            }
        }
        
        $canEditCourse = has_capability('moodle/course:update', $context);
        
        if($canEditCourse) {
        
            if(!empty($courseDetails->enddate) && ($courseDetails->enddate) < time()) {
                if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'courseenddate'), $_SESSION['SESSION']->hillhead_notifications) && $canEditCourse) {
                    $notiftext .= '<div class="alert alert-info d-flex align-items-center"></a><i class="fa fa-clock-o d-flex-item"></i><span class="d-flex-item"><strong>This course\'s end date is in the past.</strong> MyCampus enrolments are frozen, and won\'t be updated. If you\'re still using this course, you can change the end date <a class="alert-link" href="edit.php?id='.$courseDetails->id.'">on the settings page</a>.</span><a class="close d-flex-item ml-auto" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'courseenddate').'" aria-label="Close"><span aria-hidden="true">&times;</span></a></div>';
                    $automaticEnrolmentsDisabled = true;
                    $automaticEnrolmentsReason = 'the course\'s end date is in the past. Any old MyCampus enrolments have been frozen and won\'t be removed.';
                }
            }
            
            if(!empty($courseDetails->startdate) && ($courseDetails->startdate) > time()) {
                if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'coursestartdate'), $_SESSION['SESSION']->hillhead_notifications) && $canEditCourse) {
                     $notiftext .= '<div class="alert alert-info d-flex align-items-center"></a><i class="fa fa-clock-o d-flex-item"></i><span class="d-flex-item"><strong>This course\'s start date is in the future.</strong> MyCampus enrolments are frozen, and won\'t be updated. If you\'re already using this course right now, you can change the start date <a class="alert-link" href="edit.php?id='.$courseDetails->id.'">on the settings page</a>.</span><a class="close d-flex-item ml-auto" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'coursestartdate').'" aria-label="Close"><span aria-hidden="true">&times;</span></a></div>';
                    $automaticEnrolmentsDisabled = true;
                    $automaticEnrolmentsReason = 'the course\'s start date is in the future.';
                }
            }
            
            if(empty($courseDetails->enddate)) {
                if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'courseenddateblank'), $_SESSION['SESSION']->hillhead_notifications) && $canEditCourse) {
                     $notiftext .= '<div class="alert alert-info d-flex align-items-center"></a><i class="fa fa-clock-o d-flex-item"></i><span class="d-flex-item"><strong>This course doesn\'t have an end date.</strong> Automatic enrolments won\'t work unless you add one <a class="alert-link" href="edit.php?id='.$courseDetails->id.'">on the settings page</a>. This is to protect old courses from accidental changes.</span></span><a class="close d-flex-item ml-auto" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'courseenddateblank').'" aria-label="Close"><span aria-hidden="true">&times;</span></a></div>';
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
                $automaticEnrolmentsReason = 'MyCampus enrolments have not been set up for this course.';
            }
    
            
            if ($automaticEnrolmentsDisabled) {
                if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'nomycampus'), $_SESSION['SESSION']->hillhead_notifications)) {
                    $notiftext .= '<div class="alert alert-danger d-flex align-items-center"></a><i class="fa fa-refresh d-flex-item"></i><span class="d-flex-item"><strong>MyCampus enrolments are disabled for this course.</strong> This is because '.$automaticEnrolmentsReason.' <a class="alert-link" href="'.$CFG->wwwroot.'/enrol/instances.php?id='.$courseDetails->id.'">Manage Enrolments</a></span><a class="close d-flex-item ml-auto" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'nomycampus').'" aria-label="Close"><span aria-hidden="true">&times;</span></a></div>';
                    
                }
            }
            
            if ($usesSelfEnrolment) {
                if(empty($_SESSION['SESSION']->hillhead_notifications) || !array_key_exists(md5($courseDetails->id.'selfenabled'), $_SESSION['SESSION']->hillhead_notifications)) {
                    $notiftext .= '<div class="alert alert-warning d-flex align-items-center"></a><i class="fa fa-user-plus d-flex-item"></i><span class="d-flex-item"><strong>Self-enrolment is enabled for this course, with no enrolment key.</strong> This means anybody can add themselves to this course. <a class="alert-link" href="'.$CFG->wwwroot.'/enrol/instances.php?id='.$courseDetails->id.'">Manage Enrolments</a></span><a class="close d-flex-item ml-auto" href="'.$CFG->wwwroot.'/theme/hillhead/notification.php?h='.md5($courseDetails->id.'selfenabled').'" aria-label="Close"><span aria-hidden="true">&times;</span></a></div>';
                }
            }
        }
    }
    
}
    
?>