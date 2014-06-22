<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
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
     * @param   string  $class
     * @param   string  $method
     * @param   int     $ine
     * @param   string  $file
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