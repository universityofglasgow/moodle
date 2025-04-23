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
 * This class represents a harmonic function
 */
class HarmonicFunction extends Chord {

	private $rootPrefix = '';
	private $romanNum = '';
	private $dimHalfdim = '';
	private $invExt;
	private $secondaryTonicPrefix = '';
	private $secondaryTonic = '';
	private $key;

	/**
	 * Constructor.
	 *
	 * @param Tonality $key The key associated with the harmonic function.
	 * @param string $func The harmonic function as a string representation.
	 * @param integer $reg The register where the harmonic functions root is in
	 * when in root position.
	 * @return void
	 */
	public function __construct($key, $func, $reg = 4) {

		$this->key = $key;

		$pattern = '/^(?P<rootPrefix>(#|n|bb|b|x)?)(?P<romanNum>(N|Gr|It|Fr|iii|III|ii|II|iv|IV|i|I|vii|VII|vi|VI|v|V){1})(?P<dimHalfdim>(\\x2B|o|-o)?)(?P<invExt>(64|65|6|7-5b|7|43|42|9|11|13)?)(\/(?P<secondaryTonicPrefix>(#|n|bb|b)?)(?P<secondaryTonic>(N|Gr|It|Fr|iii|III|ii|II|iv|IV|i|I|vii|VII|vi|VI|v|V){1}))?$/';
		$parseFunc = array();
		preg_match($pattern, $func, $parseFunc);
		$this->rootPrefix = isset($parseFunc['rootPrefix']) ? $parseFunc['rootPrefix'] : '';
		$this->romanNum = isset($parseFunc['romanNum']) ? $parseFunc['romanNum'] : '';
		$this->dimHalfdim = isset($parseFunc['dimHalfdim']) ? $parseFunc['dimHalfdim'] : '';
		$this->invExt = isset($parseFunc['invExt']) ? $parseFunc['invExt'] : '';
		$this->secondaryTonicPrefix = isset($parseFunc['secondaryTonicPrefix']) ? $parseFunc['secondaryTonicPrefix'] : '';
		$this->secondaryTonic = isset($parseFunc['secondaryTonic']) ? $parseFunc['secondaryTonic'] : '';

		if (!$this->isSupported()) {
			return;
		} else if ($this->secondaryTonic === '') {
			$rootIsMaj = $key->isMajor();
			$operatingKey = $key;
		} else {
			$rootIsMaj = $this->rootIsMajor($this->secondaryTonic);
			$operatingKey = new Tonality($this->getRoot($this->secondaryTonicPrefix, $this->secondaryTonic, $key), $rootIsMaj);
		}

		$root = $this->getRoot($this->rootPrefix, $this->romanNum, $operatingKey);
		$root = new Note($root->getLetter(), $root->getAccidental(), $reg);
		$type = $this->getType($this->romanNum, $this->dimHalfdim, $this->invExt, $rootIsMaj);
		$inv = $this->convertInvExtToInt($this->invExt, $this->romanNum);
		parent::__construct($root, $type, $inv);
	}

	/**
	 * Returns true if the harmonic functions built by the constructor is
	 * supported.
	 *
	 * @return boolean
	 */
	public function isSupported() {
		if ($this->isDiatonicTriad()) {
			return true;
		} else if ($this->isDominantSeventh()) {
			return true;
		} else if ($this->isNonDominantSeventh()) {
			return true;
		} else if ($this->isLeadingTone7th('halfdim')) {
			return true;
		} else if ($this->isLeadingTone7th('fullydim')) {
			return true;
		} else if ($this->isSecDom('triad')) {
			return true;
		} else if ($this->isSecDom('7th')) {
			return true;
		} else if ($this->isSecNonDom('triad')) {
			return true;
		} else if ($this->isSecNonDom('7th')) {
			return true;
		} else if ($this->isSecLt('triad')) {
			return true;
		} else if ($this->isSecLt('halfdim')) {
			return true;
		} else if ($this->isSecLt('fullydim')) {
			return true;
		} else if ($this->isNeapolitan()) {
			return true;
		} else if ($this->isA6()) {
			return true;
		} else if ($this->isExtendedDominant()) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns the type of this harmonic function, as a string.
	 *
	 * @return string
	 */
	public function getFunctionType() {
		if ($this->isDiatonicTriad()) {
			return 'diatonictriad';
		} else if ($this->isDominantSeventh()) {
			return 'dom7th';
		} else if ($this->isNonDominantSeventh()) {
			return 'nondom7th';
		} else if ($this->isLeadingTone7th('halfdim')) {
			return 'leadingtone7thhalfdim';
		} else if ($this->isLeadingTone7th('fullydim')) {
			return 'leadingtone7thfullydim';
		} else if ($this->isSecDom('triad')) {
			return 'secdomtriad';
		} else if ($this->isSecDom('7th')) {
			return 'secdom7th';
		} else if ($this->isSecNonDom('triad')) {
			return 'secnondomtriad';
		} else if ($this->isSecNonDom('7th')) {
			return 'secnondom7th';
		} else if ($this->isSecLt('triad')) {
			return 'seclttriad';
		} else if ($this->isSecLt('halfdim')) {
			return 'seclthalfdim';
		} else if ($this->isSecLt('fullydim')) {
			return 'secltfullydim';
		} else if ($this->isNeapolitan()) {
			return 'neapolitan';
		} else if ($this->isA6()) {
			return 'aug6th';
		} else if ($this->isExtendedDominant()) {
			return 'extendeddom';
		}
	}

	/**
	 * Returns true if the harmonic function is a diatonic triad.
	 *
	 * @return boolean
	 */
	private function isDiatonicTriad() {
		if ($this->rootPrefix === '' && $this->secondaryTonic === '' &&
				$this->isTriad() &&
				((
				$this->key->isMajor() &&
				(
				($this->romanNum === 'I' && $this->dimHalfdim === '') ||
				($this->romanNum === 'ii' && $this->dimHalfdim === '') ||
				($this->romanNum === 'iii' && $this->dimHalfdim === '') ||
				($this->romanNum === 'IV' && $this->dimHalfdim === '') ||
				($this->romanNum === 'V' && $this->dimHalfdim === '') ||
				($this->romanNum === 'vi' && $this->dimHalfdim === '') ||
				($this->romanNum === 'vii' && $this->dimHalfdim === 'o')
				)
				) ||
				(
				!$this->key->isMajor() &&
				(
				($this->romanNum === 'i' && $this->dimHalfdim === '') ||
				($this->romanNum === 'ii' && ($this->dimHalfdim === 'o')) ||
				($this->romanNum === 'III' && ($this->dimHalfdim === '+' || $this->dimHalfdim === '')) ||
				($this->romanNum === 'iv' && $this->dimHalfdim === '') ||
				($this->romanNum === 'V' && $this->dimHalfdim === '') ||
				($this->romanNum === 'v' && $this->dimHalfdim === '') ||
				($this->romanNum === 'VI' && $this->dimHalfdim === '') ||
				($this->romanNum === 'vi' && $this->dimHalfdim === 'o') ||
				($this->romanNum === 'VII' && $this->dimHalfdim === '') ||
				($this->romanNum === 'vii' && $this->dimHalfdim === 'o')
				)
				))
		) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns true if the harmonic function is a dominant seventh chord.
	 *
	 * @return boolean
	 */
	private function isDominantSeventh() {
		return ($this->rootPrefix === '' && $this->secondaryTonic === '' && $this->isSeventhChord() &&
				$this->romanNum === 'V');
	}

	/**
	 * Returns true if the harmonic function is a nondominant seventh chord.
	 *
	 * @return boolean
	 */
	private function isNonDominantSeventh() {
		if ($this->rootPrefix === '' && $this->secondaryTonic === '' &&
				$this->isSeventhChord() &&
				(
				$this->key->isMajor() &&
				(
				($this->romanNum === 'I' && $this->dimHalfdim === '') ||
				($this->romanNum === 'ii' && $this->dimHalfdim === '') ||
				($this->romanNum === 'iii' && $this->dimHalfdim === '') ||
				($this->romanNum === 'IV' && $this->dimHalfdim === '') ||
				($this->romanNum === 'vi' && $this->dimHalfdim === '')
				) ||
				(
				!$this->key->isMajor() &&
				(
				($this->romanNum === 'i' && $this->dimHalfdim === '') ||
				($this->romanNum === 'ii' && ($this->dimHalfdim === '-o') ||
				($this->romanNum === 'III' && $this->dimHalfdim === '') ||
				($this->romanNum === 'iv' && $this->dimHalfdim === '') ||
				($this->romanNum === 'VI' && $this->dimHalfdim === '') ||
				($this->romanNum === 'vi' && $this->dimHalfdim === '-o')
				)
				))
				)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns true if the harmonic function is a leading tone seventh chord.
	 *
	 * @return boolean
	 */
	private function isLeadingTone7th($type) {
		if ($type === 'halfdim') {
			return ($this->rootPrefix === '' && $this->secondaryTonic === '' && $this->isSeventhChord() &&
					$this->romanNum === 'vii' && $this->dimHalfdim === '-o');
		} else if ($type === 'fullydim') {
			return ($this->rootPrefix === '' && $this->secondaryTonic === '' && $this->isSeventhChord() &&
					$this->romanNum === 'vii' && $this->dimHalfdim === 'o');
		}
	}

	/**
	 * Returns true if the harmonic function is a secondary dominant chord.
	 *
	 * @return boolean
	 */
	private function isSecDom($type) {
		if ($type === 'triad') {
			return ($this->rootPrefix === '' &&
					$this->romanNum === 'V' &&
					$this->dimHalfdim === '' &&
					$this->isTriad() &&
					$this->secondaryTonicIsDiatonic());
		} else if ($type === '7th') {
			return ($this->rootPrefix === '' &&
					$this->romanNum === 'V' &&
					$this->dimHalfdim === '' &&
					$this->isSeventhChord() &&
					$this->secondaryTonicIsDiatonic());
		}
	}

	/**
	 * Returns true if the harmonic function is a secondary nondominant seventh
	 * chord.
	 *
	 * @return boolean
	 */
	private function isSecNonDom($type) {
		if ($type === 'triad') {
			if (!($this->rootPrefix === '' &&
					$this->isTriad() &&
					$this->secondaryTonicIsDiatonic())) {
				return false;
			}
		} else if ($type === '7th') {
			if (!($this->rootPrefix === '' &&
					$this->isSeventhChord() &&
					$this->secondaryTonicIsDiatonic())) {
				return false;
			}
		}

		if ($this->rootIsMajor($this->secondaryTonic)) {
			if ($this->dimHalfdim !== '') {
				return false;
			}
			switch ($this->romanNum) {
				case 'ii':
				case 'iii':
				case 'IV':
				case 'vi':
					return true;
				default:
					return false;
			}
		} else {
			if ($type === 'triad' &&
					($this->romanNum === 'ii' && $this->dimHalfdim === 'o') ||
					($this->romanNum === 'III' && $this->dimHalfdim === '') ||
					($this->romanNum === 'iv' && $this->dimHalfdim === '') ||
					($this->romanNum === 'VI' && $this->dimHalfdim === '') ||
					($this->romanNum === 'vi' && $this->dimHalfdim === 'o') ||
					($this->romanNum === 'VII' && $this->dimHalfdim === '')
			) {
				return true;
			} if ($type === '7th' &&
					($this->romanNum === 'ii' && $this->dimHalfdim === '-o') ||
					($this->romanNum === 'III' && $this->dimHalfdim === '') ||
					($this->romanNum === 'iv' && $this->dimHalfdim === '') ||
					($this->romanNum === 'VI' && $this->dimHalfdim === '') ||
					($this->romanNum === 'vi' && $this->dimHalfdim === '-o') ||
					($this->romanNum === 'VII' && $this->dimHalfdim === '')
			) {
				return true;
			} else {
				return false;
			}
		}
	}

	/**
	 * Returns true if the harmonic function is a secondary leading tone chord.
	 *
	 * @return boolean
	 */
	private function isSecLt($type) {
		if ($type === 'triad') {
			return ($this->secondaryTonicIsDiatonic() && $this->isTriad() &&
					$this->rootPrefix === '' && $this->romanNum === 'vii' && $this->dimHalfdim === 'o');
		} else if ($type === 'halfdim') {
			return ($this->secondaryTonicIsDiatonic() && $this->isSeventhChord() &&
					$this->rootPrefix === '' && $this->romanNum === 'vii' && $this->dimHalfdim === '-o');
		} else if ($type === 'fullydim') {
			return ($this->secondaryTonicIsDiatonic() && $this->isSeventhChord() &&
					$this->rootPrefix === '' && $this->romanNum === 'vii' && $this->dimHalfdim === 'o');
		}
	}

	/**
	 * Returns true if the secondary tonic is diatonic.
	 *
	 * @return boolean
	 */
	private function secondaryTonicIsDiatonic() {
		if ($this->secondaryTonicPrefix !== '') {
			return false;
		}
		if ($this->key->isMajor()) {
			switch ($this->secondaryTonic) {
				case 'ii':
				case 'iii':
				case 'IV':
				case 'V':
				case 'vi':
					return true;
				default:
					return false;
			}
		} else {
			switch ($this->secondaryTonic) {
				case 'III':
				case 'iv':
				case 'V':
				case 'VI':
				case 'VII':
					return true;
				default:
					return false;
			}
		}
	}

	/**
	 * Returns true if the harmonic function is a neapolitan chord.
	 *
	 * @return boolean
	 */
	public function isNeapolitan() {
		return ($this->rootPrefix === '' && $this->secondaryTonic === '' && $this->isTriad() &&
				$this->romanNum === 'N');
	}

	/**
	 * Returns true if the harmonic function is an augmented sixth chord.
	 *
	 * @return boolean
	 */
	private function isA6() {
		if ($this->rootPrefix === '' &&
				($this->romanNum === 'Gr' || $this->romanNum === 'Fr' || $this->romanNum === 'It') &&
				($this->invExt === '') && $this->secondaryTonic === '') {
			return true;
		}
	}

	/**
	 * Returns true if the harmonic function is an extended dominant chord.
	 *
	 * @return boolean
	 */
	private function isExtendedDominant() {
		return ($this->rootPrefix === '' && $this->secondaryTonic === '' && $this->isExtended() &&
				$this->romanNum === 'V');
	}

	/**
	 * Returns the root of a given harmonic function in a given key.
	 *
	 * @param string $rootPrefix The prefix to the roman numeral, if any
	 * @param string $romanNum The roman numeral of the harmonic function whose
	 * root is to be returned.
	 * @param Tonality $key The key to use when determining the root.
	 * @return Note The root of the requested chord in the given key.
	 */
	private function getRoot($rootPrefix, $romanNum, $key) {

		switch ($romanNum) {
			case 'i':
			case 'I':
				return $key->getTonic();
				break;
			case 'N':
				return $key->getTonic()->getNoteFromInterval(new Interval('+', 'm', 2));
				break;
			case 'ii':
			case 'II':
				if ($rootPrefix === '#') {
					return $key->getTonic()->getNoteFromInterval(new Interval('+', 'A', 2));
				} else {
					return $key->getTonic()->getNoteFromInterval(new Interval('+', 'M', 2));
				}
				break;
			case 'iii':
			case 'III':
				if ($key->isMajor()) {
					if ($rootPrefix === 'b' || $rootPrefix === 'n' || $rootPrefix === 'bb') {
						return $key->getTonic()->getNoteFromInterval(new Interval('+', 'm', 3));
					} else {
						return $key->getTonic()->getNoteFromInterval(new Interval('+', 'M', 3));
					}
				} else {
					if ($rootPrefix === '#' || $rootPrefix === 'n' || $rootPrefix === 'x') {
						return $key->getTonic()->getNoteFromInterval(new Interval('+', 'M', 3));
					} else {
						return $key->getTonic()->getNoteFromInterval(new Interval('+', 'm', 3));
					}
				}
				break;
			case 'iv':
			case 'IV':
				return $key->getTonic()->getNoteFromInterval(new Interval('+', 'P', 4));
				break;
			case 'v':
			case 'V':
				return $key->getTonic()->getNoteFromInterval(new Interval('+', 'P', 5));
				break;
			case 'vi':
				if ($key->isMajor()) {
					if ($rootPrefix === 'b' || $rootPrefix === 'n' || $rootPrefix === 'bb') {
						return $key->getTonic()->getNoteFromInterval(new Interval('+', 'm', 6));
					} else if ($rootPrefix === '#') {
						return $key->getTonic()->getNoteFromInterval(new Interval('+', 'A', 6));
					} else {
						return $key->getTonic()->getNoteFromInterval(new Interval('+', 'M', 6));
					}
				} else {
					return $key->getTonic()->getNoteFromInterval(new Interval('+', 'M', 6));
				}
				break;
			case 'VI':
				if ($key->isMajor()) {
					if ($rootPrefix === 'b' || $rootPrefix === 'n' || $rootPrefix === 'bb') {
						return $key->getTonic()->getNoteFromInterval(new Interval('+', 'm', 6));
					} else {
						return $key->getTonic()->getNoteFromInterval(new Interval('+', 'M', 6));
					}
				} else {
					if ($rootPrefix === '#' || $rootPrefix === 'n' || $rootPrefix === 'x') {
						return $key->getTonic()->getNoteFromInterval(new Interval('+', 'M', 6));
					} else {
						return $key->getTonic()->getNoteFromInterval(new Interval('+', 'm', 6));
					}
				}
				break;
			case 'vii':
				return $key->getTonic()->getNoteFromInterval(new Interval('+', 'M', 7));
				break;
			case 'VII':
				if ($key->isMajor()) {
					if ($rootPrefix === 'b' || $rootPrefix === 'n' || $rootPrefix === 'bb') {
						return $key->getTonic()->getNoteFromInterval(new Interval('+', 'm', 7));
					} else {
						return $key->getTonic()->getNoteFromInterval(new Interval('+', 'M', 7));
					}
				} else {
					return $key->getTonic()->getNoteFromInterval(new Interval('+', 'm', 7));
				}
				break;
			case 'Gr':
			case 'It':
			case 'Fr':
				return $key->getTonic()->getNoteFromInterval(new Interval('+', 'm', 6));
				break;
		}

		return null;
	}

	/**
	 * Given a string representation of a given chord's position, returns its
	 * integer representation (i.e. root = 0, 1st inversion = 1, etc.)
	 *
	 * @param string $invExt The string representation of the chord position.
	 * @param string $romanNum The chord's roman numeral.
	 * @return integer The integer representation of the chord position.
	 */
	private function convertInvExtToInt($invExt, $romanNum) {
		switch ($invExt) {
			case '':
			case '7':
			case '9':
			case '11':
			case '13':
				return 0;
				break;
			case '6':
			case '65':
				if ($romanNum === 'Gr' || $romanNum === 'It' || $romanNum === 'Fr') {
					return 0;
				} else {
					return 1;
				}
				break;
			case '64':
			case '43':
				return 2;
				break;
			case '42':
				return 3;
				break;
			case '7-5b':
				return 0;
				break;
			default:
				return 0;
		}
	}

	/**
	 * Returns true if a given roman numeral is major.
	 *
	 * @param string $romanNum The roman numeral.
	 * @return boolean
	 */
	private function rootIsMajor($romanNum) {
		switch ($romanNum) {
			case 'i':
			case 'ii':
			case 'iii':
			case 'iv':
			case 'v':
			case 'vi':
				return false;

			case 'I':
			case 'II':
			case 'III':
			case 'IV':
			case 'V':
			case 'VI':
			case 'VII':
			case 'N':
				return true;
			default:
				return false;
		}
	}

	/**
	 * Returns a given chord's structure of this harmonic function
	 * (M, m, Mm, MM,etc.)
	 *
	 * @param string $romanNum The given chord's roman numeral.
	 * @param string $dimHalfdim The given chord's roman numeral suffix (dim,
	 * half dim, augmented).
	 * @param string $invExt The given chord's position or extention.
	 * @param boolean $rootIsMajor True if the given chord's root triad is
	 * major.
	 * @return string The given chord's structure.
	 */
	private function getType($romanNum, $dimHalfdim, $invExt, $rootIsMajor) {
		if ($this->isTriad()) {
			if ($dimHalfdim === 'o') {
				return 'D';
			} else if ($dimHalfdim === '+') {
				return 'A';
			}
			switch ($romanNum) {
				case 'i':
				case 'ii':
				case 'iii':
				case 'iv':
				case 'v':
				case 'vi':
				case 'vii':
					return 'm';
					break;
				case 'I':
				case 'II':
				case 'III':
				case 'IV':
				case 'V':
				case 'VI':
				case 'VII':
				case 'N':
					return 'M';
					break;
				case 'Gr':
					return 'Gr6';
				case 'Fr':
					return 'Fr6';
				case 'It':
					return 'It6';
			}
		} else if ($this->isExtended()) {
			if ($rootIsMajor) {
				switch ($invExt) {
					case '9':
						return '9';
						break;
					case '11':
						return '11';
						break;
					case '13':
						return '13';
						break;
				}
			} else {
				switch ($invExt) {
					case '9':
						return '9b';
						break;
					case '11':
						return '11/9b';
						break;
					case '13':
						return '13/9b';
						break;
				}
			}
		}
// seventh chord then
		else {
			if ($dimHalfdim === 'o') {
				return 'DD';
			} else if ($dimHalfdim === '-o') {
				return 'Dm';
			} else if ($dimHalfdim === '+') {
				return 'Am';
			}
			switch ($romanNum) {
				case 'V':
					if ($invExt === '7-5b') {
						return '7-5b';
					}
					return 'Mm';
					break;
				case 'i':
				case 'ii':
				case 'iii':
				case 'iv':
				case 'v':
				case 'vi':
					return 'mm';
					break;
				case 'I':
				case 'III':
				case 'IV':
				case 'VI':
				case 'N':
				case 'VII':
					return 'MM';
					break;
			}
		}

		return '';
	}

	/**
	 * Returns true if this harmonic function is a seventh chord.
	 *
	 * @return boolean
	 */
	public function isSeventhChord() {
		if ($this->invExt === '7' ||
				$this->invExt === '65' ||
				$this->invExt === '43' ||
				$this->invExt === '42') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns true if this harmonic function is a triad.
	 *
	 * @return boolean
	 */
	protected function isTriad() {
		if ($this->invExt === '' ||
				$this->invExt === '6' ||
				$this->invExt === '64') {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns true if this harmonic function is an extended chord.
	 *
	 * @return boolean
	 */
	public function isExtended() {
		if ($this->invExt === '9' ||
				$this->invExt === '11' ||
				$this->invExt === '13') {
			return true;
		} else {
			return false;
		}
	}

}