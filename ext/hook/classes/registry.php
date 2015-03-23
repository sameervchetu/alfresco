<?php

/**
 * Hook registry
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

require_once($CFG->libdir . '/moodlelib.php');

/**
 * Hook listener registry.
 */
class ext_hook_classes_registry {

    const CACHE_KEY_NAME = 'core:hooks';

    /**
     * Get all reported hooks for given hookpoint filtered for only callable ones.
     * 
     * @param string $hookpointname
     * @return array
     */
    public static function get_all($hookpointname) {
        static $hooksbyname = null;

        $cache = cache::make('ext_hook', 'hooks');
        if (is_null($hooksbyname)) {
            $hooksbyname = $cache->get(self::CACHE_KEY_NAME);
        }

        // if we didn't get them from redis then just generate them
        if (is_null($hooksbyname) || empty($hooksbyname)) {
            $hooksbyname = array();

            // These are the only types that are whitelisted to have extensions.
            $extensionstypes = array('ext', 'auth', 'mod');

            foreach ($extensionstypes as $type) {
                foreach (get_plugin_list($type) as $plugin => $pluginpath) {
                    $hookfile = "{$pluginpath}/db/hooks.php";

                    if (!file_exists($hookfile)) {
                        continue;
                    }

                    // $hooks is defined in the include_once.
                    $hooks = null;
                    global $hooks;

                    include_once($hookfile);

                    if (!isset($hooks)) {
                        debugging('A hook file was found but the necessary variable was not defined for extension ' . $plugin);
                        continue;
                    } else if (!is_array($hooks)) {
                        debugging('A hook file was found but the necessary variable was not an array ' . $plugin);
                        continue;
                    }

                    self::convert_hooks_to_objects($hooks);

                    if (!empty($hooks)) {
                        $hooksbyname = array_merge_recursive($hooksbyname, $hooks);
                    }

                    unset($hooks);
                }
            }
        }

        if (!empty($hooksbyname) && is_array($hooksbyname)) {
            $cache->set(self::CACHE_KEY_NAME, $hooksbyname);
        }

        if (empty($hooksbyname[$hookpointname])) {
            $hooksbyname[$hookpointname] = array();          // we always want the return to be an array
        }

        // Clean the hookpoint after storage to ensure predictable results 
        // across several web servers.
        static $hookpointcleaned = false;
        if ($hookpointcleaned === false || !isset($hookpointcleaned[$hookpointname])) {
            self::clean_hookpoint($hookpointname, $hooksbyname);
            $hookpointcleaned[$hookpointname] = true;
        }

        $hooks = !empty($hooksbyname[$hookpointname]) ? $hooksbyname[$hookpointname] : array();

        return $hooks;
    }

    /*
     * Should only be called by hooks_get_all to remove hooks that are not callable.
     * 
     * Calling this any earlier (say in the cacheing) would cause a lot of unecessary files to be included.
     * 
     * @uses DEBUG_DEVELOPER
     * @param array $hooks
     */

    protected static function clean_hookpoint($hookpointname, &$hooks) {
        global $CFG;

        if (empty($hooks) || empty($hooks[$hookpointname])) {
            return;
        }

        foreach ($hooks[$hookpointname] as $hooktest => &$hook) {
            $hookfile = $CFG->dirroot . '/' . $hook->file;
            $hookfunction = $hook->function;

            if (!file_exists($hookfile)) {
                unset($hook);
                debugging('Hook file ' . $hookfile . ' could not be found.', DEBUG_DEVELOPER);
            }

            require_once($hookfile);

            if (!is_callable($hookfunction)) {
                unset($hook);
                debugging('Hook function ' . $hookfunction . ' was not found in specified file ' . $hookfile, DEBUG_DEVELOPER);
            }
        }
    }

    /*
     * converts a hook into objects useful for walking across the hooks to store in more usefull format.
     * 
     * @param array $hookfunctions
     */

    protected static function convert_hooks_to_objects(&$hookarray) {
        if (empty($hookarray)) {
            return array();
        }

        foreach ($hookarray as &$hookfunctions) {
            $reformatedhookfunctions = array();

            foreach ($hookfunctions as $hookfunction => $hooklocation) {
                $hook = new stdClass();
                $hook->function = $hookfunction;
                $hook->file = $hooklocation;
                $reformatedhookfunctions[] = $hook;
            }

            $hookfunctions = $reformatedhookfunctions;
        }
    }

}
