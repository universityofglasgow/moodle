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
 * Echo360 Tiny filter plugin file.
 *
 * @package    filter_echo360tiny
 * @copyright  2023 Echo360 Inc.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class filter_echo360tiny extends moodle_text_filter {

    // Echo360 Tiny LTI Launch URL filter installation path and filter needle.
    const FILTER_PATH = '/filter/echo360tiny/lti_launch.php';

    /**
     * Look for a module id in an array of modules.
     * @param $cmid the key to look for.
     * @param $modinfos the array of mod_info where to search.
     * @return bool true if the item exists, false otherwise.
     */
    private function cm_exists($cmid, $modinfos) {
        foreach ($modinfos->cms as $cm) {
            if ($cmid === $cm->id) {
                return true;
            }
        }
        return false;
    }

    /**
     * Replaces an "embed link" with an iframe for LTI launch.
     *
     * @param  string $text
     * @param  array  $options
     * @return mixed|string
     */
    public function filter($text, array $options = array()) {
        global $CFG, $PAGE;

        // Check if Echo360 Tiny LTI Launch URLs do not exist, return immediately.
        if (stripos($text, self::FILTER_PATH) === false) {
            return $text;
        }

        // Avoid placing videos on the 'View all submissions' page where there could be potentially many of them.
        if ($PAGE->pagetype == 'mod-assign-grading') {
            return $text;
        }

        list($context, $course, $cm) = get_context_info_array($this->context->id);

        // Find all Echo360 Tiny Lti Links that exist in text and update their cmid value.
        if ($cm) {
            $cmid = $cm->id;
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

        // Find all Echo360 Tiny LTI Embeds that exist in text content.
        // Order of parameters is strict.
        $searchfilters = '%<a[\s]+[^>]*?href[\s]?=[\s]?"' .
            preg_quote($CFG->wwwroot . self::FILTER_PATH) .
            '\?url=(?<url>[^&]+?)?&(amp;)?cmid=(?<cmid>\d*)?&(amp;)?width=(?<width>\d*)?&(amp;)?height=(?<height>\d*)?'.
            '(&amp;resourcelinkid=(?<resourcelinkid>[^&]+?))?'. // LTI 1.3 parameter optional for LIT 1.0.
            '".*?>'.
            '(?<text>.*?)<\/a>%';
        if ($nofilterurls = preg_match_all($searchfilters, $text, $result)) {
            // Decode URLs in text content to make <A HREF> regex pattern replacement with <IFRAME> easier.
            $filteredtext = $text;

            // Find and replace each Echo360 LTI Launch URL with IFRAME embedded player.
            for ($i = 0; $i < $nofilterurls; $i++) {
                if ($cm) {
                    $cmid = $cm->id;
                } else {
                    $modinfos = get_fast_modinfo($course);
                    // Make sure the cmid exists in this course. It might not exist within
                    // the course if the course was cloned, and a cloned link contains the
                    // cmid from a previous course, and we cannot update its value
                    // from $PAGE->cm->id because we're in a non-module page, like the
                    // course's main page.
                    if ($this->cm_exists($result['cmid'][$i], $modinfos)) {
                        $cmid = $result['cmid'][$i];
                    } else {
                        $cmid = 0;
                    }
                }
                if (strlen($result['resourcelinkid'][$i]) > 1) {
                    $resourcelinkidparam = '&resourcelinkid=' . $result['resourcelinkid'][$i];
                } else {
                    $resourcelinkidparam = '';
                }
                $searchfilter = '~<a[\s]+[^>]*?href[\s]?=[\s]?"' .
                    preg_quote($CFG->wwwroot . self::FILTER_PATH) .
                    '\?url=' . preg_quote($result['url'][$i]) . '*(.*?)[\"\']*.*?>([^<]+|.*?)?<\/a>~';
                $replacement = '<div class="echo360-iframe"><iframe src="' . $CFG->wwwroot . self::FILTER_PATH .
                    '?url=' .     $result['url'][$i] .
                    $resourcelinkidparam .
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
        // Return original text in the case where the Echo360 Tiny LTI Launch URLs have been modified and fail regex matching.
        return $text;
    }

}
