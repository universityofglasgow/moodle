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
 * Base for aggregation class
 * This class defines basic functional logic.
 * It could be overriden for custom instances.
 *
 * @package    local_gugrades
 * @copyright  2024
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_gugrades\aggregation;

/**
 * aggregation 'rules'
 */
class base {

    /**
     * @var int $courseid
     */
    private int $courseid;

    /**
     * @var string $atype
     */

    /**
     * Constructor
     * @param int $courseid
     * @param string $atype
     */
    public function __construct(int $courseid, string $atype) {
        $this->courseid = $courseid;
        $this->atype = $atype;
    }

    /**
     * Pre-process grades for aggregation.
     * Allows grades to be 'normalised' prior to aggregation.
     * @param array $items
     * @return array
     */
    public function pre_process_items(array $items) {

        return $items;
    }

    /**
     * Drop lowest n items from grades
     * @param array $items
     * @param int $n
     * @return [array, array]
     */
    public function droplow(array $items, int $n) {

        // If we're not going to return anything, anyway...
        if ($n >= count($items)) {
            return [[], []];
        }

        // Sort items by grade (ascending).
        usort($items, function($g1, $g2) {

            // Usort only likes integers, so the 100* is required.
            $normalised1 = 100 * $g1->grade / $g1->grademax;
            $normalised2 = 100 * $g2->grade / $g2->grademax;
            return $normalised1 - $normalised2;
        });

        $notdropitems = array_slice($items, $n);

        // Find gradeitems that are being dropped
        // (So that they can be marked as such)
        $droppeditems = array_slice($items, 0, $n);

        return [$notdropitems, $droppeditems];
    }

    /**
     * Logic for admingrades in >= level2, see MGU-726
     * Works out if aggregated grade is some admin grade
     * Returns this or empty string if not.
     *
     * NOTE:  MV/IS - both treated as MV
     *        NS/CW - both treated as NS
     *        07 - any return 07
     * @param array $items
     * @return string
     */
    public function admin_grades_level2(array $items) {

        // Condition 1: are there 1 or more NS/CW? If so, result is NS.
        // Condition 2: all admin grades are MV, result is MV.
        // Condition 3: all admin grades are IS, result is IS.
        // Condition 4: mix of IS/MV. Don't know. Going to say MV (TODO).
        $countnscw = 0;
        $countmv = 0;
        $countis = 0;
        $count07 = 0;
        foreach ($items as $item) {
            $grade = $item->admingrade;
            if (($grade == 'NS') || ($grade == 'CW')) {
                $countnscw++;
            } else if ($grade == 'MV') {
                $countmv++;
            } else if ($grade == 'IS') {
                $countis++;
            } else if (strcmp($grade, '07') == 0) {
                $count07++;
            }
        }

        // Any 07 means result is 07
        if ($count07) {
            return '07';
        }

        // Check about conditions.
        // And NS/CW at all means an NS result.
        if ($countnscw) {
            return 'NS';
        }

        // All MV and no IS means MV.
        if ($countmv && !$countis) {
            return 'MV';
        }

        // All IS and no MV means IS.
        if ($countis && !$countmv) {
            return 'IS';
        }

        // TODO: mix of MV and IS - not sure about this
        // currently returning MV.
        if ($countis && $countmv) {
            return 'MV';
        }

        // No admin grade.
        return '';
    }

    /**
     * Logic for admingrades in >= level2, TODO Ticket?
     * Works out if aggregated grade is some admin grade
     * Returns this or empty string if not.
     *
     * @param array $items
     * @return string
     */
    public function admin_grades_level1(array $items) {

        // Condition 1: Any 07 - result is 07
        $countnscw = 0;
        $countmv = 0;
        $countis = 0;
        $count07 = 0;
        foreach ($items as $item) {
            $grade = $item->admingrade;
            if (($grade == 'NS') || ($grade == 'CW')) {
                $countnscw++;
            } else if ($grade == 'MV') {
                $countmv++;
            } else if ($grade == 'IS') {
                $countis++;
            } else if (strcmp($grade, '07') == 0) {
                $count07++;
            }
        }

        // Any 07 means result is 07
        if ($count07) {
            return '07';
        }

        // No admin grade.
        return '';
    }

    /**
     * Calculate completion %age for items
     * Need to be "sympathetic" with rounding on this as
     * stuff will be blocked if completion != 100%
     *
     * Completion is...
     * (sum of weights of completed items) * 100 / (sum of all weights)
     *
     * If a non-weighted aggregation strategy is used then set
     * $weighted=false. In this case weight is assumed to be 1 for all items
     *
     * NOTE: Points grades do NOT count. Admingrades do NOT count.
     * @param array $items
     * @param bool $weighted
     * @return int
     */
    public function completion(array $items, bool $weighted) {

        $totalweights = 0.0;
        $countall = 0;
        $totalcompleted = 0.0;
        $countcompleted = 0;

        foreach ($items as $item) {
            $weight = $weighted ? $item->weight : 1;
            $totalweights += $weight;
            $countall++;
            if (!$item->grademissing && !$item->admingrade && $item->isscale) {
                $totalcompleted += $weight;
                $countcompleted++;
            }
        }

        // Ideally, we shouldn't be here if countall or totalweights are zero.
        // However, just for robustness...
        if (($countall == 0) || ($totalweights == 0)) {
            return 0;
        }

        // Calculation and rounding.
        // If $totalweights == 0 then there are no weights, then use
        // counts instead.
        if ($totalweights == 0) {
            $raw = $countcompleted * 100 / $countall;
        } else {
            $raw = $totalcompleted * 100 / $totalweights;
        }

        return round($raw, 0, PHP_ROUND_HALF_UP);
    }

    /**
     * Round to a specific number of decimal places.
     * Spec says 5, but giving the opportunity to change.
     * @param float $value
     * @return float
     */
    public function round_float(float $value) {
        return round($value, 5);
    }

    /**
     * Does aggregation strategy allow specification of weights?
     * NOTE: simple weighted mean, does NOT use weights
     * @param int $aggregationid
     * @return bool
     */
    public function is_strategy_weighted(int $aggregationid) {

        return $aggregationid == \GRADE_AGGREGATE_WEIGHTED_MEAN;
    }

    //
    // Following are functions for all the basic aggregation strategies. These mostly
    // replicate what core Moodle Gradebook does and are as specified in the Moodle docs.
    //

    /**
     * Choose aggregation strategy method
     * @param int $aggregationid
     * @return string
     */
    public function strategy_factory(int $aggregationid) {

        // Array defines which aggregation type calls which function.
        $lookup = [
            \GRADE_AGGREGATE_MEAN => 'mean',
            \GRADE_AGGREGATE_MEDIAN => 'median',
            \GRADE_AGGREGATE_MIN => 'min',
            \GRADE_AGGREGATE_MAX => 'max',
            \GRADE_AGGREGATE_MODE => 'mode',
            \GRADE_AGGREGATE_WEIGHTED_MEAN => 'weighted_mean',
            \GRADE_AGGREGATE_WEIGHTED_MEAN2 => 'simple_weighted_mean',
            \GRADE_AGGREGATE_SUM => 'mean', // Natural does the same thing as mean.
        ];
        if (array_key_exists($aggregationid, $lookup)) {
            $agf = $lookup[$aggregationid];
        } else {
            throw new \moodle_exception('Unknown or unsupported aggregation strategy');
        }

        // TODO - force everything to me mean for testing, for now.

        return "strategy_" .$agf;
    }

    /**
     * Establish the maximum grade according to $atype (the aggregated type)
     */
    protected function get_max_grade() {
        if (($this->atype == \local_gugrades\GRADETYPE_SCHEDULEA) || ($this->atype == \local_gugrades\GRADETYPE_SCHEDULEB)) {
            return 22;
        }
        if ($this->atype == \local_gugrades\GRADETYPE_POINTS) {
            return 100;
        }

        // If we get here, $atype was presumably ERROR (or something we don't know about).
        throw new \moodle_exception('Unhandled aggregation type - ' . $this->atype);
    }

    /**
     * Strategy - mean of grades
     * @param array $items
     * @return float
     */
    public function strategy_mean(array $items) {
        $sum = 0.0;
        $count = 0;
        $maxgrade = $this->get_max_grade();
        foreach ($items as $item) {
            $sum += $item->grade / $item->grademax;
            $count++;
        }

        return $this->round_float($sum * $maxgrade / $count);
    }

    /**
     * Strategy - weighted mean of grades
     * @param array $items
     * @return float
     */
    public function strategy_weighted_mean(array $items) {
        $sum = 0.0;
        $count = 0;
        $sumweights = 0;
        $maxgrade = $this->get_max_grade();
        foreach ($items as $item) {
            $sum += $item->weight * $item->grade / $item->grademax;
            $sumweights += $item->weight;
            $count++;
        }

        return $this->round_float($sum * $maxgrade / $sumweights);
    }

    /**
     * Strategy - simple weighted mean of grades
     * (Essentially - sum of grades divided by sum of max grades)
     * @param array $items
     * @return float
     */
    public function strategy_simple_weighted_mean(array $items) {
        $sum = 0.0;
        $sumgrademax = 0;
        $maxgrade = $this->get_max_grade();
        foreach ($items as $item) {
            $sum += $item->grade;
            $sumgrademax += $item->grademax;
        }

        return $this->round_float($maxgrade * $sum / $sumgrademax);
    }

    /**
     * Strategy - minimum grade
     * Note that the normalised percentage grade is returned
     * @param array $items
     * @return float
     */
    public function strategy_min(array $items) {
        $grades = [];
        $maxgrade = $this->get_max_grade();
        foreach ($items as $item) {
            $norm = $maxgrade * $item->grade / $item->grademax;
            $grades[] = $norm;
        }

        return $this->round_float(min($grades));
    }

    /**
     * Strategy - maximum grade
     * Note that the normalised percentage grade is returned
     * @param array $items
     * @return float
     */
    public function strategy_max(array $items) {
        $grades = [];
        $maxgrade = $this->get_max_grade();
        foreach ($items as $item) {
            $norm = $maxgrade * $item->grade / $item->grademax;
            $grades[] = $norm;
        }

        return $this->round_float(max($grades));
    }

    /**
     * Strategy - median grade
     * Note that the normalised percentage grade is returned
     * @param array $items
     * @return float
     */
    public function strategy_median(array $items) {
        $grades = [];
        $maxgrade = $this->get_max_grade();
        foreach ($items as $item) {
            $norm = $maxgrade * $item->grade / $item->grademax;
            $grades[] = $norm;
        }

        sort($grades);

        // If odd number of grades it's just the middle value.
        $medianindex = count($grades) / 2;
        $roundindex = round($medianindex);
        if ($roundindex != $medianindex) {
            return $this->round_float($grades[$medianindex]);
        } else {

            // It's the mean of the two middle values.
            $midh = $grades[$roundindex];
            $midl = $grades[$roundindex - 1];

            return $this->round_float(($midh + $midl) / 2);
        }
    }

    /**
     * Strategy - median mode
     * Note that the normalised percentage grade is returned
     * This is rounded to int - which makes sense to me!
     * @param array $items
     * @return float
     */
    public function strategy_mode(array $items) {
        $grades = [];
        $maxgrade = $this->get_max_grade();
        foreach ($items as $item) {
            $norm = round($maxgrade * $item->grade / $item->grademax);
            $grades[] = (int)$norm;
        }

        // Witchcraft!
        $values = array_count_values($grades);
        $mode = array_search(max($values), $values);

        return $this->round_float($mode);
    }

    /**
     * Convert numeric 0-22 to Schedule A
     * @param float $rawgrade
     * @return [string, int]
     */
    protected function convert_schedulea(float $rawgrade) {
        $schedulea = \local_gugrades\mapping\schedulea::get_map();

        // This MATTERS - round the float rawgrade to an integer
        // "15.5 and all higher values less than 16.5 should become 16
        // [Guide to code of assessment].
        $grade = round($rawgrade, 0, PHP_ROUND_HALF_UP);

        if (!array_key_exists($grade, $schedulea)) {
            throw new \moodle_exception('Raw grade out of valid range - ' . $rawgrade);
        }

        return [$schedulea[$grade], $grade];
    }

    /**
     * Convert numeric 0-22 to Schedule B
     * @param float $rawgrade
     * @return [string, int]
     */
    protected function convert_scheduleb(float $rawgrade) {
        return \local_gugrades\mapping\scheduleb::convert($rawgrade);
    }

    /**
     * Convert float grade to Schedule A / B
     * @param float $rawgrade
     * @param string $atype
     * @return [string, int]
     */
    public function convert($rawgrade, $atype) {
        if ($atype == \local_gugrades\GRADETYPE_SCHEDULEA) {
            return $this->convert_schedulea($rawgrade);
        } else if ($atype == \local_gugrades\GRADETYPE_SCHEDULEB) {
            return $this->convert_scheduleb($rawgrade);
        } else {
            throw new \moodle_exception('Invalid atype - ' . $atype);
        }
    }

    /**
     * Which grade is 'passed up' from aggregation when converting to scale
     * The 'raw' grade or the graded point after conversion?
     * This is here in case there are different views about this
     * See MGU-821
     * @param float $rawgrade
     * @param int $gradepoint
     * @return float|int
     */
    public function get_grade_for_parent(float $rawgrade, int $gradepoint) {

        // Finger in the air - and use $gradepoint. If you want raw grade
        // just return the other value.
        // Decided - Grade Point it is.
        return $gradepoint;
    }

    /**
     * Format displaygrade for Schedule A / B
     * Depends on completion (<75% or not)
     * @param string $convertedgrade
     * @param float $rawgrade
     * @param float $gradepoint
     * @param int $completion
     * @param int $level
     * @return string
     */
    public function format_displaygrade(string $convertedgrade, float $rawgrade, float $gradepoint, int $completion, int $level) {

        // If >level 1, then we always return the combination (no 75% rule).
        if ($level > 1) {
            return $convertedgrade;
        }

        // Must be level 1, so grade displayed depends on completion %age.
        if ($completion > 75) {
            return $convertedgrade . " ($rawgrade)";
        } else {
            return "$rawgrade";
        }
    }
}
