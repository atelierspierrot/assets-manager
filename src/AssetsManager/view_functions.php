<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

/**
 * This file defines some default functions to facilitate views writing
 *
 * All of these functions are prefixed by an underscore `_`.
 */
use \Library\Helper;

if (!function_exists('_attribute'))
{
    /**
     * Build an HTML attribute string
     *
     * @param string $var The name of the attribute
     * @param string $val The value of the attribute
     * @return string A string representing the attribute/value couple ready to write as HTML attribute
     */
    function _attribute($var, $val)
    {
        return Helper\Html::parseAttributes(array($var => $val));
    }
}

if (!function_exists('_iecc')) 
{
    /**
     * Internet Explorer Conditional Comment
     *
     * @param string $content
     * @param string|array $condition(s)
     * @param string $operator Can be 'OR' (default) or 'AND'
     * @param bool $global May the content be also defined globally
     * @return string
     * @see Library\Helper\ConditionalComment::buildCondition()
     */
    function _iecc($content, $condition = 'if IE', $operator = 'OR', $global = false)
    {
        return Helper\ConditionalComment::buildCondition(
            $content, $condition, $operator, $global
        );
    }
}

if (!function_exists('_javascript')) 
{
    /**
     * Protect a string for javascript usage
     *
     * @param string $str The HTML string to protect
     * @param bool $protect_quotes Protect all quotes (simple and double) with a slash
     * @return string
     * @see Library\Helper\Html::javascriptProtect()
     */
    function _javascript($str, $protect_quotes = false)
    {
        return Helper\Html::javascriptProtect($str, $protect_quotes);
    }
}

if (!function_exists('_tag')) 
{
    /**
     * Build an HTML tag block
     *
     * @param string $type The name of the tag to build
     * @param string $content The content of the tag
     * @param array $attrs An array of `name => value` pairs for the tag HTML attributes
     * @return string
     */
    function _tag($type, $content, array $attrs = array())
    {
        $attr_str = '';
        if (!empty($attrs)) {
            foreach($attrs as $var=>$val) {
                $attr_str .= ' '._attribute($var, $val);
            }
        }
        return '<'.$type.$attr_str.'>'.$content.'</'.$type.'>';
    }
}

if (!function_exists('_use')) 
{
    /**
     * Assets packages automatic inclusion
     *
     * @param string $preset_name The assets preset name to include
     * @return void
     */
    function _use($preset_name = null)
    {
        if (empty($preset_name)) return;
        \AssetsManager\Loader::getInstance()->useAssetsPreset($preset_name);
    }
}

// Endfile