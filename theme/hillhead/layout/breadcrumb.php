<?php
    
    global $DB;
    
    // Use logic to determine what we're showing in the breadcrumb
    
    $showCourse = (isset($COURSE) && $COURSE->id != 1);
    
    $showCategories = ($showCourse || ($PAGE->pagetype == 'course-index-category'));
    
    // Now output what we've got

    $breadcrumbLinks = '<ul class="tabBar">';
    
    $breadcrumbLinks .= '<li><a href="'.$CFG->wwwroot.'/my"><i class="fa fa-tachometer"></i> Dashboard</a></li>';
    
    if($showCategories) {
        $categories = Array();
        
        if(isset($COURSE->category) && $COURSE->category != 0) {
            $thisCategory = $COURSE->category;
        } else {
            if(isset($_GET['categoryid'])) {
                $thisCategory = $_GET['categoryid'];
            } else {
                $thisCategory = 0;
            }
        }
        
        $num = 0;
        
        while ($thisCategory != 0) {
            $categoryDetails = $DB->get_record('course_categories', array('id' => $thisCategory), 'id, name, parent');
            
            $categories[] = Array(
                'id' => $categoryDetails->id,
                'name' => $categoryDetails->name
            );
            
            $thisCategory = $categoryDetails->parent;
            
        }
        
        if(count($categories) != 0) {
        
            $closestCategory = $categories[0];
            
            $categories = array_reverse($categories);
            
            $breadcrumbLinks .= '<li><a class="dropdown-toggle" href="#" id="courseCategoryBreadcrumb" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-list"></i> '.$closestCategory['name'].'</a><div class="dropdown-menu" aria-labelledby="courseCategoryBreadcrumb">';
            
            foreach($categories as $thisCategory) {
                $breadcrumbLinks .= '<a class="dropdown-item" href="'.$CFG->wwwroot.'/course/index.php?categoryid='.$thisCategory['id'].'"><i class="fa fa-list"></i> '.$thisCategory['name'].'</a>';
            }
            
            $breadcrumbLinks .= '</div></li>';
        }
    }
    
    if($showCourse) {
        $breadcrumbLinks .= '<li><a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'"><i class="fa fa-graduation-cap"></i> '.$COURSE->fullname.'</a></li>';

        if($PAGE->cm) {
            $sectionDetails = $DB->get_record('course_sections', ['id' => $PAGE->cm->section], '*', IGNORE_MISSING);
            $breadcrumbLinks .= '<li><a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'&section='.$sectionDetails->section.'"><i class="fa fa-list"></i> '.$sectionDetails->name.'</a></li>';
            $breadcrumbLinks .= '<li><a href="'.$PAGE->cm->url.'"><i class="fa fa-bookmark"></i> '.$PAGE->cm->name.'</a></li>';
        } else {
            if(isset($_GET['section'])) {
                $sectionDetails = $DB->get_record('course_sections', ['course' => $COURSE->id, 'section' => $_GET['section']], '*', IGNORE_MISSING);
                $breadcrumbLinks .= '<li><a href="'.$CFG->wwwroot.'/course/view.php?id='.$COURSE->id.'&section='.$_GET['section'].'"><i class="fa fa-list"></i> '.$sectionDetails->name.'</a></li>';
            } else {
                // We're in a course, but not within a module
                $isOtherPage = true;
            }
        }
    }
    
    $breadcrumbMapping = $PAGE->pagetype;
    
    if(substr($breadcrumbMapping, 0, 14) == 'admin-setting-') {
        $breadcrumbMapping = 'admin-index';
    }
    
    $pageIcon = 'fa-bookmark';
    $pageTitle = $PAGE->title.' ('.$PAGE->pagetype.' - '.$PAGE->subpage.')';
    $pageURL = $PAGE->url;
    $pageShow = true;
    switch($breadcrumbMapping) {
        case 'my-index':
        case 'course-view-topics':
        $pageShow = false;
            break;
        case 'admin-index':
        case 'admin-search':
            $pageIcon = 'fa-gear';
            $pageTitle = 'Site Administration';
            break;
        case 'admin-tool-lp-coursecompetencies':
            $pageIcon = 'fa-check-square-o';
            $pageTitle = 'Competencies';
            break;
        case 'badges-view':
            $pageIcon = 'fa-shield';
            $pageTitle = 'Badges';
            break;
        case 'calendar-view':
            $pageIcon = 'fa-calendar';
            $pageTitle = 'Calendar';
            break;
        case 'course-edit':
            $pageIcon = 'fa-pencil';
            $pageTitle = 'Edit Course';
            break;
        case 'course-management':
            $pageIcon = 'fa-pencil';
            $pageTitle = 'Course Management';
            break;
        case 'course-index-category':
            $pageIcon = 'fa-graduation-cap';
            $pageTitle = 'Browse Courses';
            break;
        case 'grade-report-grader-index':
            $pageIcon = 'fa-table';
            $pageTitle = 'Grades';
            break;
        case 'message-index':
            $pageIcon = 'fa-comment';
            $pageTitle = 'Messages';
            break;
        case 'message-output-popup-notifications':
            $pageIcon = 'fa-bell';
            $pageTitle = 'Notifications';
            break;
        case 'user-files':
            $pageIcon = 'fa-file-o';
            $pageTitle = 'Private Files';
            break;
        case 'report-enhance-index':
            $pageIcon = 'fa-magic';
            $pageTitle = 'VLE Enhancements';
            break;
        case '':
            $pageIcon = 'fa-magic';
            $pageTitle = 'VLE Enhancements';
            break;
        default:
            $pageShow = false;
    }
    
    if($pageShow) {
        $breadcrumbLinks .= '<li><a href="'.$pageURL.'"><i class="fa '.$pageIcon.'"></i> '.$pageTitle.'</a></eli>';
    }
    
    $hillheadHelpLink = get_config('theme_hillhead', 'hillhead_helpcentre');
    if(!empty($hillheadHelpLink)) {
        $breadcrumbLinks .= '<li class="help"><a href="'.$hillheadHelpLink.'" title="Help with Moodle"><i class="fa fa-question-circle"></i> Help</a></li>';
    }
    
    $breadcrumbLinks .= '</ul>';
    
?>