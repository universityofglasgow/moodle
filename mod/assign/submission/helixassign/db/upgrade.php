<?php

/**
 * Upgrade code for install
 *
 * @package   assignsubmission_helixassign
 * @copyright Streaming LTD 2013
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Stub for upgrade code
 * @param int $oldversion
 * @return bool
 */
function xmldb_assignsubmission_helixassign_upgrade($oldversion) {
    // Put any upgrade step following this

    if ($oldversion < 2014050601)
    {
        global $DB;
        $table = new xmldb_table('assignsubmission_helixassign');
        $field = new xmldb_field('submission');
        $field->set_attributes(XMLDB_TYPE_INTEGER, '10', true, true, false, "0", 'assignment');
        $DB->get_manager()->add_field($table, $field);

        $all = $DB->get_records('assignsubmission_helixassign', array("submission"=>0) );
        foreach ($all as $rec)
        {
            $all_subs=explode(",", $rec->submissions);
            $rec->submission=intval(end($all_subs));
            $DB->update_record('assignsubmission_helixassign', $rec);
        }
    }

    if ($oldversion < 2014111710)
    {
        global $DB;
        $table = new xmldb_table('assignsubmission_helixassign');
        $all = $DB->get_records('assignsubmission_helixassign' );
        foreach ($all as $rec)
        {
            $first=$rec->submission;
            $all_subs=explode(",", $rec->submissions);
            foreach ($all_subs as $sub)
            {
            echo $sub." ".$first;
                if ($sub!=$first)
                {
                    $nrec=new stdClass();
                    $nrec->assignment=$rec->assignment;
                    $nrec->submission=$sub;
                    $nrec->submissions=$sub;
                    $nrec->preid=$rec->preid;
                    $nrec->servicesalt=$rec->servicesalt;
                    $DB->insert_record('assignsubmission_helixassign', $nrec);
                }
            }
        }
    }
    
    return true;
}


