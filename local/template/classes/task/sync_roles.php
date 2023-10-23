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
     * @param $permissions array assosiative array containing string of capability and value e.g. 'local/template:usetemplate' => 1
     * @param $permission string permission to check within above array
     * @param $set          bool the expected value of the permission. Not present and false are equivalent.
     * @param $syncrole   object an {role} DB item object
     * @return              bool returns false if expected value $set does not match contents of $permissions
     * @throws \coding_exception
     */
    private function check_permission($permissions, $permission, $set, $syncrole) {
        if ($set) {
            if (!array_key_exists($permission, $permissions)) {
                mtrace("Error: Could not find capability: '{$permission}' in system context in role: '{$syncrole->shortname}' for local plugin setting: '"
                    . get_string('syncrole', 'local_template') . "'");
                return false;
            } else {
                if ($permissions[$permission]) {
                    mtrace("    {$permission}: OK (set)");
                } else {
                    mtrace("    {$permission}: Not set");
                    mtrace("Error: Could not find capability: '{$permission}' in system context in role: '{$syncrole->shortname}' for local plugin setting: '"
                        . get_string('syncrole', 'local_template') . "'");
                    return false;
                }
            }
        } else {
            if (array_key_exists($permission, $permissions)) {
                mtrace("Error: Synchronisation role: '{$syncrole->shortname}' should not contain capability: '{$permission}' for local plugin setting: '"
                    . get_string('syncrole', 'local_template') . "'");
                return false;
            } else {
                mtrace("    {$permission}: OK (not set)");
            }
        }

        return true;
    }

    /**
     * Executes the event.
     */
    public function execute() {
        global $DB;

        // If plugin setting sync role is set, and the plugin setting categories contains template category ids:
        // The role is applied to template course categories for users with course creation capability in sibling and child course categories

        // php admin/cli/scheduled_task.php --execute=\\local_template\\task\\sync_roles

        // Get plugin config setting for synchronisation role.
        $syncroleid = get_config('local_template', 'syncrole');

        if (empty($syncroleid)) {
            // No synchronisation role defined in plugin settings.
            mtrace("Error: No synchronisation role defined for local plugin setting: '" .
                get_string('syncrole', 'local_template') . "'");
            return false;
        } else {
            $syncrole = $DB->get_record('role', ['id' => $syncroleid]);
            if (empty($syncrole)) {
                // Setting does not correspond to an existing role. Stale setting?
                mtrace("Error: Could not find synchronisation role {$syncroleid} for local plugin setting: '" .
                    get_string('syncrole', 'local_template') . "'");
                return false;
            }
        }
        mtrace("Found synchronisation role: '{$syncrole->shortname}'");

        // Ensure selected role contains correct capabilities.
        // TODO: What if sync role doesnt have system context. (Maybe it shouldn't).
        $permissions = role_context_capabilities($syncroleid, \context_system::instance());
        mtrace("  Checking required capabilities for synchronisation role: '{$syncrole->shortname}'");

        if (!$this->check_permission($permissions, 'local/template:usetemplate', true, $syncrole)) {
            return false;
        }
        if (!$this->check_permission($permissions, 'moodle/backup:backupcourse', true, $syncrole)) {
            return false;
        }
        if (!$this->check_permission($permissions, 'moodle/course:create', false, $syncrole)) {
            return false;
        }
        mtrace("Finding roles with capability 'moodle/course:create'");
        // Discover roles with capability to create course (Basically managers/course creator archtypes).
        $createroles = [];
        $rolenames = '';
        $roles = get_roles_with_capability('moodle/course:create');
        if (empty($roles)) {
            mtrace("Error: No roles found with capability to create course. (This should not happen).");
            return false;
        } else {
            foreach ($roles as $role) {
                array_push($createroles, $role->id);
                if (!empty($rolenames)) {
                    $rolenames .= ', ';
                }
                $rolenames .= $role->shortname;
            }
            mtrace("  Roles with capability 'moodle/course:create': '{$rolenames}'");
        }

        // Get plugin config setting list of template course categories.
        mtrace("Finding template categories");
        $templatecategorysettings = get_config('local_template', 'categories');
        $templatecategories = explode(',', $templatecategorysettings);

        $count = 1;
        $total = count($templatecategories);
        if (empty($templatecategories) || $total == 1 && empty($templatecategories[0])) {
            mtrace("Error: Could not find template categories for local plugin setting: '" .
                get_string('categories', 'local_template') . "'");
            return false;
        } else {
            mtrace("  Found {$total} template categories");

            foreach ($templatecategories as $templatecategoryid) {

                // For each course template category.
                $templatecategory = $DB->get_record('course_categories', ['id' => $templatecategoryid]);

                if (empty($templatecategory)) {
                    mtrace("Error: Could not find category: '{$templatecategoryid}'. Please review category selection in plugin settings. [{$count}/{$total}]");
                } else {

                    $templatecategorycontext = \context_coursecat::instance($templatecategory->id);
                    mtrace('');
                    mtrace("[{$count}/{$total}] Finding users in template category: '{$templatecategory->name}' with capability: 'moodle/course:create'");

                    // Find parent.
                    $parentid = $templatecategory->parent;
                    $parentcategory = $DB->get_record('course_categories', ['id' => $parentid]);
                    if (empty($parentcategory)) {
                        mtrace("  No parent category for '{$templatecategory->name}'. (Top level category).");
                        $parentid = 0;
                        $parent = 'Top';
                        $parentpath = '';
                    } else {
                        $parentid = $parentcategory->id;
                        $parent = $parentcategory->name;
                        $parentpath = $parentcategory->path;
                    }

                    mtrace("  Syncronising role '{$syncrole->shortname}' for template category '{$templatecategory->name}' in course category '{$parent}'");

                    $path = $DB->sql_like_escape($parentpath) . '%';
                    $like = $DB->sql_like('path', ':likepath');
                    $categories = $DB->get_records_select('course_categories', "$like AND id <> :parent", ['likepath' => $path, 'parent' => $parentid]);
                    $users = [];
                    foreach ($categories as $category) {

                        // For each category in parent. (e.g. all sibling and children categories of this template course).

                        // Get category context.
                        $categorycontext = \context_coursecat::instance($category->id);

                        // Get users for this category.
                        $thiscategoryusers = get_role_users($createroles, $categorycontext, false, 'ra.id, u.id as userid', 'ra.id');
                        if (count($thiscategoryusers) == 0) {
                            mtrace("    - Subcategory '{$category->name}' - No users with create roles");
                        } else {
                            mtrace("    + Subcategory '{$category->name}' - Users with createroles +" . count($thiscategoryusers));
                        }

                        // Find all users with course create capability
                        $users += $thiscategoryusers;
                    }

                    // Synchronise role assignment for this template course category.
                    $templaterolessql = $DB->get_recordset('role_assignments', ['roleid' => $syncrole->id, 'contextid' => $templatecategorycontext->id]);

                    // moodle_recordset has a weird iterator in 3.11, copy to regular array for now.
                    $templateroles = [];
                    foreach ($templaterolessql as $templaterole) {
                        $templateroles[] = (object)[
                            'contextid' => $templaterole->contextid,
                            'roleid' => $templaterole->roleid,
                            'userid' => $templaterole->userid,
                        ];
                    }

                    if (empty($users)) {
                        mtrace("  - No users with create roles found. Skipping assign roles.");
                    } else {
                        mtrace("  Assign role '{$syncrole->shortname}' for '{$templatecategory->name}' [?/" . count($users) . "]");
                        $rolecount = 0;
                        foreach ($users as $user) {
                            // if user is not in user roles, assign the role
                            $userobject = $DB->get_record('user', ['id' => $user->userid], '*');
                            $foundflag = false;
                            foreach ($templateroles as $userrole) {
                                if ($userrole->userid == $user->userid) {
                                    $foundflag = true;
                                    break;
                                }
                            }
                            if ($foundflag) {
                                mtrace("    + Skipping user '" . fullname($userobject) . "'");
                            } else {
                                mtrace("    * Assigning role '{$syncrole->shortname}' for user '" . fullname($userobject) . "' in template course category '{$templatecategory->name}'");
                                role_assign($syncrole->id, $user->userid, $templatecategorycontext->id);
                                $rolecount++;
                            }
                        }
                        mtrace("  Assigned role '{$syncrole->shortname}' for '{$templatecategory->name}' [{$rolecount}/" . count($users) . "]");
                    }

                    if (empty($templateroles)) {
                        mtrace("  - No template role assignments present. Skipping unassign roles.");
                    } else {
                        mtrace("  Unassign role '{$syncrole->shortname}' from '{$templatecategory->name}' [?/" . count($templateroles) . "]");
                        $rolecount = 0;
                        foreach ($templateroles as $userrole) {
                            $userobject = $DB->get_record('user',['id' => $userrole->userid], '*');
                            // if userrole user is not in users, unassign the role
                            $foundflag = false;
                            foreach ($users as $user) {
                                if ($userrole->userid == $user->userid) {
                                    $foundflag = true;
                                    break;
                                }
                            }
                            if ($foundflag) {
                                mtrace("    + Skipping user '" . fullname($userobject) . "'");
                            } else {
                                mtrace("    * Unassigning role '{$syncrole->shortname}' for user '" . fullname($userobject) . "' in template course category '{$templatecategory->name}'");
                                role_unassign($syncrole->id, $userrole->userid, $templatecategorycontext->id);
                                $rolecount ++;
                            }
                        }
                        mtrace("  Unassigned role '{$syncrole->shortname}' from '{$templatecategory->name}' [{$rolecount}/" . count($templateroles) . "]");
                    }
                }
                mtrace("Completed synchronisation for template category: '{$templatecategory->name}'.");
                $count ++;
            }
            mtrace("Synchronise roles task complete.");
        }
    }
}
