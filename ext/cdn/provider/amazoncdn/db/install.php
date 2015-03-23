<?php

/**
 * Amazons CDN hookslib API file
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

function xmldb_provider_amazoncdn_install() {
    global $DB;
    
    $dbman = $DB->get_manager();

    $table = new xmldb_table('files');
    $field = new xmldb_field('cdn_enable', XMLDB_TYPE_INTEGER, '10', false, XMLDB_NOTNULL, false, 0); // You'll have to look up the definition to see what other params are needed.

    if (!$dbman->field_exists($table, $field)) {
        $dbman->add_field($table, $field);
    }
}
