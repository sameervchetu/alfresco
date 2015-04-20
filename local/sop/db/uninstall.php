<?php
/**
* Alfresco SOP integration uninstall file
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

function xmldb_local_sop_uninstall() {
    global $DB;
    
    $dbman = $DB->get_manager();

    $table = new xmldb_table('course_completion_history');
    $field = new xmldb_field('sopversion_completed'); // You'll have to look up the definition to see what other params are needed.

    if ($dbman->field_exists($table, $field)) {
        $dbman->drop_field($table, $field);
    }
}
