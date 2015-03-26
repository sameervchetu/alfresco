<?php

/**
 * Services file for Alfresco SOP
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
// We defined the web service functions to install.
$functions = array(
    'local_sop_new_sop' => array(
        'classname' => 'local_sop_external',
        'methodname' => 'new_sop',
        'classpath' => 'local/sop/externallib.php',
        'description' => 'Create new SOP course',
        'type' => 'write',
    )
);

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'Alfresco SOP service' => array(
        'functions' => array(
            'local_sop_new_sop',
            'core_user_create_users',
            'core_course_create_courses',
        ),
        'restrictedusers' => 0,
        'shortname' => 'alfresco_sop_service',
        'enabled' => 1,
    )
);
