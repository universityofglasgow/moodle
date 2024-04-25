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
 * This file defines the Tonality class.
 *
 * @package MusicTheory
 */

/**
 * This class represents a music tonality or key.
 */
class Tonality {

	/** @Note The tonic of the key. */
	private $tonic;

	/** @boolean If true, the key is major. Otherwise, the key is minor. */
	private $major;

	/**
	 * Constructor.
	 *
	 * @param Note $tonic The tonic of the key.
	 * @param boolean $isMajor If true, the key is major. Otherwise,
	 * the key is minor.
	 * @return void
	 */
	public function __construct($tonic, $isMajor) {
		$this->tonic = $tonic;
		$this->major = $isMajor;
	}

	/**
	 * Returns the tonic of the key.
	 *
	 * @return Note
	 */
	public function getTonic() {
		return $this->tonic;
	}

	/**
	 * Specifies whether the arguments passed at instantiation time
	 * produce a valid key (i.e. G major - an invalid key could be
	 * G# major).
	 *
	 * @return boolean
	 */
	public function isValidKey() {

		$mode = ($this->isMajor()) ? 'M' : 'm';
		$tonicLetter = $this->tonic->getLetter();
		$tonicAcc = $this->tonic->getAccidental();

		if (
				(
				($mode === 'M') &&
				(
				(($tonicLetter === 'C') && ($tonicAcc === 'n')) ||
				(($tonicLetter === 'G') && ($tonicAcc === 'n')) ||
				(($tonicLetter === 'D') && ($tonicAcc === 'n')) ||
				(($tonicLetter === 'A') && ($tonicAcc === 'n')) ||
				(($tonicLetter === 'E') && ($tonicAcc === 'n')) ||
				(($tonicLetter === 'B') && ($tonicAcc === 'n')) ||
				(($tonicLetter === 'F') && ($tonicAcc === '#')) ||
				(($tonicLetter === 'C') && ($tonicAcc === '#')) ||
				(($tonicLetter === 'F') && ($tonicAcc === 'n')) ||
				(($tonicLetter === 'B') && ($tonicAcc === 'b')) ||
				(($tonicLetter === 'E') && ($tonicAcc === 'b')) ||
				(($tonicLetter === 'A') && ($tonicAcc === 'b')) ||
				(($tonicLetter === 'D') && ($tonicAcc === 'b')) ||
				(($tonicLetter === 'G') && ($tonicAcc === 'b')) ||
				(($tonicLetter === 'C') && ($tonicAcc === 'b'))
				) ||
				(
				($mode === 'm') &&
				(
				(($tonicLetter === 'A') && ($tonicAcc === 'n')) ||
				(($tonicLetter === 'E') && ($tonicAcc === 'n')) ||
				(($tonicLetter === 'B') && ($tonicAcc === 'n')) ||
				(($tonicLetter === 'F') && ($tonicAcc === '#')) ||
				(($tonicLetter === 'C') && ($tonicAcc === '#')) ||
				(($tonicLetter === 'G') && ($tonicAcc === '#')) ||
				(($tonicLetter === 'D') && ($tonicAcc === '#')) ||
				(($tonicLetter === 'A') && ($tonicAcc === '#')) ||
				(($tonicLetter === 'D') && ($tonicAcc === 'n')) ||
				(($tonicLetter === 'G') && ($tonicAcc === 'n')) ||
				(($tonicLetter === 'C') && ($tonicAcc === 'n')) ||
				(($tonicLetter === 'F') && ($tonicAcc === 'n')) ||
				(($tonicLetter === 'B') && ($tonicAcc === 'b')) ||
				(($tonicLetter === 'E') && ($tonicAcc === 'b')) ||
				(($tonicLetter === 'A') && ($tonicAcc === 'b'))
				)))) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Indicates whether $this key is major.
	 *
	 * @return boolean
	 */
	public function isMajor() {
		return $this->major;
	}

	/**
	 * Given a second key, indicate whether $this key is equal to the
	 * second key.
	 *
	 * The comparison involves both the tonic and the mode.
	 *
	 * @param Tonality $tonality The second key to be compared to $this
	 * key.
	 * @return boolean
	 */
	public function equals($tonality) {
		if ($tonality->getTonic()->equals($this->tonic) && $tonality->isMajor() === $this->major) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns a string representation of the key.
	 *
	 * The representation includes the tonic's letter name and accidental,
	 * and the mode (e.g. 'Gn major', 'F# minor').
	 *
	 * @return string
	 */
	public function __toString() {
		$mode = ($this->isMajor()) ? 'major' : 'minor';
		return ((string) $this->getTonic()->getLetterAccidental()) . ' ' . $mode;
	}

	/**
	 * Given a clef, returns the key signature for $this key.
	 *
	 * @param string $clef The given clef ('treble', 'bass', 'alto' or
	 * 'tenor').
	 * @return KeySignature
	 */
	public function getKeySignature($clef) {
		return new KeySignature($this->getTonic(), $this->isMajor(), $clef);
	}

	/**
	 * Given a mode, returns all existing keys in $this mode.
	 *
	 * @param string $mode The given mode ('M' [major], 'm' [minor]).
	 * @return Tonality[]
	 */
	public static function getValidKeys($mode) {
		$arr = array();

		if ($mode === 'M') {
			array_push($arr, new Tonality(new Note('C', 'n', 4), true));
			array_push($arr, new Tonality(new Note('G', 'n', 4), true));
			array_push($arr, new Tonality(new Note('D', 'n', 4), true));
			array_push($arr, new Tonality(new Note('A', 'n', 4), true));
			array_push($arr, new Tonality(new Note('E', 'n', 4), true));
			array_push($arr, new Tonality(new Note('B', 'n', 4), true));
			array_push($arr, new Tonality(new Note('F', '#', 4), true));
			array_push($arr, new Tonality(new Note('C', '#', 4), true));
			array_push($arr, new Tonality(new Note('F', 'n', 4), true));
			array_push($arr, new Tonality(new Note('B', 'b', 4), true));
			array_push($arr, new Tonality(new Note('E', 'b', 4), true));
			array_push($arr, new Tonality(new Note('A', 'b', 4), true));
			array_push($arr, new Tonality(new Note('D', 'b', 4), true));
			array_push($arr, new Tonality(new Note('G', 'b', 4), true));
			array_push($arr, new Tonality(new Note('C', 'b', 4), true));
		} else if ($mode === 'm') {

			array_push($arr, new Tonality(new Note('A', 'n', 4), false));
			array_push($arr, new Tonality(new Note('E', 'n', 4), false));
			array_push($arr, new Tonality(new Note('B', 'n', 4), false));
			array_push($arr, new Tonality(new Note('F', '#', 4), false));
			array_push($arr, new Tonality(new Note('C', '#', 4), false));
			array_push($arr, new Tonality(new Note('G', '#', 4), false));
			array_push($arr, new Tonality(new Note('D', '#', 4), false));
			array_push($arr, new Tonality(new Note('A', '#', 4), false));
			array_push($arr, new Tonality(new Note('D', 'n', 4), false));
			array_push($arr, new Tonality(new Note('G', 'n', 4), false));
			array_push($arr, new Tonality(new Note('C', 'n', 4), false));
			array_push($arr, new Tonality(new Note('F', 'n', 4), false));
			array_push($arr, new Tonality(new Note('B', 'b', 4), false));
			array_push($arr, new Tonality(new Note('E', 'b', 4), false));
			array_push($arr, new Tonality(new Note('A', 'b', 4), false));
		}

		return $arr;
	}

}