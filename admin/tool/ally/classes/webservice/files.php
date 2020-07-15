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
 * Files web service.
 *
 * @package   tool_ally
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace tool_ally\webservice;

use tool_ally\local;
use tool_ally\local_file;

defined('MOODLE_INTERNAL') || die();

/**
 * Provide a list of files to process for accessibility.
 *
 * @package   tool_ally
 * @copyright Copyright (c) 2016 Blackboard Inc. (http://www.blackboard.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class files extends loggable_external_api {
    /**
     * @return \external_function_parameters
     */
    public static function service_parameters() {
        return new \external_function_parameters([
            'page'    => new \external_value(PARAM_INT, 'page number (0 based)', VALUE_DEFAULT, 0),
            'perpage' => new \external_value(PARAM_INT, 'items per page', VALUE_DEFAULT, 100),
        ]);
    }

    /**
     * @return \external_multiple_structure
     */
    public static function service_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id'           => new \external_value(PARAM_ALPHANUM, 'File path name SHA1 hash'),
                'courseid'     => new \external_value(PARAM_INT, 'Course ID of the file'),
                'name'         => new \external_value(PARAM_TEXT, 'File name'),
                'mimetype'     => new \external_value(PARAM_RAW, 'File mime type'),
                'contenthash'  => new \external_value(PARAM_ALPHANUM, 'File content SHA1 hash'),
                'timemodified' => new \external_value(PARAM_TEXT, 'Last modified time of the file'),
            ])
        );
    }

    /**
     * @param int $page
     * @param int $perpage
     * @return array
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     * @throws \required_capability_exception
     * @throws \restricted_context_exception
     */
    public static function execute_service($page, $perpage) {
        self::validate_context(\context_system::instance());
        require_capability('moodle/course:view', \context_system::instance());
        require_capability('moodle/course:viewhiddencourses', \context_system::instance());

        $params = self::validate_parameters(self::service_parameters(), ['page' => $page, 'perpage' => $perpage]);

        // We are betting that most courses have files, so better to preload than to fetch one at a time.
        local::preload_course_contexts();

        $files = local_file::iterator()->with_stop_at_count($params['perpage']);
        $files->with_count_start_page($params['page']);

        $return = array();
        foreach ($files as $file) {
            $return[] = [
                'id'           => $file->get_pathnamehash(),
                'courseid'     => local_file::courseid($file),
                'name'         => $file->get_filename(),
                'mimetype'     => $file->get_mimetype(),
                'contenthash'  => $file->get_contenthash(),
                'timemodified' => local::iso_8601($file->get_timemodified()),
            ];
        }

        return $return;
    }
}