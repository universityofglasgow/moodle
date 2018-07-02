<?php
/**
 * Is short list syntax ? test file
 *
 * @package PHPCompatibility
 */

namespace PHPCompatibility\Tests\BaseClass;

/**
 * isShortList() function tests
 *
 * @group utilityIsShortList
 * @group utilityFunctions
 *
 * @uses    \PHPCompatibility\Tests\BaseClass\MethodTestFrame
 * @package PHPCompatibility
 * @author  Juliette Reinders Folmer <phpcompatibility_nospam@adviesenzo.nl>
 */
class IsShortListTest extends MethodTestFrame
{

    /**
     * The file name for the file containing the test cases within the
     * `sniff-examples/utility-functions/` directory.
     *
     * @var string
     */
    protected $filename = 'is_short_list.php';

    /**
     * testIsShortList
     *
     * @dataProvider dataIsShortList
     *
     * @covers \PHPCompatibility\Sniff::isShortList
     *
     * @param string    $commentString The comment which prefaces the target token in the test file.
     * @param string    $expected      The expected boolean return value.
     * @param int|array $targetToken   The token(s) to test with.
     *
     * @return void
     */
    public function testIsShortList($commentString, $expected, $targetToken = T_OPEN_SHORT_ARRAY)
    {
        $stackPtr = $this->getTargetToken($commentString, $targetToken);
        $result   = $this->helperClass->isShortList($this->phpcsFile, $stackPtr);

        $this->assertSame($expected, $result);
    }

    /**
     * dataIsShortList
     *
     * @see testIsShortList()
     *
     * @return array
     */
    public function dataIsShortList()
    {
        return array(
            array('/* Case 1 */', false, T_ARRAY),
            array('/* Case 2 */', false, T_LIST),
            array('/* Case 3 */', false, array(T_OPEN_SHORT_ARRAY, T_OPEN_SQUARE_BRACKET)),
            array('/* Case 4 */', true),
            array('/* Case 5 */', true, T_CLOSE_SHORT_ARRAY),
            array('/* Case 6 */', true),
            array('/* Case 7 */', true),
            array('/* Case 8 */', true),
            array('/* Case 9 */', true),
            array('/* Case 10 */', true),
            array('/* Case 11 */', true, T_CLOSE_SHORT_ARRAY),
            array('/* Case 12 */', true),
            array('/* Case 13 */', true),
            array('/* Case 14 */', true),
            array('/* Case 15 */', true),
            array('/* Case 16 */', true),
            array('/* Case 17 */', true),
            array('/* Case 18 */', true),
            array('/* Case 19 */', true),
            array('/* Case 20 */', true),
            array('/* Case 21 */', true),
            array('/* Case 22 */', false, array(T_OPEN_SHORT_ARRAY, T_OPEN_SQUARE_BRACKET)),
            array('/* Case 23 */', false, array(T_OPEN_SHORT_ARRAY, T_OPEN_SQUARE_BRACKET)),
            array('/* Case 24 */', false, array(T_OPEN_SHORT_ARRAY, T_OPEN_SQUARE_BRACKET)),
            array('/* Case 25 */', false, array(T_OPEN_SHORT_ARRAY, T_OPEN_SQUARE_BRACKET)),
            array('/* Case 26 */', false, array(T_OPEN_SHORT_ARRAY, T_OPEN_SQUARE_BRACKET)),
            array('/* Case final */', false, array(T_OPEN_SHORT_ARRAY, T_OPEN_SQUARE_BRACKET)),
        );
    }
}
