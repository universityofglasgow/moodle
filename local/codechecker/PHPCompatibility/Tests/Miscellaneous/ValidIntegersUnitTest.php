<?php
/**
 * Valid Integers Sniff test file
 *
 * @package PHPCompatibility
 */

namespace PHPCompatibility\Tests\Miscellaneous;

use PHPCompatibility\Tests\BaseSniffTest;

/**
 * Valid Integers Sniff tests
 *
 * @group validIntegers
 * @group miscellaneous
 *
 * @covers \PHPCompatibility\Sniffs\Miscellaneous\ValidIntegersSniff
 *
 * @uses    \PHPCompatibility\Tests\BaseSniffTest
 * @package PHPCompatibility
 * @author  Juliette Reinders Folmer <phpcompatibility_nospam@adviesenzo.nl>
 */
class ValidIntegersUnitTest extends BaseSniffTest
{

    /**
     * testBinaryInteger
     *
     * @dataProvider dataBinaryInteger
     *
     * @param int    $line            Line number where the error should occur.
     * @param string $binary          (Start of) Binary number as a string.
     * @param bool   $testNoViolation Whether or not to test for noViolation.
     *                                Defaults to true. Set to false if another error is
     *                                expected on the same line (invalid binary).
     *
     * @return void
     */
    public function testBinaryInteger($line, $binary, $testNoViolation = true)
    {
        $file = $this->sniffFile(__FILE__, '5.3');
        $this->assertError($file, $line, "Binary integer literals were not present in PHP version 5.3 or earlier. Found: {$binary}");

        if ($testNoViolation === true) {
            $file = $this->sniffFile(__FILE__, '5.4');
            $this->assertNoViolation($file, $line);
        }
    }

    /**
     * dataBinaryInteger
     *
     * @see testBinaryInteger()
     *
     * @return array
     */
    public function dataBinaryInteger()
    {
        return array(
            array(3, '0b001001101', true),
            array(4, '0b01', false),
        );
    }


    /**
     * testInvalidBinaryInteger
     *
     * @return void
     */
    public function testInvalidBinaryInteger()
    {
        $file = $this->sniffFile(__FILE__); // Message will be shown independently of testVersion.
        $this->assertWarning($file, 4, 'Invalid binary integer detected. Found: 0b0123456');
    }


    /**
     * testInvalidOctalInteger
     *
     * @dataProvider dataInvalidOctalInteger
     *
     * @param int    $line  Line number where the error should occur.
     * @param string $octal Octal number as a string.
     *
     * @return void
     */
    public function testInvalidOctalInteger($line, $octal)
    {
        $error = "Invalid octal integer detected. Prior to PHP 7 this would lead to a truncated number. From PHP 7 onwards this causes a parse error. Found: {$octal}";

        $file = $this->sniffFile(__FILE__, '5.6');
        $this->assertWarning($file, $line, $error);

        $file = $this->sniffFile(__FILE__, '7.0');
        $this->assertError($file, $line, $error);
    }

    /**
     * dataInvalidOctalInteger
     *
     * @see testInvalidOctalInteger()
     *
     * @return array
     */
    public function dataInvalidOctalInteger()
    {
        return array(
            array(7, '08'),
            array(8, '038'),
            array(9, '091'),
        );
    }


    /**
     * testValidOctalInteger
     *
     * @return void
     */
    public function testValidOctalInteger()
    {
        $file = $this->sniffFile(__FILE__, '4.0-99.0');
        $this->assertNoViolation($file, 6);
    }


    /**
     * testHexNumericString
     *
     * @return void
     */
    public function testHexNumericString()
    {
        $error = 'The behaviour of hexadecimal numeric strings was inconsistent prior to PHP 7 and support has been removed in PHP 7. Found: \'0xaa78b5\'';

        $file = $this->sniffFile(__FILE__, '5.6');
        $this->assertWarning($file, 11, $error);
        $this->assertNoViolation($file, 12);

        $file = $this->sniffFile(__FILE__, '7.0');
        $this->assertError($file, 11, $error);
        $this->assertNoViolation($file, 12);
    }


    /*
     * `testNoViolationsInFileOnValidVersion` test omitted as this sniff will throw warnings/errors
     * about invalid integers independently of the testVersion.
     */
}
