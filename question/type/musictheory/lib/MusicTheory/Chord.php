<?php
/* Copyright (c) 2013 Eric Brisson

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the 'Software'), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE. */

/**
 * This class represents a chord structure.
 */
class Chord {

	public $root;
	public $intSequence;
	public $inversion;
	public $type;

	/**
	 * Constructor.
	 *
	 * @param Note $root The root of the chord.
	 * @param string $type The type/quality of the chord.
	 * @param integer $inv The chord position (root=0, first inversion=1, etc.)
	 * @return void
	 */
	public function __construct($root, $type, $inv) {
		$this->root = $root;
		$this->type = $type;
		$this->inversion = $inv;

		$this->intSequence = array();

		switch ($this->type) {
			case 'M' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'P', 5);
				break;
			case 'm' :
				$this->intSequence[0] = new Interval('+', 'm', 3);
				$this->intSequence[1] = new Interval('+', 'P', 5);
				break;
			case 'A' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'A', 5);
				break;
			case 'D' :
				$this->intSequence[0] = new Interval('+', 'm', 3);
				$this->intSequence[1] = new Interval('+', 'D', 5);
				break;
			case 'It6' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'A', 6);
				break;
			case 'Fr6' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'A', 4);
				$this->intSequence[2] = new Interval('+', 'A', 6);
				break;
			case 'Gr6' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'P', 5);
				$this->intSequence[2] = new Interval('+', 'A', 6);
				break;
			case 'mm' :
				$this->intSequence[0] = new Interval('+', 'm', 3);
				$this->intSequence[1] = new Interval('+', 'P', 5);
				$this->intSequence[2] = new Interval('+', 'm', 7);
				break;
			case 'Mm' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'P', 5);
				$this->intSequence[2] = new Interval('+', 'm', 7);
				break;
			case 'Am' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'A', 5);
				$this->intSequence[2] = new Interval('+', 'm', 7);
				break;
			case 'MM' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'P', 5);
				$this->intSequence[2] = new Interval('+', 'M', 7);
				break;
			case 'Dm' :
				$this->intSequence[0] = new Interval('+', 'm', 3);
				$this->intSequence[1] = new Interval('+', 'D', 5);
				$this->intSequence[2] = new Interval('+', 'm', 7);
				break;
			case 'DD' :
				$this->intSequence[0] = new Interval('+', 'm', 3);
				$this->intSequence[1] = new Interval('+', 'D', 5);
				$this->intSequence[2] = new Interval('+', 'D', 7);
				break;
			case 'AM' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'A', 5);
				$this->intSequence[2] = new Interval('+', 'M', 7);
				break;
			case '7-5b' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'D', 5);
				$this->intSequence[2] = new Interval('+', 'm', 7);
				break;
			case '9' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'P', 5);
				$this->intSequence[2] = new Interval('+', 'm', 7);
				$this->intSequence[3] = new Interval('+', 'M', 9);
				break;
			case '9b' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'P', 5);
				$this->intSequence[2] = new Interval('+', 'm', 7);
				$this->intSequence[3] = new Interval('+', 'm', 9);
				break;
			case '11' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'P', 5);
				$this->intSequence[2] = new Interval('+', 'm', 7);
				$this->intSequence[3] = new Interval('+', 'M', 9);
				$this->intSequence[4] = new Interval('+', 'P', 11);
				break;
			case '11/9b' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'P', 5);
				$this->intSequence[2] = new Interval('+', 'm', 7);
				$this->intSequence[3] = new Interval('+', 'm', 9);
				$this->intSequence[4] = new Interval('+', 'P', 11);
				break;
			case '13' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'P', 5);
				$this->intSequence[2] = new Interval('+', 'm', 7);
				$this->intSequence[3] = new Interval('+', 'M', 9);
				$this->intSequence[4] = new Interval('+', 'P', 11);
				$this->intSequence[5] = new Interval('+', 'M', 13);
				break;
			case '13/9b' :
				$this->intSequence[0] = new Interval('+', 'M', 3);
				$this->intSequence[1] = new Interval('+', 'P', 5);
				$this->intSequence[2] = new Interval('+', 'm', 7);
				$this->intSequence[3] = new Interval('+', 'm', 9);
				$this->intSequence[4] = new Interval('+', 'P', 11);
				$this->intSequence[5] = new Interval('+', 'm', 13);
				break;
		}
	}

	/**
	 * Indicates whether the given note is in the chord.
	 *
	 * @param Note $n The given note.
	 * @return boolean Returns true if the note is in the chord.
	 */
	public function noteInChord($n) {
		$i = 0;
		$fac = $this->getChordFactors();
		for ($i = 0; $i < count($fac); $i++) {
			if ($fac[$i]->getLetter() === $n->getLetter() &&
					$fac[$i]->getAccidental() === $n->getAccidental()) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Given an array of notes, returns the set of chord factors not
	 * represented by the given notes.
	 *
	 * @param array $notes The given notes.
	 * @return array Returns the chord factors (as Note objects) not represented
	 * by the given set of notes.
	 */
	public function getMissingFactors($notes) {
		$i = 0;
		$j = 0;
		$fac = $this->getChordFactors();
		$missing = array();
		$noteIsThere = false;
		$n = null;
		for ($i = 0; $i < count($fac); $i++) {
			$noteIsThere = false;
			for ($j = 0; $j < count($notes); $j++) {
				if (isset($notes[$j])) {
					$n = $notes[$j];
					if ($fac[$i]->getLetter() === $n->getLetter() &&
							$fac[$i]->getAccidental() === $n->getAccidental()) {
						$noteIsThere = true;
					}
				}
			}
			if (!$noteIsThere) {
				array_push($missing, $fac[$i]);
			}
		}
		return $missing;
	}

	/**
	 * Indicates whether the chord is extended.
	 *
	 * @return boolean Returns true if the chord is extended.
	 */
	public function isExtended() {
		return (count($this->intSequence) >= 4);
	}

	/**
	 * Returns the position of the chord.
	 *
	 * @return integerThe position of the chord (0 = root position, 1 = first
	 * inversion, etc.).
	 */
	public function getPosition() {
		return $this->inversion;
	}

	/**
	 * Returns a chord factor.
	 *
	 * @param int $factor The chord factor to return (1 = root).
	 * @param int $alteration The alteration to apply to the factor (1 = sharp,
	 * -1 = flat, 0 = no alteration, 2 = double-sharp, -2 = double-flat).
	 * @return Note The requested chord factor.
	 */
	public function getChordFactor($factor, $alteration = 0) {

		$note = null;
		switch ($factor) {
			case 1 :
				$note = $this->root;
				break;
			case 3 :
				$note = $this->root->getNoteFromInterval($this->intSequence[0]);
				break;
			case 5 :
				$note = $this->root->getNoteFromInterval($this->intSequence[1]);
				break;
			case 7 :
				$note = $this->root->getNoteFromInterval($this->intSequence[2]);
				break;
			case 9 :
				$note = $this->root->getNoteFromInterval($this->intSequence[3]);
				break;
			case 11 :
				$note = $this->root->getNoteFromInterval($this->intSequence[4]);
				break;
			case 13 :
				$note = $this->root->getNoteFromInterval($this->intSequence[5]);
				break;
			default :
				return null;
		}

		if ($note !== null) {
			$note->alter($alteration);
		}

		return $note;
	}

	/**
	 * Returns all notes (chord factors) in the chord.
	 *
	 * @return array An array of note objects representing the chord factors.
	 */
	public function getChordFactors() {
		$factors = array();

		array_push($factors, $this->root);
		$fac = 3;
		$i = 0;
		for ($i = 0; $i < count($this->intSequence); $i++) {
			array_push($factors, $this->getChordFactor($fac));
			$fac+=2;
		}

		$upNote = null;
		for ($i = 0; $i < $this->inversion; $i++) {
			$upNote = array_shift($factors);
			$upNote2 = new Note($upNote->getLetter(), $upNote->getAccidental(), $upNote->getRegister() + 1);
			array_push($factors, $upNote2);
		}

		return $factors;
	}

	/**
	 * Returns the number of notes in the chord.
	 *
	 * @return integerThe number of notes in the chord.
	 */
	public function getNumNotes() {
		return count($this->intSequence) + 1;
	}

	/**
	 * Indicates whether a given chord is identical to this chord.
	 *
	 * @param Chord $chord The given chord for comparison.
	 * @param boolean $letterAccidentalOnly If true, the register will be
	 * ignored in the comparison.
	 * @return boolean True if boths chords have the same notes.
	 */
	public function equals($chord, $letterAccidentalOnly) {
		$fac1 = $this->getChordFactors();
		$fac2 = $chord->getChordFactors();

		if (count($fac1) !== count($fac2)) {
			return false;
		}

		if ($letterAccidentalOnly) {
			for ($i = 0; $i < count($fac1); $i++) {
				if ($fac1[$i]->getLetter() !== $fac2[$i]->getLetter() ||
						$fac1[$i]->getAccidental() !== $fac2[$i]->getAccidental()) {
					return false;
				}
			}
		} else {
			for ($i = 0; $i < count($fac1); $i++) {
				if ($fac1[$i]->getLetter() !== $fac2[$i]->getLetter() ||
						$fac1[$i]->getAccidental() !== $fac2[$i]->getAccidental() ||
						$fac1[$i]->getRegister() !== $fac2[$i]->getRegister()) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Returns a string representation of the chord.
	 *
	 * @return string
	 */
	public function __toString() {
		return implode(',', $this->getChordFactors());
	}

}