<?php

// This file is NOT a part of Moodle - http://moodle.org/
//
// This client for Moodle 2 is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
/**
 * REST client for Moodle 2
 * Return JSON or XML format
 *
 * @authorr Jerome Mouneyrac
 */
/// SETUP - NEED TO BE CHANGED
require_once ('../../../config.php');
global $CFG;

$token = required_param('token', PARAM_USERNAME);
$functionname = required_param('function', PARAM_TEXT);

if ($functionname == 'newsop') {
    $functionname = 'local_sop_create_courses';
    $fullname = required_param('fullname', PARAM_TEXT);
    $shortname = required_param('shortname', PARAM_TEXT);
    $idnumber = required_param('idnumber', PARAM_ALPHANUM);

    /// PARAMETERS - NEED TO BE CHANGED IF YOU CALL A DIFFERENT FUNCTION
    $course = new stdClass();
    $course->fullname = $fullname;
    $course->shortname = $shortname;
    $course->categoryid = 1;
    $course->summary = 'Test course from web service';
    $course->idnumber = $idnumber;
    $course->lang = 'en';
    $course->customfield_sopversion = required_param('sopversion', PARAM_RAW_TRIMMED);
    $course->customfield_issop = 1;
    $course->customfield_certificationurl = required_param('certificateurl', PARAM_URL);
}
$restformat = required_param('format', PARAM_TEXT);

$domainname = 'http://alfresco.local';



$courses = array($course);
$params = array('courses' => $courses);
/// REST CALL
header('Content-Type: application/xml');
$serverurl = $domainname . '/webservice/rest/server.php' . '?wstoken=' . $token . '&wsfunction=' . $functionname;
require_once('./curl.php');
$curl = new curl;
//if rest format == 'xml', then we do not add the param for backward compatibility with Moodle < 2.2
$restformat = ($restformat == 'json') ? '&moodlewsrestformat=' . $restformat : '';
$resp = $curl->post($serverurl . $restformat, $params);
print_r($resp);
