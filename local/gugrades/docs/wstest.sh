#!/bin/bash

# You need to install 'jq' to format the json output (sudo apt install jq)

WSTOKEN="92dac22670ae0b652eace75b6f122727"
ENDPOINT="http://192.168.64.8:8081/webservice/rest/server.php"

# Get top level
COURSEID="2"
WSFUNCTION="local_gugrades_get_levelonecategories"
curl "${ENDPOINT}?wstoken=${WSTOKEN}&wsfunction=${WSFUNCTION}&moodlewsrestformat=json&courseid=${COURSEID}" | jq

# Get activities
CATEGORYID="3"
WSFUNCTION="local_gugrades_get_activities"
curl "${ENDPOINT}?wstoken=${WSTOKEN}&wsfunction=${WSFUNCTION}&moodlewsrestformat=json&courseid=${COURSEID}&categoryid=${CATEGORYID}" | jq

# Get capture page
GRADEITEMID=6
FIRSTNAME=""
LASTNAME=""
WSFUNCTION="local_gugrades_get_capture_page"
curl "${ENDPOINT}?wstoken=${WSTOKEN}&wsfunction=${WSFUNCTION}&moodlewsrestformat=json&courseid=${COURSEID}&gradeitemid=${GRADEITEMID}&firstname=${FIRSTNAME}&lastname=${LASTNAME}" | jq

# Import Grades
WSFUNCTION="local_gugrades_get_all_strings"
curl "${ENDPOINT}?wstoken=${WSTOKEN}&wsfunction=${WSFUNCTION}&moodlewsrestformat=json" | jq
