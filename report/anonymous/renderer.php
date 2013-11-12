<?php

class report_anonymous_renderer extends plugin_renderer_base {

    public function list_assign($url, $assignments) {
        echo "<h3>" . get_string('anonymousassignments', 'report_anonymous') . "</h3>";
        if (empty($assignments)) {
            echo "<div class=\"alert-warning\">" . get_string('noassignments', 'report_anonymous') . "</div>";
            return;
        }
        echo "<ul>";
        foreach ($assignments as $assignment) {
            $url->params(array('mod'=>'assign', 'assign'=>$assignment->id));
            echo "<li><a href=\"$url\">";
            echo $assignment->name;
            echo "</a></li>";
        }
        echo "</ul>";
    }

    public function list_turnitintool($url, $tts) {
        echo "<h3>" . get_string('anonymoustts', 'report_anonymous') . "</h3>";
        if (empty($tts)) {
            echo "<div class=\"alert-warning\">" . get_string('notts', 'report_anonymous') . "</div>";
            return;
        }
        echo "<ul>";
        foreach ($tts as $tt) {
            echo "<li>" . $tt->name;
            echo "<ul>";
            foreach ($tt->parts as $part) {
                $url->params(array('mod'=>'turnitintool', 'part'=>$part->id));
                echo "<li><a href=\"$url\">";
                echo $part->partname;
                echo "</a></li>";
            }
            echo "</ul></li>";
        }
        echo "</ul>";
    }

    /**
     * List of assignment users
     * @param array $ausers all assignment users
     * @param array $anotusers all assignment users who did not submit
     */
    public function report_assign($assignment, $ausers, $anotusers, $reveal) {
        echo "<h3>" . get_string('assignnotsubmit', 'report_anonymous', $assignment->name) . "</h3>";

        // keep a track of records with no idnumber
        $noids = array();
        echo "<ul>";
        foreach ($anotusers as $u) {
            if ($reveal) {
                $userurl = new moodle_url('/user/view.php', array('id'=>$u->id));
                echo "<li>";
                echo "<a href=\"$userurl\">".fullname($u)."</a>";
                echo "</li>";
            } else if ($u->idnumber) {
                echo "<li>{$u->idnumber}</li>";
            } else {
                $noids[$u->id] = $u;
            }
        }
        echo "</ul>";
        echo "<strong>" . get_string('totalassignusers', 'report_anonymous', count($ausers)) . "</strong><br />";
        echo "<strong>" . get_string('totalnotassignusers', 'report_anonymous', count($anotusers)) . "</strong><br />";
        echo "<strong>" . get_string('totalnoid', 'report_anonymous', count($noids)) . "</strong><br />";
    }

    public function reveal_link($url, $reveal) {
        if ($reveal) {
            $url->params(array('reveal'=>0));
            $text = get_string('clickhidenames', 'report_anonymous');
        } else {
            $url->params(array('reveal'=>1));
            $text = get_string('clickshownames', 'report_anonymous');
        }
        echo "<div><a class=\"button\" href=\"$url\">$text</a></div>";
    }

}
