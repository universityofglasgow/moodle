<?php
/**
 * GISMO block
 *
 * @package    block_gismo
 * @copyright  eLab Christian Milani
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// libraries & acl
require_once "common.php";
?>
<div id="inner">
    <b>What is GISMO?</b>
    <p>
        You are the instructor of one or more courses given at distance with the Moodle Course Management System. 
        Maybe your students are hundreds of kilometers away, or maybe your students are local, but you see them 
        in classroom only once or twice per semester. As you are the instructor of your course, you may probably 
        what to know what is happening to your students: Are they reading materials? Are they regularly accessing 
        the course? Are there quizzes or assignments particularly problematic? Are their submissions performed in 
        due time?
        <br>
        If this is your case, GISMO can help you. GISMO is a graphical interactive student monitoring and tracking 
        system tool that extracts tracking data from the Moodle Course Management System, and generates useful 
        graphical representations that can be explored by course instructors to examine various aspects of distance 
        students.
    </p>
    <p>
        <b>How to get and install the software</b>
    </p>
    <p>
        To install the software you need to ask the administrator of your local Moodle platform to install the block.
        <br>
        GISMO application can be downloaded by the Internet at the following address: http://sourceforge.net/projects/gismo/
        <br>
        Once your administrator has installed the software, you are ready to use it.
    </p>
    <p>
        <b>Starting the GISMO</b>
    </p>
    <p>
        GISMO appears like any other Moodle's block. You need to turn on the editing modality and add GISMO to your course. This block is visible only to the instructors of the course. Then you can click on the &quot;GISMO&quot; link that appears within the GISMO block to start the application.
    </p>
    <p>
        <b>Graphical representations</b>
    </p>
    <p>
        In this section we describe each graphical representation that can be generated with GISMO. These can be activated by clicking on the menu items. There are 3 main categories of visualizations:
    </p>
    <p>
        <em>&#8729; Students
            <br>
            &#8729; Resources
            <br>
            &#8729; Activities
        </em>
    </p>
    <p>
        For each category there is a specific item in the menu bar. We will illustrate each of them in the following sections.
    </p>
    <p>
        <b>Welcome page</b>
    </p>
    <p>
        <img src="images/help/gismo_main.png" width="500" height="330">
        <br>
        <em>[Welcome page]</em>
    </p>
    <p>
        The Figure<em> [Welcome page] </em>represents the welcome page of GISMO. As you can see, there are 3 different areas in the user interface:
    </p>
    <ul style="list-style-position: inside;">
        <li>
            Graph Panel: graphs are drawn on this panel.
        </li>
        <li>
            List Panel: contains a list of students, resources, quizzes, and assignments of the monitored course. For each list the instructor can select/deselect data to visualize.
        </li>
        <li>
            Time Panel: using this panel the instructor can reduce the selection on time and restrict the graph to a specific range of dates.
        </li>
    </ul>
    <p>
        <b>Students: Accesses by students</b>
    </p>
    <p>
        <img src="images/help/students_accesses_by_students.png" width="500" height="331">
        <br>
        <em>[Students:Accesses by students]</em>
    </p>
    <p>
        The Figure <em>[Students:Accesses by students]</em>
        reports a graph on the students' accesses to the course. A simple matrix formed by students&Otilde; names (on Y-axis) and dates of the course (on X-axis) is used to represent the course accesses. A corresponding mark represents at least one access to the course made by the student on the selected date. 
    </p>
    <p>
        <b>Students: Accesses overview</b>
    </p>
    <p>
        <img src="images/help/students_accesses_overview.png" width="500" height="331">
        <br>
        <em>[Students:Accesses overview]</em>
    </p>
    <p>
        The Figure <em>[Students:Accesses overview]</em>
        reports a histogram that shows the global number of hits to the course made by students on each date.
    </p>
    <p>
        With the previous two graphs, the instructor has an overview, at a glance, of the global accesses made by students to the course with a clear identification of patterns and trends, as well as information about the attendance of a specific student of the course.
    </p>
    <p>
        <b>Students: Accesses overview on resources</b>
    </p>
    <p>
        <img src="images/help/students_accesses_overview_on_resources.png" width="500" height="325">
        <br>
        <em>[Students:Accesses overview on resources]</em>
    </p>
    <p>
        The image in Figure <em>[Students: Accesses overview on resources]</em>
        represents the global number of accesses made by students (in X-axis) to all the resources of the course (Y-axis).
        <br />
        If the user click the &quot;eye icon&quot; in the left menu he can see the details for a specific student. This leads to the following representation. 
    </p>
    <p>
        <b>Students: Student details on resources</b>
    </p>
    <p>
        <img src="images/help/student_details_on_resources.png" width="500" height="331">
        <br>
        <em>[Students: Student details on resources]</em>
    </p>
    <p>
        The Figure <em>[Students: Student details on resources]</em>
        reports an overview of the accesses of a student on the course's resources. Dates are represented on the X-axis; resources are represented on the Y-axis.
    </p>
    <p>
        <b>Resources: Students overview</b>
    </p>
    <p>
        <img src="images/help/resources_students_overview.png" width="500" height="331">
        <br>
        <em>[Resources: Students overview]</em>
    </p>
    <p>
        Instructors could also be interested in having the details on what resources were accessed by all the students and when. A specific representation is intended to provide this information. The Figure <em>[Resources: Students overview]</em>
        reports student names on the Y-axis, and resource names on the X-axis. A mark is depicted if the student accessed this resource, and the color of the mark ranges from light-red to dark-red according to the number of times he/she accessed this resource.
    </p>
    <p>
        <b>Resources:Accesses overview</b>
    </p>
    <p>
        <img src="images/help/resources_accesses_overview.png" width="500" height="325">
        <br>
        <em>[Resource accesses overview]</em>
    </p>
    <p>
        The image in Figure <em>[Resource accesses overview]</em>
        represents the global number of accesses made by students to each resource of the course (X-axis). Each bar of the histogram represents a particular resource of the course.
        <br />
        If the user click the &quot;eye icon&quot; in the left menu he can see the details for a specific resource. This leads to the following representation.
    </p>
    <p>
        <b>Resources: Resource details on students</b>
    </p>
    <p>
        <img src="images/help/resources_resource_details_on_students.png" width="500" height="325">
        <br>
        <em>[Resources: Resource details on students]</em>
    </p>
    <p>
        The Figure <em>[Resources: Resource details on students]</em>
        reports an overview of the accesses of students to this particular resource. Dates are represented on the X-axis; students are represented on the Y-axis.
    </p>
    <p>
        <b>Activities: Assignments/Quizzes overview</b>
    </p>
    <p>
        <img src="images/help/activities_assignments.png" width="500" height="331">
        <br>
        <em>[Activities: Assignments overview]</em>
    </p>
    <p>
        The graph in Figure <em>[Activities: Assignments overview]</em>
        is indented to visually indicate the grades received by students on assignments and quizzes. On the X-axis we mapped the assignments (or quizzes in the graphs dedicated to quizzes) and marks denote students submissions. An empty square means a submission not graded, while a coloured square reports the grade: a lower grade is depicted with a light colour, a high grade is depicted with a dark colour.
    </p>
</div>