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
 * Container.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local;
defined('MOODLE_INTERNAL') || die();

/**
 * Container.
 *
 * @package    local_xp
 * @copyright  2017 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class container implements \block_xp\local\container {

    /** @var array The objects supported by this container. */
    protected static $supports = [
        'badge_url_resolver' => true,
        'block_class' => true,
        'collection_logger' => true,
        'collection_strategy' => true,
        'config' => true,
        'course_currency_factory' => true,
        'course_world_factory' => true,
        'course_world_grouped_leaderboard_factory' => true,
        'course_world_leaderboard_factory' => true,
        'course_world_navigation_factory' => true,
        'currency' => true,
        'file_server' => true,
        'grouped_leaderboard_helper' => true,
        'iomad_facade' => true,
        'renderer' => true,
        'router' => true,
        'routes_config' => true,
        'rule_event_lister' => true,
        'settings_maker' => true,
        'tasks_definition_maker' => true,
        'theme_repository' => true,
        'theme_updater' => true,
        'url_resolver' => true,
    ];

    /** @var array Object instances. */
    protected $instances = [];
    /** @var container Sub container. */
    protected $subcontainer;

    /** @var object The store cache. */
    private $actcompletionstore;
    /** @var object The collection target resolver. */
    private $usercollectiontargetresolver;
    /** @var course_config_factory The course config factory. */
    private $courseconfigfactory;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->subcontainer = new \block_xp\local\default_container();
    }

    /**
     * Get a thing.
     *
     * @param string $id The thing's name.
     * @return mixed
     */
    public function get($id) {
        if (!$this->has($id)) {
            return $this->subcontainer->get($id);
        }

        if (!isset($this->instances[$id])) {
            $method = 'get_' . $id;
            $this->instances[$id] = $this->{$method}();
        }

        return $this->instances[$id];
    }

    /**
     * Get the default badge URL resolver.
     *
     * @return moodle_url
     */
    protected function get_badge_url_resolver() {
        return new \local_xp\local\xp\badge_url_resolver_stack([
            $this->subcontainer->get('badge_url_resolver'),
            new \local_xp\local\xp\theme_badge_url_resolver(
                $this->get('theme_repository'),
                $this->get('config')->get('badgetheme')
            )
        ]);
    }

    /**
     * Block class.
     *
     * @return string
     */
    protected function get_block_class() {
        return 'local_xp\local\block\course_block';
    }

    /**
     * Get the global collection logger.
     *
     * @return logger
     */
    protected function get_collection_logger() {
        return new \local_xp\local\logger\global_collection_logger($this->get('db'));
    }

    /**
     * Collection strategy.
     *
     * @return collection_strategy
     */
    protected function get_collection_strategy() {
        $st = new \local_xp\local\strategy\collection_strategy(
            $this->get('course_world_factory'),
            $this->get('config')->get('context'),
            $this->get_user_collection_target_resolver()
        );
        return $st;
    }

    /**
     * Get the global config object.
     *
     * @return config
     */
    protected function get_config() {
        return new \block_xp\local\config\config_stack([
            new \block_xp\local\config\mdl_config(
                'local_xp',
                new \local_xp\local\config\default_admin_config()
            ),
            $this->subcontainer->get('config')
        ]);
    }

    /**
     * Get the course config factory.
     *
     * @return course_config_factory
     */
    private function get_course_config_factory() {
        if (!$this->courseconfigfactory) {
            $this->courseconfigfactory = new \local_xp\local\factory\course_config_factory(
                $this->get('config'), $this->get('db'));
        }
        return $this->courseconfigfactory;
    }

    /**
     * Get the course currency factory.
     *
     * @return course_currency_factory
     */
    protected function get_course_currency_factory() {
        return new \local_xp\local\factory\course_currency_factory(
            $this->get('course_world_factory'),
            $this->get('currency')
        );
    }

    /**
     * Get the course world grouped leaderboard factory.
     *
     * @return course_world_group_leaderboard_factory
     */
    protected function get_course_world_grouped_leaderboard_factory() {
        return new \local_xp\local\factory\default_course_world_grouped_leaderboard_factory(
            $this->get('db'),
            $this->get('renderer'),
            $this->get('iomad_facade'),
            $this->get('grouped_leaderboard_helper')
        );
    }

    /**
     * Get the course world leaderboard factory.
     *
     * @return course_world_leaderboard_factory
     */
    protected function get_course_world_leaderboard_factory() {
        return new \local_xp\local\factory\course_world_leaderboard_factory(
            $this->get('db'),
            $this->get('iomad_facade')
        );
    }

    /**
     * Course world factory.
     *
     * @return course_world_factory
     */
    protected function get_course_world_factory() {
        $db = $this->get('db');
        $config = $this->get('config');
        return new \local_xp\local\factory\course_world_factory(
            $config->get('context'),
            $db,
            $this->get_course_config_factory(),
            $this->get_user_collection_target_resolver(),
            new \local_xp\local\factory\badge_url_resolver_course_world_factory(
                $this->get('badge_url_resolver'),
                $this->get('theme_repository')
            )
        );
    }

    /**
     * Get the course world navigation factory.
     *
     * @return course_world_navigation_factory
     */
    protected function get_course_world_navigation_factory() {
        return new \local_xp\local\factory\course_world_navigation_factory(
            $this->get('url_resolver'),
            // We need this to pass our own admin config.
            new \block_xp\local\factory\default_course_world_navigation_factory(
                $this->get('url_resolver'),
                $this->get('config')
            ),
            $this->get('config')
        );
    }

    /**
     * Get the default currency.
     *
     * @return currency
     */
    protected function get_currency() {
        return new \local_xp\local\currency\default_currency(
            new \local_xp\local\currency\admin_sign_url_resolver()
        );
    }

    /**
     * Get router.
     *
     * @return routing\router
     */
    protected function get_file_server() {
        return new file\file_server(
            get_file_storage(),
            $this->get('config')->get('context')
        );
    }

    /**
     * Get the grouped leaderboard helper.
     *
     * @return grouped_leaderboard_helper
     */
    protected function get_grouped_leaderboard_helper() {
        return new \local_xp\local\leaderboard\grouped_leaderboard_helper(
            $this->get('db'),
            $this->get('iomad_facade')
        );
    }

    /**
     * Get the IOMAD facade.
     *
     * @return local_xp\local\iomad\facade
     */
    protected function get_iomad_facade() {
        return new \local_xp\local\iomad\facade($this->get('db'));
    }

    /**
     * Get renderer.
     *
     * @return renderer_base
     */
    protected function get_renderer() {
        global $PAGE;
        if (!$PAGE->has_set_url() && !WS_SERVER) {
            debugging('The renderer was requested too early in the process.', DEBUG_DEVELOPER);
        }
        $renderer = $PAGE->get_renderer('local_xp');
        return $renderer;
    }

    /**
     * Get router.
     *
     * @return routing\router
     */
    protected function get_router() {
        return new routing\router(
            $this->get('url_resolver')
        );
    }

    /**
     * Get the routes config.
     *
     * @return routes_config
     */
    protected function get_routes_config() {
        return new \local_xp\local\routing\routes_config(
            new \block_xp\local\routing\default_routes_config()
        );
    }

    /**
     * Get the rule event lister.
     *
     * @return event_lister
     */
    protected function get_rule_event_lister() {
        // Feature toggle off for now, by default users do not have the permission to earn anything
        // in the system context. Which means that those events would not work unless users are given
        // the permission in the system context. This is going to be a configuration nightmare for
        // endusers, or we need to change the defaults, which will make everyone earn points everywhere,
        // this is not a valid option. so holding off this feature for now.
        // return new \local_xp\local\rule\event_lister($this->get('config'));
        return $this->subcontainer->get('rule_event_lister');
    }

    /**
     * Get the settings maker.
     *
     * @return settings_maker
     */
    protected function get_settings_maker() {
        return new \local_xp\local\setting\settings_maker(
            new \block_xp\local\config\config_stack([
                new \local_xp\local\config\default_admin_config(),
                new \block_xp\local\config\default_admin_config(),
            ]),
            $this->get('url_resolver'),
            $this->get('iomad_facade')
        );
    }

    /**
     * Get the tasks definition maker.
     *
     * @return tasks_definition_maker
     */
    protected function get_tasks_definition_maker() {
        return new \local_xp\local\task\tasks_definition_maker();
    }

    /**
     * Get the theme repository.
     *
     * @return theme\theme_repository
     */
    protected function get_theme_repository() {
        return new \local_xp\local\theme\theme_repository($this->get('db'));
    }

    /**
     * Get the theme updater.
     *
     * @return theme\theme_updater
     */
    protected function get_theme_updater() {
        global $CFG;
        $themedir = $CFG->dirroot . '/local/xp/pix/theme';
        return new \local_xp\local\theme\theme_updater($this->get('db'), new \DirectoryIterator($themedir));
    }

    /**
     * Get URL resolver.
     *a
     * @return url_resolver
     */
    protected function get_url_resolver() {
        return new \block_xp\local\routing\default_url_resolver(
            $this->get('base_url'),
            $this->get('routes_config')
        );
    }

    /**
     * Get user collection target resolver.
     *
     * @return collection_target_resolver
     */
    private function get_user_collection_target_resolver() {
        if (!$this->usercollectiontargetresolver) {
            $this->usercollectiontargetresolver = new \local_xp\local\strategy\user_collection_target_resolver();
        }
        return $this->usercollectiontargetresolver;
    }

    /**
     * Whether this container can return an entry for the given identifier.
     *
     * @param string $id The thing's name.
     * @return bool
     */
    public function has($id) {
        return array_key_exists($id, static::$supports);
    }

}
