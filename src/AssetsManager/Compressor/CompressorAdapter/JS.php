<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\Compressor\CompressorAdapter;

use \AssetsManager\Compressor\AbstractCompressorAdapter;

/**
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
class JS
    extends AbstractCompressorAdapter
{

    public $file_extension = 'js';

    public static function merge( $input )
    {
        $input = preg_replace('!/\*.*?\*/!s', '', $input);
        $output = trim($input);
        return $output;
    }

    /**
     * Inspired by <http://code.seebz.net/p/minify-js/>
     */
    public static function minify($input)
    {
        $output = '';
        $input = self::merge($input);

        $inQuotes        = array();
        $noSpacesAround  = '{}()[]<>|&!?:;,+-*/="\'';

        $input = preg_replace("`(\r\n|\r)`", "\n", $input);
        $inputs = str_split($input);
        $inputs_count = count($inputs);
        $prevChr = null;
        for ($i=0; $i<$inputs_count; $i++) {
            $chr = $inputs[$i];
            $nextChr = $i+1 < $inputs_count ? $inputs[$i+1] : null;

            switch ($chr) {
                case '/':
                    if (!count($inQuotes) && $nextChr == '*' && $inputs[$i+2] != '@') {
                        $i = 1 + strpos($input, '*/', $i);
                        continue 2;
                    } elseif (!count($inQuotes) && $nextChr == '/') {
                        if (strpos($input, "\n", $i))
                            $i = strpos($input, "\n", $i);
                        else
                            $i = strlen($input);
                        continue 2;
                    } elseif (!count($inQuotes)) {
                        $eolPos = strpos($input, "\n", $i);
                        if ($eolPos===false) $eolPos = $inputs_count;
                        $eol = substr($input, $i, $eolPos-$i);
                        if (!preg_match('`^(/.+(?<=\\\/)/(?!/)[gim]*)[^gim]`U', $eol, $m)) {
                            preg_match('`^(/.+(?<!/)/(?!/)[gim]*)[^gim]`U', $eol, $m);
                        }
                        // it's a RegExp
                        if (isset($m[1])) {
                            $output .= $m[1];
                            $i += strlen($m[1])-1;
                            continue 2;
                        }
                    }
                    break;

                case "'":
                case '"':
                    if ($prevChr != '\\' || ($prevChr == '\\' && $inputs[$i-2] == '\\')) {
                        if (end($inQuotes) == $chr) {
                            array_pop($inQuotes);
                        } elseif (!count($inQuotes)) {
                            $inQuotes[] = $chr;
                        }
                    }
                    break;

                case ' ':
                case "\t":
                case "\n":
                    if (!count($inQuotes)) {
                        if (   strstr("{$noSpacesAround} \t\n", $nextChr)
                            || strstr("{$noSpacesAround} \t\n", $prevChr)
                        ) {
                            continue 2;
                        }
                        $chr = ' ';
                    }
                    break;

                default:
                    break;
            }

            $output .= $chr;
            $prevChr = $chr;
        }
        $output = trim($output);
        $output = str_replace(';}', '}', $output);

        return $output;
    }

    public static function buildComment($str)
    {
        return sprintf('/* %s */', $str);
    }

}

// Endfile