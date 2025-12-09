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
 * TODO describe file
 *
 * @package    local_ltiusage
 * @copyright  2025 Michael Clark <michael.d.clark@glasgow.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

require_login();

$context = context_system::instance();
require_capability('local/ltiusage:viewltiusage', $context);

// Check if user can delete LTI activities (site admin only).
$candelete = is_siteadmin();

$PAGE->set_url(new moodle_url('/local/ltiusage/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('ltiusage', 'local_ltiusage'));
$PAGE->set_heading(get_string('ltiusage', 'local_ltiusage'));

echo $OUTPUT->header();

// Initialize pagination JavaScript for AJAX
$PAGE->requires->js_call_amd('local_ltiusage/pagination', 'init');

// Fetch LTI usage: old mod_lti (Activity) and/or new ltiadv if present.
global $DB;

// Get all LTI activities with a single efficient query.
$ltisql = "SELECT l.id, l.course, l.name, l.typeid, l.toolurl,
                   c.fullname as coursename, cm.id as cmid, cm.visible, l.name as activityname
            FROM {lti} l
            JOIN {course} c ON c.id = l.course
            JOIN {course_modules} cm ON cm.instance = l.id AND cm.module = (
                SELECT id FROM {modules} WHERE name = 'lti'
            )
            ORDER BY c.fullname, l.name";

$ltirecords = $DB->get_records_sql($ltisql);

// Group by typeid.
$grouped = [];
foreach ($ltirecords as $r) {
    $typeid = $r->typeid ?? 0;
    if (!isset($grouped[$typeid])) {
        $grouped[$typeid] = [];
    }

    $link = new moodle_url('/mod/lti/view.php', ['id' => $r->cmid]);
    $deletelink = new moodle_url('/course/mod.php', ['delete' => $r->cmid, 'sesskey' => sesskey()]);
    $grouped[$typeid][] = [
        'course' => format_string($r->coursename),
        'name' => format_string($r->activityname ?? ''),
        'visible' => (int)$r->visible,
        'link' => $link->out(false),
        'deletelink' => $candelete ? $deletelink->out(false) : '',
        'show_delete' => $candelete,
        'cmid' => $r->cmid, // Keep cmid for debugging.
    ];
}

// Sort each group and get type names.
$typegroups = [];
foreach ($grouped as $typeid => $rows) {
    usort($rows, function ($a, $b) {
        return [$a['course'], $a['name']] <=> [$b['course'], $b['name']];
    });

    $typename = 'Custom/Manual LTI';
    if ($typeid > 0) {
        $type = $DB->get_record('lti_types', ['id' => $typeid]);
        if ($type) {
            $typename = format_string($type->name);
        }
    }

    $perpage = 25;
    $total = count($rows);
    $page = optional_param('page_' . $typeid, 0, PARAM_INT);
    if ($total > $perpage) {
        $start = $page * $perpage;
        $pagedrows = array_slice($rows, $start, $perpage);
        $paginated = true;
        $totalpages = ceil($total / $perpage);
        $currentpage = $page + 1;
        $prev = $page > 0 ? $page - 1 : null;
        $next = ($page + 1) < $totalpages ? $page + 1 : null;
        $lastpage = $totalpages - 1;
        $pageurl = $PAGE->url->out(false);
        $pageurl .= (strpos($pageurl, '?') === false ? '?' : '&');
    } else {
        $pagedrows = $rows;
        $paginated = false;
        $totalpages = 1;
        $currentpage = 1;
        $prev = null;
        $next = null;
        $lastpage = 0;
        $pageurl = '';
    }

    $typegroups[] = [
        'typeid' => $typeid,
        'typename' => $typename,
        'count' => $total,
        'rows' => $pagedrows,
        'paginated' => $paginated,
        'page' => $page,
        'perpage' => $perpage,
        'total' => $total,
        'totalpages' => $totalpages,
        'currentpage' => $currentpage,
        'has_prev' => $prev !== null,
        'has_next' => $next !== null,
        'prev' => $prev,
        'next' => $next,
        'lastpage' => $lastpage,
        'pageurl' => $pageurl,
        'can_delete' => $candelete,
        'first_disabled' => $page == 0,
        'last_disabled' => $page == $lastpage,
    ];
}

$data = [
    'description' => get_string('ltiusage_desc', 'local_ltiusage'),
    'typegroups' => $typegroups,
];

// Normal page render.
echo $OUTPUT->render_from_template('local_ltiusage/lti_usage', $data);
echo $OUTPUT->footer();
