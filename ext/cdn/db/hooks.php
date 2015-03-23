<?php

/**
 * CDN plugin hook file
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
// Hook array includes info of functions where code is written which used to add functionality to existing code
$hooks = array(
    'amazon_fetch_file_from_as3' => array(
        'fetch_file_from_as3' => '/ext/cdn/provider/amazoncdn/hookslib.php'
    ),
    'amazon_upload_file' => array(
        'upload_file' => '/ext/cdn/provider/amazoncdn/hookslib.php'
    ),
    'amazon_upload_scorm_files' => array(
        'upload_scorm_files' => '/ext/cdn/provider/amazoncdn/hookslib.php'
    ),
    'amazon_store_context' => array(
        'store_context' => '/ext/cdn/provider/amazoncdn/hookslib.php'
    ),
    'amazon_cdn_criteria' => array(
        'cdn_criteria' => '/ext/cdn/provider/amazoncdn/hookslib.php'
    ),
);
