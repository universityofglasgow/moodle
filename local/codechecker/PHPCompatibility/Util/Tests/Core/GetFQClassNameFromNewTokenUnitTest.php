<?php
/**
 * Classname determination test file
 *
 * @package PHPCompatibility
 */

namespace PHPCompatibility\Util\Tests\Core;

use PHPCompatibility\Util\Tests\CoreMethodTestFrame;

/**
 * Classname determination function tests
 *
 * @group utilityGetFQClassNameFromNewToken
 * @group utilityFunctions
 *
 * @uses    \PHPCompatibility\Util\Tests\CoreMethodTestFrame
 * @package PHPCompatibility
 * @author  Juliette Reinders Folmer <phpcompatibility_nospam@adviesenzo.nl>
 */
class GetFQClassNameFromNewTokenUnitTest extends CoreMethodTestFrame
{

    /**
     * testGetFQClassNameFromNewToken
     *
     * @dataProvider dataGetFQClassNameFromNewToken
     *
     * @covers \PHPCompatibility\Sniff::getFQClassNameFromNewToken
     *
     * @param string $commentString The comment which prefaces the T_NEW token in the test file.
     * @param string $expected      The expected fully qualified class name.
     *
     * @return void
     */
    public function testGetFQClassNameFromNewToken($commentString, $expected)
    {
        $stackPtr = $this->getTargetToken($commentString, \T_NEW);
        $result   = $this->helperClass->getFQClassNameFromNewToken($this->phpcsFile, $stackPtr);
        $this->assertSame($expected, $result);
    }

    /**
     * dataGetFQClassNameFromNewToken
     *
     * @see testGetFQClassNameFromNewToken()
     *
     * @return array
     */
    public function dataGetFQClassNameFromNewToken()
    {
        return array(
            array('/* Case 1 */', '\DateTime'),
            array('/* Case 2 */', '\MyTesting\DateTime'),
            array('/* Case 3 */', '\MyTesting\DateTime'),
            array('/* Case 4 */', '\DateTime'),
            array('/* Case 5 */', '\MyTesting\anotherNS\DateTime'),
            array('/* Case 6 */', '\FQNS\DateTime'),
            array('/* Case 7 */', '\AnotherTesting\DateTime'),
            array('/* Case 8 */', '\AnotherTesting\DateTime'),
            array('/* Case 9 */', '\DateTime'),
            array('/* Case 10 */', '\AnotherTesting\anotherNS\DateTime'),
            array('/* Case 11 */', '\FQNS\DateTime'),
            array('/* Case 12 */', '\DateTime'),
            array('/* Case 13 */', '\DateTime'),
            array('/* Case 14 */', '\AnotherTesting\DateTime'),
            array('/* Case 15 */', ''),
            array('/* Case 16 */', ''),
        );
    }
}
