# ubuntu2, laptop
dev=ubuntu2

if [ $dev = "ubuntu2" ]
then
    token=a84b9243a1c3295f343bb779e019ae4f
    host=http://ubuntu2.local:8081
    userid=757
    gradecategoryid=3
fi

if [ $dev = "laptop" ]
then
    token=689921a13553fc13133e70383c61f534
    host=http://192.168.64.9:8081
    userid=9
    gradecategoryid=2
fi

# get courses / top-level
curl "${host}/webservice/rest/server.php?wstoken=${token}&wsfunction=local_gugrades_dashboard_get_courses&moodlewsrestformat=json&userid=${userid}" | jq --indent 4

curl "${host}/webservice/rest/server.php?wstoken=${token}&wsfunction=local_gugrades_dashboard_get_grades&moodlewsrestformat=json&userid=${userid}&gradecategoryid=${gradecategoryid}" | jq --indent 4