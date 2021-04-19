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
 * This file defines the music scale classes.
 *
 * @package MusicTheory
 */

/**
 * This class represents a musical scale.
 */
abstract class Scale {

	/** @var Note The tonic of the scale. */
	protected $tonic;

	/** @var Interval[] The intervals between the tonic and notes of the scale. */
	protected $intSequence;

	/**
	 * Constructor.
	 *
	 * @param Note $tonic The tonic of the scale.
	 * @return void
	 */
	public function __construct($tonic) {

		$this->tonic = $tonic;
		$this->intSequence = array();
	}

	/**
	 * Retrieves a note from the scale.
	 *
	 * @param integer $degree The scale degree to retrieve (tonic = 1).
	 * @param integer $alteration Optional. If provided, alters the note by the
	 * specified number of half steps. Values between -2 and 2 are accepted - a
	 * value of 0 returns an unaltered scale degree. The default is 0.
	 * @return Note The requested scale degree.
	 */
	public function getScaleDegree($degree, $alteration = 0) {

		$note = $this->tonic->getNoteFromInterval($this->intSequence[$degree - 1]);
		$note->alter($alteration);
		return $note;
	}

	/**
	 * Return all notes from the scale.
	 *
	 * @return Note[] The scale notes.
	 */
	public function getScaleDegrees() {

		$degrees = array();
		array_push($degrees, $this->getScaleDegree(1));
		array_push($degrees, $this->getScaleDegree(2));
		array_push($degrees, $this->getScaleDegree(3));
		array_push($degrees, $this->getScaleDegree(4));
		array_push($degrees, $this->getScaleDegree(5));
		array_push($degrees, $this->getScaleDegree(6));
		array_push($degrees, $this->getScaleDegree(7));
		array_push($degrees, $this->getScaleDegree(8));
		return $degrees;
	}

	/**
	 * Returns a string describing the name of the scale (e.g. 'C major',
	 * 'G natural minor').
	 *
	 * @return string The scale name.
	 */
	public function getName() {

		return '';
	}

	/**
	 * Returns a comma-separated string representation of the scale notes.
	 * (e.g. 'Dn4,En4,F#4,Gn4,An4,Bn4,C#5,Dn5').
	 *
	 * @return string The comma-reparated scale notes.
	 */
	public function __toString() {
		return implode(',', $this->getScaleDegrees());
	}

}

/**
 * This class represents a major scale.
 */
class MajorScale extends Scale {

	/**
	 * Constructor.
	 *
	 * @param Note $tonic The tonic of the scale.
	 * @return void
	 */
	public function __construct($tonic) {

		parent::__construct($tonic);

		$int_1 = new Interval('+', 'P', 1);
		$int_2 = new Interval('+', 'M', 2);
		$int_3 = new Interval('+', 'M', 3);
		$int_4 = new Interval('+', 'P', 4);
		$int_5 = new Interval('+', 'P', 5);
		$int_6 = new Interval('+', 'M', 6);
		$int_7 = new Interval('+', 'M', 7);
		$int_8 = new Interval('+', 'P', 8);

		array_push($this->intSequence, $int_1);
		array_push($this->intSequence, $int_2);
		array_push($this->intSequence, $int_3);
		array_push($this->intSequence, $int_4);
		array_push($this->intSequence, $int_5);
		array_push($this->intSequence, $int_6);
		array_push($this->intSequence, $int_7);
		array_push($this->intSequence, $int_8);
	}

	/**
	 * Returns a string describing the name of the scale (e.g. 'C major',
	 * 'Ab major').
	 *
	 * @return string The scale name.
	 */
	public function getName() {

		return ((string) $this->tonic . getLetterAccidental()) . ' major';
	}

}

/**
 * This class represents a minor scale.
 */
abstract class MinorScale extends Scale {

	/**
	 * Constructor.
	 *
	 * @param Note $tonic The tonic of the scale.
	 * @return void
	 */
	public function __construct($tonic) {

		parent::__construct($tonic);
	}

}

/**
 * This class represents a natural minor scale.
 */
class NaturalMinorScale extends MinorScale {

	/**
	 * Constructor.
	 *
	 * @param Note $tonic The tonic of the scale.
	 * @return void
	 */
	public function __construct($tonic) {

		parent::__construct($tonic);

		$int_1 = new Interval('+', 'P', 1);
		$int_2 = new Interval('+', 'M', 2);
		$int_3 = new Interval('+', 'm', 3);
		$int_4 = new Interval('+', 'P', 4);
		$int_5 = new Interval('+', 'P', 5);
		$int_6 = new Interval('+', 'm', 6);
		$int_7 = new Interval('+', 'm', 7);
		$int_8 = new Interval('+', 'P', 8);

		array_push($this->intSequence, $int_1);
		array_push($this->intSequence, $int_2);
		array_push($this->intSequence, $int_3);
		array_push($this->intSequence, $int_4);
		array_push($this->intSequence, $int_5);
		array_push($this->intSequence, $int_6);
		array_push($this->intSequence, $int_7);
		array_push($this->intSequence, $int_8);
	}

	/**
	 * Returns a string describing the name of the scale (e.g. 'C natural minor',
	 * 'F natural minor').
	 *
	 * @return string The scale name.
	 */
	public function getName() {

		return ((string) tonic . getLetterAccidental()) . ' natural minor';
	}

}

/**
 * This class represents a harmonic minor scale.
 */
class HarmonicMinorScale extends MinorScale {

	/**
	 * Constructor.
	 *
	 * @param Note $tonic The tonic of the scale.
	 * @return void
	 */
	public function __construct($tonic) {

		parent::__construct($tonic);

		$int_1 = new Interval('+', 'P', 1);
		$int_2 = new Interval('+', 'M', 2);
		$int_3 = new Interval('+', 'm', 3);
		$int_4 = new Interval('+', 'P', 4);
		$int_5 = new Interval('+', 'P', 5);
		$int_6 = new Interval('+', 'm', 6);
		$int_7 = new Interval('+', 'M', 7);
		$int_8 = new Interval('+', 'P', 8);

		array_push($this->intSequence, $int_1);
		array_push($this->intSequence, $int_2);
		array_push($this->intSequence, $int_3);
		array_push($this->intSequence, $int_4);
		array_push($this->intSequence, $int_5);
		array_push($this->intSequence, $int_6);
		array_push($this->intSequence, $int_7);
		array_push($this->intSequence, $int_8);
	}

	/**
	 * Returns a string describing the name of the scale (e.g. 'C harmonic minor',
	 * 'F# harmonic minor').
	 *
	 * @return string The scale name.
	 */
	public function getName() {
		return $this->tonic->getLetterAccidental() . ' harmonic minor';
	}

}

/**
 * This class represents a melodic minor scale.
 */
class MelodicMinorScale extends MinorScale {

	/**
	 * Constructor.
	 *
	 * @param Note $tonic The tonic of the scale.
	 * @return void
	 */
	public function __construct($tonic) {
		parent::__construct($tonic);

		$int_1 = new Interval('+', 'P', 1);
		$int_2 = new Interval('+', 'M', 2);
		$int_3 = new Interval('+', 'm', 3);
		$int_4 = new Interval('+', 'P', 4);
		$int_5 = new Interval('+', 'P', 5);
		$int_6 = new Interval('+', 'm', 6);
		$int_7 = new Interval('+', 'm', 7);
		$int_8 = new Interval('+', 'P', 8);

		array_push($this->intSequence, $int_1);
		array_push($this->intSequence, $int_2);
		array_push($this->intSequence, $int_3);
		array_push($this->intSequence, $int_4);
		array_push($this->intSequence, $int_5);
		array_push($this->intSequence, $int_6);
		array_push($this->intSequence, $int_7);
		array_push($this->intSequence, $int_8);
	}

	/**
	 * Return all notes from the scale.
	 *
	 * @return Note[] The scale notes.
	 */
	public function getScaleDegrees() {

		$degrees = array();
		array_push($degrees, $this->getScaleDegree(1));
		array_push($degrees, $this->getScaleDegree(2));
		array_push($degrees, $this->getScaleDegree(3));
		array_push($degrees, $this->getScaleDegree(4));
		array_push($degrees, $this->getScaleDegree(5));
		array_push($degrees, $this->getScaleDegree(6, 1));
		array_push($degrees, $this->getScaleDegree(7, 1));
		array_push($degrees, $this->getScaleDegree(8));
		array_push($degrees, $this->getScaleDegree(7));
		array_push($degrees, $this->getScaleDegree(6));
		array_push($degrees, $this->getScaleDegree(5));
		array_push($degrees, $this->getScaleDegree(4));
		array_push($degrees, $this->getScaleDegree(3));
		array_push($degrees, $this->getScaleDegree(2));
		array_push($degrees, $this->getScaleDegree(1));

		return $degrees;
	}

	/**
	 * Returns a string describing the name of the scale (e.g. 'C melodic minor',
	 * 'Bb melodic minor').
	 *
	 * @return string The scale name.
	 */
	public function getName() {

		return ((string) tonic . getLetterAccidental()) . ' melodic minor';
	}

}