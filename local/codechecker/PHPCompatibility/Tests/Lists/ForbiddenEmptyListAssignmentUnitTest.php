<?php
/**
 * Empty list() assignments have been removed in PHP 7.0 sniff test file
 *
 * @package PHPCompatibility
 */

namespace PHPCompatibility\Tests\Lists;

use PHPCompatibility\Tests\BaseSniffTest;

/**
 * Empty list() assignments have been removed in PHP 7.0 sniff test file
 *
 * @group forbiddenEmptyListAssignment
 * @group lists
 *
 * @covers \PHPCompatibility\Sniffs\Lists\ForbiddenEmptyListAssignmentSniff
 *
 * @uses    \PHPCompatibility\Tests\BaseSniffTest
 * @package PHPCompatibility
 * @author  Wim Godden <wim@cu.be>
 */
class ForbiddenEmptyListAssignmentUnitTest extends BaseSniffTest
{

    /**
     * testEmptyListAssignment
     *
     * @dataProvider dataEmptyListAssignment
     *
     * @param int $line Line number where the error should occur.
     *
     * @return void
     */
    public function testEmptyListAssignment($line)
    {
        $file = $this->sniffFile(__FILE__, '7.0');
        $this->assertError($file, $line, 'Empty list() assignments are not allowed since PHP 7.0');
    }

    /**
     * dataEmptyListAssignment
     *
     * @see testEmptyListAssignment()
     *
     * @return array
     */
    public function dataEmptyListAssignment()
    {
        return array(
            array(3),
            array(4),
            array(5),
            array(6),
            array(7),
            array(8),
            array(20),
            array(21),
            array(22),
            array(23),
            array(24),
            array(25),
        );
    }


    /**
     * testNoFalsePositives
     *
     * @dataProvider dataNoFalsePositives
     *
     * @param int $line Line number with a valid list assignment.
     *
     * @return void
     */
    public function testNoFalsePositives($line)
    {
        $file = $this->sniffFile(__FILE__, '7.0');
        $this->assertNoViolation($file, $line);
    }

    /**
     * dataNoFalsePositives
     *
     * @see testNoFalsePositives()
     *
     * @return array
     */
    public function dataNoFalsePositives()
    {
        return array(
            array(13),
            array(14),
            array(15),
            array(16),
            array(17),
            array(28),
        );
    }


    /**
     * Verify no notices are thrown at all.
     *
     * @return void
     */
    public function testNoViolationsInFileOnValidVersion()
    {
        $file = $this->sniffFile(__FILE__, '5.6');
        $this->assertNoViolation($file);
    }
}
