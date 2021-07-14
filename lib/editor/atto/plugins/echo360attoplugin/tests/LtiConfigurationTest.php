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
 * Atto text editor integration
 *
 * @package   atto_echo360attoplugin
 * @copyright COPYRIGHTINFO
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace Echo360;

use \Exception as Exception;
use \TypeError as TypeError;

defined('MOODLE_INTERNAL') || die();

class MockObject {

    public $key;
    public function __construct() {
        $this->key = 'value';
    }

}

function get_config($pluginname, $configname) {
    return LtiConfigurationTest::$mockconfigval ?: \get_config($pluginname, $configname);
}

function get_user_roles($context, $id) {
    return LtiConfigurationTest::$mockuserroles ?: \get_user_roles($context, $id);
}

function role_get_name($role, $context) {
    return LtiConfigurationTest::$mockuserrolename ?: \role_get_name($role, $context);
}

function get_admins() {
    return LtiConfigurationTest::$mockadminlist ?: \get_admins();
}

function lti_get_ims_role($user, $cmid, $course, $islti2) {
    return LtiConfigurationTest::$mockimsrole ?: \lti_get_ims_role($user, $cmid, $course->id, $islti2);
}

class LtiConfigurationTest extends \PHPUnit\Framework\TestCase {

    /**
     * @var string $mockconfigval Configuration value that will be returned by get_config()
     */
    public static $mockconfigval;

    /**
     * @var array $mockuserroles User roles array that will be returned by get_user_roles()
     */
    public static $mockuserroles;

    /**
     * @var string $mockuserrolename User role names array that will be returned by role_get_name()
     */
    public static $mockuserrolename;

    /**
     * @var string $mockadminlist List of admin users that will be returned by get_admins()
     */
    public static $mockadminlist;

    /**
     * @var LtiConfiguration $lticonfiguration Test subject
     */
    private $lticonfiguration;

    /**
     * @var function $mockrequirelocallib require file stub
     */
    public static $mockrequirelocallib;

    /**
     * @var string $mockimsrole semi-colon delimited list of roles
     */
    public static $mockimsrole;

    /**
     * Create test subject before test
     */
    protected function set_up() {
        parent::set_up();
        self::$mockconfigval = 'mock_config_val';
        self::$mockuserroles = array('1');
        self::$mockuserrolename = 'Teacher';
        self::$mockadminlist = array(
            array(
                'id' => '1',
                'name' => 'Admin'
            )
        );
        self::$mockrequirelocallib = function () {
        };
        self::$mockimsrole = "ims:teacher";
        $this->ltiConfiguration = new LtiConfiguration(self::$mockcontext, PLUGIN_NAME);
    }

    /**
     * Reset custom time after test
     */
    protected function tear_down() {
        self::$mockconfigval = null;
        self::$mockuserroles = null;
        self::$mockuserrolename = null;
        self::$mockadminlist = null;
    }

    public function test_can_be_created_from_valid_context() {
        $this->assertInstanceOf(LtiConfiguration::class, $this->ltiConfiguration);
    }

    public function test_cannot_be_created_from_null_context() {
        $this->expectException(Exception::class);
        new LtiConfiguration(null, 'plugin');
    }

    public function test_cannot_be_created_from_empty_context() {
        $this->expectException(Exception::class);
        new LtiConfiguration(array(), 'plugin');
    }

    public function test_get_plugin_config() {
        $pluginconfig = LtiConfiguration::get_plugin_config('hosturl', PLUGIN_NAME);
        $this->assertEquals(self::$mockconfigval, $pluginconfig);
    }

    public function test_get_role_names() {
        $rolenames = LtiConfiguration::get_role_names(self::$mockcontext, self::$mockuserroles, PLUGIN_NAME);
        $this->assertEquals(
            array(self::$mockimsrole, self::$mockuserrolename, self::$mockadminlist[0]['name']),
            $rolenames
        );
    }

    public function test_sort_array_alphabetically() {
        $arr = array("beta" => "beta val", "charlie" => "charlie val", "alpha" => "alpha val");
        $sorted = LtiConfiguration::sort_array_alphabetically($arr);
        $this->assertEquals(array('alpha=alpha%20val', 'beta=beta%20val', 'charlie=charlie%20val'), $sorted);
    }

    public function test_cannot_sort_array_alphabetically_from_null() {
        $this->expectException(TypeError::class);
        LtiConfiguration::sort_array_alphabetically(null);
    }

    public function test_cannot_sort_array_alphabetically_from_string() {
        $this->expectException(TypeError::class);
        LtiConfiguration::sort_array_alphabetically('String');
    }

    public function test_object_to_json() {
        $obj = new MockObject();
        $tojson = LtiConfiguration::object_to_json($obj);
        $this->assertEquals('{"key":"value"}', $tojson);
    }

    public function test_can_convert_object_to_json_from_string() {
        $tojson = LtiConfiguration::object_to_json('String');
        $this->assertEquals('["String"]', $tojson);
    }

    public function test_can_convert_object_to_json_from_null() {
        $tojson = LtiConfiguration::object_to_json(null);
        $this->assertEquals('[]', $tojson);
    }

    public function test_sanitize_roles_admin() {
        $adminroles = array(
        'urn:lti:instrole:ims/lis/administrator',
        'urn:lti:sysrole:ims/lis/administrator',
        'faculty'
        );
        foreach ($adminroles as $adminrole) {
            $roles = array($adminrole);
            $role = $this->ltiConfiguration->sanitize_roles($roles);
            $this->assertEquals('Administrator', $role);
        }
    }

    public function test_sanitize_roles_instructor() {
        $instructorroles = array(
            'urn:lti:role:ims/lis/mentor',
            'urn:lti:role:ims/lis/contentdeveloper',
            'urn:lti:role:ims/lis/teachingassistant',
            'urn:lti:role:ims/lis/teachingassistant/grader'
        );
        foreach ($instructorroles as $instructorrole) {
            $roles = array($instructorrole);
            $role = $this->ltiConfiguration->sanitize_roles($roles);
            $this->assertEquals('Instructor', $role);
        }
    }

    public static $mockcontext = array(
        "cacherev" => "9999999999",
        "calendartype" => "",
        "category" => "2",
        "completionnotify" => "0",
        "defaultgroupingid" => "0",
        "enablecompletion" => "0",
        "enddate" => "1522288800",
        "format" => "weeks",
        "fullname" => "Intro to LTI",
        "groupmode" => "0",
        "groupmodeforce" => "0",
        "id" => "4",
        "idnumber" => "9999",
        "lang" => "",
        "legacyfiles" => "0",
        "marker" => "0",
        "maxbytes" => "20971520",
        "newsitems" => "0",
        "requested" => "0",
        "shortname" => "ILTI",
        "showgrades" => "0",
        "showreports" => "0",
        "sortorder" => "20001",
        "startdate" => "1516233600",
        "summary" => "<p>Introductory course to Learning tools interoperability&nbsp;</p>",
        "summaryformat" => "1",
        "theme" => "",
        "timecreated" => "1516218899",
        "timemodified" => "1516218899",
        "visible" => "1",
        "visibleold" => "1"
    );
}
