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
 * External forum API
 *
 * @package    mod_forum
 * @copyright  2012 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");

class mod_forum_external extends external_api {

    /**
     * Describes the parameters for get_forum.
     *
     * @return external_external_function_parameters
     * @since Moodle 2.5
     */
    public static function get_forums_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(new external_value(PARAM_INT, 'course ID',
                        '', VALUE_REQUIRED, '', NULL_NOT_ALLOWED), 'Array of Course IDs', VALUE_DEFAULT, array()),
            )
        );
    }

    /**
     * Returns a list of forums in a provided list of courses,
     * if no list is provided all forums that the user can view
     * will be returned.
     *
     * @param array $courseids the course ids
     * @return array the forum details
     * @since Moodle 2.5
     */
    public static function get_forums_by_courses($courseids = array()) {
        global $CFG, $DB, $USER;

        require_once($CFG->dirroot . "/mod/forum/lib.php");

        $params = self::validate_parameters(self::get_forums_by_courses_parameters(), array('courseids' => $courseids));

        if (empty($params['courseids'])) {
            // Get all the courses the user can view.
            $courseids = array_keys(enrol_get_my_courses());
        } else {
            $courseids = $params['courseids'];
        }

        // Array to store the forums to return.
        $arrforums = array();

        // Ensure there are courseids to loop through.
        if (!empty($courseids)) {
            // Go through the courseids and return the forums.
            foreach ($courseids as $cid) {
                // Get the course context.
                $context = context_course::instance($cid);
                // Check the user can function in this context.
                self::validate_context($context);
                // Get the forums in this course.
                if ($forums = $DB->get_records('forum', array('course' => $cid))) {
                    // Get the modinfo for the course.
                    $modinfo = get_fast_modinfo($cid);
                    // Get the forum instances.
                    $foruminstances = $modinfo->get_instances_of('forum');
                    // Loop through the forums returned by modinfo.
                    foreach ($foruminstances as $forumid => $cm) {
                        // If it is not visible or present in the forums get_records call, continue.
                        if (!$cm->uservisible || !isset($forums[$forumid])) {
                            continue;
                        }
                        // Set the forum object.
                        $forum = $forums[$forumid];
                        // Get the module context.
                        $context = context_module::instance($cm->id);
                        // Check they have the view forum capability.
                        require_capability('mod/forum:viewdiscussion', $context);
                        // Format the intro before being returning using the format setting.
                        list($forum->intro, $forum->introformat) = external_format_text($forum->intro, $forum->introformat,
                            $context->id, 'mod_forum', 'intro', 0);
                        // Add the course module id to the object, this information is useful.
                        $forum->cmid = $cm->id;
                        // Add the forum to the array to return.
                        $arrforums[$forum->id] = (array) $forum;
                    }
                }
            }
        }

        return $arrforums;
    }

    /**
     * Describes the get_forum return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_forums_by_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Forum id'),
                    'course' => new external_value(PARAM_TEXT, 'Course id'),
                    'type' => new external_value(PARAM_TEXT, 'The forum type'),
                    'name' => new external_value(PARAM_TEXT, 'Forum name'),
                    'intro' => new external_value(PARAM_RAW, 'The forum intro'),
                    'introformat' => new external_format_value('intro'),
                    'assessed' => new external_value(PARAM_INT, 'Aggregate type'),
                    'assesstimestart' => new external_value(PARAM_INT, 'Assess start time'),
                    'assesstimefinish' => new external_value(PARAM_INT, 'Assess finish time'),
                    'scale' => new external_value(PARAM_INT, 'Scale'),
                    'maxbytes' => new external_value(PARAM_INT, 'Maximum attachment size'),
                    'maxattachments' => new external_value(PARAM_INT, 'Maximum number of attachments'),
                    'forcesubscribe' => new external_value(PARAM_INT, 'Force users to subscribe'),
                    'trackingtype' => new external_value(PARAM_INT, 'Subscription mode'),
                    'rsstype' => new external_value(PARAM_INT, 'RSS feed for this activity'),
                    'rssarticles' => new external_value(PARAM_INT, 'Number of RSS recent articles'),
                    'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                    'warnafter' => new external_value(PARAM_INT, 'Post threshold for warning'),
                    'blockafter' => new external_value(PARAM_INT, 'Post threshold for blocking'),
                    'blockperiod' => new external_value(PARAM_INT, 'Time period for blocking'),
                    'completiondiscussions' => new external_value(PARAM_INT, 'Student must create discussions'),
                    'completionreplies' => new external_value(PARAM_INT, 'Student must post replies'),
                    'completionposts' => new external_value(PARAM_INT, 'Student must post discussions or replies'),
                    'cmid' => new external_value(PARAM_INT, 'Course module id')
                ), 'forum'
            )
        );
    }

    /**
     * Describes the parameters for get_forum_discussions.
     *
     * @return external_external_function_parameters
     * @since Moodle 2.5
     */
    public static function get_forum_discussions_parameters() {
        return new external_function_parameters (
            array(
                'forumids' => new external_multiple_structure(new external_value(PARAM_INT, 'forum ID',
                        '', VALUE_REQUIRED, '', NULL_NOT_ALLOWED), 'Array of Forum IDs', VALUE_REQUIRED),
                'limitfrom' => new external_value(PARAM_INT, 'limit from', VALUE_OPTIONAL, 0),
                'limitnum' => new external_value(PARAM_INT, 'limit number', VALUE_OPTIONAL, 0)
            )
        );
    }

    /**
     * Returns a list of forum discussions as well as a summary of the discussion
     * in a provided list of forums.
     *
     * @param array $forumids the forum ids
     * @param int $limitfrom limit from SQL data
     * @param int $limitnum limit number SQL data
     *
     * @return array the forum discussion details
     * @since Moodle 2.5
     */
    public static function get_forum_discussions($forumids, $limitfrom = 0, $limitnum = 0) {
        global $CFG, $DB, $USER;

        require_once($CFG->dirroot . "/mod/forum/lib.php");

        // Validate the parameter.
        $params = self::validate_parameters(self::get_forum_discussions_parameters(),
            array(
                'forumids'  => $forumids,
                'limitfrom' => $limitfrom,
                'limitnum'  => $limitnum,
            ));
        $forumids  = $params['forumids'];
        $limitfrom = $params['limitfrom'];
        $limitnum  = $params['limitnum'];

        // Array to store the forum discussions to return.
        $arrdiscussions = array();
        // Keep track of the course ids we have performed a require_course_login check on to avoid repeating.
        $arrcourseschecked = array();
        // Store the modinfo for the forums in an individual courses.
        $arrcoursesforuminfo = array();
        // Keep track of the users we have looked up in the DB.
        $arrusers = array();

        // Loop through them.
        foreach ($forumids as $id) {
            // Get the forum object.
            $forum = $DB->get_record('forum', array('id' => $id), '*', MUST_EXIST);
            // Check that that user can view this course if check not performed yet.
            if (!in_array($forum->course, $arrcourseschecked)) {
                // Check the user can function in this context.
                self::validate_context(context_course::instance($forum->course));
                // Add to the array.
                $arrcourseschecked[] = $forum->course;
            }
            // Get the modinfo for the course if we haven't already.
            if (!isset($arrcoursesforuminfo[$forum->course])) {
                $modinfo = get_fast_modinfo($forum->course);
                $arrcoursesforuminfo[$forum->course] = $modinfo->get_instances_of('forum');
            }
            // Check if this forum does not exist in the modinfo array, should always be false unless DB is borked.
            if (empty($arrcoursesforuminfo[$forum->course][$forum->id])) {
                throw new moodle_exception('invalidmodule', 'error');
            }
            // We now have the course module.
            $cm = $arrcoursesforuminfo[$forum->course][$forum->id];
            // If the forum is not visible throw an exception.
            if (!$cm->uservisible) {
                throw new moodle_exception('nopermissiontoshow', 'error');
            }
            // Get the module context.
            $modcontext = context_module::instance($cm->id);
            // Check they have the view forum capability.
            require_capability('mod/forum:viewdiscussion', $modcontext);
            // Check if they can view full names.
            $canviewfullname = has_capability('moodle/site:viewfullnames', $modcontext);
            // Get the unreads array, this takes a forum id and returns data for all discussions.
            $unreads = array();
            if ($cantrack = forum_tp_can_track_forums($forum)) {
                if ($forumtracked = forum_tp_is_tracked($forum)) {
                    $unreads = forum_get_discussions_unread($cm);
                }
            }
            // The forum function returns the replies for all the discussions in a given forum.
            $replies = forum_count_discussion_replies($id);
            // Get the discussions for this forum.
            $order = 'timemodified DESC';
            if ($discussions = $DB->get_records('forum_discussions', array('forum' => $id), $order, '*', $limitfrom, $limitnum)) {
                foreach ($discussions as $discussion) {
                    // If the forum is of type qanda and the user has not posted in the discussion
                    // we need to ensure that they have the required capability.
                    if ($forum->type == 'qanda' && !forum_user_has_posted($discussion->forum, $discussion->id, $USER->id)) {
                        require_capability('mod/forum:viewqandawithoutposting', $modcontext);
                    }
                    $usernamefields = user_picture::fields();
                    // If we don't have the users details then perform DB call.
                    if (empty($arrusers[$discussion->userid])) {
                        $arrusers[$discussion->userid] = $DB->get_record('user', array('id' => $discussion->userid),
                                $usernamefields, MUST_EXIST);
                    }
                    // Get the subject.
                    $subject = $DB->get_field('forum_posts', 'subject', array('id' => $discussion->firstpost), MUST_EXIST);
                    // Create object to return.
                    $return = new stdClass();
                    $return->id = (int) $discussion->id;
                    $return->course = $discussion->course;
                    $return->forum = $discussion->forum;
                    $return->name = $discussion->name;
                    $return->userid = $discussion->userid;
                    $return->groupid = $discussion->groupid;
                    $return->assessed = $discussion->assessed;
                    $return->timemodified = (int) $discussion->timemodified;
                    $return->usermodified = $discussion->usermodified;
                    $return->timestart = $discussion->timestart;
                    $return->timeend = $discussion->timeend;
                    $return->firstpost = (int) $discussion->firstpost;
                    $return->firstuserfullname = fullname($arrusers[$discussion->userid], $canviewfullname);
                    $return->firstuserimagealt = $arrusers[$discussion->userid]->imagealt;
                    $return->firstuserpicture = $arrusers[$discussion->userid]->picture;
                    $return->firstuseremail = $arrusers[$discussion->userid]->email;
                    $return->subject = $subject;
                    $return->numunread = '';
                    if ($cantrack && $forumtracked) {
                        if (isset($unreads[$discussion->id])) {
                            $return->numunread = (int) $unreads[$discussion->id];
                        }
                    }
                    // Check if there are any replies to this discussion.
                    if (!empty($replies[$discussion->id])) {
                         $return->numreplies = (int) $replies[$discussion->id]->replies;
                         $return->lastpost = (int) $replies[$discussion->id]->lastpostid;
                     } else { // No replies, so the last post will be the first post.
                        $return->numreplies = 0;
                        $return->lastpost = (int) $discussion->firstpost;
                     }
                    // Get the last post as well as the user who made it.
                    $lastpost = $DB->get_record('forum_posts', array('id' => $return->lastpost), '*', MUST_EXIST);
                    if (empty($arrusers[$lastpost->userid])) {
                        $arrusers[$lastpost->userid] = $DB->get_record('user', array('id' => $lastpost->userid),
                                $usernamefields, MUST_EXIST);
                    }
                    $return->lastuserid = $lastpost->userid;
                    $return->lastuserfullname = fullname($arrusers[$lastpost->userid], $canviewfullname);
                    $return->lastuserimagealt = $arrusers[$lastpost->userid]->imagealt;
                    $return->lastuserpicture = $arrusers[$lastpost->userid]->picture;
                    $return->lastuseremail = $arrusers[$lastpost->userid]->email;
                    // Add the discussion statistics to the array to return.
                    $arrdiscussions[$return->id] = (array) $return;
                }
            }
        }

        return $arrdiscussions;
    }

    /**
     * Describes the get_forum_discussions return value.
     *
     * @return external_single_structure
     * @since Moodle 2.5
     */
     public static function get_forum_discussions_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Forum id'),
                    'course' => new external_value(PARAM_INT, 'Course id'),
                    'forum' => new external_value(PARAM_INT, 'The forum id'),
                    'name' => new external_value(PARAM_TEXT, 'Discussion name'),
                    'userid' => new external_value(PARAM_INT, 'User id'),
                    'groupid' => new external_value(PARAM_INT, 'Group id'),
                    'assessed' => new external_value(PARAM_INT, 'Is this assessed?'),
                    'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                    'usermodified' => new external_value(PARAM_INT, 'The id of the user who last modified'),
                    'timestart' => new external_value(PARAM_INT, 'Time discussion can start'),
                    'timeend' => new external_value(PARAM_INT, 'Time discussion ends'),
                    'firstpost' => new external_value(PARAM_INT, 'The first post in the discussion'),
                    'firstuserfullname' => new external_value(PARAM_TEXT, 'The discussion creators fullname'),
                    'firstuserimagealt' => new external_value(PARAM_TEXT, 'The discussion creators image alt'),
                    'firstuserpicture' => new external_value(PARAM_INT, 'The discussion creators profile picture'),
                    'firstuseremail' => new external_value(PARAM_TEXT, 'The discussion creators email'),
                    'subject' => new external_value(PARAM_TEXT, 'The discussion subject'),
                    'numreplies' => new external_value(PARAM_TEXT, 'The number of replies in the discussion'),
                    'numunread' => new external_value(PARAM_TEXT, 'The number of unread posts, blank if this value is
                        not available due to forum settings.'),
                    'lastpost' => new external_value(PARAM_INT, 'The id of the last post in the discussion'),
                    'lastuserid' => new external_value(PARAM_INT, 'The id of the user who made the last post'),
                    'lastuserfullname' => new external_value(PARAM_TEXT, 'The last person to posts fullname'),
                    'lastuserimagealt' => new external_value(PARAM_TEXT, 'The last person to posts image alt'),
                    'lastuserpicture' => new external_value(PARAM_INT, 'The last person to posts profile picture'),
                    'lastuseremail' => new external_value(PARAM_TEXT, 'The last person to posts email'),
                ), 'discussion'
            )
        );
    }

    /**
     * Describes the parameters for get_forum_discussion_posts.
     *
     * @return external_external_function_parameters
     * @since Moodle 2.7
     */
    public static function get_forum_discussion_posts_parameters() {
        return new external_function_parameters (
            array(
                'discussionid' => new external_value(PARAM_INT, 'discussion ID', VALUE_REQUIRED),
                'sortby' => new external_value(PARAM_ALPHA,
                    'sort by this element: id, created or modified', VALUE_DEFAULT, 'created'),
                'sortdirection' => new external_value(PARAM_ALPHA, 'sort direction: ASC or DESC', VALUE_DEFAULT, 'DESC')
            )
        );
    }

    /**
     * Returns a list of forum posts for a discussion
     *
     * @param int $discussionid the post ids
     * @param string $sortby sort by this element (id, created or modified)
     * @param string $sortdirection sort direction: ASC or DESC
     *
     * @return array the forum post details
     * @since Moodle 2.7
     */
    public static function get_forum_discussion_posts($discussionid, $sortby = "created", $sortdirection = "DESC") {
        global $CFG, $DB, $USER;

        $warnings = array();

        // Validate the parameter.
        $params = self::validate_parameters(self::get_forum_discussion_posts_parameters(),
            array(
                'discussionid' => $discussionid,
                'sortby' => $sortby,
                'sortdirection' => $sortdirection));

        // Compact/extract functions are not recommended.
        $discussionid   = $params['discussionid'];
        $sortby         = $params['sortby'];
        $sortdirection  = $params['sortdirection'];

        $sortallowedvalues = array('id', 'created', 'modified');
        if (!in_array($sortby, $sortallowedvalues)) {
            throw new invalid_parameter_exception('Invalid value for sortby parameter (value: ' . $sortby . '),' .
                'allowed values are: ' . implode(',', $sortallowedvalues));
        }

        $sortdirection = strtoupper($sortdirection);
        $directionallowedvalues = array('ASC', 'DESC');
        if (!in_array($sortdirection, $directionallowedvalues)) {
            throw new invalid_parameter_exception('Invalid value for sortdirection parameter (value: ' . $sortdirection . '),' .
                'allowed values are: ' . implode(',', $directionallowedvalues));
        }

        $discussion = $DB->get_record('forum_discussions', array('id' => $discussionid), '*', MUST_EXIST);
        $forum = $DB->get_record('forum', array('id' => $discussion->forum), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $forum->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('forum', $forum->id, $course->id, false, MUST_EXIST);

        // Validate the module context. It checks everything that affects the module visibility (including groupings, etc..).
        $modcontext = context_module::instance($cm->id);
        self::validate_context($modcontext);

        // This require must be here, see mod/forum/discuss.php.
        require_once($CFG->dirroot . "/mod/forum/lib.php");

        // Check they have the view forum capability.
        require_capability('mod/forum:viewdiscussion', $modcontext, null, true, 'noviewdiscussionspermission', 'forum');

        if (! $post = forum_get_post_full($discussion->firstpost)) {
            throw new moodle_exception('notexists', 'forum');
        }

        // This function check groups, qanda, timed discussions, etc.
        if (!forum_user_can_see_post($forum, $discussion, $post, null, $cm)) {
            throw new moodle_exception('noviewdiscussionspermission', 'forum');
        }

        $canviewfullname = has_capability('moodle/site:viewfullnames', $modcontext);

        // We will add this field in the response.
        $canreply = forum_user_can_post($forum, $discussion, $USER, $cm, $course, $modcontext);

        $forumtracked = forum_tp_is_tracked($forum);

        $sort = 'p.' . $sortby . ' ' . $sortdirection;
        $posts = forum_get_all_discussion_posts($discussion->id, $sort, $forumtracked);

        foreach ($posts as $pid => $post) {

            if (!forum_user_can_see_post($forum, $discussion, $post, null, $cm)) {
                $warning = array();
                $warning['item'] = 'post';
                $warning['itemid'] = $post->id;
                $warning['warningcode'] = '1';
                $warning['message'] = 'You can\'t see this post';
                $warnings[] = $warning;
                continue;
            }

            // Function forum_get_all_discussion_posts adds postread field.
            if (!isset($post->postread)) {
                $posts[$pid]->postread = false;
            }
            $posts[$pid]->canreply = $canreply;
            if (!empty($posts[$pid]->children)) {
                $posts[$pid]->children = array_keys($posts[$pid]->children);
            } else {
                $posts[$pid]->children = array();
            }

            $user = new stdclass();
            $user = username_load_fields_from_object($user, $post);
            $posts[$pid]->userfullname = fullname($user, $canviewfullname);

            $posts[$pid] = (array) $post;
        }

        $result = array();
        $result['posts'] = $posts;
        $result['warnings'] = $warnings;
        return $result;
    }

    /**
     * Describes the get_forum_discussion_posts return value.
     *
     * @return external_single_structure
     * @since Moodle 2.7
     */
    public static function get_forum_discussion_posts_returns() {
        return new external_single_structure(
            array(
                'posts' => new external_multiple_structure(
                        new external_single_structure(
                            array(
                                'id' => new external_value(PARAM_INT, 'Post id'),
                                'discussion' => new external_value(PARAM_INT, 'Discussion id'),
                                'parent' => new external_value(PARAM_INT, 'Parent id'),
                                'userid' => new external_value(PARAM_INT, 'User id'),
                                'created' => new external_value(PARAM_INT, 'Creation time'),
                                'modified' => new external_value(PARAM_INT, 'Time modified'),
                                'mailed' => new external_value(PARAM_INT, 'Mailed?'),
                                'subject' => new external_value(PARAM_TEXT, 'The post subject'),
                                'message' => new external_value(PARAM_RAW, 'The post message'),
                                'messageformat' => new external_value(PARAM_INT, 'The post message format'),
                                'messagetrust' => new external_value(PARAM_INT, 'Can we trust?'),
                                'attachment' => new external_value(PARAM_RAW, 'Attachments'),
                                'totalscore' => new external_value(PARAM_INT, 'The post message total score'),
                                'mailnow' => new external_value(PARAM_INT, 'Mail now?'),
                                'children' => new external_multiple_structure(new external_value(PARAM_INT, 'children post id')),
                                'canreply' => new external_value(PARAM_BOOL, 'The user can reply to posts?'),
                                'postread' => new external_value(PARAM_BOOL, 'The post was read'),
                                'userfullname' => new external_value(PARAM_TEXT, 'Post author full name')
                            ), 'post'
                        )
                    ),
                'warnings' => new external_warnings()
            )
        );
    }

}
