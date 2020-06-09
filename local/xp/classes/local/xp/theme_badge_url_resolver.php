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
 * Theme badge URL resolver.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\xp;
defined('MOODLE_INTERNAL') || die();

use block_xp\local\xp\badge_url_resolver;
use local_xp\local\theme\theme_repository;

/**
 * Theme badge URL resolver.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_badge_url_resolver implements badge_url_resolver {

    /** @var theme_repository The DB. */
    protected $repo;
    /** @var renderer_base The renderer. */
    protected $renderer;
    /** @var stdClass The loaded theme. */
    protected $theme = false;
    /** @var string The theme code. */
    protected $themecode;

    /**
     * Constructor.
     *
     * @param theme_repository $repo The repo.
     * @param string $themecode The theme code.
     */
    public function __construct(theme_repository $repo, $themecode) {
        $this->repo = $repo;
        $this->themecode = $themecode;
    }

    /**
     * Get the renderer.
     *
     * @return renderer_base
     */
    protected function get_renderer() {
        if (!$this->renderer) {
            // TODO This is not nice at all, but it's here because passing the renderer all the way
            // here means that we need to initialise it early, and Moodle does not quite like that.
            // When initialised too early, the renderer doesn't inherit all the properties it should
            // such from the page as it wasn't finished initialising. I thought of making a lazy
            // renderer, but due to the lack of interface, overriding renderer_base makes it a
            // betting game, and I'd rather avoid it. Another work around is to make another service
            // to get the image URLs, but that thing would still need the renderer.
            $this->renderer = \block_xp\di::get('renderer');
        }
        return $this->renderer;
    }

    /**
     * Get the theme.
     *
     * @return stdClass
     */
    protected function get_theme() {
        if ($this->theme === false && !empty($this->themecode)) {
            $this->theme = $this->repo->get_theme($this->themecode);
        }
        return $this->theme;
    }

    /**
     * Get badge URL for level.
     *
     * @param int $level The level, as an integer.
     * @return moodle_url|null
     */
    public function get_url_for_level($level) {
        $theme = $this->get_theme();
        if (!$theme || $theme->levels < $level) {
            return null;
        }
        return $this->get_renderer()->pix_url('theme/' . $this->themecode . '/' . $level, 'local_xp');
    }

}
