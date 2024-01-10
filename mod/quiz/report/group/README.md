Group Quiz 
=======

_@author: 2017, Camille Tardy, University of Geneva_

Sub-plugin of Moodle Quiz to enable the group notion for the quiz.  
Must be install under `.../mod/quiz/report/group`.  
Available in French and English.

Works on moodle 3.1 -> 4.1.  
Not tested on Moodle 3.7, see https://github.com/cborn/moodle_quiz_group for proposed fixed. 

### How it works

##### Set up and usage
Creates a menu entry under Quiz > Report > Group quiz.
For Modole 4.0 and later, the menu is under Quiz > Results and in the drop down menu you can choose : "group quiz"
The plugin is then set up from there, by selecting the grouping to be used.

Students are grouped using the course grouping and group tools.
The teacher must select a grouping when creating the quiz to define which group to use for the group quiz.
If a grouping is selected, only one student per group can fill up the quiz.

If the `"no grouping"` choice is selected, the quiz behaves normally without taking the groups into account.


##### Copy the grades to the group's members
Once the students are done answering, the teacher can copy the grade registered for each participant to the rest of their respective group members in the Gradebook.
The grades copy can be done at any time. 
So every time a teacher edit an attempt in the result view of the quiz, or a student is moved from a group to another, the copy will take into account the changes and overwrite the previous grade.

##### Notes 
* If a teacher deletes an attempt, the Gradebook is not cleared of the copied grades. It must be done manually by the teacher. 
* If a student is in more than one group in the same grouping, the system will only consider his first affiliation.
* Do not change the grouping if some attempts exist for a given quiz.

##### Privacy

Regarding personal data handling, our plugin stores in its DB Table the userID with the corresponding quiz_attemptID for the Group representative.
The Privacy API will be handled in a future release.


### TODO in the next update  
   * Do not allow grouping changing if an attempt already exist in the DB.
   * Implement the Backup / Restore
   
