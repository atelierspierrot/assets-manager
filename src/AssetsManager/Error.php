<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (ↄ) 2013-2015 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace AssetsManager;

/**
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
class Error
{

    /**
     * @param   string  $str    error message
     * @param   string  $throw  exception class name
     * @param   string  $class  class name which throws the error
     * @param   string  $method method of the class which throws the error
     * @param   int     $line   line where the error were thrown
     * @param   string  $file   file where the error were thrown
     * @return  void
     * @throws  mixed   $throw standard exception class
     */
    public static function thrower($str, $throw = '\Exception', $class = null, $method = null, $line = null, $file = null)
    {
        $suffix = '';
        if (!empty($method)) {
            $suffix .= 'thrown by ' . $method . '()';
        } elseif (!empty($class)) {
            $suffix .= 'thrown by ' . $class;
        }
        if (!empty($method) || !empty($class)) {
            if (!empty($file)) {
                $suffix .= ' in file ' . $file;
            }
            if (!empty($line)) {
                $suffix .= ' at line ' . $line;
            }
        }
        if (strlen($suffix)) {
            $str .= ' [' . $suffix . ']';
        }

        throw new $throw($str);
    }

}

// Endfile