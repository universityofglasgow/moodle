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
 * This file defines the interval class.
 *
 * @package MusicTheory
 */

/**
 * This class represents a musical interval.
 */
class Interval {

	/** @var integer The size of the interval (between 2 and 13. */
	private $size;

	/** @var string The quality of the interval ('M', 'm', 'P', 'A', 'D'). */
	private $quality;

	/** @var string The direction of the interval ('+' [above],
	  '-' [below]). */
	private $dir;

	/**
	 * Constructor.
	 *
	 * @param string $dir The direction of the interval ('+' [above],
	  '-' [below]).
	 * @param string $quality The quality of the interval
	 * ('M', 'm', 'P', 'A', 'D').
	 * @param integer $size The size of the interval (between 2 and 13).
	 * @return void
	 */
	public function __construct($dir, $quality, $size) {
		$this->dir = $dir;
		$this->quality = $quality;
		$this->size = $size;
	}

	/**
	 * Returns the size of the interval.
	 *
	 * @return integer
	 */
	public function getSize() {
		return $this->size;
	}

	/**
	 * Returns the direction of the interval.
	 *
	 * @return string '+' [above] or '-' [below].
	 */
	public function getDir() {
		return $this->dir;
	}

	/**
	 * Returns the quality of the interval.
	 *
	 * @return string 'M', 'm', 'P', 'A' or 'D'.
	 */
	public function getQuality() {
		return $this->quality;
	}

	/**
	 * Returns a string representation of the interval.
	 *
	 * The representation contains the direction, the quality and the
	 * size (e.g. '+P5' [perfect fifth above]).
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->dir . $this->quality . $this->size;
	}

	/**
	 * Indicates whether the interval is augmented or diminished.
	 *
	 * @return boolean
	 */
	public function isAugmentedOrDiminished() {
		return ($this->quality == 'A' || $this->quality == 'D');
	}

	/**
	 * Given an interval size and the number of half steps between two
	 * notes, returns the quality of the interval between the two notes.
	 *
	 * For example, if the size of the interval is 5 and the number of
	 * half steps between the two notes is 8, the quality of the interval
	 * will be augmented.
	 *
	 * @param integer $size Between 2 and 13.
	 * @param integer $dlta The number of half steps between the two notes
	 * (between -1 and 22).
	 * @return string 'M', 'm', 'P', 'A' or 'D'.
	 */
	public function getQualityFromHSDelta($size, $dlta) {
		$this->quality = '';

		switch ($size) {
			case 1 :
				if ($dlta == 0) {
					$dlta = 0;
					$this->quality = 'P';
				} else if ($dlta == -1) {
					$this->quality = 'D';
				} else if ($dlta == 1) {
					$this->quality = 'A';
				}
				break;

			case 2 :
				if ($dlta == 2) {
					$this->quality = 'M';
				} else if ($dlta == 1) {
					$this->quality = 'm';
				} else if ($dlta == 3) {
					$this->quality = 'A';
				} else if ($dlta == 0) {
					$this->quality = 'D';
				}
				break;

			case 3 :
				if ($dlta == 4) {
					$this->quality = 'M';
				} else if ($dlta == 3) {
					$this->quality = 'm';
				} else if ($dlta == 5) {
					$this->quality = 'A';
				} else if ($dlta == 2) {
					$this->quality = 'D';
				}
				break;

			case 4 :
				if ($dlta == 5) {
					$this->quality = 'P';
				} else if ($dlta == 4) {
					$this->quality = 'D';
				} else if ($dlta == 6) {
					$this->quality = 'A';
				}
				break;

			case 5 :
				if ($dlta == 7) {
					$this->quality = 'P';
				} else if ($dlta == 6) {
					$this->quality = 'D';
				} else if ($dlta == 8) {
					$this->quality = 'A';
				}
				break;

			case 6 :
				if ($dlta == 9) {
					$this->quality = 'M';
				} else if ($dlta == 8) {
					$this->quality = 'm';
				} else if ($dlta == 10) {
					$this->quality = 'A';
				} else if ($dlta == 7) {
					$this->quality = 'D';
				}
				break;

			case 7 :
				if ($dlta == 11) {
					$this->quality = 'M';
				} else if ($dlta == 10) {
					$this->quality = 'm';
				} else if ($dlta == 12) {
					$this->quality = 'A';
				} else if ($dlta == 9) {
					$this->quality = 'D';
				}
				break;

			case 8 :
				if ($dlta == 12) {
					$this->quality = 'P';
				} else if ($dlta == 11) {
					$this->quality = 'D';
				} else if ($dlta == 13) {
					$this->quality = 'A';
				}
				break;

			case 9 :
				if ($dlta == 14) {
					$this->quality = 'M';
				} else if ($dlta == 13) {
					$this->quality = 'm';
				} else if ($dlta == 15) {
					$this->quality = 'A';
				} else if ($dlta == 12) {
					$this->quality = 'D';
				}
				break;

			case 10 :
				if ($dlta == 16) {
					$this->quality = 'M';
				} else if ($dlta == 15) {
					$this->quality = 'm';
				} else if ($dlta == 17) {
					$this->quality = 'A';
				} else if ($dlta == 14) {
					$this->quality = 'D';
				}
				break;

			case 11 :
				if ($dlta == 17) {
					$this->quality = 'P';
				} else if ($dlta == 16) {
					$this->quality = 'D';
				} else if ($dlta == 18) {
					$this->quality = 'A';
				}
				break;

			case 12 :
				if ($dlta == 19) {
					$this->quality = 'P';
				} else if ($dlta == 18) {
					$this->quality = 'D';
				} else if ($dlta == 20) {
					$this->quality = 'A';
				}
				break;

			case 13 :
				if ($dlta == 21) {
					$this->quality = 'M';
				} else if ($dlta == 20) {
					$this->quality = 'm';
				} else if ($dlta == 22) {
					$this->quality = 'A';
				} else if ($dlta == 19) {
					$this->quality = 'D';
				}

				break;

			default :
				$this->quality = ''
				;
		}

		return $this->quality;
	}

	/**
	 * Returns the number of half steps between two notes that are this
	 * interval apart.
	 *
	 * For example, the number of half steps between two notes that are
	 * a perfect 5th apart will be 7.
	 *
	 * @return integer
	 */
	public function getIntHSDelta() {

		$dlta = 0;

		switch ($this->size) {
			case 1 :
				if ($this->quality == 'P') {
					$dlta = 0;
				} else if ($this->quality == 'D') {
					$dlta = -1;
				} else if ($this->quality == 'A') {
					$dlta = 1;
				}
				break;

			case 2 :
				if ($this->quality == 'M') {
					$dlta = 2;
				} else if ($this->quality == 'm') {
					$dlta = 1;
				} else if ($this->quality == 'A') {
					$dlta = 3;
				} else if ($this->quality == 'D') {
					$dlta = 0;
				}

				break;

			case 3 :
				if ($this->quality == 'M') {
					$dlta = 4;
				} else if ($this->quality == 'm') {
					$dlta = 3;
				} else if ($this->quality == 'A') {
					$dlta = 5;
				} else if ($this->quality == 'D') {
					$dlta = 2;
				}
				break;

			case 4 :
				if ($this->quality == 'P') {
					$dlta = 5;
				} else if ($this->quality == 'D') {
					$dlta = 4;
				} else if ($this->quality == 'A') {
					$dlta = 6;
				}
				break;

			case 5 :
				if ($this->quality == 'P') {
					$dlta = 7;
				} else if ($this->quality == 'D') {
					$dlta = 6;
				} else if ($this->quality == 'A') {
					$dlta = 8;
				}
				break;

			case 6 :
				if ($this->quality == 'M') {
					$dlta = 9;
				} else if ($this->quality == 'm') {
					$dlta = 8;
				} else if ($this->quality == 'A') {
					$dlta = 10;
				} else if ($this->quality == 'D') {
					$dlta = 7;
				}

				break;

			case 7 :
				if ($this->quality == 'M') {
					$dlta = 11;
				} else if ($this->quality == 'm') {
					$dlta = 10;
				} else if ($this->quality == 'A') {
					$dlta = 12;
				} else if ($this->quality == 'D') {
					$dlta = 9;
				}

				break;

			case 8 :
				if ($this->quality == 'P') {
					$dlta = 12;
				} else if ($this->quality == 'D') {
					$dlta = 11;
				} else if ($this->quality == 'A') {
					$dlta = 13;
				}
				break;

			case 9 :
				if ($this->quality == 'M') {
					$dlta = 14;
				} else if ($this->quality == 'm') {
					$dlta = 13;
				} else if ($this->quality == 'A') {
					$dlta = 15;
				} else if ($this->quality == 'D') {
					$dlta = 12;
				}

				break;

			case 10 :
				if ($this->quality == 'M') {
					$dlta = 16;
				} else if ($this->quality == 'm') {
					$dlta = 15;
				} else if ($this->quality == 'A') {
					$dlta = 17;
				} else if ($this->quality == 'D') {
					$dlta = 14;
				}
				break;

			case 11 :
				if ($this->quality == 'P') {
					$dlta = 17;
				} else if ($this->quality == 'D') {
					$dlta = 16;
				} else if ($this->quality == 'A') {
					$dlta = 18;
				}
				break;

			case 12 :
				if ($this->quality == 'P') {
					$dlta = 19;
				} else if ($this->quality == 'D') {
					$dlta = 18;
				} else if ($this->quality == 'A') {
					$dlta = 20;
				}
				break;

			case 13 :
				if ($this->quality == 'M') {
					$dlta = 21;
				} else if ($this->quality == 'm') {
					$dlta = 20;
				} else if ($this->quality == 'A') {
					$dlta = 22;
				} else if ($this->quality == 'D') {
					$dlta = 19;
				}

				break;

			default :
				$dlta = 0
				;
		}

		if ($this->dir == '-') {
			$dlta = -($dlta);
		}

		return $dlta;
	}

	/**
	 * Given an interval size, returns the standard quality associated
	 * with this size.
	 *
	 * For intervals of size 1, 4, 5, 8, 11 and 12, the standard quality
	 * will be 'P' [perfect]. For the other intervals, the standard quality
	 * will be 'M' [major].
	 *
	 * @param integer $size Between 2 and 13.
	 * @return string
	 */
	public static function getStandardQuality($size) {
		switch ($size) {
			case 1:
			case 4:
			case 5:
			case 8:
			case 11:
			case 12:
				return 'P';
		}
		return 'M';
	}

	/**
	 * Indicates whether the given combination of size and quality yields
	 * a valid interval.
	 *
	 * For example, 'M5' [major 5th] woudl return false, but 'P5'
	 * [perfect 5th] would return true.
	 *
	 * @param string $quality 'M', 'm', 'P', 'A' or 'D'.
	 * @param integer $size Between 2 and 13.
	 * @return boolean
	 */
	public static function valid_interval($quality, $size) {
		switch ($size) {
			case 1:
			case 4:
			case 5:
			case 8:
			case 11:
			case 12:
				switch ($quality) {
					case 'M':
					case 'm':
						return false;
				}
				break;
			case 2:
			case 3:
			case 6:
			case 7:
			case 9:
			case 10:
			case 13:
				if ($quality == 'P') {
					return false;
				}
				break;
		}
		return true;
	}

}