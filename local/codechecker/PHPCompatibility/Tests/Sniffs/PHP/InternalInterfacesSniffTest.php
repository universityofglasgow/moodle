<?php
/**
 * Internal Interfaces Sniff test file
 *
 * @package PHPCompatibility
 */


/**
 * Internal Interfaces Sniff tests
 *
 * @group internalInterfaces
 * @group interfaces
 *
 * @covers PHPCompatibility_Sniffs_PHP_InternalInterfacesSniff
 *
 * @uses    BaseSniffTest
 * @package PHPCompatibility
 * @author  Juliette Reinders Folmer <phpcompatibility_nospam@adviesenzo.nl>
 */
class InternalInterfacesSniffTest extends BaseSniffTest
{
    const TEST_FILE = 'sniff-examples/internal_interfaces.php';

    /**
     * Sniffed file
     *
     * @var PHP_CodeSniffer_File
     */
    protected $_sniffFile;

    /**
     * Interface error messages.
     *
     * @var array
     */
    protected $messages = array(
        'Traversable'       => 'The interface Traversable shouldn\'t be implemented directly, implement the Iterator or IteratorAggregate interface instead.',
        'DateTimeInterface' => 'The interface DateTimeInterface is intended for type hints only and is not implementable.',
        'Throwable'         => 'The interface Throwable cannot be implemented directly, extend the Exception class instead.',
    );

    /**
     * Set up the test file for this unit test.
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        // Sniff file without testVersion as all checks run independently of testVersion being set.
        $this->_sniffFile = $this->sniffFile(self::TEST_FILE);
    }

    /**
     * Test InternalInterfaces
     *
     * @dataProvider dataInternalInterfaces
     *
     * @param string $interface Interface name.
     * @param array  $line      The line number in the test file.
     *
     * @return void
     */
    public function testInternalInterfaces($type, $line)
    {
        $this->assertError($this->_sniffFile, $line, $this->messages[$type]);
    }

    /**
     * Data provider.
     *
     * @see testInternalInterfaces()
     *
     * @return array
     */
    public function dataInternalInterfaces()
    {
        return array(
            array('Traversable', 3),
            array('DateTimeInterface', 4),
            array('Throwable', 5),
            array('Traversable', 7),
            array('Throwable', 7),

            // Anonymous classes.
            array('Traversable', 17),
            array('DateTimeInterface', 18),
            array('Throwable', 19),
            array('Traversable', 20),
            array('Throwable', 20),
        );
    }

    /**
     * Test interfaces in different cases.
     *
     * @return void
     */
    public function testCaseInsensitive()
    {
        $this->assertError($this->_sniffFile, 9, 'The interface DATETIMEINTERFACE is intended for type hints only and is not implementable.');
        $this->assertError($this->_sniffFile, 10, 'The interface datetimeinterface is intended for type hints only and is not implementable.');
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
        $this->assertNoViolation($this->_sniffFile, $line);
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
            array(13),
            array(14),
        );
    }


    /*
     * `testNoViolationsInFileOnValidVersion` test omitted as this sniff is version independent.
     */

}
