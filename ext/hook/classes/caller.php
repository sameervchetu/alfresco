<?php

/**
 * Hook caller
 *
 * @copyright Copyright 2014 eLearningExperts
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
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/weblib.php');

/**
 * Class Hook caller.
 */
class ext_hook_classes_caller {

    /**
     * Call all the hooks for the provided hookpointname.
     * 
     * This is a hacky way to do this but we needed some way around the previous implementation using debug_backtrace
     * which may be slow and seems to cause the problem of having to use deprecated language usage
     * 
     * @access public
     * @uses DEBUG_DEVELOPER
     * @param string $hookpointname
     * @param mixed &$arg1
     * @param mixed &$arg2
     * @param mixed &$arg3
     * @param mixed &$arg4
     * @param mixed &$arg5
     * @param mixed &$arg6
     * @param mixed &$arg7
     * @param mixed &$arg8
     * @param mixed &$arg9
     * @param mixed &$arg10
     * @return null
     */
    public static function call_all($hookpointname, &$arg1 = null, &$arg2 = null, &$arg3 = null, &$arg4 = null, &$arg5 = null, &$arg6 = null, &$arg7 = null, &$arg8 = null, &$arg9 = null, &$arg10 = null) {
        if (empty($hookpointname)) {
            debugging('You cannot call all hooks without a hookpointname.', DEBUG_DEVELOPER);
            return;
        }

        $hooks = ext_hook_classes_registry::get_all($hookpointname);

        foreach ($hooks as $hook) {
            self::call_hook($hook, $arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9, $arg10);
        }
    }

    /**
     * Call all the hooks for the provided hookpointname and return the highest priority result.
     * 
     * @access public
     * @uses DEBUG_DEVELOPER
     * @param string $hookpointname
     * @param mixed &$arg1
     * @param mixed &$arg2
     * @param mixed &$arg3
     * @param mixed &$arg4
     * @param mixed &$arg5
     * @param mixed &$arg6
     * @param mixed &$arg7
     * @param mixed &$arg8
     * @param mixed &$arg9
     * @param mixed &$arg10
     * @return null|mixed
     */
    public static function call_all_get_priority_result($hookpointname, &$arg1 = null, &$arg2 = null, &$arg3 = null, &$arg4 = null, &$arg5 = null, &$arg6 = null, &$arg7 = null, &$arg8 = null, &$arg9 = null, &$arg10 = null) {
        if (empty($hookpointname)) {
            debugging('You cannot call all hooks without a hookpointname.', DEBUG_DEVELOPER);
            return null;
        }

        $hooks = ext_hook_classes_registry::get_all($hookpointname);
        $results = array();

        foreach ($hooks as $hook) {
            $result = self::call_hook($hook, $arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9, $arg10);
            if (isset($result) && is_array($result)) {
                $results += $result;
            }
        }

        if (empty($results)) {
            return null;
        }

        ksort($results);

        // The last result (that with the highest priority) will be returned.
        return array_pop($results);
    }

    /**
     * Runs a single hook passing the supplied arg into it.
     * 
     * This is a hacky way to do this but we needed some way around the previous implementation using debug_backtrace
     * which may be slow and seems to cause the problem of having to use deprecated language usage
     * 
     * @access public
     * @uses DEBUG_DEVELOPER
     * @param mixed &$arg1
     * @param mixed &$arg2
     * @param mixed &$arg3
     * @param mixed &$arg4
     * @param mixed &$arg5
     * @param mixed &$arg6
     * @param mixed &$arg7
     * @param mixed &$arg8
     * @param mixed &$arg9
     * @param mixed &$arg10
     * @return mixed
     */
    protected static function call_hook($hook, &$arg1 = null, &$arg2 = null, &$arg3 = null, &$arg4 = null, &$arg5 = null, &$arg6 = null, &$arg7 = null, &$arg8 = null, &$arg9 = null, &$arg10 = null) {
        if (empty($hook) || empty($hook->function) || empty($hook->file)) {
            debugging('A hook call was made without actually passing a proper hook to call.', DEBUG_DEVELOPER);
            return;
        }

        $hookfunction = $hook->function;

        if (!is_callable($hookfunction, false, $hookfunction)) {
            debugging('A hook call was made without the hook function existing.', DEBUG_DEVELOPER);
            return;
        }

        if (stristr($hookfunction, '::') !== false) {
            list($class, $function) = explode('::', $hookfunction);
            return $class::$function($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9, $arg10);
        }

        return $hookfunction($arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9, $arg10);
    }

    /**
     * Call all the hooks for the provided hookpointname.
     * 
     * This is a hacky way to do this but we needed some way around the previous implementation using debug_backtrace
     * which may be slow and seems to cause the problem of having to use deprecated language usage
     * 
     * @access public
     * @uses DEBUG_DEVELOPER
     * @param string $hookpointname
     * @param mixed &$arg1
     * @param mixed &$arg2
     * @param mixed &$arg3
     * @param mixed &$arg4
     * @param mixed &$arg5
     * @param mixed &$arg6
     * @param mixed &$arg7
     * @param mixed &$arg8
     * @param mixed &$arg9
     * @param mixed &$arg10
     * @return null
     */
    public static function call_single($hookpointname, &$arg1 = null, &$arg2 = null, &$arg3 = null, &$arg4 = null, &$arg5 = null, &$arg6 = null, &$arg7 = null, &$arg8 = null, &$arg9 = null, &$arg10 = null) {
        if (empty($hookpointname)) {
            debugging('You cannot call all hooks without a hookpointname.', DEBUG_DEVELOPER);
            return;
        }

        $hook = ext_hook_classes_registry::get_all($hookpointname);

        if (count($hook) > 1) {
            print_error(get_string('bad_hook_implemented', 'ext_hook'));
        }

        return self::call_hook($hook[0], $arg1, $arg2, $arg3, $arg4, $arg5, $arg6, $arg7, $arg8, $arg9, $arg10);
    }

}
