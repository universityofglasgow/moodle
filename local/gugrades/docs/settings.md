# Moodle Settings Page

MyGrades has a core Moodle admin settings page in order to configure a number of critical features. This should only rarely be changed. 

The main sections are as follows...

* Mappings of Moodle scales to required integer values and Schedule A/B status
* Default conversion values
* General settings, such as maximum course size and minimum course start date
* Mapping of admin grades to current code string and description

## Scale mappings

Each scale used in MyGrades must be mapped in this section. This currently only supports scales that can represent Schedule A or Schedule B. Therefore, the scale must have exactly 23 or 8 items to be displayed in the settings. 
All other scales defined in the Moodle instance are ignored and are not displayed. 

If a scale is displayed but is not required to be used in MyGrade, leave the "define the numberic values..." text area completely empty. The scale will then be ignored by MyGrades. 

To use a scale, enter the scale item followed by a comma and its numeric value - one per line. See default values for examples. 

The second field for each scale should contain either the string 'schedulea' or 'scheduleb'. No other value is acceptable. 

## Conversion

The next two fields contain the default conversion values for Schedule A and Schedule B. These are the values that automatically populate a new table when defining a conversion schema. 

## General settings

There are currently only two settings. The first is the maximum number of participants permitted in a course. MyGrades will be disabled in courses having more than this number of participants. 

The second value is the minimum start date for courses. Courses older than this cannot be used in MyGrades. 

## Administration grades

The code and description for each type of administration grade can be edited in this section. 

Note than on saving any changes, an ad-hoc task is started to update existing grades to the updated admin grade. So this may take a few minutes. 