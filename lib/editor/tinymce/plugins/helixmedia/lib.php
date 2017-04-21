<?php


defined('MOODLE_INTERNAL') || die();

/**
 * Plugin for inserting resources from the Helix Media Library
 *
 * @package   tinymce_helixmedia
 * @copyright 2012 Petr Skoda (http://skodak.org) and modified Streaming LTD 2013
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class tinymce_helixmedia extends editor_tinymce_plugin {
    /** @var array list of buttons defined by this plugin */
    protected $buttons = array('helixmedia');

    protected function update_init_params(array &$params, context $context,
            array $options = null) {

        // Add JS file, which uses default name.
        if (has_capability("mod/helixmedia:addinstance", $context))
            $this->add_js_plugin($params);
    }

    protected function get_sort_order() {
        return 140;
    }
}
