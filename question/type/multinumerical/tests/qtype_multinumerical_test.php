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
 * ${PLUGINNAME} file description here.
 *
 * @package    ${PLUGINNAME}
 * @copyright  2023 alexandn <nicolas.alexandropoulos@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CFG;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/type/multinumerical/question.php');

class qtype_multinumerical_test extends advanced_testcase
{
    protected $question;

    protected function setUp(): void
    {
        $this->question = (new qtype_multinumerical_question);
        $this->question->usecolorforfeedback = false;
    }

    public function test_check_condition_equals()
    {
        $values = null;
        self::assertTrue($this->question->check_condition('X + Y = 12', $values, ['answer_X' => '6', 'answer_Y' => '6']));
        self::assertFalse($this->question->check_condition('X + Y = 11', $values, ['answer_X' => '6', 'answer_Y' => '6']));
        self::assertTrue($this->question->check_condition('X + Y = 0', $values, ['answer_X' => '-5', 'answer_Y' => '5']));
        self::assertTrue($this->question->check_condition('X + Y = -1', $values, ['answer_X' => '-5', 'answer_Y' => '4']));
        self::assertTrue($this->question->check_condition('X = 0', $values, ['answer_X' => '0']));
    }

    public function test_check_condition_superior()
    {
        $values = null;
        self::assertTrue($this->question->check_condition('X > 5', $values, ['answer_X' => '6']));
        self::assertTrue($this->question->check_condition('5 > X', $values, ['answer_X' => '3']));
        self::assertFalse($this->question->check_condition('5 > X', $values, ['answer_X' => '6']));
        self::assertTrue($this->question->check_condition('X + Y > 5', $values, ['answer_X' => '6', 'answer_Y' => '1']));
        self::assertTrue($this->question->check_condition('X > -2', $values, ['answer_X' => '-1']));
        self::assertTrue($this->question->check_condition('X > -2', $values, ['answer_X' => '0']));
        self::assertFalse($this->question->check_condition('X > -2', $values, ['answer_X' => '-3']));
    }

    public function test_check_conditions_inf()
    {
        $values = null;
        self::assertTrue($this->question->check_condition('X < 5', $values, ['answer_X' => '4']));
        self::assertTrue($this->question->check_condition('X < -1', $values, ['answer_X' => '-4']));
        self::assertTrue($this->question->check_condition('X < 1', $values, ['answer_X' => '0']));
        self::assertFalse($this->question->check_condition('X < 5', $values, ['answer_X' => '5']));
    }

    public function test_check_conditions_inf_equal()
    {
        $values = null;
        self::assertTrue($this->question->check_condition('X <= 5', $values, ['answer_X' => '5']));
    }

    public function test_check_conditions_superior_equal()
    {
        $values = null;
        self::assertTrue($this->question->check_condition('X >= 5', $values, ['answer_X' => '5']));
    }

    public function test_check_conditions_different()
    {
        $values = null;
        self::assertTrue($this->question->check_condition('X != 5', $values, ['answer_X' => '4']));
        self::assertTrue($this->question->check_condition('X + Y != 5', $values, ['answer_X' => '4', 'answer_Y' => '2']));
        self::assertFalse($this->question->check_condition('X + Y != 5', $values, ['answer_X' => '4', 'answer_Y' => '1']));
        self::assertFalse($this->question->check_condition('X != 5', $values, ['answer_X' => '5']));
        self::assertFalse($this->question->check_condition('X != -5', $values, ['answer_X' => '-5']));
        self::assertFalse($this->question->check_condition('X != 0', $values, ['answer_X' => '0']));
    }

}

