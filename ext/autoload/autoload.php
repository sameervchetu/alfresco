<?php

/**
 * Autoload for plugins
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
ext_autoload::extregister();
// As we always try to run the autoloader before anything else, we can use it to do a few
// simple checks and initialisations

/**
 * Autoloading classes and functions 
 */
class ext_autoload {

    /**
     * Register the Autoloader with SPL
     *
     */
    public static function extregister() {
        if (function_exists('__autoload')) {
            // Register any existing autoloader function with SPL, so we don't get any clashes
            spl_autoload_register('__autoload');
        }
        // Register ourselves with SPL
        return spl_autoload_register(array('ext_autoload', 'extload'));
    }

    /**
     * Autoload a class identified by name
     *
     * @global object $CFG
     * @param string $classname Name of the object to load
     */
    public static function extload($classname) {
        global $CFG;

        if ((class_exists($classname, false)) || (strpos($classname, 'ext_') !== 0)) {
            // Either already loaded, or not a Ext class request
            return false;
        }

        $classexplode = explode('classes_', $classname);
        $classexplode[0] = str_replace('_', '/', $classexplode[0]);
        $classfilepath = $CFG->dirroot . '/' . $classexplode[0] . 'classes/' . $classexplode[1] . '.php';

        if ((file_exists($classfilepath) === false) || (is_readable($classfilepath) === false)) {
            //  Can't load
            return false;
        }

        require($classfilepath);
    }

}
