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
 * Echo360 filter plugin file.
 *
 * @package local_echo360
 * @author  Echo360
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class filter_echo360 extends moodle_text_filter {

    // Echo360 LTI Launch URL filter installation path and filter needle.
    const FILTER_PATH = '/filter/echo360/lti_launch.php';

    /**
     * Replaces an "embed link" with an iframe for LTI launch.
     *
     * @param  string $text
     * @param  array  $options
     * @return mixed|string
     */
    public function filter($text, array $options = array()) {
        global $CFG, $PAGE;

        // Check if Echo360 LTI Launch URLs do not exist, return immediately.
        if (stripos($text, self::FILTER_PATH) === false) {
            return $text;
        }

        // Avoid placing videos on the 'View all submissions' page where there could be potentially many of them.
        if ($PAGE->pagetype == 'mod-assign-grading') {
            return $text;
        }

        // Find all Echo360 Lti Links that exist in text and update their cmid value.
        if ($PAGE->cm) {
            $cmid = $PAGE->cm->id;
            $ltilinks = '%<a[\s]+[^>]*?href[\s]?=[\s]?"' .
                preg_quote($CFG->wwwroot . self::FILTER_PATH) .
                '\?url=.+?&.*?cmid=\d+".*?>.*?<\/a>%';
            if (preg_match_all($ltilinks, $text, $matches) !== false) {
                foreach ($matches as $match) {
                    $cmidregex = '/cmid=\d+/';
                    if (isset($match[0])) {
                        $s = preg_replace($cmidregex, 'cmid='.$cmid, $match[0]);
                        $text = str_replace($match[0], $s, $text);
                    }
                }
            }
        }

        // Find all Echo360 LTI Embeds that exist in text content.
        $searchfilters = '%<a[\s]+[^>]*?href[\s]?=[\s]?"' .
            preg_quote($CFG->wwwroot . self::FILTER_PATH) .
            '\?url=(?<url>.+?)?&.*?cmid=(?<cmid>\d*)?&.*?width=(?<width>\d*)?&.*?height=(?<height>\d*)?".*?>(?<text>.*?)<\/a>%';
        if ($nofilterurls = preg_match_all($searchfilters, $text, $result)) {
            // Decode URLs in text content to make <A HREF> regex pattern replacement with <IFRAME> easier.
            $filteredtext = urldecode($text);

            // Find and replace each Echo360 LTI Launch URL with IFRAME embedded player.
            for ($i = 0; $i < $nofilterurls; $i++) {
                if ($PAGE->cm) {
                    $cmid = $PAGE->cm->id;
                } else {
                    $cmid = $result['cmid'][$i];
                }

                $searchfilter = '%<a[\s]+[^>]*?href[\s]?=[\s]?"' .
                    preg_quote($CFG->wwwroot . self::FILTER_PATH) .
                    '\?url=' . preg_quote(urldecode($result['url'][$i])) . '*(.*?)[\"\']*.*?>([^<]+|.*?)?<\/a>%';
                $replacement = '<div class="echo360-iframe"><iframe src="' . $CFG->wwwroot . self::FILTER_PATH .
                    '?url=' .     $result['url'][$i] .
                    '&cmid=' .    $cmid .
                    '&width=' .   $result['width'][$i] .
                    '&height=' .  $result['height'][$i] . '" '.
                    'width="' .   $result['width'][$i] . '" ' .
                    'height="' .  $result['height'][$i] . '" ' .
                    'frameborder="0" allowfullscreen="allowfullscreen" ' .
                    'webkitallowfullscreen="webkitallowfullscreen" ' .
                    'mozallowfullscreen="mozallowfullscreen">' .
                '</iframe></div>';
                $filteredtext = preg_replace($searchfilter, $replacement, $filteredtext, 1);
            }
            return $filteredtext;
        }
        // Return original text in the case where the Echo360 LTI Launch URLs have been modified and fail regex matching.
        return $text;
    }

}
