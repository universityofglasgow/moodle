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
 * Version information
 *
 * @package    qtype
 * @subpackage multinumerical
 * @copyright  2013 Université de Lausanne
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class qtype_multinumerical_question extends question_graded_automatically {

    public function get_expected_data() {
        $return = array();
        foreach ($this->get_parameters() as $parameter) {
            $return['answer_'.$parameter] = PARAM_RAW_TRIMMED;
        }
        return $return;
    }

    public function summarise_response(array $response) {
        $return = array();
        foreach ($this->get_parameters() as $parameter) {
            $return[] = $parameter . ' = ' . $response['answer_'.$parameter];
        }
        return implode(' ; ', $return);
    }

    public function get_parameters() {
        $parameters = explode(',', $this->parameters);
        foreach ($parameters as &$parameter) {
            $parameter = trim($parameter);
            if (preg_match('/^(\w+)/', $parameter, $matches)) {
                $parameter = $matches[1];
            }
        }
        return $parameters;
    }

    private function get_conditions() {
        $conditions = explode("\n", $this->conditions);
        foreach ($conditions as &$condition) {
            $condition = trim($condition);
        }
        return $conditions;
    }

    private function get_feedbackperconditions() {
        $feedbackperconditions = explode("\n", $this->feedbackperconditions);
        foreach ($feedbackperconditions as &$feedbackpercondition) {
            $feedbackpercondition = trim($feedbackpercondition);
        }
        return $feedbackperconditions;
    }

    public function compute_feedbackperconditions(array $response) {
    	global $CFG;
    	$score = 0;
    	$feedbackperconditions = $this->get_feedbackperconditions();
    	$conditionsfullfilled = array();
    	$feedbackperconditions_computed = array();
     	$conditions = $this->get_conditions();
    	foreach ($conditions as $conditionid => $condition) {
    		$feedbackforthiscondition = isset($feedbackperconditions[$conditionid]) ? (explode('|', $feedbackperconditions[$conditionid])) : (array('', ''));
    		$values = '';
    		if ($this->check_condition($condition, $values, $response)) {
    			$score++;
    			$conditionsfullfilled[] = 1;
    			if (strlen(trim($feedbackforthiscondition[0])) > 0) {
    				if ($this->usecolorforfeedback) {
	    				$feedbackperconditions_computed[$conditionid] = '<span style="color:#090">';
	    				$feedbackperconditions_computed[$conditionid] .= (preg_match('/(usepackage{color})/', get_config('filter_tex', 'latexpreamble'))) ? (preg_replace('/(.*)\$\$(.*)\$\$(.*)/', '${1}\$\$\\textcolor{green}{${2}}\$\$${3}', $feedbackforthiscondition[0])) : ($feedbackforthiscondition[0]);
	    				$feedbackperconditions_computed[$conditionid] .= '</span>';
    				}
    				else {
    					$feedbackperconditions_computed[$conditionid] = $feedbackforthiscondition[0];
    				}
     			}
    			else {
    				unset($feedbackperconditions[$conditionid]);
    			}
    		}
    		else {
    			$conditionsfullfilled[] = 0;
    			if (isset($feedbackforthiscondition[1]) && strlen(trim($feedbackforthiscondition[1])) > 0) {
    				if ($this->usecolorforfeedback) {
	    				$feedbackperconditions_computed[$conditionid] = '<span style="color:#f00">';
	    				$feedbackperconditions_computed[$conditionid] .= (preg_match('/(usepackage{color})/', get_config('filter_tex', 'latexpreamble'))) ? (preg_replace('/(.*)\$\$(.*)\$\$(.*)/', '${1}\$\$\\textcolor{red}{${2}}\$\$${3}', $feedbackforthiscondition[1])) : ($feedbackforthiscondition[1]);
	    				$feedbackperconditions_computed[$conditionid] .= '</span>';
    				}
    				else {
    					$feedbackperconditions_computed[$conditionid] = $feedbackforthiscondition[1];
    				}
    			}
    			else {
    				unset($feedbackperconditions[$conditionid]);
    			}
    		}
    		if ($this->displaycalc && isset($feedbackperconditions[$conditionid]) && (!preg_match('/^\s*([A-Za-z]+\d*)\s*[=|<|>].*$/', $condition, $matches) || $this->displaycalc == 1)) {
    		    $feedbackperconditions_computed[$conditionid] .= '<ul><li>'.$values.'</li></ul>';
    		}
    	}
    	$this->computedfeedbackperconditions = implode('<br /><br />', $feedbackperconditions_computed);
    	return $score;
    }

    private function check_condition($condition, &$values, $response) {
        global $CFG;
        $decsep = get_string('decsep', 'langconfig');
        $thousandssep = get_string('thousandssep', 'langconfig');
        $values = '';
        $interval = false;
        $operators = array('<=', '>=', '<', '>', '=');
        // careful with operators relative positions here, see following foreach()
        foreach ($operators as $operator) {
            $operatorposition = strpos($condition, $operator);
            if ($operatorposition !== false) {
                $conditionsides = explode($operator, $condition);
                $left = trim($conditionsides[0]);
                $right = trim($conditionsides[1]);
                break;
            }
        }
        include_once($CFG->libdir.'/evalmath/evalmath.class.php');
        $math = new EvalMath();
        $math->suppress_errors = true;
        // assigning variables values :
        foreach ($response as $param => &$value) {
            // in case someone used locale-dependant $decsep and/or $thousandssep,
            // make it machine-readable:
            $value = str_replace(' ', '', $value);
            $value = str_replace($thousandssep, '', $value);
            $value = str_replace($decsep, '.', $value);
            $value = floatval($value);
        	// EvalMath doesn't like uppercase variable names
        	$math->evaluate(strtolower(substr($param, 7)).'='.$value);
        }
        $leftvalue = $math->evaluate($left);
        if ($operator == '=') {
            $operator = '==';
            $matches = array();
            if (preg_match('/^\s*([A-Z]*[a-z]*\w*)\s*=\s*([\[|\]])(.+);(.+)([\[|\]])$/', $condition, $matches)) {
                // we're dealing with an interval
                $interval = true;
                $operator = '';
                $rightvalue = ($matches[2] == "[") ? (">=") : (">");
                $val1 = (float)$math->evaluate(strtolower($matches[3]));
                $val2 = (float)$math->evaluate(strtolower($matches[4]));
                // failsafe : EvalMath returns false instead of zero, so cast result as float
                $rightvalue .= $val1 . " && " . $leftvalue;
                $rightvalue .= ($matches[5] == "]") ? ("<=") : ("<");
                $rightvalue .= $val2;
            }
        }
        if (!$interval) {
            $rightvalue = $math->evaluate($right);
            $values .= number_format($leftvalue, 2, $decsep, $thousandssep) . ' '.$operator.' ' . number_format($rightvalue, 2, $decsep, $thousandssep);
        }
        else {
            $values .= $leftvalue . ' = ' . $matches[2] . number_format($val1, 3, $decsep, $thousandssep) . ';' . number_format($val2, 3, $decsep, $thousandssep) . $matches[5];
        }
    	if (strlen($leftvalue) > 0 && isset($operator) && strlen($rightvalue) > 0 && eval('return('.$leftvalue.$operator.$rightvalue.');')) {
    	    $valuesspan = '<span';
    	    $valuesspan .= ($this->usecolorforfeedback) ? (' style="color:#090"') : ('');
    	    $valuesspan .= '>'.get_string('conditionverified', 'qtype_multinumerical').' : '.$values.'</span>';
    	    $values = $valuesspan;
          return true;
        }
        $valuesspan = '<span';
        $valuesspan .= ($this->usecolorforfeedback) ? (' style="color:#f00"') : ('');
        $valuesspan .= '>'.get_string('conditionnotverified', 'qtype_multinumerical').' : '.$values.'</span>';
        $values = $valuesspan;
        return false;
    }

    public function is_complete_response(array $response) {
        foreach($this->get_parameters() as $param) {
            if (!array_key_exists('answer_'.$param, $response) || (!$response['answer_'.$param] && $response['answer_'.$param] !== '0')) {
                return false;
            }
        }
        return true;
    }

    public function get_validation_error(array $response) {
        if ($this->is_gradable_response($response)) {
            return '';
        }
        return get_string('pleaseenterananswer', 'qtype_multinumerical');
    }

    public function is_same_response(array $prevresponse, array $newresponse) {
        foreach($this->get_parameters() as $param) {
            if (!question_utils::arrays_same_at_key_missing_is_blank($prevresponse, $newresponse, 'answer_'.$param)) {
                return false;
            }
        }
        return true;
    }

    public function check_file_access($qa, $options, $component, $filearea,
            $args, $forcedownload) {
        if ($component == 'question' && $filearea == 'answerfeedback') {
            $currentanswer = $qa->get_last_qt_var('answer');
            $answer = $qa->get_question()->get_matching_answer(array('answer' => $currentanswer));
            $answerid = reset($args); // itemid is answer id.
            return $options->feedback && $answerid == $answer->id;

        } else if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);

        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                    $args, $forcedownload);
        }
    }

    public function get_correct_response(){
        $parameters = $this->get_parameters();
        $response = array();
        foreach ($parameters as $parameter) {
            $response['answer_'.$parameter] = get_string('noncomputable','qtype_multinumerical');
        }
        return $response;
    }

    public function grade_response(array $response){
    	$score = $this->compute_feedbackperconditions($response);
        $fraction = $score / sizeof($this->get_conditions());
        if ($this->binarygrade) {
            $fraction = floor($fraction);
        }
        return array($fraction, question_state::graded_state_for_fraction($fraction));
    }
}
