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
require_once($CFG->dirroot . '/repository/s3/S3.php');

/**
 * Instantiate the S3 class and fetch access and secrect keys
 */
function get_s3_instance() {
    $a_key = get_config('s3', 'access_key');
    $s_key = get_config('s3', 'secret_key');
    $s3 = new S3($a_key, $s_key);
    return $s3;
}

/**
 * Upload the files on amazon server after deciding the critera
 * 
 * @param string $pathname Path of the source file to be uploaded.
 * @param string $contenthash Name of the file on amazon 
 * @param string $mimetype mime type of the file
 */
function upload_file($pathname, $contenthash, $mimetype, $filerecord = null) {
    $s3 = get_s3_instance();
    $bucket = 'medialightning';
    $s3->putBucket($bucket, S3::ACL_PUBLIC_READ);
    $exists = $s3->getObjectInfo($bucket, $pathname, true);

    if (is_array($exists) && isset($exists['time']) && isset($exists['size'])) {
        if ($stored_file->get_filesize() == $exists['size']) {
            return; // file already exists. No need to upload again.
        }
    } else {
        $amz_dir = path_from_hash($contenthash);
        if (!is_null($filerecord)) {
            $fileurl = $filerecord->contextid . "/mod_scorm" . $filerecord->filepath . $filerecord->filename;
//            "http://medialightning.local/pluginfile.php/116/mod_scorm/content/1/content/page1.htm";
            $s3->putObjectFile($pathname, $bucket, $fileurl, S3::ACL_PUBLIC_READ, '', $mimetype);
            return;
        }
        $s3->putObjectFile($pathname, $bucket, $amz_dir . "/" . $contenthash, S3::ACL_PUBLIC_READ, '', $mimetype);
        return;
    }
}

/**
 * Fetch file from amazon s3 server
 *
 * @param Object $stored_file stroed file object to fetch the file from amazon s3.
 * @param Bool $delete To check whether file is deleted or not from amazon s3.
 */
function fetch_file_from_as3($stored_file, &$delete, &$filesss) {
    $s3 = get_s3_instance();
    $bucket = 'medialightning';
    $contenthash = $stored_file->get_contenthash();
    if ($stored_file->get_component() != 'mod_scorm') {
        $amz_fetch_dir = path_from_hash($contenthash);
        $uri = $amz_fetch_dir . "/" . $contenthash;
        $exists = $s3->getObjectInfo($bucket, $uri, true);

        if (is_array($exists) && isset($exists['time']) && isset($exists['size'])) {
            if ($stored_file->get_filesize() == $exists['size']) {
                header("Location: https://s3.amazonaws.com/" . $bucket . "/" . $uri);
            }
        } else {
            $delete = true;
        }
    } else {
        $filerecord->contextid . "/mod_scorm" . $filerecord->filepath . $filerecord->filename;
        $uri = $stored_file->get_contextid() . "/mod_scorm" . $stored_file->get_filepath() . $stored_file->get_filename();
        $exists = $s3->getObjectInfo($bucket, $uri, true);

        if (is_array($exists) && isset($exists['time']) && isset($exists['size'])) {
            if ($stored_file->get_filesize() == $exists['size']) {
                header("Location: https://s3.amazonaws.com/medialightning/" . $uri);
            }
        } else {
            $delete = true;
        }
    }
}

/**
 * Decide which files to be CDN enabled
 *
 * @param string $pathname Path of the object to upload
 * @param string $contenthash Name of the file to upload
 * @param stdClass object $filerecord To fetch other associated information of file like its mimetype
 */
function cdn_criteria($pathname, $contenthash, &$filerecord = NULL) {
    $all_files_config = get_config('cdn', 'enablecriteriaallfiles');
    $scorm_config = get_config('cdn', 'enablecriteriaonlyscorm');
    $extension_config = get_config('cdn', 'enablecriteriaextension');
    $size_config = get_config('cdn', 'enablecriteriasize');
    $mime_config = get_config('cdn', 'enablecriteriamimetype');
    $filesize = filesize($pathname);
    $filerecord->cdn_enable = 1;

    if ($all_files_config || $scorm_config || !empty($extension_config) || !empty($mime_config)) {
        if ($all_files_config) {
            upload_file($pathname, $contenthash, $filerecord->mimetype);
            return;
        }
//        if ($scorm_config) {
//            if ($size_config) {
//                if ($filerecord->component = 'mod_scorm' && $filesize >= $size_config) {
//                    upload_file($pathname, $contenthash, $filerecord->mimetype);
//                    return;
//                }
//            } else {
//                if ($filerecord->component = 'mod_scorm') {
//                    upload_file($pathname, $contenthash, $filerecord->mimetype);
//                    return;
//                }
//            }
//        }
        if ($extension_config) {
            $filename = $filerecord->filename;
            $fileext = pathinfo($filename, PATHINFO_EXTENSION);
            if (!empty($extension_config)) {
                $extensions = explode(',', $extension_config);
                $extensions = array_map('trim', $extensions);
            }
            if ($size_config) {
                if (in_array($fileext, $extensions) && $filesize >= $size_config) {
                    upload_file($pathname, $contenthash, $filerecord->mimetype);
                    return;
                }
            } else {
                if (in_array($fileext, $extensions)) {
                    upload_file($pathname, $contenthash, $filerecord->mimetype);
                    return;
                }
            }
        }
        if ($mime_config) {
            $filename = $filerecord->filename;
            $fileext = pathinfo($filename, PATHINFO_EXTENSION);
            if (!empty($mime_config)) {
                $extensions = explode(',', $mime_config);
                $extensions = array_map('trim', $extensions);
            }
            if ($size_config) {
                if (in_array($fileext, $extensions) && $filesize >= $size_config) {
                    upload_file($pathname, $contenthash, $filerecord->mimetype);
                    return;
                }
            } else {
                if (in_array($fileext, $extensions)) {
                    upload_file($pathname, $contenthash, $filerecord->mimetype);
                    return;
                }
            }
        }
    } else if ($size_config) {
        if ($filesize >= $size_config) {
            upload_file($pathname, $contenthash, $filerecord->mimetype);
            return;
        }
    } else {
        $filerecord->cdn_enable = 0;
        return;
    }
}

/**
 * Decide which file to be CDN enabled
 *
 * @global object $CFG
 * @global object $DB
 * @param Bool $cron_upload To check CRON request.
 */
function upload_scorm_files(&$cron_upload) {
    global $CFG, $DB;

    if ($cron_upload) {
        if (get_config('cdn', 'enablecriteriaonlyscorm')) {
            $files = $DB->get_records('scorm_pending_context');
            if (!empty($files) && is_array($files)) {
                foreach ($files as $file) {
                    $getfiles = $DB->get_records('files', array('contextid' => $file->contextid));
                    foreach ($getfiles as $uploadfile) {
                        if ($uploadfile->component == 'mod_scorm' && $uploadfile->filearea == 'content') {
                            $path = path_from_hash($uploadfile->contenthash);
                            $pathname = $path . "/" . $uploadfile->contenthash;
                            $filepath = $CFG->dataroot . "/filedir/" . $pathname;
                            if (file_exists($filepath)) {
                                upload_file($filepath, $uploadfile->contenthash, $uploadfile->mimetype, $uploadfile);
                                $dataobject = new stdClass();
                                $dataobject->id = $uploadfile->id;
                                $dataobject->cdn_enable = 1;
                                $DB->update_record('files', $dataobject);
                            }
                        }
                    }
                }
            }
        }
    }
}

/**
 * Store contextid to process it afterwards using CDN cron.
 *
 * @global object $DB
 * @param int $context id of the file
 * @param string $component component of the file which must be 'mod_scorm'
 * @param string $area filearea of the file like content or package 
 */
function store_context($context, $component, $area) {
    global $DB;

    if (get_config('cdn', 'enablecriteriaonlyscorm')) {
        if ($context && $component == 'mod_scorm' && $area == 'content') {
            $record = new stdClass();
            $record->contextid = $context;
            $record->type = $component;
            if (!$DB->record_exists('scorm_pending_context', array('contextid' => $context))) {
                $DB->insert_record('scorm_pending_context', $record);
            }
            return;
        } else {
            return;
        }
    } else {
        return;
    }
}

/**
 * Return path to file with given hash.
 *
 * NOTE: must not be public, files in pool must not be modified
 *
 * @param string $contenthash content hash
 * @return string expected file location
 */
function path_from_hash($contenthash) {
    $l1 = $contenthash[0] . $contenthash[1];
    $l2 = $contenthash[2] . $contenthash[3];
    return "$l1/$l2";
}
