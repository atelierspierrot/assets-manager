<?php
/**
 * AssetsManager - Composer plugin
 * Copyleft (c) 2013-2014 Pierre Cassat and contributors
 * <www.ateliers-pierrot.fr> - <contact@ateliers-pierrot.fr>
 * License GPL-3.0 <http://www.opensource.org/licenses/gpl-3.0.html>
 * Sources <https://github.com/atelierspierrot/assets-manager>
 */

namespace AssetsManager\AssetObject;

use \Patterns\Commons\Registry;
use \AssetsManager\Loader;

/**
 * @author  Piero Wbmstr <me@e-piwi.fr>
 */
abstract class AbstractAssetObject
{

    /**
     * @var \Patterns\Commons\Registry
     */
    protected $__registry;

    /**
     * @var \AssetsManager\Loader
     */
    protected $__assets_loader;

    /**
     * Constructor
     *
     * @param \AssetsManager\Loader $_loader
     */
    public function __construct( Loader $_loader )
    {
        $this->__registry       = new Registry;
        $this->__assets_loader  = $_loader;
        $this->init();
    }

    /**
     * Write the Asset Object strings ready for view display
     */
    public function __toString()
    {
        return $this->write();
    }

    /**
     * Clean a stack (an array) leaving just one set of an entry for the $clean_by variable
     *
     * @param array $stack The stack to clean
     * @param string $clean_by The variable name to check
     * @return array Return the stack cleaned with only one instance of $clean_by
     */
    protected function _cleanStack(array $stack, $clean_by = null)
    {
        $new_stack = array();
        foreach($stack as $_entry) {
            if (is_array($_entry) && !empty($clean_by)) {
                if (isset($_entry[$clean_by]) && !array_key_exists($_entry[$clean_by], $new_stack))
                    $new_stack[ $_entry[$clean_by] ] = $_entry;
            } elseif (is_string($_entry)) {
                $ok = array_search($_entry, $new_stack);
                if (false===$ok)
                    $new_stack[] = $_entry;
            }
        }
        return array_values($new_stack);
    }

    /**
     * Build a stack (an array) leaving just one value of an entry searching a $clean_by index
     *
     * @param array $stack The stack to clean
     * @param string $clean_by The variable name to check
     * @return array Return the extracted stack
     */
    protected function _extractFromStack(array $stack, $clean_by)
    {
        $new_stack = array();
        foreach($stack as $_entry) {
            if (is_array($_entry) && isset($_entry[$clean_by])) {
                $new_stack[] = $_entry[$clean_by];
            }
        }
        return $new_stack;
    }

    /**
     * Write the Asset Object strings ready for view display
     *
     * @param string $mask A mask to write each line via "sprintf()"
     * @return string The string to display fot this template object
     */
    abstract public function write($mask = '%s');

    /**
     * Init the object
     */
    abstract public function init();

}

// Endfile