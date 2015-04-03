<?php

/**
 * lib file for Alfresco SOP
 * 
 * @copyright Copyright 2015 eLearningExperts
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU Public License 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * To create the certification program through webservice
 *
 * @param object $newdata details of the certification program
 * @param optional param $editoroptions
 * @return integer $id id of the created certificate
 */
function create_certificate($newdata, $editoroptions = NULL) {
    require_once( '../../config.php');
    global $CFG;
    require_once($CFG->libdir . '/adminlib.php');
    require_once($CFG->dirroot . '/totara/program/lib.php');
    require_once($CFG->dirroot . '/totara/core/js/lib/setup.php');
    require_once($CFG->dirroot . '/totara/cohort/lib.php');
    require_once($CFG->dirroot . '/totara/certification/lib.php');
    require_once($CFG->dirroot . '/totara/customfield/fieldlib.php');

    global $DB, $USER;

    $newdata->iscertif = 1;
    $cert_todb = new stdClass;

    $cert_todb->availablefrom = ($newdata->availablefrom) ? $newdata->availablefrom : 0;
    $cert_todb->availableuntil = ($newdata->availableuntil) ? $newdata->availableuntil : 0;
    $available = prog_check_availability($cert_todb->availablefrom, $cert_todb->availableuntil);

    //Calcuate sortorder
    $sortorder = $DB->get_field('prog', 'MAX(sortorder) + 1', array());

    $now = time();
    $cert_todb->timecreated = $now;
    $cert_todb->timemodified = $now;
    $cert_todb->usermodified = $USER->id;
    $cert_todb->category = $newdata->categoryid;
    $cert_todb->shortname = "Certification _" . $newdata->shortname;
    $cert_todb->fullname = "Certification " . $newdata->fullname;
    $cert_todb->idnumber = $newdata->idnumber;
    $cert_todb->sortorder = !empty($sortorder) ? $sortorder : 0;
    $cert_todb->icon = $newdata->icon;
    $cert_todb->exceptionssent = 0;
    $cert_todb->available = $available;
    if (isset($newdata->visible)) {
        $cert_todb->visible = $newdata->visible;
    }
    if (isset($newdata->audiencevisible)) {
        $cert_todb->audiencevisible = $newdata->audiencevisible;
    }
    // Text editor fields will be updated later.
    $cert_todb->summary = '';
    $cert_todb->endnote = '';
    $newid = 0;

    $transaction = $DB->start_delegated_transaction();

    // Set up the program
    $newid = $DB->insert_record('prog', $cert_todb);

    $program = new program($newid);
    $transaction->allow_commit();

    $newdata->id = $newid;
    customfield_save_data($newdata, 'program', 'prog');

    // Create message manager to add default messages.
    $messagemanager = new prog_messages_manager($newid, true);

    $editoroptions = $TEXTAREA_OPTIONS;
    $editoroptions['context'] = context_program::instance($newid);

    $newdata = file_postupdate_standard_editor($newdata, 'summary', $editoroptions, $editoroptions['context'], 'totara_program', 'summary', 0);
    $newdata = file_postupdate_standard_editor($newdata, 'endnote', $editoroptions, $editoroptions['context'], 'totara_program', 'endnote', 0);
    if ($overviewfilesoptions = prog_program_overviewfiles_options($newid)) {
        // Save the course overviewfiles
        $newdata = file_postupdate_standard_filemanager($newdata, 'overviewfiles', $overviewfilesoptions, $editoroptions['context'], 'totara_program', 'overviewfiles', 0);
    }
    $DB->set_field('prog', 'summary', $newdata->summary, array('id' => $newid));
    $DB->set_field('prog', 'endnote', $newdata->endnote, array('id' => $newid));

    // Visible audiences.
    if (!empty($CFG->audiencevisibility)) {
        $visiblecohorts = totara_cohort_get_visible_learning($newid, $instancetype);
        $visiblecohorts = !empty($visiblecohorts) ? $visiblecohorts : array();
        $newvisible = !empty($newdata->cohortsvisible) ? explode(',', $newdata->cohortsvisible) : array();
        if ($todelete = array_diff(array_keys($visiblecohorts), $newvisible)) {
            // Delete removed cohorts.
            foreach ($todelete as $cohortid) {
                totara_cohort_delete_association($cohortid, $visiblecohorts[$cohortid]->associd, $instancetype, COHORT_ASSN_VALUE_VISIBLE);
            }
        }

        if ($newvisible = array_diff($newvisible, array_keys($visiblecohorts))) {
            // Add new cohort associations.
            foreach ($newvisible as $cohortid) {
                totara_cohort_add_association($cohortid, $newid, $instancetype, COHORT_ASSN_VALUE_VISIBLE);
            }
        }
    }

    add_to_log(SITEID, 'program', 'created', "edit.php?id={$newid}", $program->fullname);

    // take them straight to edit page if they have permissions,
    // otherwise view the program
    $programcontext = context_program::instance($newid);
    if (has_capability('totara/program:configuredetails', $programcontext)) {
        $viewurl = "{$CFG->wwwroot}/totara/program/edit.php?id={$newid}&amp;action=edit";
    } else {
        $viewurl = "{$CFG->wwwroot}/totara/program/view.php?id={$newid}";
    }

    // Certification
    $newcertid = 0;
    if ($newdata->iscertif) {
        $certification_todb = new stdClass;
        $certification_todb->learningcomptype = CERTIFTYPE_PROGRAM;
        $certification_todb->activeperiod = '1 year';
        $certification_todb->windowperiod = '1 month';
        $certification_todb->recertifydatetype = CERTIFRECERT_EXPIRY;
        $certification_todb->timemodified = time();

        // TODO move to prog transaction?
        $transaction = $DB->start_delegated_transaction();

        // Set up the certification
        $newcertid = $DB->insert_record('certif', $certification_todb);
        $DB->set_field('prog', 'certifid', $newcertid, array('id' => $newid));

        $transaction->allow_commit();

        add_to_log(SITEID, 'certification', 'created', "edit.php?id={$newid}", '');
    }
    prog_fix_program_sortorder($newdata->category);
    return $newdata->id;
}

/**
 * To create the URL activity in course created through web serivce 
 *
 * @param object $data details of the course and url
 * @return void
 */
function create_mod($data) {
    global $DB, $CFG;
    ;
    $data->name = $data->fullname;
    $data->externalurl = $data->customfield_certificationurl;
    $data->introeditor = array('text' => '', 'format' => 1);
    $data->mform_isexpanded_id_content = 1;
    $data->display = 1;
    $data->popupwidth = 620;
    $data->popupheight = 450;
    $data->printintro = 1;
    $data->completionunlocked = 1;
    $data->completionexpected = 0;
    $data->modulename = 'url';
    $data->module = 22;
    $data->visible = 1;
    $data->section = 1;

    require_once($CFG->dirroot . '/course/modlib.php');
    $course = $DB->get_record('course', array('id' => $data->id));
    $mod = add_moduleinfo($data, $course);
    return;
}

/**
 * Assign created course to certification content 
 *
 * @param int $csid Id of the certification program
 * @param int $courseid Id of the course to be assigned in the certification program
 * @return bool
 */
function save_courses($csid, $courseid) {
    global $DB, $CFG;
    if (!$csid) {
        return false;
    }

    // first get program enrolment plugin class
    require_once($CFG->libdir . "/enrollib.php");
    $program_plugin = enrol_get_plugin('totara_program');

    if (!$ob = $DB->get_record('prog_courseset_course', array('coursesetid' => $csid, 'courseid' => $courseid))) {

        //check if program enrolment plugin is already enabled on this course
        require_once("$CFG->dirroot/enrol/totara_program/lib.php");
        $instance = $program_plugin->get_instance_for_course($courseid);
        if (!$instance) {

            //add it
            $course = $DB - get_record('coruses', array('id' => $courseid));
            $program_plugin->add_instance($course);
        }
        $ob = new stdClass();
        $ob->coursesetid = $csid;
        $ob->courseid = $courseid;
        $DB->insert_record('prog_courseset_course', $ob);
    }
    return true;
}

/**
 * Create the course set in certification program
 *
 * @param int $programid Id of the certification program
 * @param int $courseid Id of the course to be assigned in the certification program
 * @return bool
 */

function save_set($programid, $courseid) {
    global $DB;

    $todb = new stdClass();
    $todb->programid = $programid;
    $todb->sortorder = 3;
    $todb->competencyid = 0;
    $todb->nextsetoperator = 0;
    $todb->completiontype = 1;
    $todb->timeallowed = 86400;
    $todb->recurrencetime = 0;
    $todb->recurcreatetime = 0;
    $todb->contenttype = 1;
    $todb->label = 'Course set 1';
    $todb->certifpath = 1;


    $id = $DB->insert_record('prog_courseset', $todb);

    return save_courses($id, $courseid);
}
