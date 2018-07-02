<?php
/**
 * Global with variable variables have been removed in PHP 7.0 sniff test file
 *
 * @package PHPCompatibility
 */

namespace PHPCompatibility\Tests\Sniffs\PHP;

use PHPCompatibility\Tests\BaseSniffTest;

/**
 * Global with variable variables have been removed in PHP 7.0 sniff test file
 *
 * @group forbiddenGlobalVariableVariable
 * @group variableVariables
 *
 * @covers \PHPCompatibility\Sniffs\PHP\ForbiddenGlobalVariableVariableSniff
 *
 * @uses    \PHPCompatibility\Tests\BaseSniffTest
 * @package PHPCompatibility
 * @author  Wim Godden <wim@cu.be>
 */
class ForbiddenGlobalVariableVariableSniffTest extends BaseSniffTest
{
    const TEST_FILE = 'sniff-examples/forbidden_global_variable_variable.php';

    /**
     * testGlobalVariableVariable
     *
     * @dataProvider dataGlobalVariableVariable
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testGlobalVariableVariable($line)
    {
        $file = $this->sniffFile(self::TEST_FILE, '7.0');
        $this->assertError($file, $line, 'Global with variable variables is not allowed since PHP 7.0');
    }

    /**
     * Data provider dataGlobalVariableVariable.
     *
     * @see testGlobalVariableVariable()
     *
     * @return array
     */
    public function dataGlobalVariableVariable()
    {
        return array(
            array(21),
            array(22),
            array(23),
            array(24),
            array(25),
            array(29),
            array(31),
        );
    }


    /**
     * testGlobalNonBareVariable
     *
     * @dataProvider dataGlobalNonBareVariable
     *
     * @param int $line The line number.
     *
     * @return void
     */
    public function testGlobalNonBareVariable($line)
    {
        $file = $this->sniffFile(self::TEST_FILE, '7.0');
        $this->assertWarning($file, $line, 'Global with anything other than bare variables is discouraged since PHP 7.0');
    }

    /**
     * Data provider dataGlobalNonBareVariable.
     *
     * @see testGlobalNonBareVariable()
     *
     * @return array
     */
    public function dataGlobalNonBareVariable()
    {
        return array(
            array(11), // x2
            array(17),
            array(18),
            array(35),
            array(36),
            array(37),
            array(38),
            array(39),
            array(42),
            array(43),
            array(44),
            array(45),
            array(46),
            array(47),
            array(51),
            array(52),
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
        $file = $this->sniffFile(self::TEST_FILE, '7.0');
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
            array(8),
            array(14),
            array(15),
            array(16),
            array(50),
            array(55),
        );
    }


    /**
     * Verify no notices are thrown at all.
     *
     * @return void
     */
    public function testNoViolationsInFileOnValidVersion()
    {
        $file = $this->sniffFile(self::TEST_FILE, '5.6');
        $this->assertNoViolation($file);
    }

}
