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
 * @package    qtype
 * @subpackage musictheory
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/*
 * Static class taking care of generating valid random options
 * for music theory question subtypes.
 *
 * @copyright  2013 Eric Brisson
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class qtype_musictheory_randomiser {

    /**
     * Given an array of options, randomly returns one array value
     *
     * @param array $possibleoptions The options to randomly select from.
     * @return object The randomly selected array value.
     */
    public static function get_random_field($possibleoptions) {
        return $possibleoptions[rand(0, count($possibleoptions) - 1)];
    }

    /**
     * Returns a random key.
     *
     * @param array $possiblemodes The possible modes to choose from.
     * @param boolean $includeemptykeysignatures Indicates whether the keys with
     * empty key signatures should be considered.
     * @return string The randomly selected key.
     */
    public static function get_random_key($possiblemodes, $includeemptykeysignature) {
        $modeid = rand(0, count($possiblemodes) - 1);
        $mode = $possiblemodes[$modeid];
        $possiblekeys = Tonality::getValidKeys($mode);
        if (!$includeemptykeysignature) {
            array_shift($possiblekeys);
        }
        $key = $possiblekeys[rand(0, count($possiblekeys) - 1)];
        $tonic = substr((string) $key->getTonic(), 0, 2);
        $modestr = ($key->isMajor()) ? 'M' : 'm';
        return $tonic . $modestr;
    }

    /**
     * Returns a given note for interval questions.
     *
     * @param string $clef The clef to work with - used to determine the
     * given note's register, to ensure that a randomly chosen interval
     * fits in the staff.
     * @param string $quality The interval's quality
     * @param string $size The interval's quality
     * @param string $direction The direction to work with - used to determine
     * the given note's register, to ensure that a randomly chosen interval
     * fits in the staff.
     * @return string The randomly selected key.
     */
    public static function get_random_interval_givennote($clef, $quality, $size, $direction) {
        if ($direction == 'above') {
            switch ($clef) {
                case 'treble':
                    $reg = 4;
                    break;
                case 'bass':
                    $reg = 2;
                    break;
                case 'alto':
                case 'tenor':
                    $reg = 3;
                    break;
            }
        } else {
            switch ($clef) {
                case 'treble':
                    $reg = 5;
                    break;
                case 'bass':
                    $reg = 3;
                    break;
                case 'alto':
                case 'tenor':
                    $reg = 4;
                    break;
            }
        }

        $possiblegivennotes = array('Cn', 'Gn', 'Dn', 'An', 'En', 'Bn', 'F#', 'C#',
            'Fn', 'Bb', 'Eb', 'Ab', 'Db', 'Gb', 'Cb');
        // Remove cases when the inverval doesn't exist above the given note.
        switch ($quality . $size . '-' . $direction) {
            case 'A2-below':
            case 'D7-above':
            case 'A9-below':
                $possiblegivennotes = array_slice($possiblegivennotes, 0, 14);
                break;
            case 'D3-above':
            case 'A6-below':
            case 'D10-above':
            case 'A13-below':
                $possiblegivennotes = array_slice($possiblegivennotes, 0, 13);
                break;
            case 'A3-below':
            case 'D6-above':
            case 'A10-below':
            case 'D13-above':
                $possiblegivennotes = array_slice($possiblegivennotes, 0, 12);
                break;
            case 'D2-above':
            case 'A7-below':
            case 'D9-above':
                $possiblegivennotes = array_slice($possiblegivennotes, 0, 11);
                break;
        }

        $givennotename = self::get_random_field($possiblegivennotes);
        $givennote = new Note(substr($givennotename, 0, 1), substr($givennotename, 1, 1), $reg);

        return $givennote;
    }

    /**
     * Returns a random interval.
     *
     * @param array $possiblequalities The possible interval qualities to
     * choose from.
     * @param array $possiblesizes The possible interval sizes to
     * choose from.
     * @return array The randomly selected interval, with two keys,
     * 'quality' and 'size'.
     */
    public static function get_random_interval($possiblequalities, $possiblesizes) {
        $validcombos = array();

        foreach ($possiblequalities as $quality) {
            foreach ($possiblesizes as $size) {
                if (Interval::valid_interval($quality, $size)) {
                    $combo = array(
                        'quality' => $quality,
                        'size'    => $size
                    );
                    array_push($validcombos, $combo);
                }
            }
        }
        return self::get_random_field($validcombos);
    }

    /**
     * Returns a random scale tonic.
     *
     * @param string $mode The mode of the scale.
     * @param string $clef The clef that will be used to display the
     * scale.
     * @return Note The randomly selected tonic.
     */
    public static function get_random_scale_tonic($mode, $clef) {
        switch ($clef) {
            case 'treble':
                $reg = 4;
                break;
            case 'bass':
                $reg = 2;
                break;
            case 'alto':
            case 'tenor':
                $reg = 3;
                break;
        }

        $tonic = self::get_random_tonic($mode);
        $givennote = new Note($tonic->getLetter(), $tonic->getAccidental(), $reg);
        return $givennote;
    }

    /**
     * Given a mode, returns a randomly selected tonic in that mode.
     *
     * @param string $mode The given mode to choose the tonic from.
     * @return Note The randomly selected tonic.
     */
    private static function get_random_tonic($mode) {
        $tonics = Tonality::getValidKeys($mode);
        $rand = rand(0, count($tonics) - 1);
        $tonic = $tonics[$rand]->getTonic();
        return $tonic;
    }

    /**
     * Returns a randomly selected letter name.
     *
     * @return String The randomly selected letter name.
     */
    public static function get_random_letter_name() {
        $ltrs = array('A', 'B', 'C', 'D', 'E', 'F', 'G');
        return self::get_random_field($ltrs);
    }

    /**
     * Returns a randomly selected accidental.
     *
     * @return String The randomly selected accidental.
     */
    public static function get_random_accidental($includedoubleaccidentals = true) {
        if ($includedoubleaccidentals) {
            $accs = array('n', '#', 'b', 'x', 'bb');
        } else {
            $accs = array('n', '#', 'b');
        }
        return self::get_random_field($accs);
    }

    /**
     * Returns a randomly selected register whose notes would fit in the
     * given clef.
     *
     * @param string $clef The clef to be used.
     * @return String The randomly selected letter name.
     */
    public static function get_random_register($clef, $ltr) {
        switch ($clef) {
            case 'treble':
                $regs = ($ltr === 'B') ? array(3, 4, 5) : array(3, 4, 5, 6);
                break;
            case 'bass':
                $regs = ($ltr === 'C' || $ltr === 'D') ? array(2, 3, 4) : array(1, 2, 3, 4);
                break;
            case 'alto':
                $regs = ($ltr === 'C') ? array(3, 4, 5) : array(2, 3, 4, 5);
                break;
            case 'tenor':
                $regs = ($ltr === 'A' || $ltr === 'B') ? array(2, 3, 4) : array(2, 3, 4, 5);
                break;
            case 'grandstaff':
                switch ($ltr) {
                    case 'B':
                        $regs = array(2, 3, 4, 5);
                        break;
                    case 'C':
                    case 'D':
                        $regs = array(2, 3, 4, 5, 6);
                        break;
                    default:
                        $regs = array(1, 2, 3, 4, 5, 6);
                }
        }
        return self::get_random_field($regs);
    }

    /**
     * Returns a randomly selected harmonic function.
     *
     * @param string $hftype The type of harmonic function to return.
     * @param string $keymode The key and mode.
     * @return array An array with three indices ('hfprimary', 'hfinvext' and
     * 'hfsecondary') describing the randomly generated harmonic function.
     */
    public static function get_random_harmonicfunction($hftype, $keymode) {
        $hf = array();
        $mode = substr($keymode, 2, 1);
        $key = substr($keymode, 0, 2);

        switch ($hftype) {
            case 'diatonictriad':
                if ($mode === 'M') {
                    $possprimary = array('I', 'ii', 'iii', 'IV', 'V', 'vi', 'viio');
                } else {
                    $possprimary = array('i', 'iio', 'III', 'III+', 'iv', 'V', 'VI', 'vio', 'VII', 'viio');
                }
                $hf['primary'] = self::get_random_field($possprimary);
                $hf['invext'] = self::get_random_field(array('', '6', '64'));
                $hf['secondary'] = '';
                break;
            case 'dom7th':
                $hf['primary'] = 'V';
                $hf['invext'] = self::get_random_field(array('7', '65', '43', '42'));
                $hf['secondary'] = '';
                break;
            case 'nondom7th':
                if ($mode === 'M') {
                    $possprimary = array('I', 'ii', 'iii', 'IV', 'vi');
                } else {
                    $possprimary = array('i', 'ii-o', 'III', 'iv', 'VI', 'vi-o');
                }
                $hf['primary'] = self::get_random_field($possprimary);
                $hf['invext'] = self::get_random_field(array('7', '65', '43', '42'));
                $hf['secondary'] = '';
                break;
            case 'leadingtone7thhalfdim':
                $hf['primary'] = 'vii-o';
                $hf['invext'] = self::get_random_field(array('7', '65', '43', '42'));
                $hf['secondary'] = '';
                break;
            case 'leadingtone7thfullydim':
                $hf['primary'] = 'viio';
                $hf['invext'] = self::get_random_field(array('7', '65', '43', '42'));
                $hf['secondary'] = '';
                break;
            case 'secdomtriad':
                if ($mode === 'M') {
                    $posssecondary = array('/ii', '/iii', '/IV', '/V', '/vi');
                } else {
                    $posssecondary = array('/III', '/iv', '/V', '/VI', '/VII');
                }
                $hf['primary'] = 'V';
                $hf['invext'] = self::get_random_field(array('', '6', '64'));
                $hf['secondary'] = self::get_random_field($posssecondary);
                break;
            case 'secdom7th':
                if ($mode === 'M') {
                    $posssecondary = array('/ii', '/iii', '/IV', '/V', '/vi');
                } else {
                    $posssecondary = array('/III', '/iv', '/V', '/VI', '/VII');
                }
                $hf['primary'] = 'V';
                $hf['invext'] = self::get_random_field(array('7', '65', '43', '42'));
                $hf['secondary'] = self::get_random_field($posssecondary);
                break;
            case 'secnondomtriad':
                if ($mode === 'M') {
                    $posssecondary = array('/ii', '/iii', '/IV', '/V', '/vi');
                } else {
                    $posssecondary = array('/III', '/iv', '/V', '/VI', '/VII');
                }
                $hf['invext'] = self::get_random_field(array('', '6', '64'));
                $hf['secondary'] = self::get_random_field($posssecondary);
                switch ($hf['secondary']) {
                    case '/ii':
                    case '/iii':
                    case '/iv':
                    case '/vi':
                        $possprimary = array('iio', 'III', 'iv', 'VI', 'viio', 'VII');
                        break;
                    default:
                        $possprimary = array('ii', 'iii', 'IV', 'vi');
                        break;
                }
                $hf['primary'] = self::get_random_field($possprimary);
                break;
            case 'secnondom7th':
                if ($mode === 'M') {
                    $posssecondary = array('/ii', '/iii', '/IV', '/V', '/vi');
                } else {
                    $posssecondary = array('/III', '/iv', '/V', '/VI', '/VII');
                }
                $hf['invext'] = self::get_random_field(array('7', '65', '43', '42'));
                $hf['secondary'] = self::get_random_field($posssecondary);
                switch ($hf['secondary']) {
                    case '/ii':
                    case '/iii':
                    case '/iv':
                    case '/vi':
                        $possprimary = array('ii-o', 'III', 'iv', 'VI', 'vi-o', 'VII');
                        break;
                    default:
                        $possprimary = array('ii', 'iii', 'IV', 'vi');
                        break;
                }
                $hf['primary'] = self::get_random_field($possprimary);
                break;
            case 'seclttriad':
                if ($mode === 'M') {
                    $posssecondary = array('/ii', '/iii', '/IV', '/V', '/vi');
                } else {
                    $posssecondary = array('/III', '/iv', '/V', '/VI', '/VII');
                }
                $hf['primary'] = 'viio';
                $hf['invext'] = self::get_random_field(array('', '6', '64'));
                $hf['secondary'] = self::get_random_field($posssecondary);
                break;
            case 'seclthalfdim':
                if ($mode === 'M') {
                    $posssecondary = array('/ii', '/iii', '/IV', '/V', '/vi');
                } else {
                    $posssecondary = array('/III', '/iv', '/V', '/VI', '/VII');
                }
                $hf['primary'] = 'vii-o';
                $hf['invext'] = self::get_random_field(array('7', '65', '43', '42'));
                $hf['secondary'] = self::get_random_field($posssecondary);
                break;
            case 'secltfullydim':
                if ($mode === 'M') {
                    $posssecondary = array('/ii', '/iii', '/IV', '/V', '/vi');
                } else {
                    $posssecondary = array('/III', '/iv', '/V', '/VI', '/VII');
                }
                $hf['primary'] = 'viio';
                $hf['invext'] = self::get_random_field(array('7', '65', '43', '42'));
                $hf['secondary'] = self::get_random_field($posssecondary);
                break;
            case 'neapolitan':
                $hf['primary'] = 'N';
                $hf['invext'] = '6';
                $hf['secondary'] = '';
                break;
            case 'aug6th':
                $hf['primary'] = self::get_random_field(array('Gr', 'Fr', 'It'));
                $hf['invext'] = '';
                $hf['secondary'] = '';
                break;
            case 'extendeddom':
                $hf['primary'] = 'V';
                $hf['invext'] = self::get_random_field(array('9', '11', '13'));
                $hf['secondary'] = '';
                break;
            default:
                $hf['primary'] = 'I';
                $hf['invext'] = '';
                $hf['secondary'] = '';
                break;
        }
        return $hf;
    }

}
