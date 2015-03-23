<?php

/**
 * CDN plugin settings API file
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

global $PAGE;

defined('MOODLE_INTERNAL') || die();

$temp = new admin_settingpage('cdnsettings', get_string('cdnsettings', 'ext_cdn'));
$temp->add(new admin_setting_configtext('cdn/enablecriteriamimetype', get_string('cdnenablefilesbymimetype', 'ext_cdn'),
        get_string('cdnenablefilesbymimetype_desc', 'ext_cdn'), ''));
$temp->add(new admin_setting_configtext('cdn/enablecriteriasize', get_string('cdnenablefilesbysize', 'ext_cdn'),
        get_string('cdnenablefilesbysize_desc', 'ext_cdn'), ''));
$temp->add(new admin_setting_configtext('cdn/enablecriteriaextension', get_string('cdnenablefilesbyextension', 'ext_cdn'),
        get_string('cdnenablefilesbyextension_desc', 'ext_cdn'), ''));
$temp->add(new admin_setting_configcheckbox('cdn/enablecriteriaonlyscorm', get_string('cdnenablefilesbyonlyscorm', 'ext_cdn'),
        get_string('cdnenablefilesbyonlyscorm_desc', 'ext_cdn'), ''));
$temp->add(new admin_setting_configcheckbox('cdn/enablecriteriaallfiles', get_string('cdnenablefilesbyallfiles', 'ext_cdn'),
        get_string('cdnenablefilesbyallfiles_desc', 'ext_cdn'), ''));

$ADMIN->add('root', $temp);

$PAGE->requires->js_init_call('M.ext_cdn.init');