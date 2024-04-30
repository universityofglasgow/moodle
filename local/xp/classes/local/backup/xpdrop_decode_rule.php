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
 * Decode rule.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\backup;

use block_xp\di;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/backup/util/helper/restore_decode_rule.class.php');

/**
 * Decode rule.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class xpdrop_decode_rule extends \restore_decode_rule {

    /** @var local_xp\local\drop\drop_repository */
    protected $droprepo;

    /**
     * Constructor.
     *
     * @param string $placeholder The placeholder.
     */
    public function __construct($placeholder = 'LOCALXPSHORTCODEDROP') {
        parent::__construct($placeholder, '', 'local_xp_drop');
        $this->droprepo = di::get('drop_repository');
    }

    /**
     * Nasty override to get things done.
     *
     * @param string $content The content.
     * @return string
     */
    public function decode($content) {
        if (preg_match_all($this->cregexp, $content, $matches) === 0) {
            return $content;
        }

        foreach ($matches[0] as $key => $tosearch) {
            $drop = null;
            foreach ($this->mappings as $mappingkey => $mappingsource) {
                $oldid = $matches[$mappingkey][$key];
                $newid = $this->get_mapping('local_xp_drop', $oldid);
                $drop = $this->droprepo->get_by_id($newid);
            }

            $shortcode = "[xpdrop id=0 secret=unknown]";
            if ($drop) {
                $shortcode = "[xpdrop id={$drop->get_id()} secret={$drop->get_secret()}]";
            }
            $content = str_replace($tosearch, $shortcode, $content);
        }

        return $content;
    }

    /**
     * Encodes the content.
     *
     * @param string $content The content.
     * @return string The content.
     */
    public static function encode_content($content) {
        global $CFG;

        if (!class_exists('filter_shortcodes\shortcodes')) {
            return $content;
        }

        require_once($CFG->dirroot . '/filter/shortcodes/lib/helpers.php');
        $content = filter_shortcodes_process_text($content, function($tag) {
            if ($tag !== 'xpdrop') {
                return null;
            }
            return (object) ['hascontent' => false, 'contentprocessor' => function($args, $content) {
                return '$@LOCALXPSHORTCODEDROP*' . ($args['id'] ?? '0') . '@$';
            }];
        });

        return $content;
    }

    /**
     * Bypass the validation.
     *
     * @param string $linkname The link name.
     * @param string $urltemplate The URL template.
     * @param string $mappings The mapping.
     * @return array
     */
    protected function validate_params($linkname, $urltemplate, $mappings) {
        return ['1' => $mappings];
    }

}
