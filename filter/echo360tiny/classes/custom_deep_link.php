<?php

namespace filter_echo360tiny;

require_once($CFG->dirroot . "/mod/lti/locallib.php");

class custom_deep_link {
    /**
     * Generate the form for initiating a login request for an LTI 1.3 message
     *
     * @param int            $courseid  Course ID
     * @param int            $id        LTI instance ID
     * @param stdClass|null  $instance  LTI instance
     * @param stdClass       $config    Tool type configuration
     * @param string         $messagetype   LTI message type
     * @param string         $title     Title of content item
     * @param string         $text      Description of content item
     * @return string
     */
    public function lti_initiate_login($courseid, $id, $instance, $config, $messagetype, $title, $text,
            $deeplinkurl, $customparams) {
        global $SESSION;

        $stdparams = lti_build_login_request($courseid, $id, $instance, $config, $messagetype);
        $params = array_merge($stdparams, $customparams);

        // To prevent multiple embeds in pages/forums inadvertently overriding
        // or clearing each other's session checks in the launch stage in the
        // auth.php file.
        $SESSION->lti_message_hint_arr["{$id}"] = "{$courseid},{$config->typeid},{$id}," . base64_encode($title) . ',' .
            base64_encode($text) . ',' . base64_encode($deeplinkurl);

        $r = "<form action=\"" . $config->lti_initiatelogin .
            "\" name=\"ltiInitiateLoginForm\" id=\"ltiInitiateLoginForm\" method=\"post\" " .
            "encType=\"application/x-www-form-urlencoded\">\n";

        foreach ($params as $key => $value) {
            $key = htmlspecialchars($key);
            $value = htmlspecialchars($value);
            $r .= "  <input type=\"hidden\" name=\"{$key}\" value=\"{$value}\"/>\n";
        }
        $r .= "</form>\n";

        $r .= "<script type=\"text/javascript\">\n" .
            "//<![CDATA[\n" .
            "document.ltiInitiateLoginForm.submit();\n" .
            "//]]>\n" .
            "</script>\n";

        return $r;
    }
}
