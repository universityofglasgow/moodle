<?php
/*
    Copyright 2016 Watershed Systems
    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at
    http://www.apache.org/licenses/LICENSE-2.0
    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.

Watershed client library

@module WatershedClient
*/

namespace WatershedClient;
use Exception;

class Watershed {

    //@class WatershedClient

    protected $endpoint;
    protected $auth;
    protected $orgId;
    protected $dashboard = null;
    protected $dashboardId;

    /*
    @constructor
    @param {String} [$url] API base endpoint. 
    e.g. "https://watershedlrs.com" or "https://sandbox.watershedlrs.com" (does not include "api/")
    @param {Array} [$authCfg] Authentication details.
        @param {String} [method] Authentication method: "BASIC" only. Defaults to "BASIC".
        Later versions may support "COOKIE".
        @param {String} [header] Complete Basic HTTP Authentication header value.
        @param {String} [username] Watershed username to generate Basic HTTP Authentication 
        header value if not provided.
        @param {String} [password] Watershed password to generate Basic HTTP Authentication 
        header value if not provided.
    @param {Integer} [$orgId] Organization ID to make calls in
    @param {Integer} [$dashboard] Dashboard ID to make calls in
    */
    public function __construct($url, $authCfg, $orgId, $dashboard) {
        $this->setEndpoint($url);
        $this->setAuth($authCfg);
        $this->setOrgId($orgId);
        $this->setDashboard($dashboard);
    }

    /*
    @method setEndpoint Sets the API endpoint to use. 
    @param {String} [$value] Endpoint url, with out without the slash at the end
    */
    public function setEndpoint($value) {
        if (substr($value, -1) != "/") {
            $value .= "/";
        }
        $this->endpoint = $value;
        return $this;
    }


    /*
    @method setAuth Sets the authentication header to use. 
    @param {Array} [$authCfg] Authentication details. See constructor.
    */
    public function setAuth($authCfg) {

        if (isset($authCfg["method"])){
            $this->auth["method"] = $authCfg["method"];
        }
        else
        {
            $this->auth["method"] = "BASIC";
        }

        switch ($this->auth["method"]) {
            //Default to BASIC. Add other supported methods here.
            default:
                if (isset($authCfg["header"])) {
                    $this->auth["header"] = $authCfg["header"];
                }
                else {
                    if (!isset($authCfg["username"])){
                        $authCfg["username"] = "";
                    }
                    if (!isset($authCfg["password"])){
                        $authCfg["password"] = "";
                    }
                    $this->auth["header"] = "Basic ".base64_encode($authCfg["username"].":".$authCfg["password"]);
                }
                break;
        }

        return $this;
    }

    /*
    @method setOrgId Sets the org id to use. 
    @param {String} [$value] dashboard name
    */
    public function setOrgId($value) {
        $this->orgId = $value;
        return $this;
    }

    /*
    @method setDashboard Sets the dashboard to use. 
    @param {String} [$value] dashboard name
    */
    public function setDashboard($value) {
        if (!$this->dashboard == $value){
            $this->dashboard = $value;
            $response = $this->getCardGroup($this->orgId, $value);
            if ($response["status"] == 200) {
                $this->dashboardId = $response["groupId"];
            }
            else {
                throw new Exception('Unable to set dashboard id.');
            }
        }
        return $this;
    }

    /*
    @method sendRequest Sends a request to the API.
    @param {String} [$method] Method of the request e.g. POST.
    @param {String} [$path] Relative path of the resource. Does not include "/api/".
    @param {Array} [$options] Array of optional properties.
        @param {String} [content] Content of the request (should be JSON).
    @return {Array} Details of the response
        @return {String} [metadata] Raw metadata of the response
        @return {String} [content] Raw content of the response
        @return {Integer} [status] HTTP status code of the response e.g. 201
    */
    public function sendRequest($method, $path) {
        $options = func_num_args() === 3 ? func_get_arg(2) : array();

        $url = $this->endpoint."api/".$path;

        $http = array(
            //
            // we don't expect redirects
            //
            'max_redirects' => 0,
            //
            // this is here for some proxy handling
            //
            'request_fulluri' => 1,
            //
            // switching this to false causes non-2xx/3xx status codes to throw exceptions
            // but we need to handle the "error" status codes ourselves in some cases
            //
            'ignore_errors' => true,
            'method' => $method,
            'header' => array()
        );
        
        if ($this->auth["method"] == "BASIC"){
            array_push($http['header'], 'Authorization: ' . $this->auth["header"]);
        }
        else {
            throw new \Exception("Unsupported authentication method.");
        }

        if (($method === 'PUT' || $method === 'POST') && isset($options['content'])) {
            $http['content'] = $options['content'];
            array_push($http['header'], 'Content-length: ' . strlen($options['content']));
            array_push($http['header'], 'Content-Type: application/json');
        }
        $context = stream_context_create(array( 'http' => $http ));
        $fp = fopen($url, 'rb', false, $context);
        if (! $fp) {
            throw new \Exception("Request failed: $php_errormsg");
        }
        $metadata = stream_get_meta_data($fp);
        $content  = stream_get_contents($fp);
        $responseCode = (int)explode(' ', $metadata["wrapper_data"][0])[1];

        fclose($fp);

        return array (
            "metadata" => $metadata,
            "content" => $content,
            "status" => $responseCode
        );
    }

    /*
    @method getUUID Returns a valid version 4 UUID
    @return {String} UUID
    */
    //
    // Based on code from
    // http://stackoverflow.com/questions/2040240/php-function-to-generate-v4-uuid
    // Taken fron TinCanPHP Copyright Rustici Software 2014
    // https://github.com/RusticiSoftware/TinCanPHP/blob/cce69fdf886945779be2684c272c0969e61096ec/src/Util.php
    //
    public static function getUUID() {
        $randomString = openssl_random_pseudo_bytes(16);
        $time_low = bin2hex(substr($randomString, 0, 4));
        $time_mid = bin2hex(substr($randomString, 4, 2));
        $time_hi_and_version = bin2hex(substr($randomString, 6, 2));
        $clock_seq_hi_and_reserved = bin2hex(substr($randomString, 8, 2));
        $node = bin2hex(substr($randomString, 10, 6));
        /**
         * Set the four most significant bits (bits 12 through 15) of the
         * time_hi_and_version field to the 4-bit version number from
         * Section 4.1.3.
         * @see http://tools.ietf.org/html/rfc4122#section-4.1.3
        */
        $time_hi_and_version = hexdec($time_hi_and_version);
        $time_hi_and_version = $time_hi_and_version >> 4;
        $time_hi_and_version = $time_hi_and_version | 0x4000;
        /**
         * Set the two most significant bits (bits 6 and 7) of the
         * clock_seq_hi_and_reserved to zero and one, respectively.
         */
        $clock_seq_hi_and_reserved = hexdec($clock_seq_hi_and_reserved);
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;
        return sprintf(
            '%08s-%04s-%04x-%04x-%012s',
            $time_low,
            $time_mid,
            $time_hi_and_version,
            $clock_seq_hi_and_reserved,
            $node
        );
    }

    /*
    @method buildListString Takes an array of strings [x,y,z] and returns a string list "x, y and z".
    @param {Array} [$array] Array of strings. 
    @return {String} Human readable list. 
    */
    public function buildListString($array) {
        $counter = 0;
        $count = count($array);
        $returnStr = "";
        foreach ($array as $item) {
            $counter++;
            if ($counter == $count) {
                $returnStr .= $item;
            }
            elseif ($counter == ($count - 1)) {
                $returnStr .= $item." and ";
            }
            else {
                $returnStr .= $item.", ";
            }
        }
        return $returnStr;
    }

    /*
    @method buildMeasure Build a measure "object" (actually an associative array, but it will be an object in JSON).
    @param {String} [$name] Human readable name of the measure.
    @param {String} [$aggregationType] Type of aggregation. See docs for possible values. 
    @param {String} [$property] Statement property to use. Includes some additional calculated properties. See docs.
    @param {String} [$match] Value to match against the value of the statement property.
    @return {Array} Measure object formatted for card configuration.
    */
    public function buildMeasure($name, $aggregationType, $property, $match = NULL){
        $measure = array (
            "name" => $name,
            "aggregation" => array (
                "type" => $aggregationType
            ),
            "valueProducer" => array (
                "type" => "STATEMENT_PROPERTY",
                "statementProperty" => $property
            )
        );

        if (!is_null($match)) {
            $measure["valueProducer"]["type"] = "SIMPLE_IF";
            $measure["valueProducer"]["match"] = $match;
        }

        return $measure;
    }

    /*
    @method getMeasure convert a simple language measure name into a measure object. Helper function to call buildMeasure.
    @param {String} [$measureName] Name of the measure e.g. "First score", "Verb Count" or "Unique raw score count"
    @param {String} [$match] Value to match against the value of the statement property. 
        Only used with "X count" and "Unique X Count" style measures. 
    @param {String} [$measureTitle] Human readbale display of the measure, if different from the name. 
    @return {Array} Measure object formatted for card configuration.
    */
    public function getMeasure($measureName, $match = TRUE, $measureTitle = NULL) {

        if ($measureTitle == NULL) {
            $measureTitle = $measureName;
        }

        $aggregationMap = array (
            "first" => "FIRST",
            "latest" => "LAST",
            "highest" => "MAX",
            "longest" => "MAX",
            "lowest" => "MIN",
            "shortest" => "MIN",
            "average" => "AVERAGE",
            "total" => "SUM"
        );

        $propertyMap = array (
            "score" => "result.score.scaled",
            "scaled" => "result.score.scaled",
            "raw" => "result.score.raw",
            "time" => "result.durationCentiseconds",
            "statement" => "id",
            "activity" => "object.id",
            "verb" => "verb.id",
            "completion" => "result.completion",
            "success" => "result.success",
            "person" => "actor.person.id"
        );

        $aggregationType;
        $property;

        $measureNameLC = strtolower($measureName);
        $measureNameArr = explode(" ", $measureNameLC);

        $lastword = substr($measureNameLC, strrpos($measureNameLC, ' ') + 1);

        if ($lastword == "count") {
            $firstword = strtok($measureNameLC, " ");
            if ($firstword == "unique") {
                $aggregationType = "DISTINCT_COUNT";
                $property = $propertyMap[$measureNameArr[1]];
            }
            else {
                $aggregationType = "COUNT";
                $property = $propertyMap[$measureNameArr[0]];
            }
        }
        else {
            $aggregationType = $aggregationMap[$measureNameArr[0]];
            $property = $propertyMap[$measureNameArr[1]];
            $match = NULL;
        }

        return $this->buildMeasure($measureTitle, $aggregationType, $property, $match);
    }

    /*
    @method getDimension convert a simple language dimension name into a dimension object.
    @param {String} [$dimensionName] Human readable name of the measure e.g. "month", "Activity Type" or "person"
    @return {Array} Dimension object formatted for card configuration.
    */
    public function getDimension($dimensionName) {

        //$dimensionNam is case insensitive
        $dimensionName = strtolower($dimensionName);

        $dimension = array (
            "type" => "STATEMENT_PROPERTY",
        );

        switch ($dimensionName) {
            case 'activity':
                $dimension["statementProperty"] = "object.id";
                break;

            case 'activity type':
                $dimension["statementProperty"] = "object.definition.type";
                break;

            case 'day':
            case 'week':
            case 'month':
            case 'year':
                $dimension["type"] = "TIME";
                $dimension["timePeriod"] = strtoupper($dimensionName);
                break;

            default: 
                $dimension["statementProperty"] = "actor.person.id";
                break;
        }

        return $dimension;
    }

    /*
    @method createOrganization Calls the API to create a new organization. 
    @param {String} [$name] Name of the orgaization to create. Must be unique. 
    @return {Array} Details of the result of the request
        @return {Boolean} [success] Was the request was a success?
        @return {String} [content] Raw content of the response
        @return {Integer} [status] HTTP status code of the response e.g. 201
        @return {Integer} [orgId] Id of the organization created. 
    */
    public function createOrganization($name) {
        $response = $this->sendRequest("POST", "organizations", array(
                "content" => json_encode( 
                    array(
                        "name"=> $name
                    )
                )
            )
        );

        $success = FALSE;
        if ($response["status"] === 201) {
            $success = TRUE ;
        }

        $return = array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        $content = json_decode($response["content"]);

        if (isset ($content->id)) {
            $return["orgId"] = $content->id;
        }
        else {
            $return["orgId"] = NULL;
        }

        return $return;
    }

        /*
    @method getOrganizations Gets a list of all organizations 
    @param {String} [$search] partial org name to search for
    @return {Array} Details of the result of the request
        @return {Boolean} [success] Was the request was a success?
        @return {String} [content] Raw content of the response
        @return {Integer} [status] HTTP status code of the response e.g. 201
        @return {Integer} [orgId] Id of the organization created. 
    */
    public function getOrganizations($search = null) {
        $url = "organizations";
        if (!is_null($search)){
            $url = $url . '?in_name=' . urlencode($search);
        }
        $response = $this->sendRequest("GET", $url, array());

        $success = FALSE;
        if ($response["status"] === 200) {
            $success = TRUE ;
        }

        $return = array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        return $return;
    }

    /*
    @method updateOrganization Calls the API to update an organization. 
    @param {Object} [$org] Updated Org
    @return {Array} Details of the result of the request
        @return {Boolean} [success] Was the request was a success?
        @return {String} [content] Raw content of the response
        @return {Integer} [status] HTTP status code of the response e.g. 201
        @return {Integer} [orgId] Id of the organization created. 
    */
    public function updateOrganization($org) {
        $response = $this->sendRequest("PUT", "organizations/".$org->id, array(
                "content" => json_encode($org)
            )
        );

        $success = FALSE;
        if ($response["status"] === 204) {
            $success = TRUE ;
        }

        $return = array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        return $return;
    }

    /*
    @method getOrganizationSettings gets the settings for an org
    @param {String} [$orgid] id of org to get settings for
    @return {Array} Details of the result of the request
        @return {Boolean} [success] Was the request was a success?
        @return {String} [content] Raw content of the response
        @return {Integer} [status] HTTP status code of the response e.g. 201
        @return {Integer} [orgId] Id of the organization created. 
    */
    public function getOrganizationSettings($orgId) {
        $url = "organizations/".$orgId."/settings";

        $response = $this->sendRequest("GET", $url, array());

        $success = FALSE;
        if ($response["status"] === 200) {
            $success = TRUE ;
        }

        $return = array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        return $return;
    }

    /*
    @method getOrganizationSettings gets the settings for an org
    @param {String} [$orgid] id of org to set settings for
    @param {Object} [$settings] new settings for the org
    @return {Array} Details of the result of the request
        @return {Boolean} [success] Was the request was a success?
        @return {String} [content] Raw content of the response
        @return {Integer} [status] HTTP status code of the response e.g. 201
        @return {Integer} [orgId] Id of the organization created. 
    */
    public function updateOrganizationSettings($orgId, $settings) {
        $url = "organizations/".$orgId."/settings";

        $response = $this->sendRequest("PUT", $url, array(
            "content" => json_encode($settings)
        ));

        $success = FALSE;
        if ($response["status"] === 204) {
            $success = TRUE ;
        }

        $return = array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        return $return;
    }

    /*
    @method createActivityProvider Calls the API to create new actvity provider credentials. 
    @param {String} [$name] Name of the activity to create. 
    @param {String} [$orgId] Id of the organization to create the AP on.
    @return {Array} Details of the result of the request
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response
        @return {Integer} [status] HTTP status code of the response e.g. 201
        @return {String} [key] xAPI Basic Auth key/login
        @return {String} [secret] xAPI Basic Auth secret/password
        @return {String} [LRSEndpoint] xAPI LRS endpoint
    */
    public function createActivityProvider($name, $orgId) {
        $key = $this->getUUID();
        $secret = $this->getUUID();

        $response = $this->sendRequest("POST", "organizations/{$orgId}/activity-providers", array(
                "content" => json_encode( 
                    array(
                        "name" => $name,
                        "key" => $key,
                        "secret" => $secret,
                        "active" => TRUE,
                        "rootAccess" => TRUE
                    )
                )
            )
        );

        $success = FALSE;
        if ($response["status"] === 201) {
            $success = TRUE ;
        }

        $return = array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"],
            "key" => $key,
            "secret" => $secret,
            "LRSEndpoint" => $this->endpoint."api/organizations/{$orgId}/lrs/"
        );

        return $return;
    }

        /*
    @method deleteActivityProvider Calls the API to delete actvity provider credentials. 
    @param {String} [$id] Id of the activity to delete. 
    @param {String} [$orgId] Id of the organization to delete the AP on.
    @return {Array} Details of the result of the request
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response
        @return {Integer} [status] HTTP status code of the response e.g. 201
    */
    public function deleteActivityProvider($id, $orgId) {
        $key = $this->getUUID();
        $secret = $this->getUUID();

        $response = $this->sendRequest("DELETE", "organizations/{$orgId}/activity-providers/{$id}", array());

        $success = FALSE;
        if ($response["status"] === 200) {
            $success = TRUE ;
        }

        $return = array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"],
        );

        return $return;
    }

    /*
    @method createInvitation Calls the API to invite a user to an org. 
    @param {String} [$name] Full name of the person to invite.
    @param {String} [$email] Email address of the person to invite.
    @param {String} [$role] Role to assign: admin, owner or user. 
    @param {String} [$orgId] Id of the organization to create the invite on.
    @return {Array} Details of the result of the request
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response
        @return {Integer} [status] HTTP status code of the response e.g. 201
    */
    public function createInvitation($name, $email, $role, $orgId) {
        $response = $this->sendRequest("POST", "memberships", array(
                "content" => json_encode( 
                    array(
                        "user" => array(
                            "name" => $name,
                            "email" => $email
                        ),
                        "organization" => array(
                            "id" => $orgId
                        ),
                        "role" => $role,
                        "invitationUrlTemplate" => $this->endpoint."app/outside.html#invitation-signup/{token}"
                    )
                )
            )
        );

        $success = FALSE;
        if ($response["status"] === 201) {
            $success = TRUE ;
        }

        $return = array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"],
        );

        return $return;
    }



     /*
    @method deleteMembershipByUsername Calls the API to from a user from an org. 
    @param {String} [$email] Email address of the person to invite.
    @param {String} [$orgId] Id of the organization to create the invite on.
    @return {Array} Details of the result of the request
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response
        @return {Integer} [status] HTTP status code of the response e.g. 201
    */
    public function deleteMembershipByUsername($email, $orgId) {

        $response = $this->sendRequest("GET", "organizations/".$orgId."/memberships", array());

        $success = FALSE;
        if ($response["status"] !== 200) {
            return array (
                "success" => $success, 
                "status" => $response["status"],
                "content" => $response["content"],
            );
        }

        $memberships = json_decode($response["content"])->results;

        foreach ($memberships as $membership) {
            if ($membership->user->username === $email) {
                $response = $this->sendRequest("DELETE", "memberships/".$membership->id, array());
                if ($response["status"] === 200) {
                    $success = TRUE;
                }
            } 
        }

        $return = array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"],
        );

        return $return;
    }

    /*
    @method getCard Fetches a card, if it exists. 
    @param {String} [$orgId] Id of the organization to create the card on.
    @param {String} [$cardId] Id of the card.
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? (false if group does not exist)
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 404 if group does not exist
        @return {Integer} [groupId] Id of the group found. 
        @return {Array} [cardIds] Ids of the cards in the group.
    */
    public function getCard($orgId, $cardId) {
        $response = $this->sendRequest(
            "GET", 
            "organizations/{$orgId}/cards/?id={$cardId}"
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $content = json_decode($response["content"]);

            if ($content->count > 0) {
                $return["success"] = TRUE;
                $return["cardId"] = $content->results[0]->id;
            }
            else {
                // No result
                $return["status"] = 404;
            }

        }

        return $return;

    }

    /*
    @method createCard Calls the API to create a card. use helper functions for specific card types. 
    @param {Array} [$configuration] Card configuration "object" (do not JSON encode!).
    @param {String} [$template] name of card template to use e.g. "leaderboard" or "activity".
    @param {Array} [$cardText] array of card test values
        @param {String} [$title] Card title
        @param {String} [$description] Card description
        @param {String} [$summary] Card summary
    @param {String} [$orgId] Id of the organization to create the card on.
    @return {Array} Details of the result of the request.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 201.
        @return {Integer} [cardId] Id of the card created.
    */
    public function createCard($configuration, $template, $cardText, $orgId) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $response = $this->sendRequest("POST", "cards", array(
                "content" => json_encode(
                    array (
                        "configuration" => $configuration,
                        "organization" => array (
                            "id" => $orgId
                        ),
                        "template" => array (
                            "name" => strtolower($template)
                        ),
                        "title" => $cardText["title"],
                        "description" => $cardText["description"],
                        "summary" => $cardText["summary"]
                    )
                )
            )
        );

        $success = FALSE;
        if ($response["status"] === 201) {
            $success = TRUE;
        }

        $return = array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        $content = json_decode($response["content"]);

        if (isset ($content->id)) {
            $return["cardId"] = $content->id;
        }
        else {
            $return["cardId"] = NULL;
        }

        return $return;
    }

    /*
    @method createCard Calls the API to create a card within a group 
    @param {Array} [$configuration] Card configuration "object" (do not JSON encode!).
    @param {String} [$template] name of card template to use e.g. "leaderboard" or "activity".
    @param {Array} [$cardText] array of card test values
        @param {String} [$title] Card title
        @param {String} [$description] Card description
        @param {String} [$summary] Card summary
    @param {String} [$orgId] Id of the organization to create the card on.
    @param {String} [$groupId] Id of the group to create the card in.
    @return {Array} Details of the result of the request.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 201.
        @return {Integer} [cardId] Id of the card created.
    */
    public function createCardInGroup($configuration, $template, $cardText, $orgId, $groupId = null, $groupName = null, $groupIsDashboard = false) {
        $groupName;
        if ($groupId == null) {
            $groupId = $this->dashboardId;
            $groupName = $this->dashboard;
            $groupIsDashboard = true;
        }

        // Create card
        $response = $this->createCard($configuration, $template, $cardText, $orgId);
        if ($response["success"] == FALSE) {
            $response["method"] = "createCard";
            return $response;
        }

        $cardId = $response["cardId"];

        // Put card in group
        $response = $this->AddCardsToGroup([$cardId], $groupName, $orgId, $groupIsDashboard);

        $return = $response;
        $return["cardId"]  = $cardId;

        return $return;
    }

    /*
    @method deleteCard Calls the API to delete a card. 
    @param {String} [$cardId] Id of the card to delete. 
    @param {String} [$orgId] Id of the organization to delete the card on.
    @return {Array} Details of the result of the request
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response
        @return {Integer} [status] HTTP status code of the response e.g. 201
    */
    public function deleteCard($cardId, $orgId) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }

        $response = $this->sendRequest("DELETE", "cards/{$cardId}", array());

        $success = FALSE;
        if ($response["status"] === 200) {
            $success = TRUE ;
        }

        $return = array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        return $return;
    }

    /*
    @method updateCardConfig Calls the API to update a card's config. 
    @param {String} [$id] Id of the card to delete. 
    @param {String} [$orgId] Id of the organization to delete the card on.
    @return {Array} Details of the result of the request
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response
        @return {Integer} [status] HTTP status code of the response e.g. 201
    */
    public function updateCardConfig($cardId, $newConfig, $orgId) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }

        $response = $this->getCard($orgId, $cardId);
        $content = null;
        if ($response["status"] === 200) {
            $content = json_decode($response["content"], true)["results"][0];
        }
        else {
            return $response;
        }

        $content["configuration"] = $this->array_merge_recursive_distinct($content["configuration"],$newConfig);

        $response = $this->sendRequest("PUT", "cards/{$cardId}", array(
                "content" => json_encode($content)
            )
        );

        $success = FALSE;
        if ($response["status"] === 200) {
            $success = TRUE ;
        }

        $return = array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        return $return;
    }

    /*
    @method createActivityStreamCard Calls the API to create an interactions card filtered by a base activity id URL.
    Uses regex to filter all activity ids starting with the activity id provided. 
    @param {String} [$activityName] xAPI activity name 
    @param {String} [$xAPIActivityId] xAPI activity id (or start of activity id)
    @param {Integer} [$orgId] Id of the organization to create the skill and card on.
    @param {Integer} [$groupId] Id of the group to create the card in.
    @param {String} [$groupName] Name of the group to create the card in.
    @param {Array} [$cardText] array of card test values
        @param {String} [$title] Card title
        @param {String} [$description] Card description
        @param {String} [$summary] Card summary
    @return {Array} Details of the result of the request.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 201.
        @return {Integer} [cardId] Id of the card created. 
    */
    public function createActivityStreamCard($activityName, $xAPIActivityId, $orgId, $groupId = null, $groupName = null, $cardText = array()) {
        $defaultCardText = array(
            "title" => "{$activityName} Activity",
            "description" => "The Interactions report card tells you what's happening now.",
            "summary" => "The Interactions report card tells you what's happening now."
        );
        $cardText = array_merge($defaultCardText, $cardText);

        $configuration = array(
            "filter" => array(
                "activityIds" => array (
                    "ids" => array ($xAPIActivityId.".*"),
                    "regExp" => TRUE
                )
            )
        );

        $response = $this->createCardInGroup(
            $configuration, 
            "interactions", 
            $cardText,
            $orgId,
            $groupId
        );

        return $response;
    }

    /*
    @method createActivityDetailCard Calls the API to create an activity card for a given activity id.
    @param {String} [$activityName] xAPI activity name 
    @param {String} [$xAPIActivityId] xAPI activity id
    @param {Integer} [$orgId] Id of the organization to create the skill and card on.
    @param {Integer} [$groupId] Id of the group to create the card in.
    @param {String} [$groupName] Name of the group to create the card in.
    @param {Array} [$cardText] array of card test values
        @param {String} [$title] Card title
        @param {String} [$description] Card description
        @param {String} [$summary] Card summary
    @return {Array} Details of the result of the request.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 201.
        @return {Integer} [cardId] Id of the card created. 
    */
    public function createActivityDetailCard($activityName, $xAPIActivityId, $orgId, $groupId = null, $groupName = null, $cardText = array()) {
        $defaultCardText = array(
            "title" => "{$activityName} Detail",
            "description" => "The Activity report card enables you to explore an activity in detail.",
            "summary" => "The Activity report card enables you to explore an activity in detail."
        );
        $cardText = array_merge($defaultCardText, $cardText);

        $configuration = array(
            "filter" => array(
                "activityIds" => array (
                    "ids" => array ($xAPIActivityId),
                    "regExp" => FALSE
                )
            )
        );

        $response = $this->createCardInGroup(
            $configuration, 
            "activity", 
            $cardText, 
            $orgId,
            $groupId,
            $groupName
        );

        return $response;
    }

    /*
    @method createLeaderBoardCard Calls the API to create a leaderboard card for a given activity id.
    @param {Array} [$measureList] List measures to use in the leaderboard. Contains an array of measure config arrays. 
        Each measure config array has a name key, and optional match and title keys. See getMeasure above for details. 
    @param {Array} [$dimensionName] xAPI activity name 
    @param {String} [$activityName] xAPI activity name 
    @param {String} [$xAPIActivityId] xAPI activity id
    @param {Integer} [$orgId] Id of the organization to create the skill and card on.
    @param {Integer} [$groupId] Id of the group to create the card in.
    @param {String} [$groupName] Name of the group to create the card in.
    @param {Array} [$filter] Filter to use in place of default
    @param {Array} [$cardText] array of card test values
        @param {String} [$title] Card title
        @param {String} [$description] Card description
        @param {String} [$summary] Card summary
    @return {Array} Details of the result of the request.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 201.
        @return {Integer} [cardId] Id of the card created. 
    */
    public function createLeaderBoardCard($measureList, $dimensionName, $activityName, $xAPIActivityId, $orgId, $groupId = null, $groupName = null, $filter = null, $cardText = array()) {
        $measureNames = array();
        $measures = array();
        foreach ($measureList as $measureItem) {
            if (!isset($measureItem["match"])) {
                $measureItem["match"] = NULL;
            }
            if (!isset($measureItem["title"])) {
                $measureItem["title"] = $measureItem["name"];
            }
            array_push($measures, $this->getMeasure($measureItem["name"], $measureItem["match"], $measureItem["title"]));
            array_push($measureNames, $measureItem["title"]);
        }    

        $dimensions = array(
            $this->getDimension($dimensionName)
        );

        if ($filter == null) {
            $filter = array(
                "activityIds" => array (
                    "ids" => array ($xAPIActivityId),
                    "regExp" => FALSE
                )
            );
        }

        $configuration = array(
            "filter" => $filter,
            "dimensions" => $dimensions,
            "measures" => $measures
        );

        $description = "Use this Leaderboard to find the ";
        $description .= $this->buildListString($measureNames);
        $description .= " of each {$dimensionName}.";

        $defaultCardText = array(
            "title" => "{$activityName} Leaderboard",
            "description" => $description,
            "summary" => $description
        );
        $cardText = array_merge($defaultCardText, $cardText);

        $response = $this->createCardInGroup(
            $configuration, 
            "leaderboard", 
            $cardText,
            $orgId,
            $groupId,
            $groupName
        );
        return $response;
    }

    /*
    @method createBarchartCard Calls the API to create a barchart card for a given activity id.
    @param {Array} [$measureList] List measures to use in the barchart. Contains an array of measure config arrays. 
        Each measure config array has a name key, and optional match and title keys. See getMeasure above for details. 
    @param {Array} [$dimensionName] xAPI activity name 
    @param {String} [$activityName] xAPI activity name 
    @param {String} [$xAPIActivityId] xAPI activity id
    @param {Integer} [$orgId] Id of the organization to create the skill and card on.
    @param {Integer} [$groupId] Id of the group to create the card in.
    @param {String} [$groupName] Name of the group to create the card in.
    @param {Array} [$filter] Filter to use in place of default
    @param {Array} [$cardText] array of card test values
        @param {String} [$title] Card title
        @param {String} [$description] Card description
        @param {String} [$summary] Card summary
    @param {Bool} [$singleGraph] Present all measures on a single chart. Default true. 
    @param {Bool} [$vertical] Arrange the bar chart vertically. Default true. 
    @return {Array} Details of the result of the request.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 201.
        @return {Integer} [cardId] Id of the card created. 
    */
    public function createBarchartCard($measureList, $dimensionName, $activityName, $xAPIActivityId, $orgId, $groupId = null, $groupName = null, $filter = null, $cardText = array(), $singleGraph = true, $vertical = true) {
        $measureNames = array();
        $measures = array();
        foreach ($measureList as $measureItem) {
            if (!isset($measureItem["match"])) {
                $measureItem["match"] = NULL;
            }
            if (!isset($measureItem["title"])) {
                $measureItem["title"] = $measureItem["name"];
            }
            array_push($measures, $this->getMeasure($measureItem["name"], $measureItem["match"], $measureItem["title"]));
            array_push($measureNames, $measureItem["title"]);
        }    

        $dimensions = array(
            $this->getDimension($dimensionName)
        );

        if ($filter == null) {
            $filter = array(
                "activityIds" => array (
                    "ids" => array ($xAPIActivityId),
                    "regExp" => FALSE
                )
            );
        }

        $configuration = array(
            "filter" => $filter,
            "dimensions" => $dimensions,
            "measures" => $measures,
            "singleGraph" => $singleGraph,
            "vertical" => $vertical
        );

        $description = "Use this Barchart to find the ";
        $description .= $this->buildListString($measureNames);
        $description .= " of each {$dimensionName}.";

        $defaultCardText = array(
            "title" => "{$activityName} Barchart",
            "description" => $description,
            "summary" => $description
        );
        $cardText = array_merge($defaultCardText, $cardText);

        $response = $this->createCardInGroup(
            $configuration, 
            "bar", 
            $cardText,
            $orgId,
            $groupId,
            $groupName
        );
        return $response;
    }


    /*
    @method createLinechartCard Calls the API to create a barchart card for a given activity id.
    @param {Array} [$measureList] List measures to use in the barchart. Contains an array of measure config arrays. 
        Each measure config array has a name key, and optional match and title keys. See getMeasure above for details. 
    @param {Array} [$dimensionName] xAPI activity name 
    @param {String} [$activityName] xAPI activity name 
    @param {String} [$xAPIActivityId] xAPI activity id
    @param {Integer} [$orgId] Id of the organization to create the skill and card on.
    @param {Integer} [$groupId] Id of the group to create the card in.
    @param {String} [$groupName] Name of the group to create the card in.
    @param {Array} [$filter] Filter to use in place of default
    @param {Array} [$cardText] array of card test values
        @param {String} [$title] Card title
        @param {String} [$description] Card description
        @param {String} [$summary] Card summary
    @param {Bool} [$singleGraph] Present all measures on a single chart. Default true. 
    @param {Bool} [$line] Use a single line instead of an area effect. Default true. 
    @return {Array} Details of the result of the request.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 201.
        @return {Integer} [cardId] Id of the card created. 
    */
    public function createLinechartCard($measureList, $dimensionName, $activityName, $xAPIActivityId, $orgId, $groupId = null, $groupName = null, $filter = null, $cardText = array(), $singleGraph = true, $line = true, $allowGaps = false) {
        $measureNames = array();
        $measures = array();
        foreach ($measureList as $measureItem) {
            if (!isset($measureItem["match"])) {
                $measureItem["match"] = NULL;
            }
            if (!isset($measureItem["title"])) {
                $measureItem["title"] = $measureItem["name"];
            }
            array_push($measures, $this->getMeasure($measureItem["name"], $measureItem["match"], $measureItem["title"]));
            array_push($measureNames, $measureItem["title"]);
        }    

        $dimensions = array(
            $this->getDimension($dimensionName)
        );

        if ($filter == null) {
            $filter = array(
                "activityIds" => array (
                    "ids" => array ($xAPIActivityId),
                    "regExp" => FALSE
                )
            );
        }

        $configuration = array(
            "filter" => $filter,
            "dimensions" => $dimensions,
            "measures" => $measures,
            "singleGraph" => $singleGraph,
            "line" => $line,
            "allowGaps" => $allowGaps
        );

        $description = "Use this Linechart to find the ";
        $description .= $this->buildListString($measureNames);
        $description .= " of each {$dimensionName}.";

        $defaultCardText = array(
            "title" => "{$activityName} Linechart",
            "description" => $description,
            "summary" => $description
        );
        $cardText = array_merge($defaultCardText, $cardText);

        $response = $this->createCardInGroup(
            $configuration, 
            "line", 
            $cardText,
            $orgId,
            $groupId,
            $groupName
        );
        return $response;
    }

    /*
    @method createCorrelationCard Calls the API to create a correlation card for a given activity id.
    @param {Array} [$measureList] List measures to use in the leaderboard. Contains an array of measure config arrays. 
        Each measure config array has a name key, and optional match and title keys. See getMeasure above for details. 
    @param {Array} [$dimensionName] xAPI activity name 
    @param {String} [$activityName] xAPI activity name 
    @param {String} [$xAPIActivityId] xAPI activity id
    @param {Integer} [$orgId] Id of the organization to create the skill and card on.
    @param {Integer} [$groupId] Id of the group to create the card in.
    @param {String} [$groupName] Name of the group to create the card in.
    @param {Array} [$cardText] array of card test values
        @param {String} [$title] Card title
        @param {String} [$description] Card description
        @param {String} [$summary] Card summary
    @return {Array} Details of the result of the request.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 201.
        @return {Integer} [cardId] Id of the card created. 
    */
    public function createCorrelationCard($measureList, $dimensionName, $activityName, $xAPIActivityId, $orgId, $groupId = null, $groupName = null, $cardText = array()) {
        $measureNames = array();
        $measures = array();
        foreach ($measureList as $measureItem) {
            if (!isset($measureItem["match"])) {
                $measureItem["match"] = NULL;
            }
            if (!isset($measureItem["title"])) {
                $measureItem["title"] = $measureItem["name"];
            }
            array_push($measures, $this->getMeasure($measureItem["name"], $measureItem["match"], $measureItem["title"]));
            array_push($measureNames, $measureItem["title"]);
        }   

        $dimensions = array(
            $this->getDimension($dimensionName)
        );

        $configuration = array(
            "filter" => array(
                "activityIds" => array (
                    "ids" => array ($xAPIActivityId),
                    "regExp" => FALSE
                )
            ),
            "dimensions" => $dimensions,
            "measures" => $measures
        );

        $description = "Use this Correlation to explore relationships between the ";
        $description .= $this->buildListString($measureNames);
        $description .= " of each {$dimensionName}.";

        $defaultCardText = array(
            "title" => "{$activityName} Correlation",
            "description" => $description,
            "summary" => $description
        );
        $cardText = array_merge($defaultCardText, $cardText);

        $response = $this->createCardInGroup(
            $configuration, 
            "correlation", 
            $cardText,
            $orgId,
            $groupId,
            $groupName
        );
        return $response;
    }

    /*
    @method groupCards Makes a series of API calls to put a list of cards into a group.
    @param {Array} [$cardIds] List of integer card ids. 
    @param {String} [$orgId] Id of the organization to create the card on.
    @param {String} [$cardGroupName] Unqiue name of the card group.
    @param {String} [$cardGroupTitle] Display title of the card group.
    @param {String} [$parentGroupName] Name of the card group the cards are currently in, if not the default.
        New cards created by an admin or owner account need to be added to a group after creation. 
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {Integer} [groupId] Id of the group created. 
        @return {Integer} [cardId] Id of the card created. 
    */
    public function groupCards($cardIds, $orgId, $cardGroupName, $cardGroupTitle, $parentGroupName = NULL) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        if ($parentGroupName == NULL) {
            $parentGroupName = $this->dashboard;
        }

        $parentGroupId;
        $startingCards;
        $newCards;
        $cardId;
        $groupId;

        //get the parent group id
        $response = $this->getCardGroup($orgId, $parentGroupName);
        if ($response["status"] == 200) {
            $parentGroupId = $response["groupId"];
            $startingCards = $response["cardIds"];
        }
        else {
            $response["method"] = "getCardGroup";
            return $response;
        }

        $response = createCardGroup($cardIds, $orgId, $cardGroupName, $cardGroupTitle, $parentGroupId);
        $groupId = $response['groupId'];
        $cardId = $response['cardId'];

        //hide grouped cards from parent group
        $response = $this->hideGroupedCards($startingCards, $cardIds, $cardId, $parentGroupId, $parentGroupName, $orgId);
        if (!$response["success"]) {
            $response["method"] = "hideGroupedCards";
            return $response;
        }

        //return group id and card id
        return array (
            "success" => TRUE,
            "groupId" => $groupId,
            "cardId" => $cardId
        );
    }

     /*
    @method createCardGroupAndCard creates an empty card group and a card for that group
    @param {Array} [$cardIds] List of integer card ids. Can be an empty array.
    @param {String} [$orgId] Id of the organization to create the card on.
    @param {String} [$cardGroupName] Unqiue name of the card group.
    @param {String} [$cardGroupTitle] Display title of the card group.
    @param {String} [$parentGroupId] Where to create the group card (id)
    @param {String} [$parentGroupName] Where to create the group card (name)
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {Integer} [groupId] Id of the group created. 
        @return {Integer} [cardId] Id of the card created. 
    */
    public function createCardGroupAndCard($cardIds, $orgId, $cardGroupName, $cardGroupTitle, $parentGroupId = null, $parentGroupName = null) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $cardId;
        $groupId;

        //create card group
        $response = $this->createCardGroup($cardGroupName, $cardIds, $orgId);
        if ($response["success"]) {
            $groupId = $response["groupId"];
        }
        else {
            $response["method"] = "createCardGroup";
            return $response;
        }

        //create group card to display cards
        $cardText = array(
            "title" => $cardGroupTitle,
            "description" => "",
            "summary" => ""
        );

        $response = $this->createCardInGroup(
            array (
                "cardGroupId"=> $groupId
            ), 
            "card-group", 
            $cardText, 
            $orgId, 
            $parentGroupId,
            $parentGroupName,
            true
        );
        if ($response["success"]) {
            $cardId = $response["cardId"];
        }
        else {
            $response["method"] = "createCard";
            return $response;
        }

        //return group id and card id
        return array (
            "success" => TRUE,
            "groupId" => $groupId,
            "groupName" => $cardGroupName,
            "cardId" => $cardId
        );
    }

    /*
    @method getCardGroup Fetches a card group, if it exists. 
    @param {String} [$orgId] Id of the organization to create the card on.
    @param {String} [$cardGroupName] Unqiue name of the card group.
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? (false if group does not exist)
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 404 if group does not exist
        @return {Integer} [groupId] Id of the group found. 
        @return {Array} [cardIds] Ids of the cards in the group.
    */
    public function getCardGroup($orgId, $cardGroupName) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $response = $this->sendRequest(
            "GET", 
            "organizations/{$orgId}/card-groups/?name={$cardGroupName}"
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $content = json_decode($response["content"]);

            if ($content->count > 0) {
                $return["success"] = TRUE;
                $return["groupId"] = $content->results[0]->id;
                $return["groupName"] = $content->results[0]->name;
                $return["cardIds"] = $content->results[0]->cardIds;
            }
            else {
                // No result
                $return["status"] = 404;
            }

        }

        return $return;

    }

    /*
    @method getCardGroupById Fetches a card group, if it exists. 
    @param {String} [$orgId] Id of the organization to create the card on.
    @param {Integer} [$cardGroupId] Unqiue id of the card group.
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? (false if group does not exist)
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 404 if group does not exist
        @return {Integer} [groupId] Id of the group found. 
        @return {Array} [cardIds] Ids of the cards in the group.
    */
    public function getCardGroupById($orgId, $cardGroupId) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $response = $this->sendRequest(
            "GET", 
            "organizations/{$orgId}/card-groups/?id={$cardGroupId}"
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $content = json_decode($response["content"]);

            if ($content->count > 0) {
                $return["success"] = TRUE;
                $return["groupId"] = $content->results[0]->id;
                $return["groupName"] = $content->results[0]->name;
                $return["cardIds"] = $content->results[0]->cardIds;
            }
            else {
                // No result
                $return["status"] = 404;
            }

        }

        return $return;

    }

    /*
    @method deleteCardGroup Deletes a card group,. 
    @param {String} [$orgId] Id of the organization to create the card on.
    @param {Integer} [$cardGroupId] Unqiue id of the card group.
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 404 if group does not exist
    */
    public function deleteCardGroup($orgId, $cardGroupId) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $response = $this->sendRequest(
            "DELETE", 
            "card-groups/{$cardGroupId}"
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $return["success"] = TRUE;
        }

        return $return;

    }

    /*
    @method createCardGroup Uses the API to create a group of cards.
    @param {String} [$cardGroupName] Unqiue name of the card group.
    @param {Array} [$cardIds] List of integer card ids. 
    @param {String} [$orgId] Id of the organization to create the card on.
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 201.
        @return {Integer} [groupId] Id of the group created. 
    */
    public function createCardGroup ($cardGroupName, $cardIds, $orgId) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $response = $this->sendRequest(
            "POST", 
            "card-groups", 
            array (
                "content" => 
                json_encode( 
                    array(
                        "name" => $cardGroupName,
                        "cardIds" => $cardIds,
                        "organization" => array (
                            "id" => $orgId
                        )
                    )
                )
            )
        );

        $success = FALSE;
        if ($response["status"] === 201) {
            $success = TRUE ;
        }

        $return = array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        $content = json_decode($response["content"]);
        
        if (isset ($content->id)) {
            $return["groupId"] = $content->id;
        }
        else {
            $return["groupId"] = NULL;
        }

        return $return;
    }

    /*
    @method hideGroupedCards removes a set of cards from a group and adds a card to that group. 
    Designed to be used to remove a set of cards that have been grouped and add the group card. 
    @param {Array} [$startingCards] List of integer card ids. 
        Those cards which are in the group to begin with (not including the group card to add). 
    @param {Array} [$groupedCards] List of integer card ids. 
        Those cards which are to be removed from the group. (I.e. those that have been added to the new group)
    @param {Integer} [$groupCardId] Id of card to add to the group (I.e. the group card)
    @param {String} [$parentGroupId] Id of the card group to be editted.
    @param {String} [$parentGroupName] Name of the card group to be editted.
    @param {String} [$orgId] Id of the organization the group exists in.
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 201.
    */
    public function hideGroupedCards ($startingCards, $groupedCards, $groupCardId, $parentGroupId, $parentGroupName, $orgId){
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $newCards = array_values(array_diff($startingCards, $groupedCards));

        if (!is_null($groupCardId)) {
            array_push($newCards, $groupCardId);
        }

        $response = $this->sendRequest(
            "PUT", 
            "card-groups/{$parentGroupId}", 
            array (
                "content" => json_encode(
                    array(
                        "name" => $parentGroupName,
                        "cardIds" => $newCards,
                        "organization" => array (
                            "id" => $orgId
                        )
                    )
                )
            )
        );

        $success = FALSE;
        if ($response["status"] === 204) {
            $success = TRUE ;
        }

        return array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"]
        );
    }

    /*
    @method moveCardsGroup removes a set of cards from one group and adds them to another.
    @param {Array} [$cardIds] List of integer card ids. 
        Those cards which are to be moved. 
    @param {String} [$newGroupName] Name of the card group to move the cards to.
    @param {String} [$oldGroupName] Name of the card group to move the cards from.
    @param {String} [$orgId] Id of the organization the group exists in.
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 201.
    */
    public function moveCardsGroup ($cardIds, $newGroupName, $oldGroupName, $orgId){
        if ($orgId == null) {
            $orgId = $this->orgId;
        }

        // remove cards from the old group
        $response = $this->removeCardsFromGroup ($cardIds, $oldGroupName, $orgId);

        if ($response['success'] === FALSE) {
            return $response;
        }

        // add cards to the new group
        $response = $this->AddCardsToGroup ($cardIds, $newGroupName, $orgId);

        $success = FALSE;
        if ($response["status"] === 204) {
            $success = TRUE ;
        }

        return array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"]
        );
    }

    /*
    @method removeCardsFromGroup removes a set of cards from one group and adds them to another.
    @param {Array} [$cardIds] List of integer card ids. 
        Those cards which are to be moved. 
    @param {String} [$oldGroupName] Name of the card group to move the cards from.
    @param {String} [$orgId] Id of the organization the group exists in.
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 204.
    */
    public function removeCardsFromGroup ($cardIds, $oldGroupName, $orgId){
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        if ($oldGroupName == null) {
            $oldGroupName = $this->dashboardId;
        }

        // remove cards from the old group
        $oldGroup = $this->getCardGroup($orgId, $oldGroupName);

        if ($oldGroup['success'] === FALSE) {
            $oldGroup['method'] = 'getCardGroup';
            return $oldGroup;
        }

        $response = $this->hideGroupedCards ($oldGroup['cardIds'], $cardIds, null, $oldGroup['groupId'], $oldGroupName, $orgId);

        if ($response['success'] === FALSE) {
            $response['method'] = 'hideGroupedCards';
            return $response;
        }

        $success = FALSE;
        if ($response["status"] === 204) {
            $success = TRUE ;
        }

        return array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"]
        );
    }

    /*
    @method AddCardsToGroup add a set of cards to a group identified by name.
    @param {Array} [$cardIds] List of integer card ids. 
        Those cards which are to be moved. 
    @param {String} [$newGroupName] Name of the card group to move the cards to.
    @param {String} [$orgId] Id of the organization the group exists in.
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 201.
    */
    public function AddCardsToGroup ($cardIds, $newGroupName, $orgId, $groupIsDashboard = false){
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        // add cards to the new group
        $newGroup = $this->getCardGroup($orgId, $newGroupName);

        if ($newGroup['success'] === FALSE) {
            $newGroup['method'] = 'getCardGroup';
            return $newGroup;
        }

        $newCards = array_merge($newGroup['cardIds'], $cardIds);

        $content = array(
            "name" => $newGroupName,
            "cardIds" => $newCards,
            "organization" => array (
                "id" => $orgId
            )
        );

        if ($groupIsDashboard == true) {
            $content["dashboard"] = true;
        }

        $response = $this->sendRequest(
            "PUT", 
            "card-groups/".$newGroup["groupId"], 
            array (
                "content" => json_encode(
                    $content
                )
            )
        );

        $success = FALSE;
        if ($response["status"] === 204) {
            $success = TRUE ;
        }

        return array (
            "success" => $success, 
            "status" => $response["status"],
            "content" => $response["content"]
        );
    }

    public function getCardData($orgId, $cardId, $cardType, $requireCached, $orderType = "-", $orderBy = '0', $limit = null) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }

        if ($orderType  == '+') {
            $orderType = '';
        }

        $requireCachedStr = ($requireCached) ? 'true' : 'false';

        $limitstr = "";
        if (!is_null($limit)) {
           $limitstr = '&_limit='.$limit;
        }

        $path = 'organizations/'.$orgId.'/'.$cardType.'/data';
        $orderby = urlencode($orderType."measure[".$orderBy."].value");
        $queryString = "order_by=".$orderby."&cardId=".$cardId."&requireCached=".$requireCachedStr.$limitstr;

        $response = $this->sendRequest(
            "GET", 
            $path.'?'.$queryString,
            array ()
        );

        if ($response["status"] === 404 && $requireCached === true) {
            $response = $this->getCardData($orgId, $cardId, $cardType, false, $orderType, $orderBy, $limit);
        }

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $return["success"] = TRUE;
            $return["content"] = $this->processCardDataContent($requireCached, $response["content"]);
        } 

        return $return;
    }

    public function processCardDataContent($cached, $rawContent){
        if ($cached) {
            return json_decode($rawContent);
        }
        else {
            $content = explode("event: ", $rawContent);
            array_shift($content); 
            foreach ($content as $index => $event) {
                $eventArr = explode("data: ", $event);
                if (trim(preg_replace('/\s\s+/', ' ', $eventArr[0])) == "results") {
                    return json_decode($eventArr[1]);
                }
            }
        }
        return null;
    }

    /*
    @method getPersonByPersona Fetches a person by persona, if it exists. 
    @param {String} [$orgId] Id of the organization.
    @param {Object} [$persona] Persona object.
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? (false if group does not exist)
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 404 if group does not exist
        @return {Integer} [personId] Person's id
        @return {String} [name] Person's name
        @return {Array} [personas] List of personas belonging to the persona
    */
    public function getPersonByPersona($orgId, $persona) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $response = $this->sendRequest(
            "GET", 
            'organizations/'.$orgId.'/people/with-persona?persona='.urlencode(json_encode($persona))
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $content = json_decode($response["content"]);
            $return["success"] = TRUE;
            $return["personId"] = $content->id;
            $return["name"] = $content->name;
            $return["personas"] = $content->personas;
        }

        return $return;
    }

    /*
    @method createPerson Creates a person 
    @param {String} [$orgId] Id of the organization.
    @param {Object} [$person] Person object.
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? (false if group does not exist)
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 404 if group does not exist
        @return {Integer} [personId] Person's id
    */
    public function createPerson($orgId, $person) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $response = $this->sendRequest(
            "POST", 
            'organizations/'.$orgId.'/people/',
            array (
                'content' => json_encode($person)
            )
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 201) {
            $content = json_decode($response["content"]);
            $return["success"] = TRUE;
            $return["personId"] = $content->id;
        }

        return $return;
    }

    /*
    @method updatePerson Creates a person 
    @param {String} [$orgId] Id of the organization.
    @param {Object} [$person] Person object.
    @param {Integer} [personId] Person's id
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? (false if group does not exist)
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 404 if group does not exist
    */
    public function updatePerson($orgId, $personId, $person) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $response = $this->sendRequest(
            "PUT", 
            'organizations/'.$orgId.'/people/'.$personId,
            array (
                'content' => json_encode($person)
            )
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 204) {
            $return["success"] = TRUE;
        }

        return $return;
    }

    /*
    @method createPerson Creates a person 
    @param {String} [$orgId] Id of the organization
    @param {Object} [$permission] Permission object.
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? (false if group does not exist)
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response e.g. 404 if group does not exist
    */
    public function createPermission($orgId, $permission) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $response = $this->sendRequest( 
            'POST', 
            'organizations/'.$orgId.'/group-permissions/',
            array(
                'content' => json_encode($permission)
            )
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 201 || $response["status"] === 409) {
            $content = json_decode($response["content"]);
            $return["success"] = TRUE;
        }

        return $return;
    }

    /*
    @method getGroupTypes Fetches a list of all Group Types in an org.
    @param {String} [$orgId] Id of the organization to search.
    @param {String} [$name] Partial name to search for. 
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response
        @return {Array} [groupTypes] List of group types
    */
    public function getGroupTypes($orgId, $name) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $namestr = "";
        if (!is_null($name)) {
           $namestr = '?in_name='.$name;
        }

        $response = $this->sendRequest(
            "GET", 
            'organizations/'.$orgId.'/group-types'.$namestr
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $content = json_decode($response["content"]);
            $return["success"] = TRUE;
            $return["groupTypes"] = $content->results;
        }
        return $return;
    }

    /*
    @method deleteGroupType Deletes a Group Type. 
    @param {String} [$orgId] Id of the organization to search.
    @param {Int} [$groupTypeId] Id of the group type to delete
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response
    */
    public function deleteGroupType($orgId, $groupTypeId) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $response = $this->sendRequest(
            'DELETE', 
            'organizations/'.$orgId.'/group-types/'.$groupTypeId
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 204) {
            $return["success"] = TRUE;
        }
        return $return;
    }

    /*
    @method createGroupType Creates a Group Type. 
    @param {String} [$orgId] Id of the organization to search.
    @param {string} [$name] The display name of the Group Type
    @param {string} [$plural] The plural display name of the Group Type
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response
    */
    public function createGroupType($orgId, $name, $plural) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        if ($plural == null) {
            $plural = $name.'s';
        }

        $content = array(
            'name' => $name,
            'pluralName' => $plural
        );

        $opts = array(
            'content' => json_encode($content)
        );

        $response = $this->sendRequest(
            'POST', 
            'organizations/'.$orgId.'/group-types/',
            $opts
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $return["success"] = TRUE;
        }
        return $return;
    }

    /*
    @method getGroups Fetches a list of all Group in an org.
    @param {String} [$orgId] Id of the organization to search.
    @param {String} [$customId] Partial customId to search for. 
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response
        @return {Array} [groupTypes] List of group types
    */
    public function getGroups($orgId, $customId) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $customIdStr = "";
        if (!is_null($customId)) {
           $customIdStr = '?in_customId='.$customId;
        }

        $response = $this->sendRequest(
            "GET", 
            'organizations/'.$orgId.'/groups'.$customIdStr
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $content = json_decode($response["content"]);
            $return["success"] = TRUE;
            $return["groups"] = $content->results;
        }
        return $return;
    }

    /*
    @method getGroupsByName Fetches a list of all Group in an org.
    @param {String} [$orgId] Id of the organization to search.
    @param {String} [$name] name to search for. 
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response
        @return {Array} [groupTypes] List of group types
    */
    public function getGroupsByName($orgId, $name) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }
        $nameStr = "";
        if (!is_null($name)) {

           $nameStr = '?name='.urlencode($name);
        }

        $response = $this->sendRequest(
            "GET", 
            'organizations/'.$orgId.'/groups'.$nameStr
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $content = json_decode($response["content"]);
            $return["success"] = TRUE;
            $return["groups"] = $content->results;
        }
        return $return;
    }

    /*
    @method createGroup Creates a Group. 
    @param {String} [$orgId] Id of the organization to search.
    @param {Obj} [$group] Group to create
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response
    */
    public function createGroup($orgId, $group) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }

        $opts = array(
            'content' => json_encode($group)
        );

        $response = $this->sendRequest(
            'POST', 
            'organizations/'.$orgId.'/groups/',
            $opts
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $return["success"] = TRUE;
        }
        return $return;
    }

    /*
    @method updateGroup Updates a Group. 
    @param {String} [$orgId] Id of the organization to search.
    @param {Int} [$groupId] Id of the organization to search.
    @param {Obj} [$group] Group to create
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response
    */
    public function updateGroup($orgId, $groupId, $group) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }

        $opts = array(
            'content' => json_encode($group)
        );

        $response = $this->sendRequest(
            'PUT', 
            'organizations/'.$orgId.'/groups/'.$groupId,
            $opts
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $return["success"] = TRUE;
        }
        return $return;
    }

    /*
    @method deleteGroup deletes a Group. 
    @param {String} [$orgId] Id of the organization to search.
    @param {Int} [$groupId] Id of the organization to search.
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response
    */
    public function deleteGroup($orgId, $groupId) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }

        $response = $this->sendRequest(
            'DELETE', 
            'organizations/'.$orgId.'/groups/'.$groupId
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $return["success"] = TRUE;
        }
        return $return;
    }

    /*
    @method setMeasures sets the measures for an org
    @param {String} [$orgId] Id of the organization .
    @param {Array} [$measures] measures to set
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response
    */
    public function setMeasures($orgId, $measures) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }

        $response;

        foreach ($measures as $measure) {
            $config = (object)[
                'name' => $measure->name,
                'config' => json_encode($measure)
            ];

            $opts = array(
            'content' => json_encode($config)
            );
            
            $response = $this->sendRequest(
                'POST', 
                'organizations/'.$orgId.'/measures/',
                $opts
            );

            if ($response["status"] !== 201) {
                break;
            }
        }

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 201) {
            $return["success"] = TRUE;
        }
        return $return;
    }

    /*
    @method getMeasures gets the measures for an org
    @param {String} [$orgId] Id of the organization .
    @param {Array} [$measures] measures to set
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Array} [measures] fetched measures.
        @return {Integer} [status] HTTP status code of the response
    */
    public function getMeasures($orgId) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }

        $response = $this->sendRequest(
            'GET', 
            'organizations/'.$orgId.'/measures/',
            null
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $return["success"] = TRUE;
            $content = json_decode($response["content"]);
            $return["measures"] = $content->results;
        }
        return $return;
    }

    /*
    @method deleteMeasure deletes a measure
    @param {String} [$orgId] Id of the organization .
    @param {Array} [$measures] measures to set
    @return {Array} Details of the result of the series of requests.
        @return {Boolean} [success] Was the request was a success? 
        @return {String} [content] Raw content of the response.
        @return {Integer} [status] HTTP status code of the response
    */
    public function deleteMeasure($orgId, $measureId) {
        if ($orgId == null) {
            $orgId = $this->orgId;
        }

        $response = $this->sendRequest(
            'DELETE', 
            'organizations/'.$orgId.'/measures/'.$measureId,
            null
        );

        $return = array (
            "success" => FALSE, 
            "status" => $response["status"],
            "content" => $response["content"]
        );

        if ($response["status"] === 200) {
            $return["success"] = TRUE;
        }
        return $return;
    }

    // http://php.net/manual/en/function.array-merge-recursive.php#92195
    protected function array_merge_recursive_distinct( array &$array1, array &$array2)
    {
      $merged = $array1;

      foreach ( $array2 as $key => &$value )
      {
        if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
        {
          $merged [$key] = $this->array_merge_recursive_distinct ( $merged [$key], $value );
        }
        else
        {
          $merged [$key] = $value;
        }
      }

      return $merged;
    }

}
