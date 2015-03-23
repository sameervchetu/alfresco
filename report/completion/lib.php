<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details.
 *
 * @package    report
 * @subpackage completion
 * @copyright  2009 Sam Hemelryk
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * This function extends the navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $course The course to object for the report
 * @param stdClass $context The context of the course
 */
function report_completion_extend_navigation_course($navigation, $course, $context) {
    global $CFG;

    require_once($CFG->libdir.'/completionlib.php');

    if (has_capability('report/completion:view', $context)) {
        $completion = new completion_info($course);
        if ($completion->is_enabled() && $completion->has_criteria()) {
            $url = new moodle_url('/report/completion/index.php', array('course'=>$course->id));
            $navigation->add(get_string('pluginname','report_completion'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
        }
    }
}

/**
 * This function extends the course navigation with the report items
 *
 * @param navigation_node $navigation The navigation node to extend
 * @param stdClass $user
 * @param stdClass $course The course to object for the report
 */
function report_completion_extend_navigation_user($navigation, $user, $course) {
    if (completion_can_view_data($user->id, $course->id)) {
        $url = new moodle_url('/report/completion/user.php', array('id'=>$user->id, 'course'=>$course->id));
        $navigation->add(get_string('coursecompletion'), $url);
    }
}

/**
 * Return a list of page types
 * @param string $pagetype current page type
 * @param stdClass $parentcontext Block's parent context
 * @param stdClass $currentcontext Current context of block
 * @return array
 */
function report_completion_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $array = array(
        '*'                       => get_string('page-x', 'pagetype'),
        'report-*'                => get_string('page-report-x', 'pagetype'),
        'report-completion-*'     => get_string('page-report-completion-x',  'report_completion'),
        'report-completion-index' => get_string('page-report-completion-index',  'report_completion'),
        'report-completion-user'  => get_string('page-report-completion-user',  'report_completion')
    );
    return $array;
}
