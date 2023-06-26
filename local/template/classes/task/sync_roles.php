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
 * sync_roles class.
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_template\task;

use core\task\scheduled_task;

/**
 * sync_roles class.
 *
 * @package    local_template
 * @copyright  2023 David Aylmer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sync_roles extends scheduled_task {
    /**
     * Gets the name of this event.
     * @return string Name of this event.
     */
    public function get_name() {
        return get_string('tasksyncroles', 'local_template');
    }

    /**
     * Executes the event.
     */
    public function execute() {
        global $DB;

        // Get plugin config setting synchronisation role.
        $syncroleid = get_config('local_template', 'syncrole');

        if (empty($syncroleid)) {
            // No synchronisation role defined.
            return;
        } else {
            $syncrole = $DB->get_record('role', ['id' => $syncroleid]);
            if (empty($syncrole)) {
                mtrace("Could not find synchronisation role {$syncroleid}");
                return;
            }
        }

        // Get plugin config setting list of template course categories.
        $templatecategorysettings = get_config('local_template', 'categories');
        $templatecategories = explode(',', $templatecategorysettings);


        if (!empty($templatecategories)) {
            foreach ($templatecategories as $templatecategoryid) {

                // For each course template category.

                $templatecategory = $DB->get_record('course_categories', ['id' => $templatecategoryid]);
                $templatecategorycontext = \context_coursecat::instance($templatecategory->id);

                if (empty($templatecategory)) {
                    mtrace("Could not find category {$templatecategoryid}");
                } else {
                    // Find parent.
                    $parentid = $templatecategory->parent;
                    $parentcategory = $DB->get_record('course_categories', ['id' => $parentid]);
                    if (empty($parentcategory)) {
                        mtrace("Could not find parent category {$parentid}");
                    } else {
                        mtrace("Syncronising role '{$syncrole->name}' for template category '{$templatecategory->name}' in course category '{$parentcategory->name}'");

                        $path = $DB->sql_like_escape($parentcategory->path) . '%';
                        $like = $DB->sql_like('path', ':likepath');
                        $categories = $DB->get_records_select('course_categories', "$like AND id <> :parent", ['likepath' => $path, 'parent' => $parentcategory->id]);
                        $users = [];
                        foreach ($categories as $category) {

                            // For each category in parent. (e.g. all sibling and children categories of this template course).

                            // Get category context.
                            $categorycontext = \context_coursecat::instance($category->id);

                            $createroles = [];
                            $roles = get_roles_with_capability('moodle/course:create');
                            foreach($roles as $role) {
                                array_push($createroles, $role->id);
                            }

                            // Find all users with course create capability
                            $users = $users + get_role_users($createroles, $categorycontext, false, 'ra.id', 'ra.id');
                        }

                        // Synchronise role assignment for this template course category.
                        $templateroles = $DB->get_records_select('role_assignments', '', ['roleid' => $syncrole->id, 'contextid' => $templatecategorycontext->id]);
                        foreach ($templateroles as $userrole) {
                            // if userrole user is not in users, unassign the role
                            $foundflag = false;
                            foreach ($users as $user) {
                                if ($userrole->userid == $user->id) {
                                    $foundflag = true;
                                }
                            }
                            if (!$foundflag) {
                                mtrace("Unassigning role {$syncrole->name} for user " . fullname($user) . " in template course category {$templatecategory->name}");
                                role_unassign($syncrole->id, $user->id, $templatecategorycontext->id);
                            }
                        }

                        foreach ($users as $user) {
                            // if user is not in user roles, assign the role

                            $foundflag = false;
                            foreach ($templateroles as $userrole) {
                                if ($userrole->userid == $user->id) {
                                    $foundflag = true;
                                }
                            }
                            if (!$foundflag) {
                                mtrace("Assigning role {$syncrole->name} for user " . fullname($user) . " in template course category {$templatecategory->name}");
                                role_assign($syncrole->id, $user->id, $templatecategorycontext->id);
                            }
                        }

                    }
                }
            }
        }
    }
}
