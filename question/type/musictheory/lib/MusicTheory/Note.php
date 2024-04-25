<?php
/* Copyright (c) 2013 Eric Brisson

  Permission is hereby granted, free of charge, to any person obtaining a copy
  of this software and associated documentation files (the "Software"), to deal
  in the Software without restriction, including without limitation the rights
  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
  copies of the Software, and to permit persons to whom the Software is
  furnished to do so, subject to the following conditions:

  The above copyright notice and this permission notice shall be included in
  all copies or substantial portions of the Software.

  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
  THE SOFTWARE. */

/**
 * This file defines the note class.
 *
 * @package MusicTheory
 */

/**
 * This class represents a musical note.
 */
class Note {

	/** @var integer Unique id for a given pitch class in a given register. */
	public $id;

	/** @var integer Represents the note's letter name (A=0, B=1, etc.). */
	private $letter;

	/**
	 * Constructor.
	 *
	 * @param string $letterName The note's letter name (/[a-gA-g]/).
	 * @param string $accidentalStr The note's accidental (/(n|#|b|x|bb)/).
	 * @param integer $register Optional. The note's register (/[1-8]/). The
	 * default is 4.
	 * @return void
	 */
	public function __construct($letterName, $accidentalStr, $register = 4) {

		$this->letter = $this->letterToInt($letterName);
		$accint = $this->accidentalToInt($accidentalStr);
		$this->id = $this->getPitchClass($this->letter, $accint, false) + $register * 12;
	}

	/**
	 * Returns a new note, given a note name.
	 *
	 * @param string $noteStr The note name (/^[a-gA-g](n|#|b|x|bb)[1-8]$/).
	 * @return Note
	 */
	public function toNote($noteStr) {

		$arr = $this->stringToArray($noteStr);
		return new Note($arr[0], $arr[1], $arr[2]);
	}

	/**
	 * Converts a note name string into an array of strings containing the
	 * letter name, the accidental and the register.
	 *
	 * @param string $noteStr The note name (/^[a-gA-g](n|#|b|x|bb)[1-8]$/).
	 * @return array Index 1 = letter name, Index 2 = accidental,
	 * Index 3 = register
	 */
	private function stringToArray($noteStr) {

		if (strlen($noteStr) == 0) {
			return null;
		}
		$ltr = substr($noteStr, 0, 1);
		$acc = null;
		$reg = null;
		switch (strlen($noteStr)) {
			case 2:
				$acc = '';
				$reg = substr($noteStr, 1, 1);
				break;
			case 3:
				$acc = substr($noteStr, 1, 1);
				$reg = substr($noteStr, 2, 1);
				break;
			case 4:
				$acc = substr($noteStr, 1, 2);
				$reg = substr($noteStr, 3, 1);
				break;
		}
		return array($ltr, $acc, $reg);
	}

	/**
	 * Returns the note's accidental.
	 *
	 * @param integer $id The note's unique integer id.
	 * @param integer $letter The note's letter name (A=0, B=1, etc.).
	 * @param integer $register The note's register ([1-8]).
	 * @return string (/(n|#|b|x|bb)/)
	 */
	private function accidental($id, $letter, $register) {
		return $id - ($this->getPitchClass($letter, 0, false) + $register * 12);
	}

	/**
	 * Returns the note's register.
	 *
	 * @param integer $id The note's unique integer id.
	 * @param integer $letter The note's letter name (A=0, B=1, etc.).
	 * @return integer (/[1-8]/)
	 */
	private function register($id, $letter) {

		if (($letter == 0) && (($id % 12) > 2)) {
			return floor($id / 12) + 1;
		} else if ($letter == $this->letterToInt('b') && ($id % 12) < 9) {
			return floor($id / 12) - 1;
		} else {
			return floor($id / 12);
		}
	}

	/**
	 * Alter the note's accidental.
	 *
	 * @param integer $alterDelta The number of half steps to alter the
	 * note by (between -2 and 2). A value of 0 leaves the note unchanged.
	 * @return void
	 */
	public function alter($alterDelta) {
		$this->id += $alterDelta;
	}

	/* INTERVAL FUNCTIONS */

	/**
	 * Given a second note, return the simple interval formed by the two
	 * notes.
	 *
	 * If the interval formed by the two notes is composite, the simple
	 * for of the interval is returned (e.g. a Perfect 12th would be
	 * returned as a Perfect 5th).
	 *
	 * @param Note $n The second note forming the interval with this note.
	 * @return Interval
	 */
	public function getSimpleIntervalFromNote($n) {
		$sizeDiff = $this->getSizeDifferential($n);

		while ($sizeDiff > 8) {
			$sizeDiff -= 7;
		}

		$HSDistance = $this->getHalfStepDistance($n);
		while ($HSDistance > 12) {
			$HSDistance -= 12;
		}

		$intval1 = new Interval('+', 'P', 5);
		$quality = $intval1->getQualityFromHSDelta($sizeDiff, $HSDistance);
		$intval2 = new Interval('+', $quality, $sizeDiff);
		return $intval2;
	}

	/**
	 * Given an interval, returns the second note that builds this interval
	 * on this note.
	 *
	 * @param Interval $intval The interval to build on this note.
	 * @return Note|null Returns null if the interval cannot be built on
	 * this note.
	 */
	public function getNoteFromInterval($intval) {
		$newNoteID = $this->id + $intval->getIntHSDelta();
		$sizeDifferential = $intval->getSize() - 1;

		if ($intval->getDir() == '-') {
			$sizeDifferential = -($sizeDifferential);
		}


		$newLetter = ($this->letter + $sizeDifferential) % 7;
		if ($newLetter < 0) {
			$newLetter += 7;
		}

		$newRegister = $this->register($newNoteID, $newLetter);

		if ($this->intToAccidental(
						$this->accidental($newNoteID, $newLetter, $newRegister)) == 'error') {
			return null;
		} else {
			$ltr = $this->intToLetter($newLetter);
			$acc = $this->intToAccidental($this->accidental($newNoteID, $newLetter, $newRegister));
			return new Note($ltr, $acc, $newRegister);
		}
	}

	/**
	 * Given a second note, compute the size of the interval between this
	 * note and the second note.
	 *
	 * @param Note $note The second note.
	 * @return integer
	 */
	public function getSizeDifferential($note) {
		$lowestNote = null;
		$highestNote = null;
		if ($this->greaterThanLetterRegister($note->getLetter(), $note->getRegister()) <= 0) {
			$lowestNote = $this;
			$highestNote = $note;
		} else {
			$lowestNote = $note;
			$highestNote = $this;
		}
		return $this->letterToInt($highestNote->getLetter()) +
				(abs($highestNote->getRegister() -
						$lowestNote->getRegister()) - 1) * 7 +
				(8 - $this->letterToInt($lowestNote->getLetter()));
	}

	/**
	 * Given a second note, compute the number of half steps between this
	 * note and the second note.
	 *
	 * @param Note $n The second note.
	 * @return integer
	 */
	private function getHalfStepDistance($n) {
		return abs($n->id - $this->id);
	}

	/* OUTPUT FUNCTIONS */

	/**
	 * Returns a string representation of this note.
	 *
	 * This string representation contains the letter name,
	 * the accidental and the register (e.g. 'Cn4', 'G#5', etc.).
	 *
	 * @return string
	 */
	public function __toString() {

		return $this->getLetter() . $this->getAccidental() . $this->getRegister();
	}

	/**
	 * Returns a string representation of this note.
	 *
	 * This string representation contains the letter name and the accidental
	 * (e.g. 'Cn', 'G#', etc.).
	 *
	 * @return string
	 */
	public function getLetterAccidental() {

		$acc = $this->intToAccidental($this->accidental($this->id, $this->letter, $this->register($this->id, $this->letter)));
		return $this->intToLetter($this->letter) . $acc;
	}

	/**
	 * Returns a string representation of the note's letter name, as an
	 * uppercase letter.
	 *
	 * @return string
	 */
	public function getLetter() {
		return $this->intToLetter($this->letter);
	}

	/**
	 * Returns a the note's register (/[1-8/]).
	 *
	 * @return integer
	 */
	public function getRegister() {
		return $this->register($this->id, $this->letter);
	}

	/**
	 * Returns a string representation of the note's accidental (/(n|#|b|x|bb)/).
	 *
	 * @return string
	 */
	public function getAccidental() {
		$accint = $this->accidental($this->id, $this->letter, $this->register($this->id, $this->letter));
		return $this->intToAccidental($accint);
	}

	/**
	 * Returns an integer representation of the note's accidental.
	 *
	 * The mapping from integer to accidental is as follows:
	 * <ul>
	 * <li>-2: double-flat</li>
	 * <li>-1: flat</li>
	 * <li>0: natural</li>
	 * <li>1: sharp</li>
	 * <li>2: double-sharp</li>
	 * </ul>
	 *
	 * @return string
	 */
	public function getAccidentalInt() {
		return $this->accidental($this->id, $this->letter, $this->getRegister());
	}

	/* COMPARISON FUNCTIONS */

	/**
	 * Compares a second note with this one.
	 *
	 * This comparison takes into account the letter name, the accidental
	 * and the register.
	 *
	 * @param Note $note The second note to compare with.
	 * @param boolean $spellingWise If false, only the pitch classes will be
	 * compared (e.g. C# and Db would be equal). If true, the note spelling will
	 * be taken into consideration (e.g. Db would be higher than C#).
	 * @return integer Returns:
	 * <ul>
	 * <li>-1 if second note is lower than this</li>
	 * <li>0 if second note is equal to this</li>
	 * <li>1 if second note is higher than this</li>
	 * </ul>
	 */
	public function greaterThan($note, $spellingWise) {
		if ($note->id == $this->id) {
			if ($spellingWise) {
				if ($this->letterToInt($note->getLetter()) == $this->letter) {
					return 0;
				} else if (($this->letterToInt($note->getLetter()) > $this->letter &&
						$note->getRegister() == $this->getRegister()) ||
						$note->getRegister() > $this->getRegister()) {
					return 1;
				} else {
					return -1;
				}
			} else {
				return 0;
			}
		} else if ($note->id > $this->id) {
			return 1;
		} else {
			return -1;
		}
	}

	/**
	 * Compares a second note with this one.
	 *
	 * This comparison takes into account the letter name and the register
	 * only (e.g. Cn4 would be higher than Bn3, but C#4 and Cn4 would be
	 * equal).
	 *
	 * @param string $let The letter name of the second note to compare
	 * with.
	 * @param integer $regist The register of the second note to compare
	 * with.
	 * @return integer Returns:
	 * <ul>
	 * <li>-1 if second note is lower than this</li>
	 * <li>0 if second note is equal to this</li>
	 * <li>1 if second note is higher than this</li>
	 * </ul>
	 */
	public function greaterThanLetterRegister($let, $regist) {
		if ($regist == $this->getRegister()) {
			if ($this->letter == $this->letterToInt($let)) {
				return 0;
			} else if ($this->letter > $this->letterToInt($let)) {
				return 1;
			} else {
				return -1;
			}
		} else if ($this->getRegister() > $regist) {
			return 1;
		} else {
			return -1;
		}
	}

	/**
	 * Determines if this note is equal to a second note.
	 *
	 * @param Note $note The second note to compare with.
	 * @param boolean $letterRegisterOnly Optional. If true, only the
	 * letter and the register will be considered (e.g. Gn4 and G#4 will
	 * be considered equal). If false, the accidental will be taken into
	 * account (e.g. Gn4 and G#4 will not be equal). The default is false.
	 * @return integer Returns:
	 * <ul>
	 * <li>-1 if second note is lower than this</li>
	 * <li>0 if second note is equal to this</li>
	 * <li>1 if second note is higher than this</li>
	 * </ul>
	 */
	public function equals($note, $letterRegisterOnly = false) {
		if ($letterRegisterOnly) {
			if ($this->getLetter() == $note->getLetter() &&
					$this->getRegister() == $note->getRegister()) {
				return true;
			} else {
				return false;
			}
		} else {
			if ($this->id == $note->id && $this->getLetter() == $note->getLetter()) {
				return true;
			} else {
				return false;
			}
		}
	}

	/* UTILITIES */

	/**
	 * Converts a note's letter name to an integer.
	 *
	 * The conversion is as follows:
	 * <ul>
	 * <li>C = 0</li>
	 * <li>D = 1</li>
	 * <li>E = 2</li>
	 * <li>F = 3</li>
	 * <li>G = 4</li>
	 * <li>A = 5</li>
	 * <li>B = 6</li>
	 * </ul>
	 *
	 * @param string $letterName The letter name to be converted.
	 * @return integer
	 */
	private function letterToInt($letterName) {
		switch (strtolower($letterName)) {
			case 'a':
				return 5;
				break;
			case 'b':
				return 6;
				break;
			case 'c':
				return 0;
				break;
			case 'd':
				return 1;
				break;
			case 'e':
				return 2;
				break;
			case 'f':
				return 3;
				break;
			case 'g':
				return 4;
				break;
			default:
				return -1;
		}
	}

	/**
	 * Converts a note letter's integer representation to a string.
	 *
	 * The conversion is as follows:
	 * <ul>
	 * <li>0 = A</li>
	 * <li>1 = B</li>
	 * <li>2 = C</li>
	 * <li>3 = D</li>
	 * <li>4 = F</li>
	 * <li>5 = G</li>
	 * <li>6 = A</li>
	 * </ul>
	 *
	 * @param integer $letterInt The letter name as integer to be
	 * converted to a string.
	 * @return string
	 */
	private function intToLetter($letterInt) {
		switch ($letterInt) {
			case 0:
				return 'C';
				break;
			case 1:
				return 'D';
				break;
			case 2:
				return 'E';
				break;
			case 3:
				return 'F';
				break;
			case 4:
				return 'G';
				break;
			case 5:
				return 'A';
				break;
			case 6:
				return 'B';
				break;
			default:
				return 'Error';
		}
	}

	/**
	 * Converts the string representation of an accidentsl to an integer.
	 *
	 * The conversion is as follows:
	 * <ul>
	 * <li>bb = -2</li>
	 * <li>b = -1</li>
	 * <li>n = 0</li>
	 * <li># = 1</li>
	 * <li>x = 2</li>
	 * </ul>
	 *
	 * @param string $accidentalStr The accidental to be converted.
	 * @return integer
	 */
	private function accidentalToInt($accidentalStr) {
		switch ($accidentalStr) {
			case 'n':
				return 0;
				break;
			case '#':
				return 1;
				break;
			case 'x':
				return 2;
				break;
			case 'b':
				return -1;
				break;
			case 'bb':
				return -2;
				break;
			default:
				return -3;
		}
	}

	/**
	 * Converts an accidental's integer representation to a string.
	 *
	 * The conversion is as follows:
	 * <ul>
	 * <li>-2 = bb</li>
	 * <li>-1 = b</li>
	 * <li>0 = n</li>
	 * <li>1 = #</li>
	 * <li>2 = x</li>
	 * </ul>
	 *
	 * @param integer $accidentalInt The accidentsl as integer to be
	 * converted to a string.
	 * @return string
	 */
	private function intToAccidental($accidentalInt) {

		switch ($accidentalInt) {
			case 0:
				return 'n';
				break;
			case 1:
				return '#';
				break;
			case 2:
				return 'x';
				break;
			case -1:
				return 'b';
				break;
			case -2:
				return 'bb';
				break;
			default:
				return 'error';
		}
	}

	/**
	 * Returns the pitch class of a given note, as an integer.
	 *
	 * The pitch classes returned are zero-based, with C = 0 (e.g. Cn => 0,
	 * D# => 3, Fn => 5).
	 *
	 * @param string $letter The note's letter name.
	 * @param integer $accidentalInt The integer representation of the note's
	 * accidental.
	 * @param boolean $absolute If true, pitch classes outside the octave
	 * (e.g. Cb, Bx) will be returned as absolute numbers (e.g. Cb = 11,
	 * Bx = 2 rather than Cb = -1 and Bx = 13).
	 * @return integer
	 */
	private function getPitchClass($letter, $accidentalInt, $absolute) {
		$pitchClass = null;

		switch ($this->intToLetter($letter)) {
			case 'A':
				$pitchClass = 9 + $accidentalInt;
				break;
			case 'B':
				$pitchClass = 11 + $accidentalInt;
				break;
			case 'C':
				$pitchClass = 0 + $accidentalInt;
				break;
			case 'D':
				$pitchClass = 2 + $accidentalInt;
				break;
			case 'E':
				$pitchClass = 4 + $accidentalInt;
				break;
			case 'F':
				$pitchClass = 5 + $accidentalInt;
				break;
			case 'G':
				$pitchClass = 7 + $accidentalInt;
				break;
			default:
				$pitchClass = 0;
		}

		if ($absolute) {
			if ($pitchClass < 0) {
				$pitchClass += 12;
			} else if ($pitchClass > 11) {
				$pitchClass -= 12;
			}
		}

		return $pitchClass;
	}

	/**
	 * Returns the pitch class and register of the given note.
	 *
	 * The pitch classes returned are zero-based, with C = 0 (e.g. Cn => 0,
	 * D# => 3, Fn => 5).
	 *
	 * @return array An array with two indices, 'pitchclass' and 'register'.
	 */
	public function getPitchClassKeyboardRegister() {
		$pitchclass = null;
		switch ($this->getLetter()) {
			case 'C':
				$pitchclass = 0;
				break;
			case 'D':
				$pitchclass = 2;
				break;
			case 'E':
				$pitchclass = 4;
				break;
			case 'F':
				$pitchclass = 5;
				break;
			case 'G':
				$pitchclass = 7;
				break;
			case 'A':
				$pitchclass = 9;
				break;
			case 'B':
				$pitchclass = 11;
				break;
		}

		switch ($this->getAccidental()) {
			case '#':
				$pitchclass += 1;
				break;
			case 'b':
				$pitchclass -= 1;
				break;
			case 'x':
				$pitchclass += 2;
				break;
			case 'bb':
				$pitchclass -= 2;
				break;
		}

		$reg = $this->getRegister();
		if ($pitchclass > 11) {
			$pitchclass -= 12;
			$reg += 1;
		} else if ($pitchclass < 0) {
			$pitchclass += 12;
			$reg -= 1;
		}

		return array('pitchclass' => (int) $pitchclass, 'register'	 => (int) $reg);
	}

}