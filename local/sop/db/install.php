<?php

/**
 * Program auto enrolment lib API file
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
function xmldb_local_sop_install() {
    global $DB, $CFG;

    $customfields = array();

    //custom field wslog
    $customfields['wslog'] = new stdClass();
    $customfields['wslog']->datatype = 'textarea';
    $customfields['wslog']->prefix = 'course';
    $customfields['wslog']->fullname = 'wslog';
    $customfields['wslog']->shortname = 'wslog';
    $customfields['wslog']->description_editor = array('text' => get_string('wslog_description', 'local_sop'), 'format' => 1, 'itemid' => 0);
    $customfields['wslog']->defaultdata_editor = array('text' => '', 'format' => 1, 'itemid' => 0);
    $customfields['wslog']->param1 = 30;
    $customfields['wslog']->param2 = 10;

    $customfields['sopversion'] = new stdClass();
    $customfields['sopversion']->datatype = 'text';
    $customfields['sopversion']->prefix = 'course';
    $customfields['sopversion']->fullname = 'sopversion';
    $customfields['sopversion']->shortname = 'sopversion';
    $customfields['sopversion']->description_editor = array('text' => get_string('sopversion_description', 'local_sop'), 'format' => 1, 'itemid' => 0);
    $customfields['sopversion']->defaultdata = '';
    $customfields['sopversion']->param1 = 30;
    $customfields['sopversion']->param2 = 2048;

    $customfields['wsupdatedate'] = new stdClass();
    $customfields['wsupdatedate']->datatype = 'datetime';
    $customfields['wsupdatedate']->prefix = 'course';
    $customfields['wsupdatedate']->fullname = 'wsupdatedate';
    $customfields['wsupdatedate']->shortname = 'wsupdatedate';
    $customfields['wsupdatedate']->description_editor = array('text' => get_string('wsupdatedate_description', 'local_sop'), 'format' => 1, 'itemid' => 0);
    $customfields['wsupdatedate']->param1 = 2015;
    $customfields['wsupdatedate']->param2 = 2035;

    $customfields['wscreatedate'] = new stdClass();
    $customfields['wscreatedate']->datatype = 'datetime';
    $customfields['wscreatedate']->prefix = 'course';
    $customfields['wscreatedate']->fullname = 'wscreatedate';
    $customfields['wscreatedate']->shortname = 'wscreatedate';
    $customfields['wscreatedate']->description_editor = array('text' => get_string('wscreatedate_description', 'local_sop'), 'format' => 1, 'itemid' => 0);
    $customfields['wscreatedate']->param1 = 2015;
    $customfields['wscreatedate']->param2 = 2035;

    $customfields['certificationurl'] = new stdClass();
    $customfields['certificationurl']->datatype = 'text';
    $customfields['certificationurl']->prefix = 'course';
    $customfields['certificationurl']->fullname = 'certificationurl';
    $customfields['certificationurl']->shortname = 'certificationurl';
    $customfields['certificationurl']->description_editor = array('text' => get_string('certificationurl_description', 'local_sop'), 'format' => 1, 'itemid' => 0);
    $customfields['certificationurl']->defaultdata = '';
    $customfields['certificationurl']->param1 = 30;
    $customfields['certificationurl']->param2 = 2048;

    $customfields['issop'] = new stdClass();
    $customfields['issop']->id = 0;
    $customfields['issop']->datatype = 'checkbox';
    $customfields['issop']->prefix = 'course';
    $customfields['issop']->fullname = 'issop';
    $customfields['issop']->shortname = 'issop';
    $customfields['issop']->description_editor = array('text' => get_string('issop_description', 'local_sop'), 'format' => 1, 'itemid' => 0);
    $customfields['issop']->defaultdata = 0;


    require_once($CFG->dirroot . '/totara/customfield/definelib.php');
    $obj = new customfield_define_base();
    foreach ($customfields as $customfield) {
        $customfield->id = 0;
        $customfield->required = 0;
        $customfield->locked = 0;
        $customfield->forceunique = 0;
        $customfield->hidden = 0;
        $customfield->typeid = 0;

        $obj->define_save($customfield, 'course');
    }
    
    //Create the web service user role for creating SOP
    $systemcontext = context_system::instance();
    // Create new Tenant administrator role.
    if (!$webserviceuser = $DB->get_record('role', array('shortname' => 'sopuser'))) {
        $webserviceuserid = create_role('SOP user', 'sopuser', 'SOP user creates the course and certification through the REST web service calls ');
    } else {
        $webserviceuserid = $webserviceuser->id;
    }
    
        // If not done already, allow assignment at system context.
    $levels = get_role_contextlevels($webserviceuserid);
    if (empty($levels)) {
        $levelsys = new stdClass;
        $levelsys->roleid = $webserviceuserid;
        $levelsys->contextlevel = CONTEXT_SYSTEM;
        $levelcat = new stdClass;
        $levelcat->roleid = $webserviceuserid;
        $levelcat->contextlevel = CONTEXT_COURSECAT;
        $levelcou = new stdClass;
        $levelcou->roleid = $webserviceuserid;
        $levelcou->contextlevel = CONTEXT_COURSE;
        $tenantlevels = array($levelsys, $levelcat, $levelcou);
        $DB->insert_records('role_context_levels', $tenantlevels);
    }
    
        // Add capabilities to the tenant administrator role.
    $webserviceusercaps = array(
        'block/course_list:myaddinstance',
        'block/course_overview:myaddinstance',
        );
    
        foreach ($webserviceusercaps as $cap) {
        assign_capability($cap, CAP_ALLOW, $webserviceuserid, $systemcontext->id);
    }
}
