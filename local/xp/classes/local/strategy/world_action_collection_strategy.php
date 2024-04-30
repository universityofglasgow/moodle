<?php
/**
 * This file is part of Level Up XP+.
 *
 * Level Up XP+ is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Level Up XP+ is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Level Up XP+.  If not, see <https://www.gnu.org/licenses/>.
 *
 * https://levelup.plus
 */

/**
 * World action collection strategy.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_xp\local\strategy;

use block_xp\local\action\action;
use block_xp\local\rulefilter\handler as rule_filter_handler;
use block_xp\local\logger\reason_collection_logger;
use block_xp\local\logger\reason_occurrence_indicator;
use block_xp\local\ruletype\resolver as rule_type_resolver;
use block_xp\local\reason\reason;
use block_xp\local\reason\reason_with_rule;
use block_xp\local\rule\dictator;
use block_xp\local\ruletype\ruletype;
use block_xp\local\strategy\action_collection_strategy;
use block_xp\local\utils\user_utils;
use block_xp\local\world;
use block_xp\local\xp\state_store_with_reason;
use DateInterval;

/**
 * World action collection strategy.
 *
 * @package    local_xp
 * @copyright  2024 Frédéric Massart
 * @author     Frédéric Massart <fred@branchup.tech>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class world_action_collection_strategy implements action_collection_strategy {

    /** @var world The world. */
    protected $world;
    /** @var reason_collection_logger The logger. */
    protected $logger;

    /** @var dictator The rule dictator. */
    protected $ruledictator;
    /** @var rule_type_resolver The rule type resolver. */
    protected $ruletyperesolver;
    /** @var rule_filter_handler The rule filter handler. */
    protected $rulefilterhandler;

    /**
     * Constructor.
     *
     * @param world $world The world.
     * @param reason_collection_logger $logger The logger.
     * @param dictator $ruledictator The rule dictator.
     * @param rule_type_resolver $ruletyperesolver The rule type resolver.
     * @param rule_filter_handler $rulefilterhandler The rule filter handler.
     */
    public function __construct(
            world $world,
            reason_collection_logger $logger,
            dictator $ruledictator,
            rule_type_resolver $ruletyperesolver,
            rule_filter_handler $rulefilterhandler
        ) {
        $this->world = $world;
        $this->logger = $logger;

        $this->ruledictator = $ruledictator;
        $this->ruletyperesolver = $ruletyperesolver;
        $this->rulefilterhandler = $rulefilterhandler;
    }

    /**
     * Collection the action.
     *
     * @param action $action The action.
     * @return void
     */
    public function collect_action(action $action) {
        $config = $this->world->get_config();
        $context = $this->world->get_context();
        $store = $this->world->get_store();

        // Check course config.
        if (!$config->get('enabled')) {
            return;
        }

        // Retrieve all the rules.
        $rules = $this->ruledictator->get_effective_rules_grouped_by_type($context, $action->get_context());
        foreach ($rules as $ruletype => $typerules) {
            if (empty($typerules)) {
                continue;
            }

            $type = $this->ruletyperesolver->get_type($ruletype);
            if (!$type) {
                continue;
            };

            if (!$type->is_action_compatible($action)) {
                continue;
            }

            if (!$type->is_action_satisfying_requirements($action)) {
                continue;
            }

            $reason = $type->make_reason($action);
            if (!$this->is_action_allowed_by_type($type, $action, $reason)) {
                continue;
            }

            $typerules = $this->ruledictator->sort_rules_by_priority($typerules);
            foreach ($typerules as $candidate) {

                // Get the filter.
                $filter = $this->rulefilterhandler->get_filter($candidate->get_filter_name());
                if (!$filter) {
                    continue;
                }

                // Test against the tester.
                $tester = $filter->get_action_tester($candidate->get_context(), $candidate->get_filter_config());
                if (!$tester->is_action_passing_constraints($action)) {
                    continue;
                }

                $rule = $candidate;
                $points = $rule->get_points();

                // Associate reason with the rule.
                if ($reason instanceof reason_with_rule) {
                    $reason->set_rule_id($rule->get_id());
                }

                // Award the points.
                if ($points > 0) {
                    if ($reason && $store instanceof state_store_with_reason) {
                        $store->increase_with_reason($action->get_user_id(), $points, $reason);
                    } else {
                        $store->increase($action->get_user_id(), $points);
                    }
                } else {
                    $this->logger->log_reason($action->get_user_id(), $points, $reason);
                }

                // Stop evaluating the other rules of the same type.
                break;
            }
        }
    }

    /**
     * Whether the action is allowed by the type.
     *
     * @param ruletype $type The type.
     * @param action $action The action.
     * @param reason $reason The reason.
     * @return bool
     */
    protected function is_action_allowed_by_type(ruletype $type, action $action, reason $reason) {
        $repeatwindow = $type->get_repeat_window();
        if ($repeatwindow !== ruletype::WINDOW_NONE && $this->logger instanceof reason_occurrence_indicator) {
            $cutoffdate = new \DateTimeImmutable('@0');
            if ($repeatwindow === ruletype::WINDOW_HOURLY) {
                $cutoffdate = $action->get_time()->sub(new DateInterval('PT1H'));
            } else if ($repeatwindow === ruletype::WINDOW_DAILY) {
                $cutoffdate = $action->get_time()
                    ->setTimezone(user_utils::get_timezone($action->get_user_id()))
                    ->setTime(0, 0, 0, 0);
            }
            if ($this->logger->has_reason_happened_since($action->get_user_id(), $reason,
                    \DateTime::createFromImmutable($cutoffdate))) {
                return false;
            }
        }
        return true;
    }


}
