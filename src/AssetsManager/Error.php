<?php
/**
 * Template Engine - PHP framework package
 * Copyleft (c) 2013 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/templatengine>
 */

namespace AssetsManager;

/**
 * @author 		Piero Wbmstr <piero.wbmstr@gmail.com>
 */
class Error
{

    /**
     * @throws $throw standard exception class
     * @return void
     */
    public static function thrower($str, $throw = '\Exception', $class = null, $method = null, $line = null, $file = null)
    {
        $suffix = '';
        if (!empty($class)) {
            $suffix .= 'thrown by ' . $class;
            if (!empty($method)) {
                $suffix .= '::' . $method;
            }
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