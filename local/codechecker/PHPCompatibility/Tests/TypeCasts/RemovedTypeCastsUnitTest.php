<?php
/**
 * Deprecated/Removed type casts sniff test file
 *
 * @package PHPCompatibility
 */

namespace PHPCompatibility\Tests\TypeCasts;

use PHPCompatibility\Tests\BaseSniffTest;

/**
 * Deprecated/Removed type casts sniff tests
 *
 * @group removedTypeCasts
 * @group typeCasts
 *
 * @covers \PHPCompatibility\Sniffs\TypeCasts\RemovedTypeCastsSniff
 *
 * @uses    \PHPCompatibility\Tests\BaseSniffTest
 * @package PHPCompatibility
 * @author  Juliette Reinders Folmer <phpcompatibility_nospam@adviesenzo.nl>
 */
class RemovedTypeCastsUnitTest extends BaseSniffTest
{

    /**
     * testDeprecatedTypeCastWithAlternative
     *
     * @dataProvider dataDeprecatedTypeCastWithAlternative
     *
     * @param string $castDescription   The type of type cast.
     * @param string $deprecatedIn      The PHP version in which the function was deprecated.
     * @param string $alternative       An alternative function.
     * @param array  $lines             The line numbers in the test file which apply to this function.
     * @param string $okVersion         A PHP version in which the function was still valid.
     * @param string $deprecatedVersion Optional PHP version to test deprecation message with -
     *                                  if different from the $deprecatedIn version.
     *
     * @return void
     */
    public function testDeprecatedTypeCastWithAlternative($castDescription, $deprecatedIn, $alternative, $lines, $okVersion, $deprecatedVersion = null)
    {
        $file = $this->sniffFile(__FILE__, $okVersion);
        foreach ($lines as $line) {
            $this->assertNoViolation($file, $line);
        }

        $errorVersion = (isset($deprecatedVersion)) ? $deprecatedVersion : $deprecatedIn;
        $file         = $this->sniffFile(__FILE__, $errorVersion);
        $error        = "{$castDescription} is deprecated since PHP {$deprecatedIn}; Use {$alternative} instead";
        foreach ($lines as $line) {
            $this->assertWarning($file, $line, $error);
        }
    }

    /**
     * Data provider.
     *
     * @see testDeprecatedTypeCastWithAlternative()
     *
     * @return array
     */
    public function dataDeprecatedTypeCastWithAlternative()
    {
        return array(
            array('The unset cast', '7.2', 'unset()', array(8, 11, 12), '7.1'),
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
        $file = $this->sniffFile(__FILE__, '99.0'); // High version beyond latest deprecation.
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
            array(5),
        );
    }


    /**
     * Verify no notices are thrown at all.
     *
     * @return void
     */
    public function testNoViolationsInFileOnValidVersion()
    {
        $file = $this->sniffFile(__FILE__, '7.1'); // Low version below the first deprecation.
        $this->assertNoViolation($file);
    }
}
