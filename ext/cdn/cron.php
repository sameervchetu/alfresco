<?php

/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Ben Lobo <ben.lobo@kineo.com>
 * @package totara
 * @subpackage program
 */

/**
 * Cron job for managing CDN files
 */
require_once($CFG->dirroot . '/ext/cdn/lib.php');

/**
 * Upload files on CDN
 *
 * @return  void
 */
function cdn_cron() {
    $result = true;
    // Run the tasks that should be run once a day
    $result = $result && cdn_daily_cron();

    // Run the tasks that should be run hourly
    $result = $result && cdn_hourly_cron();

    return $result;
}

/**
 * Cron tasks that should be run more regularly
 *
 * @return bool Success
 */
function cdn_hourly_cron() {
    global $CFG;

    $timenow = time();
    $hourlycron = 60 * 60; // one hour
    $lasthourlycron = get_config(null, 'ext_cdn_lasthourlycron');

    if ($lasthourlycron && ($timenow - $lasthourlycron <= $hourlycron)) {
        // not enough time has elapsed to rerun hourly cron
        mtrace("No need to run CDN hourly cron - has already been run recently.");
        if (isset($CFG->debugcron) && $CFG->debugcron) {
            mtrace("DEBUG - running anyway");
        } else {
            return true;
        }
    }

    if (!set_config('ext_cdn_lasthourlycron', $timenow)) {
        mtrace("Error: could not update lasthourlycron timestamp for CDN module.");
    }
    $cron_upload = true;
    ini_set('max_execution_time', 300);
    ext_hook_classes_caller::call_all('amazon_upload_scorm_files', $cron_upload);
}

/**
 * Cron tasks that should only be run once a day
 *
 * @return bool Success
 */
function cdn_daily_cron() {
    global $CFG;

    $timenow = time();
    $dailycron = 60 * 60 * 24; // one day
    $lastdailycron = get_config(null, 'ext_cdn_lastdailycron');

    if ($lastdailycron && ($timenow - $lastdailycron <= $dailycron)) {
        // not enough time has elapsed to rerun daily cron
        mtrace("No need to run CDN daily cron - has already been run today.");
        if (isset($CFG->debugcron) && $CFG->debugcron) {
            mtrace("DEBUG - running anyway");
        } else {
            return true;
        }
    }

    if (!set_config('ext_cdn_lastdailycron', $timenow)) {
        mtrace("Error: could not update lastdailycron timestamp for CDN module.");
    }
}
