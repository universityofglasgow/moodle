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
 * This file defines the music staff class.
 *
 * @package MusicTheory
 */

/**
 * This class represents a music staff.
 */
class Staff {

	/** @var string The staff's clef ('treble', 'bass', 'alto' or 'tenor). */
	private $clef;

	/** @var Note The note located on the space directly above the staff. */
	private $noteSpaceAboveStaff;

	/** @var Note The note located on the space directly below the staff. */
	private $noteSpaceBelowStaff;

	/**
	 * Constructor.
	 *
	 * @param string $clef The staff's clef ('treble', 'bass', 'alto'
	 * or 'tenor).
	 * @return void
	 */
	public function __construct($clef) {
		$this->clef = $clef;
		switch ($clef) {
			case 'treble':
				$this->noteSpaceAboveStaff = new Note('G', 'n', 5);
				$this->noteSpaceBelowStaff = new Note('D', 'n', 4);
				break;
			case 'bass':
				$this->noteSpaceAboveStaff = new Note('B', 'n', 3);
				$this->noteSpaceBelowStaff = new Note('F', 'n', 2);
				break;
			case 'alto':
				$this->noteSpaceAboveStaff = new Note('A', 'n', 4);
				$this->noteSpaceBelowStaff = new Note('E', 'n', 3);
				break;
			case 'tenor':
				$this->noteSpaceAboveStaff = new Note('F', 'n', 4);
				$this->noteSpaceBelowStaff = new Note('C', 'n', 3);
				break;
		}
	}

	/**
	 * Determines whether a given note would fit within the staff, given the
	 * number of ledger lines and the clef.
	 *
	 * @param Note $note The note to check.
	 * @param integer $numLedgerLines The number of ledger lines to consider
	 * when checking if the note fits in the staff.
	 * @return boolean
	 */
	public function noteFitsInStaff($note, $numLedgerLines) {
		$size = $numLedgerLines * 2 + 1;
		$quality = Interval::getStandardQuality($size);
		$aboveInterval = new Interval('+', $quality, $size);
		$belowInterval = new Interval('-', $quality, $size);
		if ($numLedgerLines > 0) {
			$topNote = $this->noteSpaceAboveStaff->getNoteFromInterval($aboveInterval);
			$bottomNote = $this->noteSpaceBelowStaff->getNoteFromInterval($belowInterval);
		} else {
			$topNote = $this->noteSpaceAboveStaff;
			$bottomNote = $this->noteSpacebelowStaff;
		}

		if (($note->greaterThanLetterRegister($topNote->getLetter(), $topNote->getRegister()) > 0) ||
				($note->greaterThanLetterRegister($bottomNote->getLetter(), $bottomNote->getRegister()) < 0)) {
			return false;
		} else {
			return true;
		}
	}

}