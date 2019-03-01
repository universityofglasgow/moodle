<?php
/**
 * New magic ::class constant sniff test file.
 *
 * @package PHPCompatibility
 */

namespace PHPCompatibility\Tests\Constants;

use PHPCompatibility\Tests\BaseSniffTest;

/**
 * New magic ::class constant sniff test file.
 *
 * @group newMagicClassConstant
 * @group constants
 *
 * @covers \PHPCompatibility\Sniffs\Constants\NewMagicClassConstantSniff
 *
 * @uses    \PHPCompatibility\Tests\BaseSniffTest
 * @package PHPCompatibility
 * @author  Juliette Reinders Folmer <phpcompatibility_nospam@adviesenzo.nl>
 */
class NewMagicClassConstantUnitTest extends BaseSniffTest
{

    /**
     * testNewMagicClassConstant
     *
     * @dataProvider dataNewMagicClassConstant
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testNewMagicClassConstant($line)
    {
        $file = $this->sniffFile(__FILE__, '5.4');
        $this->assertError($file, $line, 'The magic class constant ClassName::class was not available in PHP 5.4 or earlier');
    }

    /**
     * Data provider dataNewMagicClassConstant.
     *
     * @see testNewMagicClassConstant()
     *
     * @return array
     */
    public function dataNewMagicClassConstant()
    {
        return array(
            array(6),
            array(12),
        );
    }


    /**
     * testNoFalsePositives
     *
     * @dataProvider dataNoFalsePositives
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testNoFalsePositives($line)
    {
        $file = $this->sniffFile(__FILE__, '5.4');
        $this->assertNoViolation($file, $line);
    }

    /**
     * Data provider.
     *
     * @see testNoFalsePositives()
     *
     * @return array
     */
    public function dataNoFalsePositives()
    {
        return array(
            array(4),
            array(10),
            array(18),
            array(19),
        );
    }


    /**
     * Verify no notices are thrown at all.
     *
     * @return void
     */
    public function testNoViolationsInFileOnValidVersion()
    {
        $file = $this->sniffFile(__FILE__, '5.5');
        $this->assertNoViolation($file);
    }
}
